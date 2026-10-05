@props(['headers', 'rows'])
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
<div class="hidden overflow-x-auto md:block"><table class="w-full text-left text-sm">
<thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr>@foreach($headers as $header)<th class="px-5 py-4" scope="col">{{ $header }}</th>@endforeach</tr></thead>
<tbody class="divide-y divide-slate-100">@forelse($rows as $row)<tr class="hover:bg-slate-50">@foreach($row as $cell)<td class="px-5 py-4">@if(is_array($cell))<a class="font-semibold text-brand-700 underline" href="{{ $cell['url'] }}">{{ $cell['label'] }}</a>@else{{ $cell ?? '—' }}@endif</td>@endforeach</tr>@empty<tr><td colspan="{{ count($headers) }}" class="p-8 text-center text-slate-500">No records found.</td></tr>@endforelse</tbody>
</table></div>
<div class="divide-y divide-slate-100 md:hidden">@forelse($rows as $row)<article class="p-5"><dl class="grid grid-cols-2 gap-x-4 gap-y-4">@foreach($row as $index => $cell)<div class="min-w-0 {{ $index === 0 ? 'col-span-2' : '' }}"><dt class="text-xs text-slate-500">{{ $headers[$index] }}</dt><dd class="mt-1 break-words text-sm">@if(is_array($cell))<a href="{{ $cell['url'] }}" class="font-semibold text-brand-700 underline">{{ $cell['label'] }}</a>@else{{ $cell ?? '—' }}@endif</dd></div>@endforeach</dl></article>@empty<p class="p-6 text-sm text-slate-500">No records found.</p>@endforelse</div>
</div>
