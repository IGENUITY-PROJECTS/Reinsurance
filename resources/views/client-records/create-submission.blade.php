@component('layouts.cedant', ['title' => $formTitle])
<div class="mx-auto max-w-3xl">
    <a href="{{ route('client.'.$module) }}" class="text-sm font-semibold text-brand-700">&larr; {{ $module === 'claims' ? 'Claims' : 'Premium Adjustments' }}</a>
    <h1 class="mt-5 text-2xl font-semibold">{{ $formTitle }}</h1>
    <p class="mt-2 text-sm text-slate-500">Choose your cover, add the details and send your documents to your broker.</p>

    @if(!$company)
        <p class="mt-6 rounded-xl bg-amber-50 p-5 text-amber-900">Your account needs a linked cedant company before you can submit. Please contact your broker.</p>
    @elseif($covers->isEmpty())
        <p class="mt-6 rounded-xl bg-amber-50 p-5 text-amber-900">No {{ $module === 'claims' ? 'facultative ' : '' }}covers are available for your company yet. Please contact your broker.</p>
    @else
    @if($errors->any())
        <div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-semibold">Please check the following:</p>
            <ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <p class="mt-2">Please select your document files again before submitting.</p>
        </div>
    @endif
    <form method="POST" action="{{ route('client.'.$module.'.store') }}" enctype="multipart/form-data"
          x-data="{ sending: false }" x-on:submit="sending = true"
          class="mt-6 space-y-7 rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
        @csrf
        <input type="hidden" name="_submission_token" value="{{ $token }}">
        <section x-data="{
            items: @js($covers->values()->all()),
            selected: @js($selectedCover?->CoverNo ?? ''),
            searchQuery: '', loading: false, searchError: '', sequence: 0,
            get chosen() { return this.items.find(item => item.CoverNo === this.selected); },
            async search() {
                const sequence = ++this.sequence;
                this.loading = true; this.searchError = '';
                try {
                    const response = await fetch(@js(route('client.cover-options')) + '?module=' + @js($module) + '&q=' + encodeURIComponent(this.searchQuery), { headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error('Search failed');
                    const records = await response.json();
                    if (sequence !== this.sequence) return;
                    const current = this.chosen;
                    if (current && !records.some(item => item.CoverNo === current.CoverNo)) records.unshift(current);
                    this.items = records;
                    const options = [new Option('Choose a cover', '')];
                    records.forEach(item => options.push(new Option(item.CoverNo + ' · ' + (item.InsName || item.MRef || ''), item.CoverNo)));
                    this.$refs.cover.replaceChildren(...options);
                    this.$refs.cover.value = this.selected;
                } catch (error) {
                    if (sequence === this.sequence) this.searchError = 'Could not search covers. Please try again.';
                } finally {
                    if (sequence === this.sequence) this.loading = false;
                }
            }
        }" class="space-y-3">
            <h2 class="font-semibold">1. Select your cover</h2>
            <label for="cover-search" class="block text-sm text-slate-600">Search by cover number, reference or insured name</label>
            <input id="cover-search" type="search" maxlength="150" x-model="searchQuery" @input.debounce.300ms="search"
                   class="block w-full rounded-lg border border-slate-300 p-3 text-sm" placeholder="Search your company's covers">
            <label for="CoverNo" class="block text-sm font-medium">Cover <span class="text-red-600">*</span></label>
            <select id="CoverNo" name="CoverNo" x-ref="cover" x-model="selected" required class="block w-full rounded-lg border border-slate-300 bg-white p-3 text-sm">
                <option value="">Choose a cover</option>
                @foreach($covers as $cover)<option value="{{ $cover->CoverNo }}" @selected($selectedCover?->CoverNo === $cover->CoverNo)>{{ $cover->CoverNo }} · {{ $cover->InsName ?: $cover->MRef }}</option>@endforeach
            </select>
            <p class="text-xs text-slate-500">Up to 30 matches are shown. Search to find more covers.</p>
            <p x-show="loading" x-cloak role="status" class="text-xs text-slate-500">Searching…</p>
            <p x-show="searchError" x-cloak x-text="searchError" role="alert" class="text-sm text-red-700"></p>
            <div x-show="chosen" x-cloak class="rounded-xl bg-brand-50 p-4 text-sm text-brand-900">
                <p class="font-semibold" x-text="chosen?.InsName || 'Insured name not recorded'"></p>
                <p class="mt-1" x-text="'Reference: ' + (chosen?.MRef || '—')"></p>
                <p class="mt-1" x-text="'Currency: ' + (chosen?.MCurrency || '—')"></p>
            </div>
            @error('CoverNo')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
        </section>

        <section class="space-y-4">
            <h2 class="font-semibold">2. {{ $module === 'claims' ? 'Claim details' : 'Adjustment details' }}</h2>
            @if($module === 'claims')
                <label class="block text-sm font-medium" for="OrigClaimNo">Your claim reference (optional)</label>
                <input id="OrigClaimNo" name="OrigClaimNo" value="{{ old('OrigClaimNo') }}" maxlength="50" class="block w-full rounded-lg border border-slate-300 p-3">
                <p class="text-xs text-slate-500">Leave blank and we’ll generate a reference for you.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium">Date of loss <span class="text-red-600">*</span>
                        <input name="DateLoss" type="date" value="{{ old('DateLoss') }}" max="{{ now()->format('Y-m-d') }}" required class="mt-2 block w-full rounded-lg border border-slate-300 p-3">
                    </label>
                    <label class="block text-sm font-medium">Loss location (optional)
                        <input name="LossLocation" value="{{ old('LossLocation') }}" maxlength="250" class="mt-2 block w-full rounded-lg border border-slate-300 p-3">
                    </label>
                </div>
                <label class="block text-sm font-medium">Loss details <span class="text-red-600">*</span>
                    <textarea name="LossDetails" required maxlength="2000" rows="5" placeholder="Briefly describe what happened." class="mt-2 block w-full rounded-lg border border-slate-300 p-3">{{ old('LossDetails') }}</textarea>
                </label>
                {{-- Deferred: claim amounts are entered in RBS and displayed from the mirrored claim.
                <label class="block text-sm font-medium">Estimated claim amount (optional)
                    <input name="ClaimAmt" inputmode="decimal" value="{{ old('ClaimAmt') }}" placeholder="0.00" class="mt-2 block w-full rounded-lg border border-slate-300 p-3">
                    <span class="mt-1 block text-xs font-normal text-slate-500">Use the currency of your selected cover.</span>
                </label>
                --}}
            @else
                <label class="block text-sm font-medium">Adjustment details <span class="text-red-600">*</span>
                    <textarea name="details" required maxlength="10000" rows="5" class="mt-2 block w-full rounded-lg border border-slate-300 p-3">{{ old('details') }}</textarea>
                </label>
            @endif
        </section>

        <section class="space-y-3">
            <h2 class="font-semibold">3. Supporting documents</h2>
            <label for="documents" class="block text-sm font-medium">{{ $module === 'claims' ? 'Supporting documents (required)' : 'Documents (optional)' }}</label>
            <input id="documents" name="documents[]" type="file" multiple @required($module === 'claims')
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.txt" class="block w-full rounded-lg border border-slate-300 p-3 text-sm">
            <p class="text-xs text-slate-500">PDF, images, Word, Excel or text files. Up to 10 files, 10 MB each.</p>
        </section>
        <div class="flex flex-wrap items-center gap-4 border-t pt-5">
            <button type="submit" :disabled="sending" class="rounded-xl bg-brand-700 px-6 py-3 font-semibold text-white disabled:opacity-60">
                <span x-show="!sending">Submit {{ $module === 'claims' ? 'claim' : 'adjustment' }}</span>
                <span x-show="sending" x-cloak>Submitting…</span>
            </button>
            <a href="{{ route('client.'.$module) }}" class="text-sm text-slate-600">Cancel</a>
        </div>
    </form>
    @endif
</div>
@endcomponent
