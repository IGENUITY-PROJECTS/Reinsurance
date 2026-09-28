@props(['item', 'broker' => false])
<article class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div><p class="text-xs font-medium text-brand-600">{{ $item['type'] }} · {{ $item['id'] }}</p><h3 class="mt-2 text-lg font-semibold">{{ $item['title'] }}</h3><p class="mt-2 text-sm text-slate-500">{{ $item['company'] }} · {{ $item['date'] }}</p></div>
        <span class="rounded-full bg-brand-50 px-3 py-2 text-xs font-semibold text-brand-800">{{ $item['status'] }}</span>
    </div>
    <details open class="mt-4"><summary class="cursor-pointer py-2 text-sm font-semibold text-brand-700">Documents, feedback & tracking</summary>
        <p class="mt-4 text-sm text-slate-500">Policy / treaty: {{ $item['policy'] }}</p>
        <h4 class="mt-5 text-sm font-semibold">Documents</h4>
        <ul class="mt-2 space-y-2">@foreach($item['documents'] as $document)<li class="rounded-lg bg-slate-50 p-3 text-sm">{{ $document }} <span class="text-xs text-slate-500">· Sample filename, no file attached</span></li>@endforeach</ul>
        <h4 class="mt-5 text-sm font-semibold">{{ $item['type'] === 'Claim' ? 'Claim tracking' : 'Review history' }}</h4>
        <ol class="mt-3 space-y-4 border-l-2 border-brand-100 pl-4">@foreach($item['history'] as [$date, $status, $author, $message])<li><p class="text-xs text-slate-500">{{ $date }} · {{ $author }}</p><p class="mt-1 text-sm font-semibold">{{ $status }}</p><p class="mt-1 text-sm leading-6 text-slate-600">{{ $message }}</p></li>@endforeach</ol>
        @if($broker)
            <div x-data="{ preview: false, message: '', status: @js($item['status']) }" class="mt-6 rounded-xl bg-slate-50 p-4">
                <h4 class="text-sm font-semibold">Preview broker feedback</h4>
                <form x-on:submit.prevent="preview = true" class="mt-3 space-y-3">
                    <label class="block text-sm">Review status<select x-model="status" class="mt-2 block w-full rounded-lg border border-slate-300 bg-white p-3">@foreach(['Submitted', 'Under review', 'More information required', 'Resubmitted', 'Review completed'] as $status)<option>{{ $status }}</option>@endforeach</select></label>
                    <label class="block text-sm">Feedback to cedant<textarea x-model="message" required rows="3" class="mt-2 block w-full rounded-lg border border-slate-300 bg-white p-3"></textarea></label>
                    <button type="submit" class="rounded-lg bg-brand-700 px-4 py-3 text-sm font-semibold text-white">Preview feedback</button>
                </form>
                <div x-show="preview" x-cloak role="status" class="mt-4 rounded-lg border border-brand-200 bg-white p-4 text-sm"><p class="font-semibold" x-text="status"></p><p class="mt-2 whitespace-pre-wrap" x-text="message"></p><p class="mt-3 text-xs text-slate-500">Demo only. This feedback has not been saved or sent.</p></div>
            </div>
        @endif
    </details>
</article>

