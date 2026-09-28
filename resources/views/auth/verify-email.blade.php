<x-layouts.auth title="Verify Email">

<div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100 p-8 text-center">

    <div class="w-14 h-14 rounded-full bg-brand-50 flex items-center justify-center mx-auto mb-5">
        <svg class="w-7 h-7 text-brand-600" viewBox="0 0 20 20" fill="currentColor">
            <path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z"/>
            <path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z"/>
        </svg>
    </div>

    <h1 class="font-display font-600 text-2xl text-slate-900 tracking-tight mb-2">Check your email</h1>
    <p class="text-sm text-slate-500 leading-relaxed mb-6">
        Thanks for registering. Please verify your email address by clicking the link we sent you before signing in.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="flex gap-2.5 items-start bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3 mb-5 text-left">
            <svg class="w-4 h-4 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
            A new verification link has been sent to your email address.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit"
            class="w-full flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-500 py-2.5 px-4 rounded-lg transition-all hover:shadow-lg hover:shadow-brand-600/25 active:scale-[0.99]">
            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M3.105 2.289a.75.75 0 00-.826.95l1.414 4.925A1.5 1.5 0 005.135 9.25h6.115a.75.75 0 010 1.5H5.135a1.5 1.5 0 00-1.442 1.086l-1.414 4.926a.75.75 0 00.826.95 28.896 28.896 0 0015.293-7.154.75.75 0 000-1.115A28.897 28.897 0 003.105 2.289z"/></svg>
            Resend verification email
        </button>
    </form>

    <div class="flex items-center gap-3 my-5">
        <div class="flex-1 h-px bg-slate-100"></div>
        <span class="text-xs text-slate-400">or</span>
        <div class="flex-1 h-px bg-slate-100"></div>
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="text-sm text-slate-500 hover:text-brand-600 font-500 hover:underline transition-colors">
            Sign out and use a different account
        </button>
    </form>

</div>

</x-layouts.auth>


