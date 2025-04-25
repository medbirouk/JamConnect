<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserRejected extends Notification implements ShouldQueue
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
            ->subject('Your JamConnect registration was not approved')
            ->greeting('Hello ' . $this->user->name . ',')
            ->line('We appreciate your interest in joining JamConnect, but your registration was not approved at this time.')
            ->line('You may apply again later or contact support for further details.')
            ->salutation('Thank you for understanding.');
    }
}
