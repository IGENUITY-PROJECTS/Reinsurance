<section class="mx-auto max-w-7xl space-y-7">
    <div class="rounded-3xl bg-brand-900 p-6 text-white sm:p-9"><p class="text-xs font-semibold uppercase tracking-widest text-brand-200">Broker workspace</p><h1 class="mt-3 text-3xl font-semibold">Your overview</h1>{{-- Portal status deferred; use the mirrored RBS claim status.
<p class="mt-3 text-brand-100">{{ $pending }} submissions awaiting or undergoing review.</p>
--}}<a href="{{ route('admin.submissions') }}" class="mt-5 inline-block rounded-xl bg-white px-5 py-3 text-sm font-semibold text-brand-900">View submissions &rarr;</a></div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($counts as $route => [$label, $count])
            <a href="{{ route('admin.'.$route) }}" class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-brand-300"><p class="text-sm text-slate-500">{{ $label }}</p><p class="mt-3 text-3xl font-semibold">{{ $count }}</p><p class="mt-4 text-xs font-semibold text-brand-700">View all &rarr;</p></a>
        @endforeach
    </div>
    <div><div class="mb-4 flex items-center justify-between"><h2 class="text-lg font-semibold">Recent submissions</h2><a href="{{ route('admin.submissions') }}" class="text-sm font-semibold text-brand-700">View all</a></div><x-submission-table :items="$recent" :broker="true" prefix="admin" /></div>
</section>
