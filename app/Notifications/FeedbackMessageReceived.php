<?php

namespace App\Notifications;

use App\Models\FeedbackMessage;
use App\Models\User;
use App\Services\FeedbackRouting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackMessageReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public int $messageId, public bool $toBroker, public bool $newConversation = false)
    {
        $this->afterCommit();
    }

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        $message = FeedbackMessage::with('conversation')->find($this->messageId);
        if (! $message || ! $message->conversation || (int) $notifiable->id === (int) $message->author_id) {
            return false;
        }

        $user = User::find($notifiable->id);
        if (! $user || $user->access_status !== 'approved' || ! $user->hasVerifiedEmail()) {
            return false;
        }
        if ($this->toBroker) {
            return app(FeedbackRouting::class)->staff()->whereKey($user->id)->exists();
        }

        return (int) $user->id === (int) $message->conversation->created_by && $user->hasRole('client') && $user->company?->CmpCode === $message->conversation->company_code;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = FeedbackMessage::with(['conversation', 'author'])->findOrFail($this->messageId);
        $conversation = $message->conversation;
        $prefix = $this->toBroker ? 'admin' : 'client';
        $heading = $this->newConversation ? 'New feedback received' : 'Feedback reply received';
        $page = max(1, (int) ceil($conversation->messages()->where('id', '<=', $message->id)->count() / 20));

        return (new MailMessage)
            ->subject($heading.' - '.config('branding.company_name'))
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->toBroker
                ? ($message->author?->name ?? 'A portal user').' sent a feedback message.'
                : 'Your broker has replied to your feedback.')
            ->line('Subject: '.$conversation->subject)
            ->line('Topic: '.(FeedbackRouting::TOPICS[$conversation->topic] ?? 'General enquiry'))
            ->action('View conversation', route($prefix.'.help.show', ['reference' => $conversation->reference, 'page' => $page]))
            ->line('Sign in to read the message and reply.');
    }
}
