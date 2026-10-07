@component($layout, ['title' => $conversation->subject, 'section' => 'help'])
<div class="mx-auto max-w-4xl space-y-6"><a href="{{ route($prefix.'.help') }}" class="text-brand-700">&larr; Help & Feedback</a><h1 class="break-words text-2xl font-semibold">{{ $conversation->subject }}</h1>
@if($broker)<p class="text-sm text-slate-500">{{ $conversation->creator?->name ?? 'User' }} · {{ $conversation->company?->CompanyName ?? 'No company linked' }}</p>@endif
@if(session('status'))<p role="status" class="rounded-xl bg-brand-50 p-4 text-brand-800">{{ session('status') }}</p>@endif
<section aria-label="Conversation" class="space-y-4">@foreach($messages as $message)<article class="rounded-2xl border bg-white p-5"><p class="text-xs text-slate-500">{{ $message->author?->name ?? 'User' }} · {{ $message->created_at?->format('Y-m-d H:i') }}</p><p class="mt-3 whitespace-pre-line break-words">{{ $message->message }}</p></article>@endforeach</section>{{ $messages->links() }}
@if(!$broker || auth()->user()->hasAnyRole(['admin', 'super-admin']))
<form method="POST" action="{{ route($prefix.'.help.reply', $conversation->reference) }}" class="space-y-4 rounded-2xl border bg-white p-5">@csrf<label for="reply" class="block font-semibold">{{ $broker ? 'Reply to the cedant' : 'Send a follow-up' }}</label><textarea id="reply" name="message" rows="4" maxlength="5000" required class="block w-full rounded-lg border p-3">{{ old('message') }}</textarea>@error('message')<p class="text-sm text-red-700">{{ $message }}</p>@enderror<button class="rounded-xl bg-brand-700 px-5 py-3 font-semibold text-white">Send reply</button></form>
@endif</div>
@endcomponent
