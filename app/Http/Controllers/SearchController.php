<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->get('search');

        if (!$search && !$request->city) {
            return redirect(route('dashboard'));
        }

        $user = $request->user();
        $userId = $user->id;

        
        $postsQuery = Post::postsForTimeline($userId)
            ->withCount('reactions')
            ->with([
                'user',
                'group',
                'attachments',
                'comments' => fn($query) => $query->withCount('reactions'),
                'comments.user',
                'comments.reactions' => fn($query) => $query->where('user_id', $userId),
                'reactions' => fn($query) => $query->where('user_id', $userId),
                'postUsers' => fn($query) => $query->where('user_id', $userId),
            ])
            ->when($search, fn($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            }))
            ->when($request->filled('city'), fn($query) => $query->where('city', 'like', "%{$request->city}%"))
            ->whereDate('date_time', '>=', Carbon::today())
            ->reorder('date_time', 'asc');


        $paginated = $postsQuery->paginate(10)->withQueryString();

        if ($request->wantsJson()) {
            return PostResource::collection($paginated);
        }

        
        $groups = $user->groups()->with('post')->get();

        return Inertia::render('Search', [
            'posts' => PostResource::collection($paginated),
            'search' => $search,
            'city' => $request->city,
            'groups' => $groups,
            'followings' => UserResource::collection($user->followings)
        ]);
    }
}
