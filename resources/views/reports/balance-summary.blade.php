@if(!$report->isAgeing() && $report->balanceSummary()->isNotEmpty())
<h2 class="text-lg font-semibold">Outstanding Balance Summary</h2>
<table class="w-full border-collapse text-sm"><thead><tr><th>Company Code</th><th>Currency</th><th>Balance Brought Forward</th><th>Current-Year Outstanding</th><th>Total Outstanding</th></tr></thead><tbody>
@foreach($report->balanceSummary() as $balance)<tr><td>{{ $balance->company }}</td><td>{{ $balance->currency ?: 'Unspecified' }}</td><td>{{ number_format($balance->opening, 2) }}</td><td>{{ number_format($balance->year_pending, 2) }}</td><td>{{ number_format($balance->total_pending, 2) }}</td></tr>@endforeach
</tbody></table><p class="text-xs text-slate-500">Current-Year Outstanding Covers 01 Jan {{ \Carbon\CarbonImmutable::parse($report->filters['from'])->year }} To {{ \App\Support\DisplayDate::format(min($report->filters['to'], \Carbon\CarbonImmutable::parse($report->filters['from'])->year.'-12-31')) }}. These Balances Ignore The Document Search.</p>
@endif
