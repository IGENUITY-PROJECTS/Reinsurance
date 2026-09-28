<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('branding.company_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-slate-950 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
            <a href="{{ route('home') }}">
                <x-brand-logo />
            </a>

            <nav class="flex items-center gap-3 text-sm">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-md bg-brand-700 px-4 py-2 font-medium text-white shadow-sm hover:bg-brand-800">
                        Workspace
                    </a>
                @else
                    <a href="{{ route('login') }}" class="font-medium text-slate-600 hover:text-brand-700">Sign in</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-brand-700 px-4 py-2 font-medium text-white shadow-sm hover:bg-brand-800">
                        Get access
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    {{ $slot }}
</body>
</html>


