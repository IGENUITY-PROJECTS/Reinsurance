@component($layout, ['title' => $category === 'claims' ? 'Claims' : 'Premium Adjustments', 'section' => $category])
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4"><h1 class="text-2xl font-semibold">{{ $category === 'claims' ? 'Claims' : 'Premium Adjustments' }}</h1>
    @if(!$broker)<a href="{{ route('client.'.($category === 'claims' ? 'claims' : 'adjustments').'.create') }}" class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white">{{ $category === 'claims' ? 'New claim' : 'New adjustment' }}</a>@endif</div>
    <form method="GET" class="grid gap-4 rounded-2xl border bg-white p-5 sm:grid-cols-2 lg:grid-cols-4">
        <label class="text-xs font-semibold text-slate-600">Search<input name="q" value="{{ request('q') }}" placeholder="Reference, cover or details" class="mt-2 block w-full rounded-lg border p-3 text-sm"></label>
        @if($broker)<label class="text-xs font-semibold text-slate-600">Cedant<select name="company" class="mt-2 block w-full rounded-lg border p-3 text-sm"><option value="">All cedants</option>@foreach($companies as $company)<option value="{{ $company }}" @selected(request('company') === $company)>{{ $company }}</option>@endforeach</select></label>@endif
        <label class="text-xs font-semibold text-slate-600">From<input type="date" name="from" value="{{ request('from') }}" class="mt-2 block w-full rounded-lg border p-3 text-sm"></label>
        <label class="text-xs font-semibold text-slate-600">To<input type="date" name="to" value="{{ request('to') }}" class="mt-2 block w-full rounded-lg border p-3 text-sm"></label>
        <div class="flex items-center gap-3"><button class="rounded-lg bg-brand-700 px-4 py-3 text-sm font-semibold text-white">Apply filters</button><a href="{{ url()->current() }}" class="text-sm text-slate-500">Reset</a></div>
    </form>
    @if($errors->any())<p role="alert" class="text-sm text-red-700">{{ $errors->first() }}</p>@endif
    <p class="text-sm text-slate-500">{{ $records->total() }} {{ $category === 'claims' ? 'claims' : 'adjustment entries' }}</p>
    @php
        $headers = [$category === 'claims' ? 'Claim / reference' : 'Document / cover', 'Cover'];
        if ($broker) $headers[] = 'Cedant';
        $headers = array_merge($headers, [$category === 'claims' ? 'Insured' : 'Details', 'Date', 'Status', 'Currency', 'Amount', 'Action']);
        $rows = $records->map(function ($record) use ($prefix, $broker) {
            $route = match ($record->record_kind) { 'claim' => 'rbs-claims.show', 'adjustment' => 'rbs-adjustments.show', default => 'submissions.show' };
            $row = [$record->claim_number ?: $record->reference, $record->cover];
            if ($broker) $row[] = $record->company_code;
            return array_merge($row, [str($record->label ?? '')->limit(100)->toString(), $record->display_date, $record->status, $record->currency, $record->amount !== null ? number_format($record->amount, 2) : '—', ['label' => 'View', 'url' => route($prefix.'.'.$route, $record->record_key)]]);
        });
    @endphp
    <x-record-table :headers="$headers" :rows="$rows" />
    {{ $records->links() }}
</div>
@endcomponent
