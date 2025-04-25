<?php

namespace App\Notifications;

use App\Models\ChatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;

class GroupMessagePosted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ChatMessage $message) {}

    /* ───── channel ───── */
    public function via($notifiable): array
    {
        return ['mail'];          // e-mail only
    }

    /* ───── e-mail ───── */
    public function toMail($notifiable): MailMessage
    {
        // make sure the relations are present when the job runs
        $this->message->loadMissing('user', 'group.post');

        $sender   = $this->message->user->username;
        $post     = $this->message->group->post;          // the event
        $excerpt  = Str::limit($this->message->message, 100);

        return (new MailMessage)
            ->subject("New message in “{$post->title}” by {$sender}")
            ->line("**{$sender}** wrote:")
            ->line("> {$excerpt}")
            ->action('Open the chat', url(route('chats.mine', ['group' => $this->message->group_id])))
            ->line('You are receiving this because you are a participant in the event chat.');
    }
}
