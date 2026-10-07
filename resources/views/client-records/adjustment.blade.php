@component($layout, ['title' => 'Premium adjustment '.$adjustment->DocumentNo])
<div class="mx-auto max-w-5xl space-y-6"><a href="{{ route($prefix.'.adjustments') }}" class="text-brand-700">&larr; Premium Adjustments</a>
<p class="text-sm text-slate-500">Status: Not available</p><div><p class="text-sm font-semibold text-brand-700">Premium adjustment</p><h1 class="mt-2 text-2xl font-semibold">{{ $adjustment->DocumentNo }}</h1><p class="mt-2 text-slate-500">Cover {{ $adjustment->CoverNo }} · {{ $adjustment->Cedant }}</p></div>
@foreach([
    'Document details' => ['RskNo'=>'Risk number','CoverNo'=>'Cover','CoverNoDesc'=>'Cover description','DocumentDate'=>'Document date','CedantAccountNo'=>'Cedant account','Cedant'=>'Cedant','DocCurrency'=>'Currency','AccountType'=>'Account type code','AccountTypeDesc'=>'Account type','ItemNo'=>'Item number','ItemDesc'=>'Item description'],
    'Premium and calculation' => ['MinDepositPremium'=>'Minimum deposit premium','PremiumAdjRate'=>'Adjustment rate','NetAccountedPremium'=>'Net accounted premium','PremiumComputed'=>'Computed premium','Claims'=>'Claims','MinCost'=>'Minimum cost','MaxCost'=>'Maximum cost','LoadingFactor'=>'Loading factor','LoadingFactorDiv'=>'Loading factor divisor','EffectiveRate'=>'Effective rate','CedCommRate'=>'Cedant commission rate','PremiumSplit'=>'Premium split'],
] as $heading => $fields)
<section class="rounded-2xl border bg-white p-5 sm:p-6"><h2 class="text-lg font-semibold">{{ $heading }}</h2><dl class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">@foreach($fields as $field => $label)
@php($value = $adjustment->$field)
<div><dt class="text-sm text-slate-500">{{ $label }}</dt><dd class="mt-1 break-words font-medium">{{ $value instanceof \DateTimeInterface ? $value->format('Y-m-d H:i') : ($value ?? '—') }}</dd></div>
@endforeach</dl></section>@endforeach
@if($adjustment->Remarks)<section class="rounded-2xl border bg-white p-6"><h2 class="font-semibold">Remarks</h2><p class="mt-3 whitespace-pre-line">{{ $adjustment->Remarks }}</p></section>@endif
</div>@endcomponent
