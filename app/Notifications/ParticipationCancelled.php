<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ParticipationCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Post $post,            // the event
        public User $participant      // the user who cancelled
    ) {}

    /* ─────────────── channels ─────────────── */
    public function via($notifiable): array
    {
        return ['mail'];              // e-mail only
    }

    /* ─────────────── e-mail content ─────────────── */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->participant->username.' left “'.$this->post->title.'”')
            ->line('**'.$this->participant->username.'** has cancelled their participation in **'.$this->post->title.'**.')
            ->action('View the event', url(route('post.view', $this->post->id)))
            ->line('You can approve new applicants at any time.');
    }
}
