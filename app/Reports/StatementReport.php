<?php

namespace App\Reports;

use App\Models\AgedCedantClaimPayable;
use App\Models\AgedCedantPremium;
use App\Models\CedantClaimPayable;
use App\Models\CedantPremium;
use App\Models\ReinsurerPremiumPayable;
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
            'kind' => 'nullable|in:premiums,claims,reinsurer-premiums,aged-premiums,aged-claims',
            'q' => 'nullable|string|max:150',
            'company' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:50',
            'from' => ($export ? 'required' : 'nullable|required_with:to').'|date_format:Y-m-d',
            'to' => ($export ? 'required' : 'nullable|required_with:from').'|date_format:Y-m-d|after_or_equal:from',
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
        $query->where(fn ($q) => $q->where('PendingAmount', '<>', 0)->orWhereNull('PendingAmount'));
        if ($this->broker && filled($this->filters['company'] ?? null)) {
            $query->where('CedantCode', $this->filters['company']);
        }
        if (filled($this->filters['currency'] ?? null)) {
            $query->where('CurrencyCode', $this->filters['currency']);
        }
        if (filled($this->filters['from'] ?? null)) {
            $query->where('DocumentDate', '>=', $this->filters['from'].' 00:00:00');
        }
        if (filled($this->filters['to'] ?? null)) {
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

        return $query;
    }

    public function orderedQuery(): Builder
    {
        return $this->query()->orderBy('CedantCode')->orderBy('CurrencyCode')->orderBy('DocumentDate')->orderBy('DocumentNo')->orderBy('id');
    }

    public function totals(): Collection
    {
        // Aggregate at source precision; round only when displaying the result.
        return $this->query()->select('CurrencyCode')
            ->selectRaw('COUNT(*) as document_count, SUM(CAST(MAmount AS DECIMAL(24, 8))) as amount, SUM(CAST(SettledAmount AS DECIMAL(24, 8))) as settled, SUM(CAST(PendingAmount AS DECIMAL(24, 8))) as pending')
            ->groupBy('CurrencyCode')->orderBy('CurrencyCode')->get();
    }

    public function currencies(): Collection
    {
        return (clone $this->base)->whereNotNull('CurrencyCode')->distinct()->orderBy('CurrencyCode')->pluck('CurrencyCode');
    }

    public function companies(): Collection
    {
        return (clone $this->base)->select('CedantCode')->selectRaw('MAX(CedantName) as company_name')
            ->whereNotNull('CedantCode')->groupBy('CedantCode')->orderBy('CedantCode')->get();
    }

    public static function kinds(): array
    {
        return ['premiums' => 'Cedant premiums payable', 'claims' => 'Cedant claims receivable',
            'reinsurer-premiums' => 'Reinsurer premiums payable', 'aged-premiums' => 'Aged cedant premiums',
            'aged-claims' => 'Aged cedant claims receivable'];
    }

    public function isClaims(): bool
    {
        return in_array($this->kind, ['claims', 'aged-claims'], true);
    }

    public function amountOffset(): int
    {
        return count($this->headings()) - 3;
    }

    public function supportsFeedbackContext(): bool
    {
        return in_array($this->kind, ['premiums', 'claims'], true);
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
        return filled($this->filters['from'] ?? null) ? $this->filters['from'].' to '.$this->filters['to'] : 'All dates';
    }

    public function headings(): array
    {
        $headings = ['Document', 'Date', 'Cedant code', 'Cedant', 'Insured', 'Risk reference'];
        if ($this->isClaims()) {
            array_push($headings, 'Claim reference', 'Cedant claim reference', 'Date of loss');
        }

        if ($this->kind === 'reinsurer-premiums') {
            array_push($headings, 'Reinsurer code', 'Reinsurer', 'Cedant document');
        }

        return array_merge($headings, ['Currency', 'Amount', 'Settled', 'Outstanding']);
    }

    public function row($record): array
    {
        $row = [$record->DocumentNo, $record->DocumentDate?->format('Y-m-d'), $record->CedantCode, $record->CedantName,
            $record->Insured, $record->RiskNoteRef];
        if ($this->isClaims()) {
            array_push($row, $record->ClaimReference, $record->ClaimRefReinsured, $record->DOL?->format('Y-m-d'));
        }

        if ($this->kind === 'reinsurer-premiums') {
            array_push($row, $record->ReinsureCode, $record->ReinsurerName, $record->CedantDoc);
        }

        return array_merge($row, [$record->CurrencyCode, $record->MAmount, $record->SettledAmount, $record->PendingAmount]);
    }
}
