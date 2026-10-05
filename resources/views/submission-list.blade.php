@component($layout, ['title' => $title, 'section' => $category])
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4"><div><p class="text-xs font-semibold uppercase tracking-widest text-brand-600">{{ $broker ? 'Broker workspace' : 'Cedant portal' }}</p><h1 class="mt-2 text-2xl font-semibold">{{ $title }}</h1><p class="mt-2 text-sm text-slate-500">Find a submission, then select View for documents and status updates.</p></div>@if(!$broker)<a href="{{ route('client.'.($category === 'all' ? 'claims' : $category).'.create') }}" class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white">{{ $category === 'all' ? 'New claim' : 'New submission' }}</a>@endif</div>
    <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="grid gap-4 sm:grid-cols-2 {{ $broker ? 'xl:grid-cols-3' : 'xl:grid-cols-2' }}">
            <label class="text-xs font-semibold text-slate-600">Search<input name="q" value="{{ request('q') }}" placeholder="Claim reference, cover or details" class="mt-2 block w-full rounded-lg border border-slate-300 p-3 text-sm"></label>
            {{-- Portal status deferred; use the mirrored RBS claim status.
<label class="text-xs font-semibold text-slate-600">Status<select name="status" class="mt-2 block w-full rounded-lg border border-slate-300 p-3 text-sm"><option value="">All statuses</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>@endforeach</select></label>
--}}
            @if($broker)<label class="text-xs font-semibold text-slate-600">Cedant<select name="company" class="mt-2 block w-full rounded-lg border border-slate-300 p-3 text-sm"><option value="">All cedants</option>@foreach($companies as $company)<option @selected(request('company') === $company)>{{ $company }}</option>@endforeach</select></label>@endif
            <label class="text-xs font-semibold text-slate-600">From<input type="date" name="from" value="{{ request('from') }}" class="mt-2 block w-full rounded-lg border border-slate-300 p-3 text-sm"></label><label class="text-xs font-semibold text-slate-600">To<input type="date" name="to" value="{{ request('to') }}" class="mt-2 block w-full rounded-lg border border-slate-300 p-3 text-sm"></label>
            <div class="flex items-end gap-3"><button class="rounded-lg bg-brand-700 px-4 py-3 text-sm font-semibold text-white">Apply filters</button><a href="{{ url()->current() }}" class="px-3 py-3 text-sm text-slate-500">Reset</a></div>
        </div>
        @if($errors->any())<p role="alert" class="mt-3 text-sm text-red-700">{{ $errors->first() }}</p>@endif
    </form>
    <div class="flex items-center justify-between"><p class="text-sm text-slate-500">{{ $items->total() }} submissions</p><p class="text-xs text-slate-500">{{ $items->perPage() }} per page</p></div>
    <x-submission-table :items="$items" :broker="$broker" :prefix="$prefix" />
    {{ $items->links() }}
    @if(isset($officialClaims) && $officialClaims !== null)
        <h2 class="mt-8 text-xl font-semibold">Official RBS claims</h2>
        <x-record-table :headers="['Claim number', 'Your reference', 'Cover', 'Status', 'Currency', 'Amount']" :rows="$officialClaims->map(fn($r) => [['label' => $r->ClaimNo, 'url' => route($prefix.'.rbs-claims.show', $r->ClaimNo)], $r->OrigClaimNo, $r->CoverNo, $r->MStatusDesc ?: ($r->MStatusCode ?: 'Not available'), $r->ClaimCurrencyCode, number_format($r->ClaimAmt ?? 0, 2)])" />
        {{ $officialClaims->links() }}
    @endif
@include('client-records.adjustment-records')
</div>
@endcomponent
