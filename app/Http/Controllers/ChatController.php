<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\MessageUpdated;
use App\Events\MessageDeleted;
use App\Models\Group;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use App\Events\MessagePinned;
use Illuminate\Support\Facades\Gate;
use App\Notifications\GroupMessagePosted;
use Illuminate\Support\Facades\Notification;


class ChatController extends Controller
{
    public function store(Request $request, Group $group)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = ChatMessage::create([
            'group_id' => $group->id,
            'user_id' => $request->user()->id,
            'message' => $request->message,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        $recipients = $group->users()
        ->where('users.id', '!=', $request->user()->id)   
        ->get();

        Notification::send($recipients, new GroupMessagePosted($message));

        return response()->json([
            'message' => $message->load('user'),
        ]);
    }

    public function fetchMessages(Group $group)
    {
        return $group->messages()->with('user')->latest()->limit(50)->get()->reverse()->values();
    }

    

    public function update(Request $request, Group $group, ChatMessage $message)
    {
        
        if ($request->user()->id !== $message->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $message->update($data);

        
        broadcast(new MessageUpdated($message))->toOthers();

        return response()->json(['message' => $message->load('user')]);
    }

    public function destroy(Request $request, Group $group, ChatMessage $message)
    {
        if ($request->user()->id !== $message->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $id = $message->id;            
        $message->delete();

        broadcast(new MessageDeleted($group->id, $id))->toOthers();

        return response()->json(['id' => $id]);
    }

    public function pin(Request $request, Group $group, ChatMessage $message)
    {
        
        if ($request->user()->id !== $group->post->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        
        $group->pinned_message_id =
            $group->pinned_message_id === $message->id ? null : $message->id;

        $group->save();

        
        $msg = $group->pinned_message_id ? $message->load('user') : null;

        
        broadcast(new MessagePinned($msg, $group->id))->toOthers();

        
        return response()->json(['message' => $msg]);
    }
}
