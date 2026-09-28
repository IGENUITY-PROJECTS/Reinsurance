<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Workspace' }} - {{ config('branding.company_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/cedant.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100 text-slate-950 antialiased">
    <div class="flex min-h-screen flex-col lg:flex-row">
        <x-dashboard.sidebar :section="$section ?? null" />

        <div class="flex min-w-0 flex-1 flex-col">
            <x-dashboard.header :title="$title ?? 'Dashboard'" />

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>

            <x-dashboard.footer />
        </div>
    </div>
    @livewireScripts
</body>
</html>




