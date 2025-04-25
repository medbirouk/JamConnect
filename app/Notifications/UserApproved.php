<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $user) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your JamConnect account has been approved')
            ->greeting('Hello ' . $this->user->name . '!')
            ->line('Your account has been approved by the JamConnect team.')
            ->line('Please check your inbox for a separate email to verify your email address and access the platform.')
            ->salutation('Thanks for joining us!');
    }
}
