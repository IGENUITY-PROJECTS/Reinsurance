import './bootstrap';


function setupFeedback() {
    document.querySelectorAll('[data-feedback-form]').forEach((form) => {
        if (form.dataset.ready) return;
        form.dataset.ready = 'true';
        const topic = form.querySelector('[data-feedback-topic]');
        const context = form.querySelector('[data-statement-context]');
        const select = form.querySelector('[data-feedback-document]');
        const search = form.querySelector('[data-document-search]');
        const status = form.querySelector('[data-document-status]');
        let request;
        let timer;
        async function load(changed = false) {
            request?.abort();
            const isStatement = ['claims', 'premiums'].includes(topic.value);
            context.hidden = !isStatement;
            context.querySelectorAll('input, select').forEach((input) => { input.disabled = !isStatement; });
            if (changed) {
                select.replaceChildren(new Option('No specific document', ''));
                context.querySelectorAll('input').forEach((input) => { input.value = ''; });
            }
            if (!isStatement) return;
            request = new AbortController();
            status.textContent = 'Loading documents…';
            const selected = select.selectedOptions[0];
            const url = new URL(form.dataset.documentsUrl, window.location.origin);
            url.searchParams.set('topic', topic.value);
            if (search.value) url.searchParams.set('q', search.value);
            try {
                const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: request.signal });
                if (!response.ok) throw new Error('Cannot load documents');
                const documents = await response.json();
                select.replaceChildren(new Option('No specific document', ''));
                if (selected?.value) select.add(new Option(selected.text, selected.value, true, true));
                documents.forEach((doc) => {
                    if (String(doc.id) !== selected?.value) select.add(new Option(`${doc.DocumentNo || 'Document'} — ${doc.Insured || ''} (${doc.CurrencyCode || ''})`, doc.id));
                });
                status.textContent = documents.length ? 'Showing up to 30 matches. Search to find older documents.' : 'No matching documents found.';
            } catch (error) {
                if (error.name !== 'AbortError') status.textContent = 'Documents could not be loaded. You can still send your question without selecting one.';
            }
        }
        topic.addEventListener('change', () => load(true));
        search.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => load(), 250); });
        load();
    });
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setupFeedback);
else setupFeedback();
document.addEventListener('livewire:navigated', setupFeedback);
