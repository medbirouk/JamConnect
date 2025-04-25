<?php

namespace App\Notifications;

use App\Models\PostUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EventParticipationRemoved extends Notification implements ShouldQueue
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
            ->subject('You have been removed from “'.$post->title.'”')
            ->line('The organiser removed you from **'.$post->title.'**.')
            ->line('If you think this is a mistake, feel free to re-apply.');
    }
}
