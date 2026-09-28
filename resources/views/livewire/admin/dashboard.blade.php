<section class="mx-auto max-w-7xl space-y-7">
    <div class="flex flex-wrap items-center justify-between gap-4"><div><p class="text-xs font-semibold uppercase tracking-widest text-brand-600">Broker workspace</p><h1 class="mt-2 text-3xl font-semibold">Your overview</h1><p class="mt-2 text-sm text-slate-500">A quick look at the documents waiting for your team.</p></div><a href="{{ route('admin.submissions') }}" class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white">View all submissions &rarr;</a></div>
    <p class="text-xs text-amber-800">Demo workspace · Fictional submissions and cedants</p>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach(['claims' => ['Claims', 'Claim'], 'adjustments' => ['Premium adjustments', 'Premium adjustment'], 'commissions' => ['Profit commissions', 'Profit commission']] as $route => [$label, $type])
            <a href="{{ route('admin.'.$route) }}" class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-brand-300"><p class="text-sm text-slate-500">{{ $label }}</p><p class="mt-3 text-3xl font-semibold">{{ $all->where('type', $type)->count() }}</p><p class="mt-4 text-xs font-semibold text-brand-700">View all &rarr;</p></a>
        @endforeach
        <a href="{{ route('admin.help') }}" class="rounded-2xl border border-slate-200 bg-white p-5 hover:border-brand-300"><p class="text-sm text-slate-500">Help & feedback</p><p class="mt-3 text-3xl font-semibold">{{ count(config('portal_demo.feedback')) }}</p><p class="mt-4 text-xs font-semibold text-brand-700">View requests &rarr;</p></a>
    </div>
    <div><div class="mb-4 flex items-center justify-between"><h2 class="text-lg font-semibold">Recent submissions</h2><a href="{{ route('admin.submissions') }}" class="text-sm font-semibold text-brand-700">View all</a></div><x-submission-table :items="$recent" :broker="true" prefix="admin" /></div>
</section>
