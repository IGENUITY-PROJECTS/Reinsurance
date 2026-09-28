@component('layouts.cedant', ['title' => 'New submission'])
<div class="mx-auto max-w-2xl"><a href="{{ route('client.submissions') }}" class="text-sm text-brand-700">&larr; All submissions</a><h1 class="mt-5 text-2xl font-semibold">New submission</h1><p class="mt-2 text-sm text-slate-500">Demo only. Files stay on your device; nothing is saved or sent.</p>
<form x-data="{ done: false }" x-on:submit.prevent="done = true" class="mt-6 space-y-5 rounded-2xl border border-slate-200 bg-white p-6">
    <label class="block text-sm font-medium">Submission type<select required class="mt-2 block w-full rounded-lg border border-slate-300 p-3"><option>Claim</option><option>Premium adjustment</option><option>Profit commission</option><option>Help & Feedback</option></select></label>
    <label class="block text-sm font-medium">Subject<input required class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></label>
    <label class="block text-sm font-medium">Message<textarea required rows="4" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></textarea></label>
    <label class="block text-sm font-medium">Documents (optional)<input type="file" multiple class="mt-2 block w-full text-sm"></label>
    <button class="rounded-lg bg-brand-700 px-5 py-3 text-sm font-semibold text-white">Preview submission</button><p x-show="done" x-cloak role="status" class="text-sm text-brand-700">Preview complete. Nothing was saved or sent.</p>
</form></div>
@endcomponent
