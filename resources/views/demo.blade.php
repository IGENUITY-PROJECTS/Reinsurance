@component('layouts.demo', ['title' => $title, 'page' => $page])
    @if($page === 'home')
        <section class="rounded-3xl bg-brand-900 p-7 text-white sm:p-10">
            <p class="text-xs font-semibold uppercase tracking-widest text-brand-200">Savanna Assurance · CED-DEMO-001</p>
            <h1 class="mt-4 font-display text-3xl font-semibold">Welcome, Amina.</h1>
            <p class="mt-3 text-brand-100">Your covers, accounts, and conversations. All in one place.</p>
        </section>
        <h2 class="mt-8 font-display text-xl font-semibold">How can we help today?</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            @foreach([
                ['policies', 'My Policies / Covers', '3 sample covers', 'Browse your cover details and policy periods.'],
                ['statements', 'Statement of Account', 'USD 8,500.00 outstanding', 'Review your September account activity.'],
                ['claims', 'My Claims', '2 sample claims', 'Follow progress or try the claim form.'],
                ['help', 'Help & Feedback', '1 sample conversation', 'Ask a question or share an idea.'],
            ] as [$link, $label, $summary, $description])
                <a href="{{ url('/demo/'.$link) }}" class="rounded-2xl border border-slate-200 bg-white p-6 transition hover:border-brand-300 hover:shadow-md">
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-600">{{ $summary }}</p>
                    <h3 class="mt-4 font-display text-lg font-semibold">{{ $label }}</h3>
                    <p class="mt-2 text-sm text-slate-500">{{ $description }}</p>
                    <p class="mt-6 text-sm font-semibold text-brand-700">Open <span aria-hidden="true">&rarr;</span></p>
                </a>
            @endforeach
        </div>
        <section class="mt-7 rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">Recent updates</h2>
            <p class="mt-4 text-sm text-slate-600">21 September 2026 · Claim CLM-DEMO-001 moved to Under review.</p>
            <p class="mt-3 text-sm text-slate-600">20 September 2026 · September statement available.</p>
            <p class="mt-5 text-xs text-slate-500">Sample data timestamp: 22 September 2026, 09:00 EAT</p>
        </section>
    @else
        <a href="{{ url('/demo') }}" class="text-sm text-brand-700">&larr; Back to home</a>
        <h1 class="mt-5 font-display text-2xl font-semibold sm:text-3xl">{{ $title }}</h1>
        <p class="mt-2 text-sm text-slate-500">Savanna Assurance · Demo account</p>
    @endif

    @if($page === 'policies')
        <label for="policy-search" class="mt-6 block text-sm font-medium">Find a policy or cover</label>
        <input id="policy-search" type="search" placeholder="Search reference or cover name" class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 sm:max-w-md">
        <div class="mt-5 grid gap-4">
            @foreach([
                ['POL-DEMO-001', 'Commercial Property', 'Active', '01 Jan – 31 Dec 2026', 'USD 2,000,000', 'USD 12,000'],
                ['POL-DEMO-002', 'Marine Cargo', 'Active', '01 Jul 2026 – 30 Jun 2027', 'USD 750,000', 'USD 6,500'],
                ['POL-DEMO-003', 'Engineering', 'Expired', '01 Sep 2025 – 31 Aug 2026', 'USD 1,250,000', 'USD 9,000'],
            ] as [$reference, $cover, $status, $period, $sum, $premium])
                <article data-policy class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="flex flex-wrap justify-between gap-3"><div><p class="text-xs text-slate-500">{{ $reference }}</p><h2 class="mt-2 font-semibold">{{ $cover }}</h2></div><span class="self-start rounded-full bg-brand-50 px-3 py-1 text-xs text-brand-700">{{ $status }}</span></div>
                    <p class="mt-3 text-sm text-slate-600">{{ $period }}</p>
                    <details class="mt-4"><summary class="cursor-pointer py-2 text-sm font-semibold text-brand-700">Cover details</summary><dl class="mt-3 grid gap-4 rounded-xl bg-slate-50 p-4 sm:grid-cols-2"><div><dt class="text-xs text-slate-500">Sum insured</dt><dd class="mt-1 font-medium">{{ $sum }}</dd></div><div><dt class="text-xs text-slate-500">Premium</dt><dd class="mt-1 font-medium">{{ $premium }}</dd></div></dl><p class="mt-3 text-xs text-slate-500">Fictional cover. No policy document is attached to this preview.</p></details>
                </article>
            @endforeach
        </div>
        <p id="no-policies" hidden class="mt-5 text-sm text-slate-500">No sample policies match your search.</p>
    @elseif($page === 'statements')
        <div class="mt-6 flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm text-slate-500">01–30 September 2026 · USD</p><p class="mt-2 text-3xl font-semibold">8,500.00 <span class="text-sm font-normal text-slate-500">outstanding</span></p></div><button type="button" id="download-statement" class="rounded-xl bg-brand-700 px-4 py-3 text-sm font-semibold text-white">Download sample CSV</button></div>
        <div class="mt-6 grid gap-3 sm:grid-cols-3">
            @foreach(['Opening balance' => '5,000.00', 'Debits' => '18,500.00', 'Credits' => '15,000.00'] as $label => $amount)
                <div class="rounded-xl border border-slate-200 bg-white p-5"><p class="text-sm text-slate-500">{{ $label }}</p><p class="mt-2 text-lg font-semibold">USD {{ $amount }}</p></div>
            @endforeach
        </div>
        <div class="mt-5 space-y-3">
            @foreach([
                ['01 Sep', 'Opening balance', 'Balance brought forward', '—', '—', '5,000.00'],
                ['03 Sep', 'DN-DEMO-101', 'Commercial Property premium', '12,000.00', '—', '17,000.00'],
                ['10 Sep', 'RC-DEMO-042', 'Payment received', '—', '15,000.00', '2,000.00'],
                ['15 Sep', 'DN-DEMO-102', 'Marine Cargo premium', '6,500.00', '—', '8,500.00'],
            ] as [$date, $reference, $description, $debit, $credit, $balance])
                <article class="rounded-xl border border-slate-200 bg-white p-5"><div class="flex flex-wrap justify-between gap-2"><h2 class="text-sm font-semibold">{{ $description }}</h2><span class="text-xs text-slate-500">{{ $date }} 2026 · {{ $reference }}</span></div><dl class="mt-4 grid grid-cols-3 gap-2 text-sm"><div><dt class="text-xs text-slate-500">Debit</dt><dd class="mt-1">{{ $debit }}</dd></div><div><dt class="text-xs text-slate-500">Credit</dt><dd class="mt-1">{{ $credit }}</dd></div><div><dt class="text-xs text-slate-500">Balance</dt><dd class="mt-1 font-semibold">{{ $balance }}</dd></div></dl></article>
            @endforeach
        </div>
    @elseif($page === 'claims')
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            @foreach([['CLM-DEMO-001', 'Commercial Property', 'Water damage', 'Under review', '18 Sep 2026'], ['CLM-DEMO-002', 'Marine Cargo', 'Cargo damage', 'Additional documents requested', '12 Sep 2026']] as [$ref, $cover, $loss, $status, $date])
                <article class="rounded-2xl border border-slate-200 bg-white p-6"><p class="text-xs text-slate-500">{{ $ref }} · {{ $date }}</p><h2 class="mt-3 font-semibold">{{ $loss }}</h2><p class="mt-2 text-sm text-slate-500">{{ $cover }}</p><p class="mt-4 text-sm font-medium text-brand-700">{{ $status }}</p></article>
            @endforeach
        </div>
        <section class="mt-7 rounded-2xl border border-slate-200 bg-white p-6"><h2 class="font-display text-lg font-semibold">Try a claim submission</h2><p class="mt-2 text-sm text-slate-500">Preview only. Information and attachments stay in this browser and are not saved.</p>
            <form data-demo-form class="mt-5 grid gap-4">
                <label class="text-sm font-medium">Policy<select required class="mt-2 block w-full rounded-lg border border-slate-300 p-3"><option value="">Choose a policy</option><option>POL-DEMO-001 · Commercial Property</option><option>POL-DEMO-002 · Marine Cargo</option></select></label>
                <label class="text-sm font-medium">Date of loss<input required type="date" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></label>
                <label class="text-sm font-medium">What happened?<textarea required minlength="10" rows="4" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></textarea></label>
                <label class="text-sm font-medium">Supporting documents (optional)<input type="file" multiple class="mt-2 block w-full text-sm"></label>
                <button type="submit" class="justify-self-start rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white">Preview submission</button>
                <p data-form-result role="status" hidden class="rounded-lg bg-brand-50 p-4 text-sm text-brand-800">Demo claim preview complete. Nothing was saved or sent.</p>
            </form>
        </section>
    @elseif($page === 'help')
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6"><p class="text-xs text-slate-500">HELP-DEMO-001 · Sample conversation</p><h2 class="mt-3 font-semibold">Where can I find my statement?</h2><p class="mt-4 rounded-xl bg-brand-50 p-4 text-sm leading-6 text-brand-800">Support: Open Statements from the navigation bar. You can review September activity and download a sample CSV.</p></section>
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6"><h2 class="font-display text-lg font-semibold">How can we help?</h2><p class="mt-2 text-sm text-slate-500">Try the form. Demo messages are not sent to our team.</p>
            <form data-demo-form class="mt-5 grid gap-4">
                <label class="text-sm font-medium">Request type<select class="mt-2 block w-full rounded-lg border border-slate-300 p-3"><option>Ask a question</option><option>Report a problem</option><option>Share feedback</option></select></label>
                <label class="text-sm font-medium">Subject<input required class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></label>
                <label class="text-sm font-medium">Message<textarea required rows="4" class="mt-2 block w-full rounded-lg border border-slate-300 p-3"></textarea></label>
                <button type="submit" class="justify-self-start rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white">Preview message</button>
                <p data-form-result role="status" hidden class="rounded-lg bg-brand-50 p-4 text-sm text-brand-800">Demo message preview complete. Nothing was saved or sent.</p>
            </form>
        </section>
    @elseif($page === 'account')
        <dl class="mt-6 grid gap-5 rounded-2xl border border-slate-200 bg-white p-6 sm:grid-cols-2">
            @foreach(['Name' => 'Amina Demo', 'Company' => 'Savanna Assurance (fictional)', 'Email' => 'amina@example.test', 'Cedant identifier' => 'CED-DEMO-001'] as $label => $value)
                <div><dt class="text-sm text-slate-500">{{ $label }}</dt><dd class="mt-2 break-words font-medium">{{ $value }}</dd></div>
            @endforeach
        </dl>
    @endif
    <script>
        document.querySelectorAll('[data-demo-form]').forEach(form => form.addEventListener('submit', event => {
            event.preventDefault();
            form.querySelector('[data-form-result]').hidden = false;
        }));
        document.getElementById('policy-search')?.addEventListener('input', event => {
            let count = 0;
            document.querySelectorAll('[data-policy]').forEach(card => {
                card.hidden = !card.textContent.toLowerCase().includes(event.target.value.toLowerCase());
                if (!card.hidden) count++;
            });
            document.getElementById('no-policies').hidden = count > 0;
        });
        document.getElementById('download-statement')?.addEventListener('click', () => {
            const csv = 'DEMO ONLY - Savanna Assurance - USD\r\nDate,Reference,Description,Debit,Credit,Balance\r\n2026-09-01,Opening,Opening balance,0,0,5000\r\n2026-09-03,DN-DEMO-101,Commercial Property premium,12000,0,17000\r\n2026-09-10,RC-DEMO-042,Payment received,0,15000,2000\r\n2026-09-15,DN-DEMO-102,Marine Cargo premium,6500,0,8500\r\n';
            const url = URL.createObjectURL(new Blob([csv], {type: 'text/csv;charset=utf-8'}));
            const link = document.createElement('a'); link.href = url; link.download = 'DEMO-statement-september-2026.csv'; link.click();
            setTimeout(() => URL.revokeObjectURL(url), 1000);
        });
    </script>
@endcomponent
