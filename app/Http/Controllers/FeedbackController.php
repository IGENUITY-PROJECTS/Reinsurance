<?php

namespace App\Http\Controllers;

use App\Models\CedantClaimPayable;
use App\Models\CedantPremium;
use App\Models\Department;
use App\Models\FeedbackConversation;
use App\Models\FeedbackMessage;
use App\Models\User;
use App\Notifications\FeedbackMessageReceived;
use App\Services\FeedbackRouting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class FeedbackController extends Controller
{
    private function broker(Request $request): bool
    {
        return $request->routeIs('admin.*');
    }

    private function query(Request $request)
    {
        $query = FeedbackConversation::query();
        if (! $this->broker($request)) {
            $query->where('created_by', $request->user()->id)->where('company_code', $request->user()->company?->CmpCode);
        } elseif (! $request->user()->hasRole('super-admin')) {
            abort_unless($request->user()->hasRole('admin'), 403);
            $id = $request->user()->department_id;
            $query->where('department_id', $id)->when(! $id, fn ($q) => $q->whereRaw('1 = 0'))
                ->whereHas('department', fn ($q) => $q->where('is_active', true));
        }

        return $query;
    }

    private function screen(Request $request, string $view, array $data = [])
    {
        $broker = $this->broker($request);

        return view($view, array_merge($data, ['broker' => $broker, 'prefix' => $broker ? 'admin' : 'client', 'layout' => $broker ? 'layouts.dashboard' : 'layouts.cedant']));
    }

    public function index(Request $request)
    {
        $input = $request->validate(['q' => ['nullable', 'string', 'max:200']]);
        $conversations = $this->query($request)->with(['creator', 'company', 'department', 'latestMessage.author'])
            ->when(! empty($input['q']), fn ($q) => $q->where(fn ($q) => $q->where('subject', 'like', '%'.$input['q'].'%')->orWhere('statement_document_no', 'like', '%'.$input['q'].'%')))
            ->orderByDesc('updated_at')->orderByDesc('id')->paginate(15)->withQueryString();
        if ($this->broker($request)) {
            $conversations->through(function ($conversation) {
                $conversation->needs_assignment = ! app(FeedbackRouting::class)->staff($conversation->department_id)->exists();

                return $conversation;
            });
        }

        return $this->screen($request, 'client-records.feedback', compact('conversations'));
    }

    private function contextRules(): array
    {
        return ['topic' => ['required', Rule::in(array_keys(FeedbackRouting::TOPICS))],
            'statement_document_id' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'required_with:to', 'date_format:Y-m-d'], 'to' => ['nullable', 'required_with:from', 'date_format:Y-m-d', 'after_or_equal:from'],
            'currency' => ['nullable', 'string', 'max:50'], 'q' => ['nullable', 'string', 'max:150']];
    }

    private function statementQuery(Request $request, string $topic)
    {
        $code = $request->user()->company?->CmpCode;

        return ($topic === 'premiums' ? CedantPremium::query() : CedantClaimPayable::query())
            ->where('CedantCode', $code)->when(! $code, fn ($q) => $q->whereRaw('1 = 0'));
    }

    private function context(Request $request, array $input): array
    {
        $statement = in_array($input['topic'], ['premiums', 'claims'], true);
        $hasContext = filled($input['statement_document_id'] ?? null) || filled($input['from'] ?? null) || filled($input['currency'] ?? null) || filled($input['q'] ?? null);
        if (! $statement && $hasContext) {
            throw ValidationException::withMessages(['topic' => 'Choose a claims or premium statement topic for statement details.']);
        }
        $document = null;
        if (! empty($input['statement_document_id'])) {
            $document = $this->statementQuery($request, $input['topic'])->find($input['statement_document_id']);
            if (! $document) {
                throw ValidationException::withMessages(['statement_document_id' => 'Choose a document belonging to your company.']);
            }
        }

        return ['statement_kind' => $statement && $hasContext ? $input['topic'] : null,
            'statement_document_id' => $document?->id, 'statement_document_no' => $document?->DocumentNo,
            'period_from' => $input['from'] ?? null, 'period_to' => $input['to'] ?? null,
            'statement_currency' => $input['currency'] ?? null, 'statement_search' => $input['q'] ?? null];
    }

    public function create(Request $request)
    {
        $request->merge(['topic' => $request->input('topic', 'general')]);
        $input = $request->validate($this->contextRules());
        $context = $this->context($request, $input);
        $selectedTopic = $request->old('topic', $input['topic']);
        $selectedId = $request->old('statement_document_id', $context['statement_document_id']);
        $selectedDocument = in_array($selectedTopic, ['premiums', 'claims'], true) && is_numeric($selectedId) ? $this->statementQuery($request, $selectedTopic)->find($selectedId) : null;

        return $this->screen($request, 'client-records.feedback-create', ['topics' => FeedbackRouting::TOPICS, 'context' => $context, 'selectedDocument' => $selectedDocument]);
    }

    public function documents(Request $request)
    {
        $input = $request->validate(['topic' => ['required', Rule::in(['claims', 'premiums'])], 'q' => ['nullable', 'string', 'max:150']]);
        $query = $this->statementQuery($request, $input['topic']);
        if (filled($input['q'] ?? null)) {
            $query->where(fn ($q) => $q->where('DocumentNo', 'like', '%'.$input['q'].'%')->orWhere('Insured', 'like', '%'.$input['q'].'%'));
        }

        return response()->json($query->orderByDesc('DocumentDate')->orderBy('id')->limit(30)->get(['id', 'DocumentNo', 'Insured', 'CurrencyCode']));
    }

    public function store(Request $request)
    {
        $request->merge(['subject' => is_string($request->subject) ? trim($request->subject) : $request->subject, 'message' => is_string($request->message) ? trim($request->message) : $request->message]);
        $input = $request->validate($this->contextRules() + ['subject' => ['required', 'string', 'max:200'], 'message' => ['required', 'string', 'max:5000']]);
        $context = $this->context($request, $input);
        $department = app(FeedbackRouting::class)->department($input['topic']);
        [$conversation, $message] = DB::transaction(function () use ($request, $input, $context, $department) {
            $conversation = (new FeedbackConversation)->forceFill($context + ['subject' => $input['subject'], 'topic' => $input['topic'],
                'created_by' => $request->user()->id, 'company_code' => $request->user()->company?->CmpCode, 'department_id' => $department?->id,
                'needs_assignment' => ! app(FeedbackRouting::class)->staff($department?->id)->exists()]);
            $conversation->save();
            $message = $conversation->messages()->make(['message' => $input['message']]);
            $message->author_id = $request->user()->id;
            $message->save();

            return [$conversation, $message];
        });
        $this->notifyFeedback($conversation, $message, false, true);

        return redirect()->route('client.help.show', $conversation->reference)->with('status', 'Your message has been sent to the broker.');
    }

    public function show(Request $request, string $reference)
    {
        $conversation = $this->query($request)->with(['creator', 'company', 'department'])->where('reference', $reference)->firstOrFail();
        // Refresh the attention flag when staff membership changes, without exposing messages to other departments.
        if ($this->broker($request)) {
            app(FeedbackRouting::class)->brokerRecipients($conversation);
        }
        $messages = $conversation->messages()->with('author')->orderBy('id')->paginate(20)->withQueryString();

        return $this->screen($request, 'client-records.feedback-show', ['conversation' => $conversation, 'messages' => $messages,
            'departments' => $request->user()->hasRole('super-admin') ? Department::where('is_active', true)->orderBy('name')->get() : collect()]);
    }

    public function assign(Request $request, string $reference)
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);
        $input = $request->validate(['department_id' => ['required', 'integer', Rule::exists('departments', 'id')->where('is_active', true)]]);
        $conversation = FeedbackConversation::where('reference', $reference)->firstOrFail();
        $conversation->department_id = $input['department_id'];
        $conversation->needs_assignment = ! app(FeedbackRouting::class)->staff($conversation->department_id)->exists();
        $conversation->save();
        $latest = $conversation->messages()->latest('id')->first();
        if ($latest && (int) $latest->author_id === (int) $conversation->created_by) {
            $this->notifyFeedback($conversation, $latest, false);
        }

        return back()->with('status', 'Conversation department updated.');
    }

    private function notifyFeedback(FeedbackConversation $conversation, FeedbackMessage $message, bool $brokerReply, bool $newConversation = false): void
    {
        $send = function (User $recipient) use ($message, $brokerReply, $newConversation) {
            if (! filter_var($recipient->email, FILTER_VALIDATE_EMAIL) || (int) $recipient->id === (int) $message->author_id) {
                return;
            }
            try {
                $recipient->notify(new FeedbackMessageReceived($message->id, ! $brokerReply, $newConversation));
            } catch (Throwable $error) {
                report($error);
            }
        };
        try {
            if ($brokerReply) {
                if ($creator = $conversation->creator) {
                    $send($creator);
                }
            } else {
                app(FeedbackRouting::class)->brokerRecipients($conversation)->chunkById(100, function ($recipients) use ($send) {
                    foreach ($recipients as $recipient) {
                        $send($recipient);
                    }
                });
            }
        } catch (Throwable $error) {
            report($error);
        }
    }

    public function reply(Request $request, string $reference)
    {
        if ($this->broker($request)) {
            abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);
        }
        $request->merge(['message' => is_string($request->message) ? trim($request->message) : $request->message]);
        $input = $request->validate(['message' => ['required', 'string', 'max:5000']]);
        [$conversation, $message] = DB::transaction(function () use ($request, $reference, $input) {
            $conversation = $this->query($request)->where('reference', $reference)->lockForUpdate()->firstOrFail();
            $message = $conversation->messages()->make(['message' => $input['message']]);
            $message->author_id = $request->user()->id;
            $message->save();
            $conversation->touch();

            return [$conversation, $message];
        });
        $this->notifyFeedback($conversation, $message, $this->broker($request));
        $page = max(1, (int) ceil($conversation->messages()->count() / 20));

        return redirect()->route(($this->broker($request) ? 'admin' : 'client').'.help.show', ['reference' => $reference, 'page' => $page])->with('status', 'Your reply has been sent.');
    }
}
