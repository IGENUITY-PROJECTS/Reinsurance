@if(isset($officialAdjustments) && $officialAdjustments !== null)
<section class="space-y-4"><h2 class="text-xl font-semibold">RBS premium adjustments</h2><p class="text-sm text-slate-500">Recorded adjustment lines for {{ isset($submission) ? 'this cover' : ($broker ? 'all covers' : 'your covers') }}. Amounts are shown in each document's currency.</p>
<x-record-table :headers="['Document', 'Cover', 'Date', 'Item', 'Currency', 'Minimum deposit', 'Net accounted premium', 'Computed premium']" :rows="$officialAdjustments->map(fn($r) => [['label' => $r->DocumentNo ?: 'View record', 'url' => route($prefix.'.rbs-adjustments.show', $r->id)], $r->CoverNo, $r->DocumentDate?->format('Y-m-d'), $r->ItemDesc ?: $r->ItemNo, $r->DocCurrency, number_format($r->MinDepositPremium ?? 0, 2), number_format($r->NetAccountedPremium ?? 0, 2), number_format($r->PremiumComputed ?? 0, 2)])" />
{{ $officialAdjustments->links() }}</section>
@endif
