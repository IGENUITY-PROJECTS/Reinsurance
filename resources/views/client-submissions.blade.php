@component('layouts.cedant', ['title' => $title])
    <div class="mb-6 rounded-xl bg-amber-100 p-4 text-sm text-amber-950">Fictional data · {{ config('portal_demo.cedant') }} · Nothing is saved or sent.</div>
    <h1 class="text-2xl font-semibold">{{ $title }}</h1>
    <div class="mt-6 space-y-4">
        @if($type === 'Help')
            @foreach(collect(config('portal_demo.feedback'))->where('company', config('portal_demo.cedant')) as $request)
                <article class="rounded-xl border border-slate-200 bg-white p-5"><h2 class="font-semibold">{{ $request['subject'] }}</h2><p class="mt-3 text-sm">{{ $request['message'] }}</p><p class="mt-4 rounded-lg bg-brand-50 p-4 text-sm">Broker: {{ $request['reply'] ?: 'Awaiting reply' }}</p></article>
            @endforeach
        @else
            @foreach(collect(config('portal_demo.submissions'))->where('company', config('portal_demo.cedant'))->where('type', $type) as $item)<x-sample-submission :item="$item" />@endforeach
        @endif
    </div>
    <section x-data="{ checked: false }" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-semibold">{{ $type === 'Help' ? 'Ask a question or share feedback' : 'Try a document upload' }}</h2>
        <p class="mt-2 text-sm text-slate-500">Preview only. Files stay on your device and are not uploaded.</p>
        <form x-on:submit.prevent="checked = true" class="mt-4 space-y-4">
            <label class="block text-sm">Subject<input required class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></label>
            <label class="block text-sm">Message<textarea required rows="3" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></textarea></label>
            @if($type !== 'Help')<label class="block text-sm">{{ $type }} documents<input type="file" multiple required class="mt-2 block w-full text-sm"></label>@endif
            <button type="submit" class="rounded-lg bg-brand-700 px-4 py-3 text-sm font-semibold text-white">Preview submission</button>
            <p x-show="checked" x-cloak role="status" class="text-sm text-brand-700">Demo preview complete. Nothing was saved or sent.</p>
        </form>
    </section>
@endcomponent
