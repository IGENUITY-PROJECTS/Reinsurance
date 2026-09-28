<x-layouts.auth title="Reset Password">

<div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100 p-8">

    <div class="mb-7">
        <h1 class="font-display font-600 text-2xl text-slate-900 tracking-tight mb-1">Forgot password?</h1>
        <p class="text-sm text-slate-500">Enter your email and we'll send you a reset link.</p>
    </div>

    @if (session('status'))
        <div class="flex gap-2.5 items-start bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3 mb-5">
            <svg class="w-4 h-4 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" id="forgotForm" novalidate>
        @csrf

        <div class="mb-6">
            <label for="email" class="block text-sm font-500 text-slate-700 mb-1.5">Email address <span class="text-red-500">*</span></label>
            <div class="relative">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z"/><path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z"/></svg>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                    placeholder="you@company.com" autofocus required
                    class="w-full pl-10 pr-4 py-2.5 text-sm border rounded-lg outline-none transition-all {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-2 focus:ring-red-100' : 'border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-100' }}">
            </div>
            @error('email') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" id="submitBtn"
            class="w-full flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-500 py-2.5 px-4 rounded-lg transition-all hover:shadow-lg hover:shadow-brand-600/25 active:scale-[0.99] disabled:opacity-60 disabled:cursor-not-allowed">
            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M3.105 2.289a.75.75 0 00-.826.95l1.414 4.925A1.5 1.5 0 005.135 9.25h6.115a.75.75 0 010 1.5H5.135a1.5 1.5 0 00-1.442 1.086l-1.414 4.926a.75.75 0 00.826.95 28.896 28.896 0 0015.293-7.154.75.75 0 000-1.115A28.897 28.897 0 003.105 2.289z"/></svg>
            <span id="btnText">Send reset link</span>
            <svg id="btnSpinner" class="hidden w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        </button>

    </form>

    <p class="text-center mt-5">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-800 font-500 hover:underline">
            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd"/></svg>
            Back to sign in
        </a>
    </p>

</div>

<script>
document.getElementById('forgotForm').addEventListener('submit', function() {
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('btnText').textContent = 'Sending…';
    document.getElementById('btnSpinner').classList.remove('hidden');
});
</script>

</x-layouts.auth>


