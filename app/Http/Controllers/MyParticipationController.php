<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\PostUser;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Notifications\ParticipationCancelled;


class MyParticipationController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        
        $pendingApplications = PostUser::with(['post.user', 'post.postUsers.user'])
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->map(fn($pu) => $pu->post)
            ->filter();

        
        $approvedParticipations = PostUser::with(['post.user', 'post.postUsers.user'])
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->latest()
            ->get()
            ->map(fn($pu) => $pu->post)
            ->filter();


        return inertia('Participations/MyParticipations', [
            'pendingApplications' => PostResource::collection($pendingApplications),
            'approvedParticipations' => PostResource::collection($approvedParticipations),
        ]);
    }


    public function cancel(Request $request, $postId)
    {
        $user = $request->user();

        
        $postUser = PostUser::where('post_id', $postId)
        ->where('user_id', $user->id)
        ->firstOrFail();


        $post = $postUser->post;               
        $creator = $post->user;

        $creator->notify(new ParticipationCancelled($post, $user));
        
        $group = Group::where('post_id', $postId)->first();
        if ($group && $group->users()->wherePivot('user_id', $user->id)->exists()) {
            $group->users()->detach($user->id);
        }

        $postUser->delete();

        return back()->with('success', 'Your participation has been cancelled and you’ve left the chat.');
    }
}
