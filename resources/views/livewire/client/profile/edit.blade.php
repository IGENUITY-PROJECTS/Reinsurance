<section class="max-w-2xl rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold tracking-tight text-slate-950">Profile settings</h2>
    <p class="mt-2 text-sm leading-6 text-slate-600">
        Update your contact details or change your password.
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

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">New password</label>
                <input id="password" type="password" wire:model="password" class="mt-2 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm password</label>
                <input id="password_confirmation" type="password" wire:model="password_confirmation" class="mt-2 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </div>
        </div>

        <button type="submit" class="rounded-md bg-brand-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">
            Save profile
        </button>
    </form>
</section>

