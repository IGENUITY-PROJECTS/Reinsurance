@props(['variant' => 'light', 'showText' => true])
@php
    $dark = $variant === 'dark';
    $company = config('branding.company_name');
    $initials = collect(preg_split('/[\s-]+/u', trim($company), -1, PREG_SPLIT_NO_EMPTY))->take(2)->map(fn ($word) => mb_substr($word, 0, 1))->implode('');
@endphp
<div {{ $attributes->merge(['class' => 'flex min-w-0 items-center gap-3']) }}>
    @if(config('branding.logo_url'))
        <img src="{{ config('branding.logo_url') }}" alt="{{ $company }} logo" class="h-10 w-10 shrink-0 rounded-lg object-contain">
    @else
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-sm font-bold {{ $dark ? 'bg-white text-brand-700' : 'bg-brand-700 text-white' }}">{{ mb_strtoupper($initials) }}</span>
    @endif
    @if($showText)
        <div class="min-w-0 leading-tight">
            <p class="break-words text-base font-semibold tracking-tight {{ $dark ? 'text-white' : 'text-brand-950' }}">{{ $company }}</p>
            <p class="mt-1 text-xs {{ $dark ? 'text-brand-200' : 'text-slate-500' }}">{{ config('branding.tagline') }}</p>
        </div>
    @endif
</div>

