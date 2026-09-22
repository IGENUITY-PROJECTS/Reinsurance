<x-layouts.auth title="Cedant Sign In">

<div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100 p-8">

    {{-- Header --}}
    <div class="mb-7">
        <h1 class="font-display font-600 text-2xl text-slate-900 tracking-tight mb-1">Welcome to your cedant portal</h1>
        <p class="text-sm text-slate-500">Sign in to access your company portal.</p>
    </div>

    {{-- Session status --}}
    @if (session('status'))
        <div class="flex gap-2.5 items-start bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3 mb-5">
            <svg class="w-4 h-4 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
            {{ session('status') }}
        </div>
    @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="flex gap-2.5 items-start bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg px-4 py-3 mb-5">
            <svg class="w-4 h-4 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
        @csrf

        {{-- Email --}}
        <div class="mb-4">
            <label for="email" class="block text-sm font-500 text-slate-700 mb-1.5">
                Email address <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z"/>
                    <path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z"/>
                </svg>
                <input
                    id="email" type="email" name="email"
                    value="{{ old('email') }}"
                    placeholder="you@company.com"
                    autocomplete="email" autofocus required
                    class="w-full pl-10 pr-4 py-2.5 text-sm border rounded-lg outline-none transition-all
                           {{ $errors->has('email')
                               ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-2 focus:ring-red-100'
                               : 'border-slate-200 bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-100' }}"
                >
            </div>
            @error('email')
                <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Password --}}
        <div class="mb-4">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-500 text-slate-700">
                    Password <span class="text-red-500">*</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-brand-600 hover:text-brand-800 font-500 hover:underline">
                        Forgot password?
                    </a>
                @endif
            </div>
            <div class="relative">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                </svg>
                <input
                    id="password" type="password" name="password"
                    placeholder="••••••••"
                    autocomplete="current-password" required
                    class="w-full pl-10 pr-11 py-2.5 text-sm border rounded-lg outline-none transition-all
                           {{ $errors->has('password')
                               ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-2 focus:ring-red-100'
                               : 'border-slate-200 bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-100' }}"
                >
                <button type="button" onclick="togglePassword('password','eyeShow','eyeHide')"
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-brand-500 transition-colors">
                    <svg id="eyeShow" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/><path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
                    <svg id="eyeHide" class="w-4 h-4 hidden" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 00-1.06 1.06l14.5 14.5a.75.75 0 101.06-1.06l-1.745-1.745a10.029 10.029 0 003.3-4.38 1.651 1.651 0 000-1.185A10.004 10.004 0 009.999 3a9.956 9.956 0 00-4.744 1.194L3.28 2.22zM7.752 6.69l1.092 1.092a2.5 2.5 0 013.374 3.373l1.091 1.092a4 4 0 00-5.557-5.557z" clip-rule="evenodd"/><path d="M10.748 13.93l2.523 2.524a10.02 10.02 0 01-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 010-1.186A10.007 10.007 0 012.839 6.02L6.07 9.252a4 4 0 004.678 4.678z"/></svg>
                </button>
            </div>
            @error('password')
                <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Remember me --}}
        <div class="flex items-center gap-2.5 mb-6">
            <input id="remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                class="w-4 h-4 rounded border-slate-300 text-brand-600 accent-brand-600 cursor-pointer">
            <label for="remember" class="text-sm text-slate-600 cursor-pointer select-none">
                Keep me signed in
            </label>
        </div>

        {{-- Submit --}}
        <button type="submit" id="submitBtn"
            class="w-full flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-500 py-2.5 px-4 rounded-lg transition-all hover:shadow-lg hover:shadow-brand-600/25 active:scale-[0.99] disabled:opacity-60 disabled:cursor-not-allowed">
            <span id="btnText">Sign in</span>
            <svg id="btnSpinner" class="hidden w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        </button>

    </form>

    @if (Route::has('register'))
        <p class="text-center text-sm text-slate-500 mt-5">
            New to the portal?
            <a href="{{ route('register') }}" class="text-brand-600 hover:text-brand-800 font-500 hover:underline">Create a cedant account</a>
        </p>
    @endif

</div>

<script>
function togglePassword(inputId, showId, hideId) {
    const input = document.getElementById(inputId);
    input.type = input.type === 'password' ? 'text' : 'password';
    document.getElementById(showId).classList.toggle('hidden');
    document.getElementById(hideId).classList.toggle('hidden');
}
document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    document.getElementById('btnText').textContent = 'Signing in…';
    document.getElementById('btnSpinner').classList.remove('hidden');
});
</script>

</x-layouts.auth>

