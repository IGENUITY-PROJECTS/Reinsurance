@component($layout, ['title' => $item['id'], 'section' => 'submissions'])
    <div class="mx-auto max-w-4xl space-y-5">
        <a href="{{ route($prefix.'.submissions') }}" class="text-sm font-semibold text-brand-700">&larr; All submissions</a>
        <p class="text-xs text-amber-800">Demo detail · Changes are not saved or sent.</p>
        <x-sample-submission :item="$item" :broker="$broker" />
    </div>
@endcomponent
