@component($layout, ['title' => 'New feedback', 'section' => 'help'])
<div class="mx-auto max-w-3xl space-y-6"><a href="{{ route('client.help') }}" class="text-brand-700">&larr; Help & Feedback</a><h1 class="text-2xl font-semibold">How can we help?</h1><p class="text-sm text-slate-500">Send your question or feedback. Your broker can reply here.</p>
<form method="POST" action="{{ route('client.help.store') }}" class="space-y-5 rounded-2xl border bg-white p-5 sm:p-6">@csrf
<label class="block text-sm font-medium" for="feedback-subject">Subject</label><input id="feedback-subject" name="subject" value="{{ old('subject') }}" maxlength="200" required class="block w-full rounded-lg border p-3">@error('subject')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
<label class="block text-sm font-medium" for="feedback-message">Message</label><textarea id="feedback-message" name="message" rows="6" maxlength="5000" required class="block w-full rounded-lg border p-3">{{ old('message') }}</textarea>@error('message')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
<button class="rounded-xl bg-brand-700 px-5 py-3 font-semibold text-white">Send feedback</button></form></div>
@endcomponent
