<section class="space-y-4 rounded-2xl border bg-white p-5 sm:p-6">
    <h2 class="text-lg font-semibold">Cover Reinsurers</h2>
    <p class="text-sm text-slate-500">Calculated From The Official Claim Amount × Allocated Share. This Is An Allocation Estimate.</p>
    @if($reinsurers->total())
        <x-record-table :headers="['Code', 'Reinsurer', 'Allocated Share (%)', 'Currency', 'Calculated Claim Share']" :rows="$reinsurers->map(fn ($reinsurer) => [$reinsurer->ReinCode, $reinsurer->ReinName, $reinsurer->ShrAlloc !== null ? number_format($reinsurer->ShrAlloc, 2).'%' : 'Not Available', $allocationClaim?->ClaimCurrencyCode ?: 'Not Available', $allocationClaim?->ClaimAmt !== null && $reinsurer->ShrAlloc !== null ? number_format($allocationClaim->ClaimAmt * $reinsurer->ShrAlloc / 100, 2) : 'Not Available'])" />
        {{ $reinsurers->links() }}
    @else
        <p class="text-sm text-slate-500">No reinsurer details are available for this cover yet.</p>
    @endif
</section>
