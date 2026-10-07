<section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold tracking-tight text-slate-950">
            {{ $editingUserId ? 'Edit user' : 'Create user' }}
        </h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">
            Create an account and choose which parts of the portal it can access.
        </p>

        @if (session('status'))
            <div class="mt-5 rounded-md border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800">
                {{ session('status') }}
            </div>
        @endif

        <form wire:submit="save" class="mt-6 space-y-5">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                <input id="name" type="text" wire:model="name" class="mt-2 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
                <input id="email" type="email" wire:model="email" class="mt-2 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                <input id="password" type="password" wire:model="password" class="mt-2 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                <p class="mt-2 text-xs text-slate-500">{{ $editingUserId ? 'Leave blank to keep the current password.' : 'At least 12 characters, including uppercase and lowercase letters, a number and a symbol.' }}</p>
                @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <p class="block text-sm font-medium text-slate-700">Roles</p>
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach ($roles as $role)
                        <label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700">
                            <input type="checkbox" wire:model.live="selectedRoles" value="{{ $role->name }}" class="rounded border-slate-300 text-brand-700 focus:ring-brand-600">
                            {{ $role->name === 'client' ? 'Cedant' : str($role->name)->headline() }}
                        </label>
                    @endforeach
                </div>
                @error('selectedRoles') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            @if(in_array('client', $selectedRoles, true))
                <div>
                    <label for="companyCode" class="block text-sm font-medium text-slate-700">Cedant company code <span class="text-red-600">*</span></label>
                    <input id="companyCode" type="text" wire:model="companyCode" required maxlength="50" placeholder="Enter the cedant company code" class="mt-2 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    @error('companyCode')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            @endif

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">
                    {{ $editingUserId ? 'Update user' : 'Create user' }}
                </button>

                @if ($editingUserId)
                    <button type="button" wire:click="resetForm" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                @endif
            </div>
        </form>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-semibold tracking-tight text-slate-950">Users</h2>
            <p class="mt-1 text-sm text-slate-500">Manage broker admins and cedant accounts.</p>
        </div>

        <div class="divide-y divide-slate-200">
            @foreach ($users as $user)
                <div wire:key="user-{{ $user->id }}" class="flex flex-col gap-4 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="font-medium text-slate-950">{{ $user->name }}</p>
                        <p class="text-sm text-slate-500">{{ $user->email }}</p>
                        @if($user->company)<p class="mt-1 text-sm text-slate-500">{{ $user->company->CompanyName }} · {{ $user->company->CmpCode }}</p>@endif
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse ($user->roles as $role)
                                <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-800">{{ $role->name === 'client' ? 'Cedant' : str($role->name)->headline() }}</span>
                            @empty
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">No role</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if(auth()->user()->hasRole('super-admin') || ! $user->hasRole('super-admin'))
                        <button type="button" wire:click="edit({{ $user->id }})" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Edit
                        </button>

                        @if (auth()->id() !== $user->id)
                            <button type="button" wire:click="delete({{ $user->id }})" wire:confirm="Delete this user?" class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
                                Delete
                            </button>
                        @endif
                        @else
                            <span class="text-xs font-medium text-slate-500">Protected account</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="border-t border-slate-200 px-6 py-4">{{ $users->links() }}</div>
    </div>
</section>


