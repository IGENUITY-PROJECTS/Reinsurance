@if(isset($officialAdjustments) && $officialAdjustments !== null)
<section class="space-y-4"><h2 class="text-xl font-semibold">Premium Adjustments</h2>
<x-record-table :headers="['Document', 'Cover', 'Date', 'Item', 'Currency', 'Total Deposit', 'Amount']" :rows="$officialAdjustments->map(fn($r) => [['label' => $r->DocumentNo ?: 'View record', 'url' => route($prefix.'.rbs-adjustments.show', $r->id)], $r->CoverNo, $r->DocumentDate?->format('d M Y'), $r->ItemDesc ?: $r->ItemNo, $r->DocCurrency, number_format($r->MinDepositPremium ?? 0, 2), $r->invoice_amount !== null ? number_format($r->invoice_amount, 2) : 'Not available'])" />
{{ $officialAdjustments->links() }}</section>
@endif
