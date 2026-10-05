<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Home' }} · {{ config('branding.company_name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/cedant.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-50 font-body text-slate-900 antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:block focus:bg-white focus:p-4">Skip to content</a>
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-5 sm:px-6">
            <a href="{{ route('client.dashboard') }}" aria-label="Cedant portal home"><x-brand-logo /></a>
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('client.help') }}" class="rounded-full bg-brand-50 px-4 py-2.5 font-semibold text-brand-700 hover:bg-brand-100">Help</a>
                <a href="{{ route('client.profile.edit') }}" class="rounded-full px-3 py-2.5 hover:bg-slate-100">My account</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-full px-3 py-2.5 text-slate-600 hover:bg-slate-100" type="submit">Sign out</button>
                </form>
            </div>
        </div>
        <nav aria-label="Portal navigation" class="mx-auto flex max-w-6xl flex-wrap gap-1 px-4 pb-3 sm:px-6">
            @foreach(['client.dashboard' => 'Home', 'client.policies' => 'Policies / Covers', 'client.statements' => 'Statements', 'client.claims' => 'Claims', 'client.adjustments' => 'Premium Adjustments', 'client.help' => 'Help & Feedback'] as $route => $label)
                <a href="{{ route($route) }}" @if(request()->routeIs($route)) aria-current="page" @endif class="rounded-lg px-3 py-3 text-sm font-medium {{ request()->routeIs($route) ? 'bg-brand-700 text-white' : 'text-slate-600 hover:bg-brand-50 hover:text-brand-800' }}">{{ $label }}</a>
            @endforeach
        </nav>
    </header>
    <main id="main-content" class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-12">
        {{ $slot }}
    </main>
    <footer class="mx-auto flex max-w-6xl flex-wrap justify-between gap-3 px-4 py-6 text-xs text-slate-500 sm:px-6">
        <span>&copy; {{ date('Y') }} {{ config('branding.company_name') }}</span>
        <span>Cedant portal</span>
    </footer>
    @livewireScripts
</body>
</html>



