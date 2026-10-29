<footer class="border-t border-slate-200 bg-white px-4 py-4 text-sm text-slate-500 sm:px-6 lg:px-8">
    <div class="flex flex-col justify-between gap-2 sm:flex-row">
        <span>&copy; {{ date('Y') }} {{ config('branding.company_name') }}.</span>
        <nav aria-label="Footer Navigation" class="flex flex-wrap gap-4"><a href="{{ route('admin.policies') }}" class="hover:text-brand-700">Covers</a><a href="{{ route('admin.statements') }}" class="hover:text-brand-700">Statements</a><a href="{{ route('admin.help') }}" class="hover:text-brand-700">Help &amp; Feedback</a></nav>
    </div>
</footer>


