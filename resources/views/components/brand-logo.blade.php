@props([
    'variant' => 'light',
    'showText' => true,
])

@php
    $iconClass = $variant === 'dark'
        ? 'bg-white text-brand-700 ring-white/20'
        : 'bg-brand-700 text-white ring-brand-200';

    $textClass = $variant === 'dark'
        ? 'text-white'
        : 'text-brand-950';

    $subTextClass = $variant === 'dark'
        ? 'text-brand-200'
        : 'text-slate-500';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <div class="flex h-10 w-10 items-center justify-center rounded-lg shadow-sm ring-1 {{ $iconClass }}">
        <span class="text-sm font-bold">AA</span>
    </div>

    @if ($showText)
        <div class="leading-tight">
            <p class="text-base font-semibold tracking-tight {{ $textClass }}">Afro-Asian Reinsurance</p>
            <p class="text-xs font-medium {{ $subTextClass }}">Reinsurance Brokerage</p>
        </div>
    @endif
</div>

