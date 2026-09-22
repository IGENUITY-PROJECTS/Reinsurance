@component('layouts.cedant', ['title' => $title])
    <a href="{{ route('client.dashboard') }}" class="text-sm font-medium text-brand-700 hover:underline">&larr; Back to home</a>
    <h1 class="mt-6 font-display text-2xl font-semibold sm:text-3xl">{{ $title }}</h1>
    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">{{ $description }}</p>
    <div class="mt-8 rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center">
        <span class="inline-flex rounded-full bg-brand-50 px-4 py-2 text-xs font-semibold text-brand-700">{{ $status }}</span>
        <h2 class="mt-5 font-display text-lg font-semibold">{{ $emptyTitle }}</h2>
        <p class="mx-auto mt-3 max-w-lg text-sm leading-7 text-slate-500">{{ $emptyMessage }}</p>
        @if($section === 'help')
            <div class="mx-auto mt-6 grid max-w-2xl gap-3 text-left sm:grid-cols-3">
                @foreach(['Ask a question', 'Report a problem', 'Share feedback'] as $type)
                    <div class="rounded-xl bg-slate-50 p-4 text-sm font-medium text-slate-600">{{ $type }}</div>
                @endforeach
            </div>
        @endif
    </div>
@endcomponent
