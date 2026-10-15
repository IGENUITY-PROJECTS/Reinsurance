@component($layout, ['title' => 'New feedback', 'section' => 'help'])
<div class="mx-auto max-w-3xl space-y-6"><a href="{{ route('client.help') }}" class="text-brand-700">&larr; Help & Feedback</a><h1 class="text-2xl font-semibold">How can we help?</h1><p class="text-sm text-slate-500">Choose a topic so your question reaches the right team.</p>
@if($errors->any())<div role="alert" class="rounded-xl bg-red-50 p-4 text-sm text-red-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<form method="POST" action="{{ route('client.help.store') }}" data-feedback-form data-documents-url="{{ route('client.help.documents') }}" class="space-y-5 rounded-2xl border bg-white p-5 sm:p-6">@csrf
<label class="block text-sm font-medium">Topic<select name="topic" data-feedback-topic required class="mt-2 block w-full rounded-lg border p-3">@foreach($topics as $key => $label)<option value="{{ $key }}" @selected(old('topic', request('topic', 'general')) === $key)>{{ $label }}</option>@endforeach</select></label>
<div data-statement-context class="space-y-4 rounded-xl bg-slate-50 p-4">
<p class="text-sm font-semibold">Ask about a statement (optional)</p>
<label class="block text-sm">Find a document<input type="search" data-document-search maxlength="150" placeholder="Search document number or insured" class="mt-2 block w-full rounded-lg border p-3"></label>
<label class="block text-sm">Document<select name="statement_document_id" data-feedback-document class="mt-2 block w-full rounded-lg border p-3"><option value="">No specific document</option>@if($selectedDocument)<option selected value="{{ $selectedDocument->id }}">{{ $selectedDocument->DocumentNo }} — {{ $selectedDocument->Insured }}</option>@endif</select></label><p data-document-status role="status" class="text-xs text-slate-500"></p>
<div class="grid gap-4 sm:grid-cols-2"><label class="text-sm">Period from<input type="date" name="from" value="{{ old('from', request('from')) }}" class="mt-2 block w-full rounded-lg border p-3"></label><label class="text-sm">Period to<input type="date" name="to" value="{{ old('to', request('to')) }}" class="mt-2 block w-full rounded-lg border p-3"></label></div>
<label class="block text-sm">Currency filter (optional)<input name="currency" maxlength="50" value="{{ old('currency', request('currency')) }}" class="mt-2 block w-full rounded-lg border p-3"></label>
<label class="block text-sm">Statement search (optional)<input name="q" maxlength="150" value="{{ old('q', request('q')) }}" class="mt-2 block w-full rounded-lg border p-3"></label>
</div>
<label class="block text-sm font-medium" for="feedback-subject">Subject</label><input id="feedback-subject" name="subject" value="{{ old('subject', $selectedDocument ? 'Question about '.$selectedDocument->DocumentNo : '') }}" maxlength="200" required class="block w-full rounded-lg border p-3">
<label class="block text-sm font-medium" for="feedback-message">Message</label><textarea id="feedback-message" name="message" rows="6" maxlength="5000" required class="block w-full rounded-lg border p-3">{{ old('message') }}</textarea>
<button class="rounded-xl bg-brand-700 px-5 py-3 font-semibold text-white">Send feedback</button></form></div>
@endcomponent
