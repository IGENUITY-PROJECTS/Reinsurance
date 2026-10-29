@component($layout, ['title' => 'Covers'])
<div class="space-y-6"><h1 class="text-2xl font-semibold">Covers</h1>
<form method="GET" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-3">
    <label class="text-sm font-medium">Company
        <select name="company" class="mt-2 block w-full rounded-lg border border-slate-300 p-3">
            <option value="">{{ $broker ? 'All Companies' : 'Your company' }}</option>
            @foreach($companies as $company)
                <option value="{{ $company->CusCode }}" @selected(request('company') === $company->CusCode)>{{ $company->CusCode }} - {{ $company->company_name ?: $company->CusCode }}</option>
            @endforeach
        </select>
    </label>
    <label class="text-sm font-medium">Reinsurance Type
        <select name="reinsurance_type" class="mt-2 block w-full rounded-lg border border-slate-300 p-3">
            <option value="">All Covers</option><option value="FC" @selected(request('reinsurance_type') === 'FC')>Facultative</option><option value="TC" @selected(request('reinsurance_type') === 'TC')>Treaty</option>
        </select>
    </label>
    <label class="text-sm font-medium">Search Covers
        <input name="q" value="{{ request('q') }}" maxlength="150" placeholder="Cover, reference or insured" class="mt-2 block w-full rounded-lg border border-slate-300 p-3">
    </label>
    <div class="flex items-end gap-3"><button class="rounded-lg bg-brand-700 px-4 py-3 font-semibold text-white">Search</button><a href="{{ route($prefix.'.policies') }}" class="rounded-lg border px-4 py-3 text-sm">Reset</a></div>
</form>
<x-record-table :headers="['Cover', 'Cedant', 'Reference', 'Insured', 'Reinsurance Type', 'Period']" :rows="$covers->map(fn($r) => [['label' => $r->CoverNo, 'url' => route($prefix.'.policies.show', $r->CoverNo)], $r->CusName ?: $r->CusCode, $r->MRef, $r->InsName, match ($r->RETypeCode) { 'FC' => 'Facultative', 'TC' => 'Treaty', default => $r->RETypeDesc ?: 'Not Available' }, \App\Support\DisplayDate::format($r->CVStartDate).' – '.\App\Support\DisplayDate::format($r->CVEndDate)])" />
{{ $covers->links() }}</div>
@endcomponent
