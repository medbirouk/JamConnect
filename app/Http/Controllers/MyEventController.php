<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\PostUser;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Notifications\EventJoinApproved;
use App\Notifications\EventJoinRejected;
use App\Notifications\EventParticipationRemoved;


class MyEventController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        
        $posts = Post::where('user_id', $userId)
            ->with([
                'postUsers.user', 
                'postUsers' => fn($q) => $q->orderBy('status'), 
            ])
            ->latest()
            ->get();

        return inertia('Events/MyEvents', [
            'events' => PostResource::collection($posts),
        ]);
    }

    public function approve(Request $request, $postUserId)
    {
        $postUser = PostUser::findOrFail($postUserId);

        
        if ($postUser->post->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $postUser->status = 'approved';
        $postUser->save();

        $group = Group::where('post_id', $postUser->post_id)->first();
        if ($group && !$group->users->contains($postUser->user_id)) {
            $group->users()->attach($postUser->user_id);
        }

        $postUser->user->notify(new EventJoinApproved($postUser));

        return back()->with('success', 'User approved successfully.');
    }

    public function reject(Request $request, $postUserId)
    {
        $postUser = PostUser::findOrFail($postUserId);

        
        if ($postUser->post->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }


        $postUser->user->notify(new EventJoinRejected($postUser));
        
        $postUser->delete();

        

        return back()->with('success', 'User rejected.');
    }

    public function remove(Request $request, $postUserId){

        $postUser = PostUser::findOrFail($postUserId);
        $group = Group::where('post_id', $postUser->post_id)->first();
        if ($group) {
            $group->users()->detach($postUser->user_id);
        }

        $postUser->user->notify(new EventParticipationRemoved($postUser));

        $postUser->delete();

        

        return back()->with('success', 'Participation cancelled.');

    }
}
