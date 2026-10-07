<?php

namespace App\Notifications;

use App\Models\FeedbackMessage;
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

        return $this->toBroker
            ? $notifiable->hasRole('admin')
            : (int) $notifiable->id === (int) $message->conversation->created_by && $notifiable->hasRole('client');
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
                ? ($message->author?->name ?? 'A cedant').' sent you a message.'
                : 'Your broker has replied to your feedback.')
            ->line('Subject: '.$conversation->subject)
            ->action('View conversation', route($prefix.'.help.show', ['reference' => $conversation->reference, 'page' => $page]))
            ->line('Sign in to read the message and reply.');
    }
}
