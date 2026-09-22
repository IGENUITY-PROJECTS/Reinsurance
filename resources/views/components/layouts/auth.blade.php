<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — {{ $title ?? 'Authentication' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/cedant.js'])
    @livewireStyles
</head>
<body class="min-h-screen flex font-body antialiased bg-slate-50">

    {{-- Left brand panel — hidden on mobile --}}
    <aside class="hidden lg:flex lg:w-[42%] min-h-screen flex-col p-10 bg-gradient-to-br from-brand-900 via-brand-700 to-brand-500 text-white relative overflow-hidden">

        {{-- Subtle geometric decoration --}}
        <div class="absolute inset-0 opacity-5 pointer-events-none"
             style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 32px 32px;"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-white opacity-5 pointer-events-none"></div>

        {{-- Logo --}}
        <div class="relative flex items-center gap-3">
            <div class="w-9 h-9 shrink-0">
                <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 4L36 13V27L20 36L4 27V13L20 4Z" fill="white" fill-opacity="0.15"/>
                    <path d="M20 4L36 13V27L20 36L4 27V13L20 4Z" stroke="white" stroke-width="1.5"/>
                    <path d="M20 12L28 17V23L20 28L12 23V17L20 12Z" fill="white" fill-opacity="0.35"/>
                    <circle cx="20" cy="20" r="3" fill="white"/>
                </svg>
            </div>
            <div class="leading-tight">
                <p class="font-display font-700 text-base tracking-tight">Afro-Asian</p>
                <p class="text-[0.7rem] text-white/60 uppercase tracking-widest font-300">Reinsurance Brokerage</p>
            </div>
        </div>

        {{-- Tagline --}}
        <div class="relative mt-auto mb-10">
            <h2 class="font-display font-600 text-4xl leading-tight tracking-tight mb-4">
                Your cover.<br>Your account.<br>Your portal.
            </h2>
            <p class="text-white/65 text-sm leading-relaxed max-w-xs">
                A simpler way to stay connected with Afro-Asian Reinsurance.
            </p>
        </div>

        {{-- Features --}}
        <ul class="relative space-y-3.5">
            @foreach(['Policies & covers', 'Statements of account', 'Claims', 'Help & feedback'] as $feature)
            <li class="flex items-center gap-3 text-sm text-white/80">
                <span class="flex items-center justify-center w-5 h-5 rounded-full bg-white/15 shrink-0">
                    <svg class="w-2.5 h-2.5 text-amber-300" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/>
                    </svg>
                </span>
                {{ $feature }}
            </li>
            @endforeach
        </ul>

        <p class="relative mt-10 text-xs text-white/30">&copy; {{ date('Y') }} Afro-Asian Reinsurance Brokerage</p>
    </aside>

    {{-- Right: auth card area --}}
    <main class="flex-1 flex items-center justify-center px-5 py-10 min-h-screen">
        <div class="w-full max-w-sm">

            {{-- Mobile logo --}}
            <div class="flex items-center justify-center gap-2.5 mb-8 lg:hidden">
                <div class="w-8 h-8 text-brand-600">
                    <svg viewBox="0 0 40 40" fill="none">
                        <path d="M20 4L36 13V27L20 36L4 27V13L20 4Z" fill="currentColor" fill-opacity="0.15"/>
                        <path d="M20 4L36 13V27L20 36L4 27V13L20 4Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M20 12L28 17V23L20 28L12 23V17L20 12Z" fill="currentColor" fill-opacity="0.4"/>
                        <circle cx="20" cy="20" r="3" fill="currentColor"/>
                    </svg>
                </div>
                <span class="font-display font-600 text-brand-800">Afro-Asian Reinsurance</span>
            </div>

            {{ $slot }}

        </div>
    </main>

    @livewireScripts
</body>
</html>

