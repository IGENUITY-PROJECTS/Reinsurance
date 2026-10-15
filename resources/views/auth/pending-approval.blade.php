<x-layouts.auth title="Account access">
<div class="space-y-5 rounded-2xl border bg-white p-8">
<h1 class="text-2xl font-semibold">{{ $user->access_status === 'pending' ? 'Awaiting broker approval' : 'Account access unavailable' }}</h1>
<p class="text-sm text-slate-600">{{ $user->access_status === 'pending' ? 'Your email is verified. Your broker must confirm that you belong to the company before you can access its records.' : 'Please contact your broker for assistance with your account.' }}</p>
@if(session('status'))<p role="status" class="text-sm text-brand-700">{{ session('status') }}</p>@endif
@if($errors->any())<p role="alert" class="text-sm text-red-700">{{ $errors->first() }}</p>@endif
@if($user->access_status === 'pending' && $user->access_expires_at?->isPast())
<p class="text-sm">Your pending place expired. You can request a new place if one is available.</p>
<form method="POST" action="{{ route('account.renew') }}">@csrf<button class="rounded-lg bg-brand-700 px-4 py-3 text-white">Renew approval request</button></form>
@endif
<form method="POST" action="{{ route('logout') }}">@csrf<button class="text-sm text-brand-700 underline">Sign out</button></form>
</div></x-layouts.auth>
