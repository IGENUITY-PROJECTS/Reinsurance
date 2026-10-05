<x-layouts.auth title="Cedant Registration">

    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100 p-8">

        <div class="mb-6">
            <h1 class="font-display font-semibold text-2xl text-slate-900 tracking-tight mb-1">Create your cedant account</h1>
            <p class="text-sm text-slate-500">Register with your work email to get started.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" id="registerForm" novalidate>
            @csrf

            {{-- Full name --}}
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Full name <span
                        class="text-red-500">*</span></label>
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                        viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
                    </svg>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Jane Doe"
                        autocomplete="name" required
                        class="w-full pl-10 pr-4 py-2.5 text-sm border rounded-lg outline-none transition-all {{ $errors->has('name') ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-2 focus:ring-red-100' : 'border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-100' }}">
                </div>
                @error('name') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Cedant company --}}
            <div class="mb-4">
                <label for="company_code" class="block text-sm font-medium text-slate-700 mb-1.5">Company code <span class="text-red-500">*</span></label>
                <input id="company_code" name="company_code" type="text" value="{{ old('company_code') }}" required maxlength="50"
                    placeholder="Enter your cedant company code" aria-describedby="company_code_help"
                    class="w-full px-4 py-2.5 text-sm border rounded-lg outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 {{ $errors->has('company_code') ? 'border-red-400' : 'border-slate-200' }}">
                <p id="company_code_help" class="mt-1.5 text-xs text-slate-500">Use the company code provided by your broker.</p>
                @error('company_code') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            {{-- Email --}}
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email address <span
                        class="text-red-500">*</span></label>
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                        viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z" />
                        <path
                            d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z" />
                    </svg>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@company.com"
                        autocomplete="email" required
                        class="w-full pl-10 pr-4 py-2.5 text-sm border rounded-lg outline-none transition-all {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-2 focus:ring-red-100' : 'border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-100' }}">
                </div>
                @error('email') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Password --}}
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password <span
                        class="text-red-500">*</span></label>
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                        viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z"
                            clip-rule="evenodd" />
                    </svg>
                    <input id="password" type="password" name="password" placeholder="Min. 8 characters"
                        autocomplete="new-password" required oninput="checkStrength(this.value)"
                        class="w-full pl-10 pr-11 py-2.5 text-sm border rounded-lg outline-none transition-all {{ $errors->has('password') ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-2 focus:ring-red-100' : 'border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-100' }}">
                    <button type="button" onclick="togglePassword('password','eyeShow','eyeHide')"
                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-brand-500 transition-colors">
                        <svg id="eyeShow" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                            <path fill-rule="evenodd"
                                d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <svg id="eyeHide" class="w-4 h-4 hidden" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M3.28 2.22a.75.75 0 00-1.06 1.06l14.5 14.5a.75.75 0 101.06-1.06l-1.745-1.745a10.029 10.029 0 003.3-4.38 1.651 1.651 0 000-1.185A10.004 10.004 0 009.999 3a9.956 9.956 0 00-4.744 1.194L3.28 2.22zM7.752 6.69l1.092 1.092a2.5 2.5 0 013.374 3.373l1.091 1.092a4 4 0 00-5.557-5.557z"
                                clip-rule="evenodd" />
                            <path
                                d="M10.748 13.93l2.523 2.524a10.02 10.02 0 01-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 010-1.186A10.007 10.007 0 012.839 6.02L6.07 9.252a4 4 0 004.678 4.678z" />
                        </svg>
                    </button>
                </div>
                <div id="strengthWrap" class="hidden mt-2">
                    <div class="flex gap-1 mb-1">
                        <div id="bar1" class="h-1 flex-1 rounded-full bg-slate-200 transition-all duration-300"></div>
                        <div id="bar2" class="h-1 flex-1 rounded-full bg-slate-200 transition-all duration-300"></div>
                        <div id="bar3" class="h-1 flex-1 rounded-full bg-slate-200 transition-all duration-300"></div>
                        <div id="bar4" class="h-1 flex-1 rounded-full bg-slate-200 transition-all duration-300"></div>
                    </div>
                    <span id="strengthLabel" class="text-xs text-slate-400"></span>
                </div>
                @error('password')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @else
                <p class="mt-1.5 text-xs text-slate-400">At least 8 characters with letters and numbers.</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">Confirm
                    password <span class="text-red-500">*</span></label>
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                        viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z"
                            clip-rule="evenodd" />
                    </svg>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                        placeholder="Repeat your password" autocomplete="new-password" required
                        class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-200 rounded-lg outline-none transition-all focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                </div>
            </div>

            <button type="submit" id="submitBtn"
                class="w-full flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-4 rounded-lg transition-all hover:shadow-lg hover:shadow-brand-600/25 active:scale-[0.99] disabled:opacity-60 disabled:cursor-not-allowed">
                <span id="btnText">Create account</span>
                <svg id="btnSpinner" class="hidden w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
            </button>

        </form>

        <p class="text-center text-sm text-slate-500 mt-5">
            Already have an account?
            <a href="{{ route('login') }}" class="text-brand-600 hover:text-brand-800 font-medium hover:underline">Sign
                in</a>
        </p>

    </div>

    <script>
        function togglePassword(inputId, showId, hideId) {
    const input = document.getElementById(inputId);
    input.type = input.type === 'password' ? 'text' : 'password';
    document.getElementById(showId).classList.toggle('hidden');
    document.getElementById(hideId).classList.toggle('hidden');
}
function checkStrength(val) {
    const wrap = document.getElementById('strengthWrap');
    const label = document.getElementById('strengthLabel');
    const bars = [1,2,3,4].map(n => document.getElementById('bar'+n));
    const colors = ['bg-red-500','bg-amber-500','bg-blue-500','bg-emerald-500'];
    const labels = ['Weak','Fair','Good','Strong'];
    if (!val) { wrap.classList.add('hidden'); return; }
    wrap.classList.remove('hidden');
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    if (score === 0) score = 1;
    label.textContent = labels[score - 1];
    bars.forEach((bar, i) => {
        bar.className = 'h-1 flex-1 rounded-full transition-all duration-300 ' + (i < score ? colors[score-1] : 'bg-slate-200');
    });
}
document.getElementById('registerForm').addEventListener('submit', function() {
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('btnText').textContent = 'Creating account…';
    document.getElementById('btnSpinner').classList.remove('hidden');
});
    </script>

</x-layouts.auth>



