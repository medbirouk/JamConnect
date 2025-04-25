<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->status !== 'approved') {
            return redirect()->route('pending-approval');
        }

        
        $followingIds = $user->followings->pluck('id')->all();

        
        $authorIds = array_merge([ $user->id ], $followingIds);

        
        $postsQuery = Post::whereIn('user_id', $authorIds)
            ->withCount('reactions')
            ->with([
                'user',
                'group',
                'attachments',
                'comments'           => fn($q) => $q->withCount('reactions'),
                'comments.user',
                'comments.reactions' => fn($q) => $q->where('user_id', $user->id),
                'reactions'          => fn($q) => $q->where('user_id', $user->id),
                'postUsers'          => fn($q) => $q->where('user_id', $user->id),
            ])
            ->orderByRaw("CASE WHEN id = ? THEN 0 ELSE 1 END", [$user->pinned_post_id])
            ->orderBy('date_time', 'desc');

        
        $paginated = $postsQuery->paginate(10)->withQueryString();

        if ($request->wantsJson()) {
            return PostResource::collection($paginated);
        }

        
        $groups     = $user->groups()->with('post')->get();
        $followings = UserResource::collection($user->followings);

        return Inertia::render('Home', [
            'posts'      => PostResource::collection($paginated),
            'groups'     => $groups,
            'followings' => $followings,
        ]);
    }
}
