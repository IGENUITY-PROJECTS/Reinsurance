<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>{{ $report->title() }}</title>
<style>
@page { margin: 28px 24px 42px; }
body { font-family: DejaVu Sans, sans-serif; color: #243b45; font-size: 8px; }
h1 { font-size: 19px; margin: 0 0 5px; color: #175b56; } h2 { font-size: 12px; margin: 0 0 12px; }
p { margin: 3px 0; } .meta { margin-bottom: 15px; } .logo { max-width: 100px; max-height: 45px; float: right; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; } thead { display: table-header-group; }
th { background: #175b56; color: white; text-align: left; padding: 7px 4px; font-size: 7px; }
td { border-bottom: 1px solid #dde5e7; padding: 6px 4px; vertical-align: top; word-wrap: break-word; }
tr { page-break-inside: avoid; } .number { text-align: right; } .muted { color: #637681; }
.totals { margin-top: 18px; width: 65%; } .note { margin-top: 10px; font-size: 7px; }
.footer { position: fixed; bottom: -25px; left: 0; right: 0; font-size: 7px; color: #637681; }
</style></head>
<body>
@if($logo)<img class="logo" src="{{ $logo }}" alt="Company logo">@endif
<h1>{{ config('branding.company_name') }}</h1><h2>{{ $report->title() }}</h2>
<div class="meta"><p><strong>Cedant:</strong> {{ $report->scopeLabel() }}</p><p><strong>Period:</strong> {{ $report->period() }}</p>
<p><strong>Currency:</strong> {{ $report->filters['currency'] ?? 'All currencies' }} · <strong>Documents:</strong> {{ number_format($records->count()) }}</p>
@if(filled($report->filters['q'] ?? null))<p><strong>Search:</strong> {{ $report->filters['q'] }}</p>@endif
<p class="muted">Generated {{ $generatedAt->format('Y-m-d H:i T') }}</p></div>
<table><thead><tr>@foreach($report->headings() as $heading)<th>{{ $heading }}</th>@endforeach</tr></thead>
<tbody>@foreach($records as $record)<tr>@foreach($report->row($record) as $i => $value)
@php($numeric = in_array($i, $report->kind === 'claims' ? [10, 11, 12] : [7, 8, 9]))
<td @class(['number' => $numeric])>{{ $numeric && $value !== null ? number_format($value, 2) : ($value ?? '—') }}</td>
@endforeach</tr>@endforeach</tbody></table>
<table class="totals"><thead><tr><th>Currency</th><th>Documents</th><th>Amount</th><th>Settled</th><th>Outstanding</th></tr></thead><tbody>
@foreach($totals as $total)<tr><td>{{ $total->CurrencyCode ?: 'Unspecified' }}</td><td>{{ $total->document_count }}</td><td class="number">{{ number_format($total->amount ?? 0, 2) }}</td><td class="number">{{ number_format($total->settled ?? 0, 2) }}</td><td class="number">{{ number_format($total->pending ?? 0, 2) }}</td></tr>@endforeach
</tbody></table><p class="note muted">Balances as currently recorded. Reversed documents included.</p>
<div class="footer">{{ config('branding.company_name') }} · {{ $report->period() }}</div>

</body></html>
