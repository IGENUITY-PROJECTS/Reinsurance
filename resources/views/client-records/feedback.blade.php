@component($layout, ['title' => 'Help & Feedback', 'section' => 'help'])
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3"><div><h1 class="text-2xl font-semibold">Help & Feedback</h1><p class="mt-2 text-sm text-slate-500">{{ $broker ? 'Read general questions and reply to cedants.' : 'Ask a question or share feedback with your broker.' }}</p></div>
    @if(!$broker)<a href="{{ route('client.help.create') }}" class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white">New feedback</a>@endif</div>
    <form method="GET" class="flex gap-3"><input aria-label="Search feedback" name="q" value="{{ request('q') }}" placeholder="Search subjects" class="min-w-0 flex-1 rounded-lg border p-3"><button class="rounded-lg bg-brand-700 px-4 text-white">Search</button></form>
    <p class="text-sm text-slate-500">{{ $conversations->total() }} conversations</p>
    @php
        $headers = $broker ? ['Cedant', 'From', 'Subject', 'Latest message', 'Updated', 'Action'] : ['Subject', 'Latest message', 'Updated', 'Action'];
        $rows = $conversations->map(function ($conversation) use ($prefix, $broker) {
            $row = $broker ? [$conversation->company?->CompanyName ?? 'No company linked', $conversation->creator?->name ?? 'User', $conversation->subject] : [$conversation->subject];
            return array_merge($row, [str($conversation->latestMessage?->message ?? '')->limit(100)->toString(), $conversation->updated_at?->format('Y-m-d H:i'), ['label' => 'View', 'url' => route($prefix.'.help.show', $conversation->reference)]]);
        });
    @endphp
    <x-record-table :headers="$headers" :rows="$rows" />
    {{ $conversations->links() }}
</div>
@endcomponent
