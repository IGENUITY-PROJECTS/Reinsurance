@component($layout, ['title' => $submission->submission_reference])
<div class="mx-auto max-w-4xl space-y-6"><a href="{{ route($prefix.'.'.($isClaim ? 'claims' : 'adjustments')) }}" class="text-brand-700">&larr; {{ $isClaim ? 'Claims' : 'Premium Adjustments' }}</a>
@if(session('status'))<p role="status" class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">{{ session('status') }}</p>@endif
<h1 class="text-2xl font-semibold">{{ $isClaim ? 'Claim' : 'Premium adjustment' }} · {{ $isClaim ? $submission->OrigClaimNo : $submission->CoverNo }}</h1>
{{-- Deferred: 'Portal status' => str($submission->portal_status)->replace('_', ' ')->title() --}}
<div class="rounded-2xl border bg-white p-6"><dl class="grid gap-4 sm:grid-cols-2">
@foreach(['Company' => $submission->company?->CompanyName ?? $submission->company_code, 'Cover' => $submission->CoverNo, 'Status' => $isClaim ? ($official ? ($official->MStatusDesc ?: ($official->MStatusCode ?: 'Not available')) : 'Submitted') : 'Not available', 'Submitted' => $submission->submitted_at?->format('Y-m-d H:i')] as $label => $value)<div><dt class="text-sm text-slate-500">{{ $label }}</dt><dd class="mt-1 break-words">{{ $value ?? '—' }}</dd></div>@endforeach</dl>
@if($isClaim)<p class="mt-4">Insured: {{ $submission->InsuredName }}</p>{{-- Deferred: submitted amounts are no longer collected. <p class="mt-2">Claim amount: {{ $submission->ClaimCurrencyCode }} {{ number_format($submission->ClaimAmt ?? 0, 2) }}</p> --}}@endif
<p class="mt-4 whitespace-pre-line">{{ $isClaim ? $submission->LossDetails : $submission->details }}</p></div>
@if($official)<div class="rounded-2xl border bg-brand-50 p-5"><p class="font-semibold">Claim number: <a class="underline" href="{{ route($prefix.'.rbs-claims.show', $official->ClaimNo) }}">{{ $official->ClaimNo }}</a></p><p class="mt-2">Status: {{ $official->MStatusDesc ?: ($official->MStatusCode ?: 'Not available') }}</p><p class="mt-2">Claim amount: @if($official->ClaimAmt !== null){{ $official->ClaimCurrencyCode }} {{ number_format($official->ClaimAmt, 2) }}@else Not available @endif</p></div>@elseif($isClaim)<p class="text-sm text-slate-500">Your claim has been submitted. Further details will appear when available.</p>@endif
<h2 class="text-lg font-semibold">Documents</h2><ul class="space-y-2">@forelse($documents as $document)<li class="rounded-xl border bg-white p-4"><a class="text-brand-700 underline" href="{{ route($prefix.'.documents.download', ['kind' => $isClaim ? 'claims' : 'adjustments', 'id' => $document->id]) }}">{{ $document->original_name }}</a></li>@empty<li>No documents uploaded.</li>@endforelse</ul>{{ $documents->links() }}
{{-- Portal status deferred; use the mirrored RBS claim status.
@if($isClaim)<h2 class="text-lg font-semibold">Review history</h2><ol class="space-y-3">@forelse($history as $entry)<li class="rounded-xl border bg-white p-4"><p class="font-medium">{{ str($entry->to_status)->replace('_',' ')->title() }}</p><p class="text-xs text-slate-500">{{ $entry->changed_at?->format('Y-m-d H:i') }} · {{ $entry->changedBy?->name ?? 'System' }}</p><p class="mt-2">{{ $entry->remarks }}</p></li>@empty<li>No review updates yet.</li>@endforelse</ol>{{ $history->links() }}@endif
--}}




@include('client-records.adjustment-records')
@if($errors->any())<div role="alert" class="rounded-xl bg-red-50 p-4 text-red-800">{{ $errors->first() }}</div>@endif
{{-- Portal status deferred; use the mirrored RBS claim status.
@if($broker && auth()->user()->hasAnyRole(['admin', 'super-admin']) && $submission->portal_status !== 'draft')
<form method="POST" action="{{ route('admin.submissions.review', $submission->submission_reference) }}" class="space-y-4 rounded-2xl border bg-white p-5 sm:p-6">
@csrf<input type="hidden" name="previous_status" value="{{ $submission->portal_status }}">
<h2 class="text-lg font-semibold">Review submission</h2><p class="text-sm text-slate-500">Update the status so the cedant can track progress. The official Status is shown separately.</p>
<label class="block text-sm font-medium">Review status<select name="portal_status" required class="mt-2 block w-full rounded-lg border p-3"><option value="">Choose a status</option>@foreach(['under_review' => 'Under review', 'awaiting_documents' => 'Awaiting documents', 'accepted' => 'Accepted', 'rejected' => 'Rejected'] as $value => $label)<option value="{{ $value }}" @selected(old('portal_status', $submission->portal_status) === $value)>{{ $label }}</option>@endforeach</select></label>
<button class="rounded-xl bg-brand-700 px-5 py-3 font-semibold text-white">Save review</button></form>
@endif
--}}
@if(!$broker || auth()->user()->hasAnyRole(['admin', 'super-admin']))


<form method="POST" enctype="multipart/form-data" action="{{ route($prefix.'.submissions.documents', $submission->submission_reference) }}" class="space-y-4 rounded-2xl border bg-white p-5 sm:p-6">@csrf
<h2 class="text-lg font-semibold">Add supporting documents</h2><label class="block text-sm font-medium">Files<input type="file" name="documents[]" required multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.txt" class="mt-2 block w-full rounded-lg border p-3"></label><p class="text-xs text-slate-500">Up to 10 files, 10 MB each. PDF, images, Word, Excel or text files.</p><button class="rounded-xl bg-brand-700 px-5 py-3 font-semibold text-white">Upload documents</button></form>


@endif

</div>@endcomponent
