@component('layouts.cedant', ['title' => $formTitle ?? 'New claim'])
@php
    $module = $module ?? 'claims';
    $formTitle = $formTitle ?? 'New claim';
    $backLabel = $backLabel ?? 'Claims';
@endphp
<div class="mx-auto max-w-2xl">
    <a href="{{ route('client.'.$module) }}" class="text-sm text-brand-700">&larr; {{ $backLabel }}</a>
    <h1 class="mt-5 text-2xl font-semibold">{{ $formTitle }}</h1>
    <p class="mt-2 text-sm text-slate-500">Demo only. Files stay on your device; nothing is saved or sent.</p>
    <form x-data="{ done: false }" x-on:submit.prevent="done = true" class="mt-6 space-y-5 rounded-2xl border border-slate-200 bg-white p-6">
        @if($module === 'claims')
            <label class="block text-sm font-medium">Your claim reference (optional)
                <input name="OrigClaimNo" maxlength="50" class="mt-2 block w-full rounded-lg border border-slate-300 p-3">
                <span class="mt-1 block text-xs font-normal text-slate-500">Leave blank to have a reference generated when your claim is saved. This preview does not save a claim.</span>
            </label>
            <label class="block text-sm font-medium">Cover number
                <input name="CoverNo" required maxlength="50" class="mt-2 block w-full rounded-lg border border-slate-300 p-3">
            </label>
            <label class="block text-sm font-medium">Insured name
                <input name="InsuredName" required maxlength="250" class="mt-2 block w-full rounded-lg border border-slate-300 p-3">
            </label>
            <label class="block text-sm font-medium">Date of loss
                <input name="DateLoss" type="date" required class="mt-2 block w-full rounded-lg border border-slate-300 p-3">
            </label>
            <label class="block text-sm font-medium">Loss details
                <textarea name="LossDetails" required maxlength="2000" rows="4" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></textarea>
            </label>
        @elseif($module === 'adjustments')
            <label class="block text-sm font-medium">Your adjustment reference (optional)
                <input name="adjustment_reference" maxlength="50" class="mt-2 block w-full rounded-lg border border-slate-300 p-3">
            </label>
            <label class="block text-sm font-medium">Cover number
                <input name="CoverNo" required maxlength="50" class="mt-2 block w-full rounded-lg border border-slate-300 p-3">
            </label>
            <label class="block text-sm font-medium">Adjustment details
                <textarea name="details" required rows="4" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></textarea>
            </label>
        @else
            <label class="block text-sm font-medium">{{ $module === 'help' ? 'Subject' : 'Cover reference' }}
                <input name="subject" required class="mt-2 block w-full rounded-lg border border-slate-300 p-3">
            </label>
            <label class="block text-sm font-medium">{{ $module === 'help' ? 'Message' : 'Profit commission details' }}
                <textarea name="message" required rows="4" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></textarea>
            </label>
        @endif
        <label class="block text-sm font-medium">{{ $module === 'claims' ? 'Supporting documents (required)' : 'Documents (optional)' }}
            <input name="documents[]" type="file" multiple @required($module === 'claims') class="mt-2 block w-full text-sm">
        </label>
        <button class="rounded-lg bg-brand-700 px-5 py-3 text-sm font-semibold text-white">Preview submission</button>
        <p x-show="done" x-cloak role="status" class="text-sm text-brand-700">Preview complete. Nothing was saved or sent.</p>
    </form>
</div>
@endcomponent
