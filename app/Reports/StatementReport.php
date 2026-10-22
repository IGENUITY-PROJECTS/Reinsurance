<?php

namespace App\Reports;

use App\Models\AgedCedantClaimPayable;
use App\Models\AgedCedantPremium;
use App\Models\CedantClaimPayable;
use App\Models\CedantPremium;
use App\Models\Company;
use App\Models\ReinsurerPremiumPayable;
use App\Support\DisplayDate;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StatementReport
{
    public readonly bool $broker;

    public readonly string $kind;

    public readonly array $filters;

    private readonly Builder $base;

    public function __construct(Request $request, bool $export = false)
    {
        $this->broker = $request->routeIs('admin.*');
        abort_unless($request->user() && ($this->broker
            ? ($request->user()->hasAnyRole(['admin', 'super-admin']) || $request->user()->can('view reports'))
            : $request->user()->hasRole('client')), 403);
        $this->filters = $request->validate([
            'kind' => 'nullable|in:premiums,claims',
            'section' => 'nullable|in:statement,ageing',
            'as_at' => 'nullable|date_format:Y-m-d',
            'q' => 'nullable|string|max:150',
            'company' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:50',
            'from' => ($export && $request->input('section') !== 'ageing' ? 'required' : 'nullable').'|date_format:Y-m-d',
            'to' => ($export && $request->input('section') !== 'ageing' ? 'required' : 'nullable').'|date_format:Y-m-d|after_or_equal:from',
        ]);
        $this->kind = $this->filters['kind'] ?? 'premiums';
        $this->base = match ($this->kind) {
            'premiums' => CedantPremium::query(),
            'claims' => CedantClaimPayable::query(),
            'reinsurer-premiums' => ReinsurerPremiumPayable::query(),
            'aged-premiums' => AgedCedantPremium::query(),
            'aged-claims' => AgedCedantClaimPayable::query(),
        };
        if (! $this->broker) {
            $code = $request->user()->company?->CmpCode;
            $this->base->where('CedantCode', $code)->when(! $code, fn ($q) => $q->whereRaw('1 = 0'));
        }
    }

    public function query(): Builder
    {
        $query = clone $this->base;
        // A missing balance is unknown, not proof that a document was fully settled.
        if ($this->isAgeing()) {
            $query->where(fn ($q) => $q->where('PendingAmount', '<>', 0)->orWhereNull('PendingAmount'));
        }
        if ($this->broker && filled($this->filters['company'] ?? null)) {
            $query->where('CedantCode', $this->filters['company']);
        }
        if (filled($this->filters['currency'] ?? null)) {
            $query->where('CurrencyCode', $this->filters['currency']);
        }
        if (! $this->isAgeing() && filled($this->filters['from'] ?? null)) {
            $query->where('DocumentDate', '>=', $this->filters['from'].' 00:00:00');
        }
        if ($this->isAgeing()) {
            $query->whereNotNull('DocumentDate')->where('DocumentDate', '<', $this->asAt()->addDay()->format('Y-m-d').' 00:00:00')->whereNotNull('PendingAmount');
        } elseif (filled($this->filters['to'] ?? null)) {
            $query->where('DocumentDate', '<', CarbonImmutable::parse($this->filters['to'])->addDay()->format('Y-m-d').' 00:00:00');
        }
        if (filled($this->filters['q'] ?? null)) {
            $term = '%'.$this->filters['q'].'%';
            $query->where(function ($q) use ($term) {
                $q->where('DocumentNo', 'like', $term)->orWhere('RiskNoteRef', 'like', $term)->orWhere('Insured', 'like', $term);
                if ($this->isClaims()) {
                    $q->orWhere('ClaimReference', 'like', $term)->orWhere('ClaimRefReinsured', 'like', $term);
                }
                if ($this->kind === 'reinsurer-premiums') {
                    $q->orWhere('ReinsureCode', 'like', $term)->orWhere('ReinsurerName', 'like', $term)->orWhere('CedantDoc', 'like', $term);
                }
            });
        }

        if (! $this->isAgeing()) {
            $query->select($this->base->getModel()->getTable().'.*')->selectRaw($this->outstandingFunctionalSql().' as outstanding_functional');
        }

        return $query;
    }

    public function orderedQuery(): Builder
    {
        if ($this->isAgeing()) {
            return $this->ageingQuery();
        }

        return $this->query()->orderBy('CedantCode')->orderBy('CurrencyCode')->orderBy('DocumentDate')->orderBy('DocumentNo')->orderBy('id');
    }

    public function totals(): Collection
    {
        // Aggregate at source precision; round only when displaying the result.
        return $this->query()->select('CurrencyCode')
            ->selectRaw('COUNT(*) as document_count, SUM(CAST(MAmount AS DECIMAL(24, 8))) as amount, SUM(CAST(SettledAmount AS DECIMAL(24, 8))) as settled, SUM(CAST(PendingAmount AS DECIMAL(24, 8))) as pending, SUM('.$this->outstandingFunctionalSql().') as outstanding_functional')
            ->groupBy('CurrencyCode')->orderBy('CurrencyCode')->get();
    }

    public function broughtForwardYear(): ?int
    {
        return filled($this->filters['from'] ?? null) ? CarbonImmutable::parse($this->filters['from'])->year - 1 : null;
    }

    public function broughtForward(): Collection
    {
        if ($this->isAgeing()) {
            return collect();
        }
        $year = $this->broughtForwardYear();
        if ($year === null) {
            return collect();
        }
        $query = clone $this->base;
        $query->where('DocumentDate', '>=', $year.'-01-01 00:00:00')
            ->where('DocumentDate', '<', ($year + 1).'-01-01 00:00:00')
            ->where('PendingAmount', '<>', 0);
        if ($this->broker && filled($this->filters['company'] ?? null)) {
            $query->where('CedantCode', $this->filters['company']);
        }
        if (filled($this->filters['currency'] ?? null)) {
            $query->where('CurrencyCode', $this->filters['currency']);
        }

        // Prior-year balances are independent of the current document search and month.
        return $query->select('CedantCode', 'CurrencyCode')->selectRaw('SUM(CAST(PendingAmount AS DECIMAL(24, 8))) as pending')
            ->groupBy('CedantCode', 'CurrencyCode')->orderBy('CedantCode')->orderBy('CurrencyCode')->get();
    }

    public function dataChecks(): object
    {
        $query = clone $this->base;
        if ($this->broker && filled($this->filters['company'] ?? null)) {
            $query->where('CedantCode', $this->filters['company']);
        }
        if (filled($this->filters['currency'] ?? null)) {
            $query->where('CurrencyCode', $this->filters['currency']);
        }

        return $query->selectRaw('COUNT(*) as source_rows, SUM(CASE WHEN DocumentDate IS NULL THEN 1 ELSE 0 END) as missing_dates, SUM(CASE WHEN PendingAmount IS NULL THEN 1 ELSE 0 END) as missing_balances, SUM(CASE WHEN PendingAmount = 0 THEN 1 ELSE 0 END) as settled_rows')->first();
    }

    public function includeSettled(): bool
    {
        return ! $this->isAgeing();
    }

    public function outstandingFunctionalSql(): string
    {
        return 'CAST(CAST(PendingAmount AS DECIMAL(24,8)) * CAST(ExchangeRate AS DECIMAL(24,8)) AS DECIMAL(38,8))';
    }

    public function exchangeRateOffset(): int
    {
        return array_search('Exchange Rate', $this->headings(), true);
    }

    public function balanceSummary(): Collection
    {
        if ($this->isAgeing() || empty($this->filters['from']) || empty($this->filters['to'])) {
            return collect();
        }
        $year = CarbonImmutable::parse($this->filters['from'])->year;
        $end = min($this->filters['to'], $year.'-12-31');
        $query = clone $this->base;
        $query->where('DocumentDate', '>=', ($year - 1).'-01-01 00:00:00')->where('DocumentDate', '<', CarbonImmutable::parse($end)->addDay()->format('Y-m-d').' 00:00:00')->where('PendingAmount', '<>', 0);
        if ($this->broker && filled($this->filters['company'] ?? null)) {
            $query->where('CedantCode', $this->filters['company']);
        }
        if (filled($this->filters['currency'] ?? null)) {
            $query->where('CurrencyCode', $this->filters['currency']);
        }

        return $query->selectRaw('CedantCode as company, CurrencyCode as currency')
            ->selectRaw('SUM(CASE WHEN DocumentDate < ? THEN CAST(PendingAmount AS DECIMAL(24,8)) ELSE 0 END) as opening', [$year.'-01-01 00:00:00'])
            ->selectRaw('SUM(CASE WHEN DocumentDate >= ? THEN CAST(PendingAmount AS DECIMAL(24,8)) ELSE 0 END) as year_pending', [$year.'-01-01 00:00:00'])
            ->selectRaw('SUM(CAST(PendingAmount AS DECIMAL(24,8))) as total_pending')
            ->groupBy('CedantCode', 'CurrencyCode')->orderBy('CedantCode')->orderBy('CurrencyCode')->get();
    }

    public function brandTitle(): string
    {
        $name = config('branding.report_name', config('branding.company_name'));

        return str_contains(strtolower($name), 'reinsurance') ? $name : $name.' Reinsurance';
    }

    public function currencies(): Collection
    {
        return (clone $this->base)->whereNotNull('CurrencyCode')->distinct()->orderBy('CurrencyCode')->pluck('CurrencyCode');
    }

    public function companies(): Collection
    {
        $source = (clone $this->base)->select('CedantCode')->selectRaw('MAX(CedantName) as company_name')->whereNotNull('CedantCode')->groupBy('CedantCode')->get();

        return Company::query()->selectRaw('CmpCode as CedantCode, CompanyName as company_name')->get()
            ->concat($source)->unique('CedantCode')->sortBy('CedantCode')->values();
    }

    public static function kinds(): array
    {
        return ['premiums' => 'Cedant Premiums Payable', 'claims' => 'Cedant Claims Receivable',
            // 'reinsurer-premiums' => 'Reinsurer premiums payable',
            // 'aged-premiums' => 'Aged cedant premiums',
            // 'aged-claims' => 'Aged cedant claims receivable'
        ];
    }

    public function isClaims(): bool
    {
        return in_array($this->kind, ['claims', 'aged-claims'], true);
    }

    public function amountOffset(): int
    {
        return $this->isAgeing() ? 3 : count($this->headings()) - 6;
    }

    public function supportsFeedbackContext(): bool
    {
        return ! $this->isAgeing() && in_array($this->kind, ['premiums', 'claims'], true);
    }

    public function isAgeing(): bool
    {
        return ($this->filters['section'] ?? 'statement') === 'ageing';
    }

    public function asAt(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->filters['as_at'] ?? $this->filters['to'] ?? now()->format('Y-m-d'))->startOfDay();
    }

    public function ageingQuery(): Builder
    {
        $query = $this->query()->select('CedantCode', 'CurrencyCode')->selectRaw('MAX(CedantName) as CedantName');
        foreach ([[0, 30], [31, 60], [61, 90], [91, 180], [181, 365], [366, null]] as $i => [$min,$max]) {
            $upper = $this->asAt()->subDays($min)->addDay()->format('Y-m-d').' 00:00:00';
            $condition = 'DocumentDate < ?';
            $bindings = [$upper];
            if ($max !== null) {
                $condition .= ' AND DocumentDate >= ?';
                $bindings[] = $this->asAt()->subDays($max)->format('Y-m-d').' 00:00:00';
            }
            $query->selectRaw('SUM(CASE WHEN '.$condition.' THEN CAST(PendingAmount AS DECIMAL(24,8)) ELSE 0 END) as bucket_'.$i, $bindings);
        }

        return $query->selectRaw('SUM(CAST(PendingAmount AS DECIMAL(24,8))) as balance')->groupBy('CedantCode', 'CurrencyCode')->orderBy('CedantCode')->orderBy('CurrencyCode');
    }

    public function sectionLabel(): string
    {
        return ($this->filters['section'] ?? 'statement') === 'ageing' ? 'Ageing' : 'Statement';
    }

    public function title(): string
    {
        return match ($this->kind) {
            'premiums' => 'Cedant Premium Statement',
            'claims' => 'Cedant Claims Receivable Statement',
            'reinsurer-premiums' => 'Reinsurer Premiums Payable Statement',
            'aged-premiums' => 'Aged Cedant Premium Statement',
            'aged-claims' => 'Aged Cedant Claims Receivable Statement',
        };
    }

    public function scopeLabel(): string
    {
        if ($this->broker && empty($this->filters['company'])) {
            return 'All cedants';
        }
        $company = $this->query()->select('CedantCode', 'CedantName')->first();

        return $company ? (($company->CedantName ?: $company->CedantCode).' ('.$company->CedantCode.')') : 'Your company';
    }

    public function period(): string
    {
        if ($this->isAgeing()) {
            return 'As at '.$this->asAt()->format('d M Y');
        }

        return filled($this->filters['from'] ?? null) ? DisplayDate::format($this->filters['from']).' to '.DisplayDate::format($this->filters['to']) : 'All dates';
    }

    public function headings(): array
    {
        if ($this->isAgeing()) {
            return ['Cedant Code', 'Cedant', 'Currency', '0–30', '31–60', '61–90', '91–180', '181–365', '365+', 'Balance'];
        }
        $headings = ['Document', 'Date', 'Cedant Code', 'Cedant', 'Insured', 'Risk Reference'];
        if ($this->isClaims()) {
            array_push($headings, 'Claim Reference', 'Cedant Claim Reference', 'Date Of Loss');
        }

        if ($this->kind === 'reinsurer-premiums') {
            array_push($headings, 'Reinsurer code', 'Reinsurer', 'Cedant document');
        }

        return array_merge($headings, ['Currency', 'Amount', 'Settled', 'Pending Amount', 'Exchange Rate', 'Functional Amount', 'Outstanding Functional Amount']);
    }

    public function row($record): array
    {
        if ($this->isAgeing()) {
            return [$record->CedantCode, $record->CedantName, $record->CurrencyCode, $record->bucket_0, $record->bucket_1, $record->bucket_2, $record->bucket_3, $record->bucket_4, $record->bucket_5, $record->balance];
        }
        $row = [$record->DocumentNo, $record->DocumentDate?->format('d M Y'), $record->CedantCode, $record->CedantName,
            $record->Insured, $record->RiskNoteRef];
        if ($this->isClaims()) {
            array_push($row, $record->ClaimReference, $record->ClaimRefReinsured, $record->DOL?->format('d M Y'));
        }

        if ($this->kind === 'reinsurer-premiums') {
            array_push($row, $record->ReinsureCode, $record->ReinsurerName, $record->CedantDoc);
        }

        return array_merge($row, [$record->CurrencyCode, $record->MAmount, $record->SettledAmount, $record->PendingAmount, $record->ExchangeRate, $record->FunctionalAmount, $record->outstanding_functional]);
    }
}
