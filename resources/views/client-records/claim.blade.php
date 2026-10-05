@component($layout, ['title' => $claim->ClaimNo])
<div class="space-y-6"><a href="{{ route($prefix.'.claims') }}" class="text-brand-700">&larr; Claims</a><h1 class="text-2xl font-semibold">RBS claim {{ $claim->ClaimNo }}</h1>
<div class="rounded-2xl border bg-white p-6"><dl class="grid gap-4 sm:grid-cols-2">
@foreach(['Your reference' => $claim->OrigClaimNo, 'Cover' => $claim->CoverNo, 'Insured' => $claim->InsuredName, 'RBS status' => $claim->MStatusDesc ?: ($claim->MStatusCode ?: 'Not available'), 'Date of loss' => $claim->DateLoss, 'Currency' => $claim->ClaimCurrencyCode, 'Claim amount' => number_format($claim->ClaimAmt ?? 0, 2), 'Loss reserve' => number_format($claim->LossReserve ?? 0, 2)] as $label => $value)<div><dt class="text-sm text-slate-500">{{ $label }}</dt><dd class="mt-1">{{ $value ?? '—' }}</dd></div>@endforeach</dl><p class="mt-5 whitespace-pre-line">{{ $claim->LossDetails }}</p></div></div>
@endcomponent
