<?php

namespace App\Http\Controllers;

use App\Models\FeedbackConversation;
use App\Models\FeedbackMessage;
use App\Models\User;
use App\Notifications\FeedbackMessageReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            // General questions belong to their sender, including accounts without a company.
            $query->where('created_by', $request->user()->id);
        }

        return $query;
    }

    private function screen(Request $request, string $view, array $data = [])
    {
        $broker = $this->broker($request);

        return view($view, array_merge($data, [
            'broker' => $broker, 'prefix' => $broker ? 'admin' : 'client',
            'layout' => $broker ? 'layouts.dashboard' : 'layouts.cedant',
        ]));
    }

    public function index(Request $request)
    {
        $input = $request->validate(['q' => ['nullable', 'string', 'max:200']]);
        $conversations = $this->query($request)->with(['creator', 'company', 'latestMessage.author'])
            ->when(! empty($input['q']), fn ($q) => $q->where('subject', 'like', '%'.$input['q'].'%'))
            ->orderByDesc('updated_at')->orderByDesc('id')->paginate(15)->withQueryString();

        return $this->screen($request, 'client-records.feedback', compact('conversations'));
    }

    public function create(Request $request)
    {
        return $this->screen($request, 'client-records.feedback-create');
    }

    public function store(Request $request)
    {
        $request->merge(['subject' => is_string($request->subject) ? trim($request->subject) : $request->subject,
            'message' => is_string($request->message) ? trim($request->message) : $request->message]);
        $input = $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
        ]);
        [$conversation, $message] = DB::transaction(function () use ($request, $input) {
            $conversation = (new FeedbackConversation)->forceFill([
                'subject' => $input['subject'], 'created_by' => $request->user()->id,
                'company_code' => $request->user()->company?->CmpCode,
            ]);
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
        $conversation = $this->query($request)->with(['creator', 'company'])->where('reference', $reference)->firstOrFail();
        $messages = $conversation->messages()->with('author')->orderBy('id')->paginate(20)->withQueryString();

        return $this->screen($request, 'client-records.feedback-show', compact('conversation', 'messages'));
    }

    private function notifyFeedback(FeedbackConversation $conversation, FeedbackMessage $message, bool $brokerReply, bool $newConversation = false): void
    {
        $send = function (User $recipient) use ($message, $brokerReply, $newConversation) {
            if (! filter_var($recipient->email, FILTER_VALIDATE_EMAIL) || $recipient->id === $message->author_id) {
                return;
            }
            try {
                $recipient->notify(new FeedbackMessageReceived($message->id, ! $brokerReply, $newConversation));
            } catch (Throwable $exception) {
                // Message is already committed; delivery failures must not lose it or encourage duplicate submissions.
                report($exception);
            }
        };
        try {
            if ($brokerReply) {
                if ($creator = $conversation->creator) {
                    $send($creator);
                }
            } else {
                User::role('admin')->whereNotNull('email')->chunkById(100, function ($recipients) use ($send) {
                    foreach ($recipients as $recipient) {
                        $send($recipient);
                    }
                });
            }
        } catch (Throwable $exception) {
            report($exception);
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

        return redirect()->route(($this->broker($request) ? 'admin' : 'client').'.help.show', ['reference' => $reference, 'page' => $page])
            ->with('status', 'Your reply has been sent.');
    }
}
