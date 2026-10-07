<?php

namespace App\Reports;

use App\Models\CedantClaimPayable;
use App\Models\CedantPremium;
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
            'q' => 'nullable|string|max:150',
            'company' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:50',
            'from' => ($export ? 'required' : 'nullable|required_with:to').'|date_format:Y-m-d',
            'to' => ($export ? 'required' : 'nullable|required_with:from').'|date_format:Y-m-d|after_or_equal:from',
        ]);
        $this->kind = $this->filters['kind'] ?? 'premiums';
        $this->base = $this->kind === 'premiums' ? CedantPremium::query() : CedantClaimPayable::query();
        if (! $this->broker) {
            $code = $request->user()->company?->CmpCode;
            $this->base->where('CedantCode', $code)->when(! $code, fn ($q) => $q->whereRaw('1 = 0'));
        }
    }

    public function query(): Builder
    {
        $query = clone $this->base;
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
                if ($this->kind === 'claims') {
                    $q->orWhere('ClaimReference', 'like', $term)->orWhere('ClaimRefReinsured', 'like', $term);
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

    public function title(): string
    {
        return $this->kind === 'premiums' ? 'Cedant Premium Statement' : 'Cedant Claims Receivable Statement';
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
        if ($this->kind === 'claims') {
            array_push($headings, 'Claim reference', 'Cedant claim reference', 'Date of loss');
        }

        return array_merge($headings, ['Currency', 'Amount', 'Settled', 'Outstanding', 'Reversed', 'Reversing document']);
    }

    public function row($record): array
    {
        $row = [$record->DocumentNo, $record->DocumentDate?->format('Y-m-d'), $record->CedantCode, $record->CedantName,
            $record->Insured, $record->RiskNoteRef];
        if ($this->kind === 'claims') {
            array_push($row, $record->ClaimReference, $record->ClaimRefReinsured, $record->DOL?->format('Y-m-d'));
        }

        return array_merge($row, [$record->CurrencyCode, $record->MAmount, $record->SettledAmount, $record->PendingAmount,
            $record->Reversed === null ? 'Not available' : ($record->Reversed ? 'Yes' : 'No'), $record->ReversingDoc]);
    }
}
