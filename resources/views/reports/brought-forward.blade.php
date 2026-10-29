@if($report->broughtForwardYear())
<div class="space-y-3"><h2 class="text-lg font-semibold">Balance Brought Forward - {{ $report->broughtForwardYear() }}</h2>
<x-record-table :headers="['Company Code', 'Currency', 'Pending Amount']" :rows="$report->broughtForward()->map(fn ($balance) => [$balance->CedantCode, $balance->CurrencyCode ?: 'Unspecified', number_format($balance->pending, 2)])" />
<p class="text-xs text-slate-500">Prior-year documents’ current outstanding balances.</p></div>
@endif
