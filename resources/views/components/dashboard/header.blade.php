@props(['title' => 'Dashboard'])

<header class="border-b border-slate-200 bg-white">
    <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-brand-600">Workspace</p>
            <h1 class="text-lg font-semibold tracking-tight text-slate-950">{{ $title }}</h1>
        </div>

        <div class="flex items-center gap-4">
            <div class="hidden text-right sm:block">
                <p class="text-sm font-medium text-slate-950">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:border-brand-300 hover:bg-brand-50 hover:text-brand-800">
                    Sign out
                </button>
            </form>
        </div>
    </div>
</header>
