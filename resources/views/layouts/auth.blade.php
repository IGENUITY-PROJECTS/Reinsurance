<x-layouts.auth :title="$title ?? 'Authentication'">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
        <h1 class="font-display font-semibold text-2xl text-slate-900 mb-6">{{ $heading ?? 'Access your workspace' }}</h1>
        {{ $slot }}
    </div>
</x-layouts.auth>
