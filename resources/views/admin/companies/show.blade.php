@component('layouts.dashboard', ['title' => $company->CompanyName ?: $company->CmpCode, 'section' => 'companies'])
<div class="mx-auto max-w-5xl space-y-6">
    <a href="{{ route('admin.companies.index') }}" class="text-sm font-semibold text-brand-700">&larr; Companies</a>
    <header><p class="text-sm font-semibold text-brand-700">{{ $company->CmpCode }}</p><h1 class="mt-2 break-words text-3xl font-semibold">{{ $company->CompanyName }}</h1></header>
    @foreach([
        'Company Details' => ['CmpCode' => 'Company Code', 'CompanyName' => 'Company Name', 'ShortName' => 'Short Name', 'CmpSubTypeDesc' => 'Company Category'],
        'Contact Details' => ['EmailAdd' => 'Company Email', 'Website' => 'Website', 'PostalAdd' => 'Postal Address', 'MTown' => 'Town / City'],
        'Location' => ['CountryName' => 'Country', 'CountryCode' => 'Country Code', 'MktZoneDesc' => 'Market Zone'],
    ] as $heading => $fields)
    <section class="rounded-2xl border bg-white p-5 sm:p-6"><h2 class="text-lg font-semibold">{{ $heading }}</h2><dl class="mt-5 grid gap-5 sm:grid-cols-2">@foreach($fields as $field => $label)<div class="min-w-0"><dt class="text-sm text-slate-500">{{ $label }}</dt><dd class="mt-1 whitespace-pre-line break-words font-medium">{{ filled($company->$field) ? $company->$field : 'Not Available' }}</dd></div>@endforeach</dl></section>
    @endforeach
    @if($company->CmpSubTypeCode === '100')<nav aria-label="Company Records" class="flex flex-wrap gap-3"><a href="{{ route('admin.policies', ['company' => $company->CmpCode]) }}" class="rounded-xl bg-brand-700 px-5 py-3 font-semibold text-white">View Covers</a><a href="{{ route('admin.statements', ['company' => $company->CmpCode]) }}" class="rounded-xl border bg-white px-5 py-3 font-semibold text-brand-700">View Statements</a></nav>@endif
</div>
@endcomponent
