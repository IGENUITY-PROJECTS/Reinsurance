@component('layouts.auth', ['title' => 'Confirm Password', 'heading' => 'Confirm your password'])
<form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
    @csrf

    <div>
        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password"
            class="mt-2 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-950 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
        @error('password')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit"
        class="w-full rounded-md bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
        Confirm
    </button>
</form>
@endcomponent
