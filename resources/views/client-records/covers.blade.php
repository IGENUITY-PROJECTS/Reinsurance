@component($layout, ['title' => 'Policies / Covers'])
<div class="space-y-6"><h1 class="text-2xl font-semibold">Policies / Covers</h1>
<form method="GET" class="flex gap-3"><input aria-label="Search covers" name="q" value="{{ request('q') }}" placeholder="Cover, reference or insured" class="min-w-0 flex-1 rounded-lg border border-slate-300 p-3"><button class="rounded-lg bg-brand-700 px-4 text-white">Search</button></form>
<x-record-table :headers="['Cover', 'Cedant', 'Reference', 'Insured', 'Period', 'Currency', 'Premium']" :rows="$covers->map(fn($r) => [['label' => $r->CoverNo, 'url' => route($prefix.'.policies.show', $r->CoverNo)], $r->CusName ?: $r->CusCode, $r->MRef, $r->InsName, $r->CVStartDate.' – '.$r->CVEndDate, $r->MCurrency, number_format($r->MPremium ?? 0, 2)])" />
{{ $covers->links() }}</div>
@endcomponent
