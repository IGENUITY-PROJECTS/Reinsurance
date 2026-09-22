<section class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Accounts</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $userCount }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Access groups</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $roleCount }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Environment</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950">Live</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Permissions</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $permissionCount }}</p>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold tracking-tight text-slate-950">Operations overview</h2>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
            Use this control center for internal management, reporting, team settings, and project-specific administration modules.
        </p>

        <div class="mt-5 flex flex-wrap gap-3">
            @can('manage users')
                <a href="{{ route('admin.users.index') }}" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">
                    Manage users
                </a>
            @endcan

            @can('manage roles')
                <a href="{{ route('admin.access.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Manage access
                </a>
            @endcan
        </div>
    </div>
</section>
