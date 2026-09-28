<section class="space-y-8"><p class="text-xs text-amber-800">Demo preview · Fictional company records</p>
    <div class="relative overflow-hidden rounded-3xl bg-brand-900 px-6 py-9 text-white sm:p-10">
        <p class="text-xs font-semibold uppercase tracking-widest text-brand-200">Your cedant portal</p>
        <h1 class="mt-4 break-words font-display text-2xl font-semibold tracking-tight sm:text-3xl">Welcome, {{ auth()->user()->name }}.</h1>
        <p class="mt-3 max-w-xl text-sm leading-7 text-brand-100 sm:text-base">Your covers, accounts, and conversations. All in one place.</p>
    </div>
    <div>
        <h2 class="font-display text-xl font-semibold">How can we help today?</h2>
        <p class="mt-2 text-sm text-slate-500">Choose where you would like to go.</p>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            @foreach([
                ['route' => 'client.policies', 'title' => 'My Policies / Covers', 'description' => 'Find your cover details and policy documents.', 'action' => 'View policies', 'icon' => 'M9 12l2 2 4-4 M12 3l8 4v5c0 5-8 9-8 9s-8-4-8-9V7l8-4'],
                ['route' => 'client.statements', 'title' => 'Statement of Account', 'description' => 'Find your statements and account information.', 'action' => 'View statements', 'icon' => 'M6 3h9l3 3v15H6V3 M9 10h6 M9 14h6 M9 18h4'],
                ['route' => 'client.claims', 'title' => 'My Claims', 'description' => 'Your place to submit a claim and follow its progress.', 'action' => 'View claims', 'icon' => 'M9 5H5v16h14V5h-4 M9 3h6v4H9V3 M8 12h8 M8 16h5'],
                ['route' => 'client.adjustments', 'title' => 'Premium Adjustments', 'description' => 'Upload adjustment schedules and read broker feedback.', 'action' => 'View adjustments', 'icon' => 'M4 6h16 M4 12h16 M4 18h16'],
                ['route' => 'client.commissions', 'title' => 'Profit Commissions', 'description' => 'Submit calculations and follow document reviews.', 'action' => 'View commissions', 'icon' => 'M4 6h16 M4 12h16 M4 18h16'],
                ['route' => 'client.help', 'title' => 'Help & Feedback', 'description' => 'Questions, problems, or ideas? Start here.', 'action' => 'Get help', 'icon' => 'M21 11a8 8 0 0 1-8 8H7l-4 3V11a9 9 0 0 1 18 0 M8 11h.01 M12 11h.01 M16 11h.01'],
            ] as $item)
                <a href="{{ route($item['route']) }}" class="group flex flex-col rounded-2xl border border-slate-200 bg-white p-6 transition hover:border-brand-300 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-600">
                    <span class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <svg aria-hidden="true" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $item['icon'] }}" /></svg>
                    </span>
                    <h3 class="font-display text-lg font-semibold">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ $item['description'] }}</p>
                    <span class="mt-6 flex items-center justify-between text-sm font-semibold text-brand-700">{{ $item['action'] }} <span aria-hidden="true">&rarr;</span></span>
                </a>
            @endforeach
        </div>
    </div>
    <div><div class="mb-4 flex items-center justify-between"><h2 class="text-lg font-semibold">Recent submissions</h2><a href="{{ route('client.submissions') }}" class="text-sm font-semibold text-brand-700">View all</a></div><x-submission-table :items="collect(config('portal_demo.submissions'))->where('company', config('portal_demo.cedant'))->sortByDesc('date')->take(3)" /></div>
</section>




