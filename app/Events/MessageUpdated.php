<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\ChatMessage;

class MessageUpdated implements ShouldBroadcast
{
    public function __construct(public ChatMessage $message) {}

    public function broadcastOn() { return new Channel("group.{$this->message->group_id}"); }
    public function broadcastAs() { return 'message.updated'; }
}