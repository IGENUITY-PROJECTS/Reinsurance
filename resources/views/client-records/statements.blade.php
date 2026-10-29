@component($layout, ['title' => 'Statements'])
<div class="space-y-6">
    <div><h1 class="text-2xl font-semibold">Statements</h1><p class="mt-1 text-sm text-slate-500">{{ $report->brandTitle() }} · {{ $report->title() }}</p></div>
    <div class="flex flex-wrap gap-3">
        @foreach($report->kinds() as $kind => $label)
            <a class="rounded-xl border bg-white px-4 py-3 {{ $report->kind === $kind ? 'border-brand-700 font-semibold text-brand-700' : 'text-slate-600' }}" href="{{ route($prefix.'.statements', array_merge(request()->only(['from', 'to', 'company', 'currency', 'section', 'as_at']), ['kind' => $kind])) }}">{{ $label }}</a>
        @endforeach
    </div>
    @if($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <form method="GET" action="{{ route($prefix.'.statements') }}" class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:grid-cols-2 lg:grid-cols-3">
        <label class="text-sm font-medium">Report Section<select name="section" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"><option value="statement" @selected(($report->filters['section'] ?? 'statement') === 'statement')>Statement</option><option value="ageing" @selected(($report->filters['section'] ?? 'statement') === 'ageing')>Ageing</option></select></label>
        <input type="hidden" name="kind" value="{{ $report->kind }}">
        <label class="text-sm font-medium">From Date (Statement)<input type="date" name="from" value="{{ old('from', request('from')) }}" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></label>
        <label class="text-sm font-medium">To Date (Statement)<input type="date" name="to" value="{{ old('to', request('to')) }}" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></label>
        <label class="text-sm font-medium">As At Date<input type="date" name="as_at" value="{{ request('as_at', now()->format('Y-m-d')) }}" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></label>
        @if($broker)
            <label class="text-sm font-medium">Cedant<select name="company" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"><option value="">All Cedants</option>@foreach($companies as $company)<option value="{{ $company->CedantCode }}" @selected(request('company') === $company->CedantCode)>{{ $company->company_name ?: $company->CedantCode }} ({{ $company->CedantCode }})</option>@endforeach</select></label>
        @endif
        <label class="text-sm font-medium">Currency<select name="currency" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"><option value="">All currencies</option>@foreach($currencies as $currency)<option value="{{ $currency }}" @selected(request('currency') === $currency)>{{ $currency }}</option>@endforeach</select></label>
        <label class="text-sm font-medium">Search<input name="q" value="{{ request('q') }}" maxlength="150" placeholder="Document, reference or insured" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></label>
        <div class="flex items-end gap-3"><button type="submit" class="rounded-lg bg-brand-700 px-5 py-3 font-semibold text-white">Apply Filters</button><a href="{{ route($prefix.'.statements', ['kind' => $report->kind, 'section' => $report->filters['section'] ?? 'statement']) }}" class="rounded-lg border px-4 py-3 text-sm">Reset</a></div>
    </form>
    @if($broker)
    @php($checks = $report->dataChecks())
    <details class="rounded-xl border bg-white p-4 text-sm"><summary class="cursor-pointer font-semibold">Statement Data Checks</summary><p class="mt-2">{{ number_format($checks->source_rows) }} Mirror Rows · {{ number_format($checks->settled_rows ?? 0) }} Fully Settled · {{ number_format($checks->missing_dates ?? 0) }} Missing Document Dates · {{ number_format($checks->missing_balances ?? 0) }} Missing Pending Amounts</p><p class="mt-2 text-slate-500">Counts Cover The Selected Company And Currency Before Date And Search Filters. Missing Dates Cannot Be Assigned To A Period. Export Includes All Matching Pages.</p></details>
    @endif
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="text-sm text-slate-600"><p class="font-semibold">{{ number_format($records->total()) }} {{ Str::plural('document', $records->total()) }}</p><p>{{ $report->period() }}</p></div>
        <div class="flex flex-wrap gap-3">
            @if(!$broker && $report->supportsFeedbackContext())<a class="rounded-lg border px-4 py-3 text-sm text-brand-700" href="{{ route('client.help.create', array_merge(request()->only(['from', 'to', 'currency', 'q']), ['topic' => $report->kind])) }}">Ask about this statement</a>@endif
            @php($canDownload = ($records->total() > 0 || $report->broughtForward()->isNotEmpty()) && ($report->isAgeing() || (filled($report->filters['from'] ?? null) && filled($report->filters['to'] ?? null))))
            @foreach(['xlsx' => 'Download Excel', 'pdf' => 'Download PDF'] as $format => $label)
                @if($canDownload)<a class="rounded-lg border border-brand-700 bg-white px-4 py-3 text-sm font-semibold text-brand-700" href="{{ route($prefix.'.statements.download', array_merge($report->filters, ['format' => $format])) }}">{{ $label }}</a>
                @else<button disabled class="cursor-not-allowed rounded-lg border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-400">{{ $label }}</button>@endif
            @endforeach
        </div>
    </div>
    @if(!$report->isAgeing() && !filled($report->filters['from'] ?? null))<p class="text-sm text-slate-500">Select a start and end date to download a statement.</p>@endif
    @if($report->isAgeing())<p class="text-sm text-slate-500">{{ $report->period() }} · Outstanding Risk Notes Aged From Document Date. Only Non-Reversed Balances Above 0.05 Are Included.</p>@endif
    @unless($report->isAgeing())<p class="text-xs text-slate-500">Outstanding Functional Amount = Pending Amount × Exchange Rate. Missing Values Are Shown As Not Available.</p>@endunless
    @unless($report->isAgeing())@include('reports.brought-forward')@endunless
    @include('reports.balance-summary')
    <h2 class="text-lg font-semibold">{{ $report->sectionLabel() }}</h2>
    @if($records->total() === 0)
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500" role="status">No records found for the selected period.</div>
    @else
        <x-record-table :headers="($broker || !$report->supportsFeedbackContext()) ? $report->headings() : array_merge($report->headings(), ['Help'])" :rows="$records->map(function ($record) use ($report, $broker) { $row = $report->row($record); $offset = $report->amountOffset(); foreach (range($offset, count($row) - 1) as $i) { $row[$i] = $row[$i] === null ? 'Not Available' : number_format($row[$i], !$report->isAgeing() && $i === $report->exchangeRateOffset() ? 8 : 2); } if (!$broker && $report->supportsFeedbackContext()) $row[] = ['label' => 'Ask a question', 'url' => route('client.help.create', array_merge(request()->only(['from', 'to', 'currency', 'q']), ['topic' => $report->kind, 'statement_document_id' => $record->id]))]; return $row; })" />
        {{ $records->links() }}

    @endif
</div>
@endcomponent
