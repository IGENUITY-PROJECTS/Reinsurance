@component($layout, ['title' => 'Help & Feedback'])
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Help & Feedback</h1>
        <a class="rounded-lg bg-brand-700 px-4 py-3 text-white" href="{{ route($prefix.'.submissions') }}">Choose a submission</a>
    </div>
    <p class="text-sm text-slate-500">Select View to read the full conversation and {{ $broker ? 'reply to the cedant' : 'send a message to your broker' }}.</p>
    <p class="text-sm text-slate-500">{{ $messages->total() }} messages · {{ $messages->perPage() }} per page</p>
    @php
        $headers = $broker ? ['Cedant', 'Module', 'Claim reference / cover', 'From', 'Message', 'Date', 'Action'] : ['Claim reference / cover', 'Module', 'From', 'Message', 'Date', 'Action'];
        $rows = $messages->map(function ($message) use ($prefix, $broker) {
            $parent = $message->claimSubmission ?? $message->premiumAdjustmentSubmission;
            $reference = $parent->OrigClaimNo ?? $parent->CoverNo;
            $module = $message->claim_submission_id ? 'Claim' : 'Premium adjustment';
            $row = $broker
                ? [$parent->company?->CompanyName ?? $parent->company_code, $module, $reference]
                : [$reference, $module];
            return array_merge($row, [
                $message->author?->name ?? 'User',
                str($message->message)->limit(100)->toString(),
                $message->created_at?->format('Y-m-d H:i'),
                ['label' => 'View', 'url' => route($prefix.'.submissions.show', $parent->submission_reference).'#feedback'],
            ]);
        });
    @endphp
    <x-record-table :headers="$headers" :rows="$rows" />
    {{ $messages->links() }}
</div>
@endcomponent