@component($layout, ['title' => 'Premium Adjustment '.$adjustment->DocumentNo])
<div class="mx-auto max-w-5xl space-y-6"><a href="{{ route($prefix.'.adjustments') }}" class="text-brand-700">&larr; Premium Adjustments</a>
<div><p class="text-sm font-semibold text-brand-700">Premium Adjustment</p><h1 class="mt-2 text-2xl font-semibold">{{ $adjustment->DocumentNo }}</h1><p class="mt-2 text-slate-500">Cover {{ $adjustment->CoverNo }} · {{ $adjustment->Cedant }}</p></div>
@foreach([
    'Document Details' => ['RskNo'=>'Risk Number','CoverNo'=>'Cover','CoverNoDesc'=>'Cover Description','DocumentDate'=>'Document Date','CedantAccountNo'=>'Cedant Account','Cedant'=>'Cedant','DocCurrency'=>'Currency','AccountType'=>'Account Type Code','AccountTypeDesc'=>'Account Type','ItemNo'=>'Item Number','ItemDesc'=>'Item Description'],
    'Premium And Calculation' => ['invoice_amount'=>'Amount','MinDepositPremium'=>'Total Deposit','PremiumAdjRate'=>'Adjustment Rate','MinCost'=>'Minimum Burning Deposit','MaxCost'=>'Maximum Burning Deposit','LoadingFactor'=>'Loading Factor','LoadingFactorDiv'=>'Loading Factor Divisor','EffectiveRate'=>'Effective Rate'],
] as $heading => $fields)
<section class="rounded-2xl border bg-white p-5 sm:p-6"><h2 class="text-lg font-semibold">{{ $heading }}</h2><dl class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">@foreach($fields as $field => $label)
@php($value = $field === 'invoice_amount' ? ($adjustment->invoice_amount !== null ? number_format($adjustment->invoice_amount, 2) : 'Not available') : $adjustment->$field)
<div><dt class="text-sm text-slate-500">{{ $label }}</dt><dd class="mt-1 break-words font-medium">{{ $value instanceof \DateTimeInterface ? $value->format('d M Y H:i') : ($value ?? '—') }}</dd></div>
@endforeach</dl></section>@endforeach
@if($adjustment->Remarks)<section class="rounded-2xl border bg-white p-6"><h2 class="font-semibold">Remarks</h2><p class="mt-3 whitespace-pre-line">{{ $adjustment->Remarks }}</p></section>@endif
</div>@endcomponent
