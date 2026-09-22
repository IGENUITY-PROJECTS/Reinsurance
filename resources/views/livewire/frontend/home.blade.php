<main>
    <section class="border-b border-slate-200 bg-[linear-gradient(135deg,#f5f8ff_0%,#ffffff_48%,#ecfeff_100%)]">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-16 md:grid-cols-[1.1fr_0.9fr] md:items-center md:py-20">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-brand-700">Operational workspace starter</p>
                <h1 class="mt-4 max-w-2xl text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl">
                    Noble Portal for secure teams and client access.
                </h1>
                <p class="mt-5 max-w-xl text-base leading-7 text-slate-600">
                    Launch projects with polished authentication, role-aware routing, and separate workspaces for internal teams and clients.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md bg-brand-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">
                            Open workspace
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="rounded-md bg-brand-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">
                            Create account
                        </a>
                        <a href="{{ route('login') }}" class="rounded-md border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:border-brand-300 hover:bg-brand-50 hover:text-brand-800">
                            Sign in
                        </a>
                    @endauth
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-xl shadow-brand-950/5">
                <div class="grid gap-4">
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-semibold text-slate-950">Control Center</p>
                        <p class="mt-1 text-sm text-slate-500">Private access for administrators and operators.</p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-brand-50 p-4">
                        <p class="text-sm font-semibold text-slate-950">Client Hub</p>
                        <p class="mt-1 text-sm text-slate-500">A focused space for customer-facing accounts.</p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-accent-50 p-4">
                        <p class="text-sm font-semibold text-slate-950">Corporate-blue theme</p>
                        <p class="mt-1 text-sm text-slate-500">Global colors plus inline classes for quick editing.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
