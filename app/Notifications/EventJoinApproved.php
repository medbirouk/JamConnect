<?php

namespace App\Notifications;

use App\Models\PostUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EventJoinApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PostUser $joinRequest) {}

    /* e-mail only */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $post = $this->joinRequest->post;

        return (new MailMessage)
            ->subject('Your request to join “'.$post->title.'” was approved')
            ->line('Great news! The organiser accepted your request to join **'.$post->title.'**.')
            ->line('Role: **'.($this->joinRequest->artist_role ?? 'participant').'**')
            ->action('Open the event', url(route('post.view', $post->id)))
            ->line('See you there!');
    }
}
