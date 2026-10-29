@component('layouts.dashboard', ['title' => 'Companies', 'section' => 'companies'])
<div class="mx-auto max-w-7xl space-y-6">
    <header><h1 class="text-3xl font-semibold tracking-tight">Companies</h1><p class="mt-2 text-sm text-slate-500">Find company contact details and account information.</p></header>
    @if($errors->any())<p role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</p>@endif
    <form method="GET" action="{{ route('admin.companies.index') }}" class="grid gap-4 rounded-2xl border bg-white p-5 sm:grid-cols-2 lg:grid-cols-3">
        <label class="text-sm font-medium">Search Companies<input name="q" value="{{ request('q') }}" maxlength="150" placeholder="Company name, code, email or country" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></label>
        <label class="text-sm font-medium">Company Category<select name="category" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"><option value="">All Companies</option>@foreach($categories as $category)<option value="{{ $category->CmpSubTypeCode }}" @selected(request('category') === $category->CmpSubTypeCode)>{{ $category->description ?: $category->CmpSubTypeCode }}</option>@endforeach</select></label>
        <div class="flex items-end gap-3"><button class="rounded-lg bg-brand-700 px-5 py-3 font-semibold text-white">Search</button><a href="{{ route('admin.companies.index') }}" class="rounded-lg border px-4 py-3 text-sm">Reset</a></div>
    </form>
    <p class="text-sm text-slate-500">{{ number_format($companies->total()) }} {{ Str::plural('Company', $companies->total()) }}</p>
    <x-record-table :headers="['Company Code', 'Company Name', 'Category', 'Country', 'Email', 'Action']" :rows="$companies->map(fn ($company) => [$company->CmpCode, $company->CompanyName, $company->CmpSubTypeDesc, $company->CountryName, $company->EmailAdd, ['label' => 'View', 'url' => route('admin.companies.show', $company->CmpCode)]])" />
    {{ $companies->links() }}
</div>
@endcomponent
