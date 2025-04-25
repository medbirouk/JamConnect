<?php 


namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessagePinned implements ShouldBroadcast
{
    use SerializesModels;

    public ?ChatMessage $message;   
    public int $groupId;

    public function __construct(?ChatMessage $message, int $groupId)
    {
        $this->message = $message;
        $this->groupId = $groupId;
    }

   
    public function broadcastOn(): Channel
    {
        return new Channel("group.{$this->groupId}");
    }

    public function broadcastAs(): string
    {
        return 'message.pinned';
    }
}
