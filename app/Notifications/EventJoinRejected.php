<?php

namespace App\Notifications;

use App\Models\PostUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EventJoinRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PostUser $joinRequest) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $post = $this->joinRequest->post;

        return (new MailMessage)
            ->subject('Join request declined – '.$post->title)
            ->line('Unfortunately, the organiser declined your request to join **'.$post->title.'**.')
            ->line('Feel free to explore other events on JamConnect.');
    }
}
