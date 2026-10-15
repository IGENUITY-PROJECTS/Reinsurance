<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PortalAccessChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public string $status)
    {
        $this->afterCommit();
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return $notifiable->hasVerifiedEmail() && $notifiable->access_status === $this->status;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject(($this->status === 'approved' ? 'Your portal account is approved' : 'Portal account update').' - '.config('branding.company_name'))
            ->greeting('Hello '.$notifiable->name.',')->line($this->status === 'approved' ? 'Your broker has approved your portal account. You can now sign in.' : 'Your portal access has been updated. Please contact your broker for assistance.')
            ->action('Sign in to the portal', route('login'));
    }
}
