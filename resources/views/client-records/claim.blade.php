@component($layout, ['title' => $claim->ClaimNo])
<div class="space-y-6"><a href="{{ route($prefix.'.claims') }}" class="text-brand-700">&larr; Claims</a><h1 class="text-2xl font-semibold">Claim {{ $claim->ClaimNo }}</h1>
<div class="rounded-2xl border bg-white p-6"><dl class="grid gap-4 sm:grid-cols-2">
@foreach(['Your Reference' => $claim->OrigClaimNo, 'Cover' => $claim->CoverNo, 'Insured' => $claim->InsuredName, 'Status' => $claim->MStatusDesc ?: ($claim->MStatusCode ?: 'Not available'), 'Date Of Loss' => \App\Support\DisplayDate::format($claim->DateLoss), 'Currency' => $claim->ClaimCurrencyCode, 'Claim Amount' => number_format($claim->ClaimAmt ?? 0, 2), 'Loss Reserve' => number_format($claim->LossReserve ?? 0, 2)] as $label => $value)<div><dt class="text-sm text-slate-500">{{ $label }}</dt><dd class="mt-1">{{ $value ?? 'Not Available' }}</dd></div>@endforeach</dl><p class="mt-5 whitespace-pre-line">{{ $claim->LossDetails }}</p></div>
@include('client-records.claim-reinsurers', ['allocationClaim' => $claim])
@if($submission)
<div class="space-y-4 rounded-2xl border bg-white p-6"><div class="flex flex-wrap items-center justify-between gap-3"><h2 class="text-lg font-semibold">Submitted Documents</h2><a class="text-brand-700 underline" href="{{ route($prefix.'.submissions.show', $submission->submission_reference) }}">View original submission</a></div>
<ul class="space-y-2">@forelse($documents as $document)<li><a class="text-brand-700 underline" href="{{ route($prefix.'.documents.download', ['kind' => 'claims', 'id' => $document->id]) }}">{{ $document->original_name }}</a></li>@empty<li class="text-sm text-slate-500">No documents uploaded.</li>@endforelse</ul>{{ $documents->links() }}</div>
@endif
</div>
@endcomponent
