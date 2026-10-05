@component($layout, ['title' => $cover->CoverNo])
<div class="space-y-6"><a href="{{ route($prefix.'.policies') }}" class="text-brand-700">&larr; Covers</a><h1 class="text-2xl font-semibold">{{ $cover->CoverNo }} · {{ $cover->MTitle }}</h1>
<div class="rounded-2xl border bg-white p-6"><dl class="grid gap-4 sm:grid-cols-2">
@foreach(['Reference' => $cover->MRef, 'Insured' => $cover->InsName, 'Start date' => $cover->CVStartDate, 'End date' => $cover->CVEndDate, 'Class' => $cover->REClassDesc, 'Currency' => $cover->MCurrency, 'Premium' => number_format($cover->MPremium ?? 0, 2), 'Risk insured' => number_format($cover->RskInsured ?? 0, 2)] as $label => $value)<div><dt class="text-sm text-slate-500">{{ $label }}</dt><dd class="mt-1">{{ $value ?? '—' }}</dd></div>@endforeach</dl><p class="mt-5 whitespace-pre-line">{{ $cover->CVDetails }}</p></div>
@if(!$broker && $cover->RETypeCode === 'FC')<a class="inline-block rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white" href="{{ route($prefix.'.claims.create', ['cover' => $cover->CoverNo]) }}">Submit a claim for this cover</a>@endif
<h2 class="text-xl font-semibold">Reinsurers</h2>
<x-record-table :headers="['Code', 'Reinsurer', 'Allocated share (%)', 'Amount']" :rows="$reinsurers->map(fn($r) => [$r->ReinCode, $r->ReinName, number_format($r->ShrAlloc ?? 0, 2), number_format($r->TotalAmt ?? 0, 2)])" />
{{ $reinsurers->links() }}</div>@endcomponent
