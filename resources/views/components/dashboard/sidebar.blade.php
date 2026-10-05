@props(['section' => null])
<aside class="w-full shrink-0 border-b border-brand-900 bg-brand-950 text-white lg:min-h-screen lg:w-64 lg:border-b-0 lg:border-r">
    <a href="{{ route('admin.dashboard') }}" class="block border-b border-white/10 px-5 py-6"><x-brand-logo variant="dark" /></a>
    <nav aria-label="Broker navigation" class="flex flex-wrap gap-1 p-3 text-sm lg:block lg:space-y-1 lg:p-4">
        @foreach(['admin.dashboard' => 'Overview', 'admin.submissions' => 'All submissions', 'admin.policies' => 'Policies / Covers', 'admin.statements' => 'Statements', 'admin.claims' => 'Claims', 'admin.adjustments' => 'Premium Adjustments', 'admin.help' => 'Help & Feedback'] as $route => $label)
            <a href="{{ route($route) }}" @if(request()->routeIs($route)) aria-current="page" @endif class="block rounded-xl px-3 py-3 font-medium {{ request()->routeIs($route) ? 'bg-white text-brand-900' : 'text-brand-100 hover:bg-white/10' }}">{{ $label }}</a>
        @endforeach
        @can('manage users')<a href="{{ route('admin.users.index') }}" class="block rounded-xl px-3 py-3 text-brand-100 hover:bg-white/10">User Management</a>@endcan
        @can('manage roles')<a href="{{ route('admin.access.index') }}" class="block rounded-xl px-3 py-3 text-brand-100 hover:bg-white/10">Roles & Permissions</a>@endcan
    </nav>
</aside>
