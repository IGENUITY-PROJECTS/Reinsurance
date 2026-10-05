<main>
    <section class="border-b border-slate-200 bg-gradient-to-br from-brand-50 via-white to-slate-50">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 md:grid-cols-2 md:items-center md:py-20">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-brand-700">Your covers, statements and claims</p>
                <h1 class="mt-4 break-words text-3xl font-semibold tracking-tight text-slate-950 sm:text-5xl">Welcome.</h1>
                <p class="mt-5 max-w-xl text-base leading-7 text-slate-600">Sign in to view your covers and statements, submit documents, and follow your claims.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-800">Open your portal</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-800">Sign in</a>
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-brand-50">Create an account</a>
                        @endif
                    @endauth
                </div>
            </div>
            <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @foreach(['Policies / Covers' => 'Find your cover information and policy documents.', 'Statement of Account' => 'Review your account activity and statements.', 'My Claims' => 'Keep track of your claims and their progress.', 'Premium Adjustments' => 'Submit your adjustment schedules and supporting documents.'] as $label => $description)
                    <div class="rounded-xl bg-brand-50 p-4"><h2 class="text-sm font-semibold text-brand-900">{{ $label }}</h2><p class="mt-2 text-sm leading-6 text-slate-600">{{ $description }}</p></div>
                @endforeach
            </div>
        </div>
    </section>
</main>

