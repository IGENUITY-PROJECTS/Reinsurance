@component($layout, ['title' => $conversation->subject, 'section' => 'help'])
<div class="mx-auto max-w-4xl space-y-6"><a href="{{ route($prefix.'.help') }}" class="text-brand-700">&larr; Help & Feedback</a><h1 class="break-words text-2xl font-semibold">{{ $conversation->subject }}</h1>
@if($broker)<p class="text-sm text-slate-500">{{ $conversation->creator?->name ?? 'User' }} · {{ $conversation->company?->CompanyName ?? 'No company linked' }}</p>@endif
@if(session('status'))<p role="status" class="rounded-xl bg-brand-50 p-4 text-brand-800">{{ session('status') }}</p>@endif
<div class="space-y-2 rounded-2xl border bg-white p-5 text-sm"><p><strong>Topic:</strong> {{ \App\Services\FeedbackRouting::TOPICS[$conversation->topic] ?? 'General enquiry' }}</p>
@if($conversation->statement_kind)<p><strong>Statement:</strong> {{ $conversation->statement_kind === 'claims' ? 'Claims receivable' : 'Premiums payable' }}</p>@endif
@if($conversation->statement_document_no)<p><strong>Document:</strong> {{ $conversation->statement_document_no }}</p>@endif
@if($conversation->period_from)<p><strong>Period:</strong> {{ $conversation->period_from->format('Y-m-d') }} to {{ $conversation->period_to?->format('Y-m-d') }}</p>@endif
@if($conversation->statement_currency)<p><strong>Currency:</strong> {{ $conversation->statement_currency }}</p>@endif
@if($conversation->statement_search)<p><strong>Statement search:</strong> {{ $conversation->statement_search }}</p>@endif
@if($broker)<p><strong>Department:</strong> {{ $conversation->department?->name ?? 'Unassigned' }}</p>@if($conversation->needs_assignment)<p class="font-semibold text-amber-700">Needs attention: no verified, approved broker staff assigned to this department.</p>@endif @endif
</div>
@if($broker && auth()->user()->hasRole('super-admin'))<form method="POST" action="{{ route('admin.help.assign', $conversation->reference) }}" class="flex flex-wrap items-center gap-3 rounded-xl border bg-white p-4">@csrf<label for="department">Assign department</label><select id="department" name="department_id" required class="rounded-lg border p-3">@foreach($departments as $department)<option value="{{ $department->id }}" @selected((int)$conversation->department_id === $department->id)>{{ $department->name }}</option>@endforeach</select><button class="rounded-lg border px-4 py-3 text-brand-700">Save assignment</button></form>@endif
<section aria-label="Conversation" class="space-y-4">@foreach($messages as $message)<article class="rounded-2xl border bg-white p-5"><p class="text-xs text-slate-500">{{ $message->author?->name ?? 'User' }} · {{ $message->created_at?->format('Y-m-d H:i') }}</p><p class="mt-3 whitespace-pre-line break-words">{{ $message->message }}</p></article>@endforeach</section>{{ $messages->links() }}
@if(!$broker || auth()->user()->hasAnyRole(['admin', 'super-admin']))
<form method="POST" action="{{ route($prefix.'.help.reply', $conversation->reference) }}" class="space-y-4 rounded-2xl border bg-white p-5">@csrf<label for="reply" class="block font-semibold">{{ $broker ? 'Reply to the cedant' : 'Send a follow-up' }}</label><textarea id="reply" name="message" rows="4" maxlength="5000" required class="block w-full rounded-lg border p-3">{{ old('message') }}</textarea>@error('message')<p class="text-sm text-red-700">{{ $message }}</p>@enderror<button class="rounded-xl bg-brand-700 px-5 py-3 font-semibold text-white">Send reply</button></form>
@endif</div>
@endcomponent
