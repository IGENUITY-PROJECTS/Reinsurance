<section class="space-y-6">
    <div class="rounded-lg border border-brand-200 bg-gradient-to-r from-brand-950 via-brand-900 to-brand-700 p-6 text-white shadow-sm">
        <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-accent-200">Access control</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight">Roles & Permissions Center</h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-brand-100">
                    Create roles for teams, assign permissions to each role, then attach roles to users from User Management.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                <div class="rounded-md bg-white/10 px-4 py-3 ring-1 ring-white/15">
                    <p class="text-brand-100">Roles</p>
                    <p class="mt-1 text-2xl font-semibold">{{ $roles->count() }}</p>
                </div>
                <div class="rounded-md bg-white/10 px-4 py-3 ring-1 ring-white/15">
                    <p class="text-brand-100">Permissions</p>
                    <p class="mt-1 text-2xl font-semibold">{{ $permissions->count() }}</p>
                </div>
                <div class="rounded-md bg-white/10 px-4 py-3 ring-1 ring-white/15">
                    <p class="text-brand-100">Selected</p>
                    <p class="mt-1 text-2xl font-semibold">{{ count($selectedPermissions) }}</p>
                </div>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-md border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-brand-50 text-sm font-bold text-brand-700">1</div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-semibold tracking-tight text-slate-950">Add a role</h3>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Examples: finance manager, support agent, content editor.</p>

                    <form wire:submit="createRole" class="mt-4 flex flex-col gap-3 sm:flex-row">
                        <input type="text" wire:model="roleName" placeholder="Example: finance manager" class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                        <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">Add role</button>
                    </form>
                    @error('roleName') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-accent-50 text-sm font-bold text-accent-700">2</div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-semibold tracking-tight text-slate-950">Add a permission</h3>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Use plain names like manage users, view reports, approve invoices.</p>

                    <form wire:submit="createPermission" class="mt-4 flex flex-col gap-3 sm:flex-row">
                        <input type="text" wire:model="permissionName" placeholder="Example: approve invoices" class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                        <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">Add permission</button>
                    </form>
                    @error('permissionName') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-semibold tracking-tight text-slate-950">Choose a role</h2>
                <p class="mt-1 text-sm text-slate-500">The highlighted role is the one you are editing.</p>
            </div>

            <div class="space-y-3 p-4">
                @forelse ($roles as $role)
                    <button type="button" wire:key="role-{{ $role->id }}" wire:click="selectRole({{ $role->id }})" class="block w-full rounded-lg border px-4 py-4 text-left transition {{ $selectedRoleId === $role->id ? 'border-brand-300 bg-brand-50 shadow-sm ring-2 ring-brand-100' : 'border-slate-200 bg-white hover:border-brand-200 hover:bg-slate-50' }}">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-950">{{ str($role->name)->headline() }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $role->permissions->count() }} permissions assigned</p>
                            </div>

                            @if ($selectedRoleId === $role->id)
                                <span class="rounded-full bg-brand-700 px-2.5 py-1 text-xs font-semibold text-white">Editing</span>
                            @endif
                        </div>

                        @if ($role->permissions->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($role->permissions->take(3) as $permission)
                                    <span class="rounded-full bg-white px-2.5 py-1 text-xs font-medium text-brand-800 ring-1 ring-brand-100">{{ str($permission->name)->headline() }}</span>
                                @endforeach

                                @if ($role->permissions->count() > 3)
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">+{{ $role->permissions->count() - 3 }} more</span>
                                @endif
                            </div>
                        @endif
                    </button>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center">
                        <p class="font-medium text-slate-950">No roles yet</p>
                        <p class="mt-1 text-sm text-slate-500">Create your first role above.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div wire:key="permissions-for-role-{{ $selectedRoleId ?? 'none' }}" class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
                <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                    <div>
                        <h2 class="text-lg font-semibold tracking-tight text-slate-950">
                            {{ $selectedRole ? str($selectedRole->name)->headline() : 'Select a role' }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $selectedRole ? 'Choose what this role can access.' : 'Pick a role on the left to begin.' }}
                        </p>
                    </div>

                    @if ($selectedRole)
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="selectAllPermissions" class="rounded-md border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-800 hover:bg-brand-100">
                                Select all
                            </button>
                            <button type="button" wire:click="clearPermissions" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                Clear
                            </button>
                            <span class="rounded-md bg-brand-700 px-3 py-1.5 text-xs font-semibold text-white">
                                {{ count($selectedPermissions) }} selected
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <form wire:submit="saveRolePermissions" class="space-y-5 p-6">
                @if ($permissions->isEmpty())
                    <div class="rounded-lg border border-dashed border-slate-300 p-8 text-center">
                        <p class="font-medium text-slate-950">No permissions yet</p>
                        <p class="mt-1 text-sm text-slate-500">Create permissions above, then assign them here.</p>
                    </div>
                @else
                    <div class="space-y-5">
                        @foreach ($permissionGroups as $groupName => $groupPermissions)
                            <div wire:key="permission-group-{{ $groupName }}" class="rounded-lg border border-slate-200">
                                <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                                    <h3 class="text-sm font-semibold text-slate-950">{{ $groupName }} permissions</h3>
                                </div>

                                <div class="grid gap-2 p-4 md:grid-cols-2">
                                    @foreach ($groupPermissions as $permission)
                                        @php
                                            $isSelected = in_array($permission->name, $selectedPermissions, true);
                                        @endphp

                                        <label wire:key="permission-{{ $selectedRoleId ?? 'none' }}-{{ $permission->id }}" class="flex items-start gap-3 rounded-md border px-3 py-3 text-sm transition {{ $isSelected ? 'border-brand-500 bg-brand-100 text-brand-950 shadow-sm ring-2 ring-brand-200' : 'border-slate-200 text-slate-700 hover:border-brand-200 hover:bg-slate-50' }}">
                                            <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}" @checked($isSelected) class="mt-0.5 rounded border-slate-300 text-brand-700 focus:ring-brand-600">
                                            <span>
                                                <span class="flex items-center gap-2 font-medium">
                                                    {{ str($permission->name)->headline() }}
                                                    @if ($isSelected)
                                                        <span class="rounded-full bg-brand-700 px-2 py-0.5 text-[11px] font-semibold text-white">Active</span>
                                                    @endif
                                                </span>
                                                <span class="mt-0.5 block text-xs text-slate-500">{{ $permission->name }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @error('selectedRoleId') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                <div class="flex flex-col justify-between gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center">
                    <p class="text-sm text-slate-500">Changes apply to every user with this role.</p>

                    <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800 disabled:cursor-not-allowed disabled:bg-slate-300" @disabled(! $selectedRole)>
                        Save permissions
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
