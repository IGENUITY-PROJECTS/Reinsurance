@props(['section' => null])

<aside class="hidden w-72 shrink-0 border-r border-brand-900 bg-brand-950 text-white lg:block">
    <div class="flex h-16 items-center border-b border-white/10 px-6">
        <a href="{{ route('dashboard') }}" class="text-lg font-semibold tracking-tight">
            <x-brand-logo variant="dark" />
        </a>
    </div>

    <nav class="space-y-1 px-4 py-5 text-sm">
        @can('view admin dashboard')
            <a href="{{ route('admin.dashboard') }}" class="block rounded-md px-3 py-2 font-medium {{ $section === 'admin' ? 'bg-white text-brand-800 shadow-sm' : 'text-brand-100 hover:bg-white/10 hover:text-white' }}">
                Control Center
            </a>
        @endcan

        @can('manage users')
            <a href="{{ route('admin.users.index') }}" class="block rounded-md px-3 py-2 font-medium {{ $section === 'users' ? 'bg-white text-brand-800 shadow-sm' : 'text-brand-100 hover:bg-white/10 hover:text-white' }}">
                User Management
            </a>
        @endcan

        @can('manage roles')
            <a href="{{ route('admin.access.index') }}" class="block rounded-md px-3 py-2 font-medium {{ $section === 'access' ? 'bg-white text-brand-800 shadow-sm' : 'text-brand-100 hover:bg-white/10 hover:text-white' }}">
                Roles & Permissions
            </a>
        @endcan

        @role('client')
            <a href="{{ route('client.dashboard') }}" class="block rounded-md px-3 py-2 font-medium {{ $section === 'client' ? 'bg-white text-brand-800 shadow-sm' : 'text-brand-100 hover:bg-white/10 hover:text-white' }}">
                Client Hub
            </a>

            <a href="{{ route('client.profile.edit') }}" class="block rounded-md px-3 py-2 font-medium {{ $section === 'client-profile' ? 'bg-white text-brand-800 shadow-sm' : 'text-brand-100 hover:bg-white/10 hover:text-white' }}">
                Profile Settings
            </a>
        @endrole

        <a href="{{ route('home') }}" class="block rounded-md px-3 py-2 font-medium text-brand-100 hover:bg-white/10 hover:text-white">
            Public Site
        </a>
    </nav>
</aside>
