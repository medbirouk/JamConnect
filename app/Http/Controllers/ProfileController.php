<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\PostAttachmentResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Follower;
use App\Models\Post;
use App\Models\PostAttachment;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index(Request $request, User $user)
    {
        $isCurrentUserFollower = false;

        if (!Auth::guest()) {
            $isCurrentUserFollower = Follower::where('user_id', $user->id)
                ->where('follower_id', Auth::id())
                ->exists();
        }

        $followerCount = Follower::where('user_id', $user->id)->count();

        $postsQuery = Post::query()
            ->where('user_id', $user->id)
            ->with(['postUsers', 'attachments', 'comments', 'reactions'])
            ->orderByDesc('created_at');
    
        $paginated = $postsQuery->paginate(10)->withQueryString();

        if ($request->wantsJson()) {
            return PostResource::collection($paginated);
        }


        $photos = PostAttachment::where('created_by', $user->id)
            ->where(function ($q) {
                $q->where('for_profile', true)
                    ->orWhere('mime', 'like', 'image/%');
            })
            ->latest()
            ->get();

        $videos = PostAttachment::where('created_by', $user->id)
            ->where(function ($q) {
                $q->where('for_profile', true)
                    ->orWhere('mime', 'like', 'video/%');
            })
            ->latest()
            ->get();

        return Inertia::render('Profile/View', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'success' => session('success'),
            'isCurrentUserFollower' => $isCurrentUserFollower,
            'followerCount' => $followerCount,
            'user' => new UserResource($user),
            'posts' => PostResource::collection($paginated),
            'followers' => UserResource::collection($user->followers),
            'followings' => UserResource::collection($user->followings),
            'photos' => PostAttachmentResource::collection($photos),
            'videos' => PostAttachmentResource::collection($videos),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile', $request->user())->with('success', 'Your profile details were updated.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function updateImage(Request $request)
    {
        $data = $request->validate([
            'cover' => ['nullable', 'image'],
            'avatar' => ['nullable', 'image']
        ]);

        $user = $request->user();

        $success = '';
        if (isset($data['cover'])) {
            if ($user->cover_path) {
                Storage::disk('public')->delete($user->cover_path);
            }
            $user->cover_path = $data['cover']->store('user-' . $user->id, 'public');
            $success = 'Your cover image was updated.';
        }

        if (isset($data['avatar'])) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $data['avatar']->store('user-' . $user->id, 'public');
            $success = 'Your avatar image was updated.';
        }

        $user->save();

        return back()->with('success', $success);
    }

    public function storeMedia(Request $request)
    {
        $data = Validator::make($request->all(), [
            'file' => ['required', 'mimes:jpg,jpeg,png,gif,mp4,webm,mov', 'max:10240']
        ])->validate();

        $user = $request->user();

        $file   = $data['file'];
        $path   = $file->store('user-' . $user->id, 'public');

        $media = PostAttachment::create([
            'post_id'     => null,
            'for_profile' => true,
            'name'        => $file->getClientOriginalName(),
            'path'        => $path,
            'mime'        => $file->getMimeType(),
            'size'        => $file->getSize(),
            'created_by'  => $user->id,
        ]);

        return PostAttachmentResource::make($media);
    }

   
    public function destroyMedia(Request $request, PostAttachment $attachment)
    {
        if ($attachment->created_by !== $request->user()->id || !$attachment->for_profile) {
            abort(403);
        }

        $attachment->delete();

        return response()->noContent();
    }

    public function updateRoles(Request $request)
    {
        $data = $request->validate([
            'roles'   => 'array',
            'roles.*' => 'string',
        ]);

        $user = $request->user();
        $user->roles = $data['roles'];
        $user->save();

        return back()->with('success', 'Roles updated.');
    }
}
