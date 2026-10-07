<section class="mx-auto max-w-5xl space-y-6">
    <h1 class="text-2xl font-semibold tracking-tight text-slate-950">My account</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
        <h2 class="text-lg font-semibold">Your details</h2>
        <dl class="mt-5 grid gap-5 sm:grid-cols-2">
            <div><dt class="text-sm text-slate-500">Name</dt><dd class="mt-1 break-words font-medium">{{ $user->name }}</dd></div>
            <div><dt class="text-sm text-slate-500">Login email</dt><dd class="mt-1 break-words font-medium">{{ $user->email }}</dd></div>
        </dl>
    </div>
    @if($company)
        @foreach([
            'Company details' => ['CompanyName' => 'Company name', 'CmpCode' => 'Company code', 'ShortName' => 'Short name', 'CmpTypeDesc' => 'Company type', 'CmpSubTypeDesc' => 'Company category', 'StartDate' => 'Start date'],
            'Contact details' => ['EmailAdd' => 'Company email', 'Website' => 'Website', 'PostalAdd' => 'Postal address', 'MTown' => 'Town / city', 'MFax' => 'Fax'],
            'Location' => ['CountryName' => 'Country', 'CountryCode' => 'Country code', 'MktZoneDesc' => 'Market zone', 'MktZoneCode' => 'Market zone code', 'ZoneName' => 'Zone', 'ZoneCode' => 'Zone code'],
            'Account details' => ['NatAcNo' => 'Account number', 'NatAcName' => 'Account name', 'PinNo' => 'Tax PIN'],
        ] as $heading => $fields)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                <h2 class="text-lg font-semibold">{{ $heading }}</h2>
                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    @foreach($fields as $field => $label)
                        <div class="min-w-0"><dt class="text-sm text-slate-500">{{ $label }}</dt><dd class="mt-1 whitespace-pre-line break-words font-medium">{{ filled($company->$field) ? $company->$field : '—' }}</dd></div>
                    @endforeach
                </dl>
            </div>
        @endforeach
    @else
        <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Your account has no linked company. Please contact your broker.</p>
    @endif
    <a href="{{ route('client.help') }}" class="inline-block rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white">Contact your broker</a>
</section>
