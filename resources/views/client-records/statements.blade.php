@component($layout, ['title' => 'Statements'])
<div class="space-y-6">
    <div><h1 class="text-2xl font-semibold">Statements</h1><p class="mt-1 text-sm text-slate-500">{{ $report->title() }}</p><p class="mt-2 text-xs text-slate-500">Fully settled documents are excluded.</p></div>
    <div class="flex flex-wrap gap-3">
        @foreach($report->kinds() as $kind => $label)
            <a class="rounded-xl border bg-white px-4 py-3 {{ $report->kind === $kind ? 'border-brand-700 font-semibold text-brand-700' : 'text-slate-600' }}" href="{{ route($prefix.'.statements', array_merge(request()->only(['from', 'to', 'company', 'currency']), ['kind' => $kind])) }}">{{ $label }}</a>
        @endforeach
    </div>
    @if($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <form method="GET" action="{{ route($prefix.'.statements') }}" class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:grid-cols-2 lg:grid-cols-3">
        <input type="hidden" name="kind" value="{{ $report->kind }}">
        <label class="text-sm font-medium">From date<input type="date" name="from" value="{{ old('from', request('from')) }}" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></label>
        <label class="text-sm font-medium">To date<input type="date" name="to" value="{{ old('to', request('to')) }}" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></label>
        @if($broker)
            <label class="text-sm font-medium">Cedant<select name="company" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"><option value="">All cedants</option>@foreach($companies as $company)<option value="{{ $company->CedantCode }}" @selected(request('company') === $company->CedantCode)>{{ $company->company_name ?: $company->CedantCode }} ({{ $company->CedantCode }})</option>@endforeach</select></label>
        @endif
        <label class="text-sm font-medium">Currency<select name="currency" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"><option value="">All currencies</option>@foreach($currencies as $currency)<option value="{{ $currency }}" @selected(request('currency') === $currency)>{{ $currency }}</option>@endforeach</select></label>
        <label class="text-sm font-medium">Search<input name="q" value="{{ request('q') }}" maxlength="150" placeholder="Document, reference or insured" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></label>
        <div class="flex items-end gap-3"><button type="submit" class="rounded-lg bg-brand-700 px-5 py-3 font-semibold text-white">Apply filters</button><a href="{{ route($prefix.'.statements', ['kind' => $report->kind]) }}" class="rounded-lg border px-4 py-3 text-sm">Reset</a></div>
    </form>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="text-sm text-slate-600"><p class="font-semibold">{{ number_format($records->total()) }} {{ Str::plural('document', $records->total()) }}</p><p>{{ $report->period() }}</p></div>
        <div class="flex flex-wrap gap-3">
            @if(!$broker && $report->supportsFeedbackContext())<a class="rounded-lg border px-4 py-3 text-sm text-brand-700" href="{{ route('client.help.create', array_merge(request()->only(['from', 'to', 'currency', 'q']), ['topic' => $report->kind])) }}">Ask about this statement</a>@endif
            @php($canDownload = $records->total() > 0 && filled($report->filters['from'] ?? null) && filled($report->filters['to'] ?? null))
            @foreach(['xlsx' => 'Download Excel', 'pdf' => 'Download PDF'] as $format => $label)
                @if($canDownload)<a class="rounded-lg border border-brand-700 bg-white px-4 py-3 text-sm font-semibold text-brand-700" href="{{ route($prefix.'.statements.download', array_merge($report->filters, ['format' => $format])) }}">{{ $label }}</a>
                @else<button disabled class="cursor-not-allowed rounded-lg border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-400">{{ $label }}</button>@endif
            @endforeach
        </div>
    </div>
    @if(!filled($report->filters['from'] ?? null))<p class="text-sm text-slate-500">Select a start and end date to download a statement.</p>@endif
    @if($records->total() === 0)
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500" role="status">No records found for the selected period.</div>
    @else
        <x-record-table :headers="($broker || !$report->supportsFeedbackContext()) ? $report->headings() : array_merge($report->headings(), ['Help'])" :rows="$records->map(function ($record) use ($report, $broker) { $row = $report->row($record); $offset = $report->amountOffset(); foreach ([$offset, $offset + 1, $offset + 2] as $i) { $row[$i] = $row[$i] === null ? '—' : number_format($row[$i], 2); } if (!$broker && $report->supportsFeedbackContext()) $row[] = ['label' => 'Ask a question', 'url' => route('client.help.create', array_merge(request()->only(['from', 'to', 'currency', 'q']), ['topic' => $report->kind, 'statement_document_id' => $record->id]))]; return $row; })" />
        {{ $records->links() }}
        <div class="space-y-3"><h2 class="text-lg font-semibold">Totals by currency</h2>
            <x-record-table :headers="['Currency', 'Documents', 'Amount', 'Settled', 'Outstanding']" :rows="$totals->map(fn ($total) => [$total->CurrencyCode ?: 'Unspecified', number_format($total->document_count), number_format($total->amount ?? 0, 2), number_format($total->settled ?? 0, 2), number_format($total->pending ?? 0, 2)])" />
            <p class="text-xs text-slate-500">Balances as currently recorded.</p>
        </div>
    @endif
</div>
@endcomponent
