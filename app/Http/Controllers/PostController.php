<?php

namespace App\Http\Controllers;

use App\Http\Enums\ReactionEnum;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostUser;
use App\Models\PostAttachment;
use App\Models\Reaction;
use App\Models\User;
use App\Models\Group;
use App\Notifications\CommentCreated;
use App\Notifications\CommentDeleted;
use App\Notifications\PostCreated;
use App\Notifications\PostDeleted;
use App\Notifications\ReactionAddedOnComment;
use App\Notifications\ReactionAddedOnPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use OpenAI\Laravel\Facades\OpenAI;

class PostController extends Controller
{

    public function view(Request $request, Post $post)
    {

        $post->loadCount('reactions');
        $post->load([
            'comments' => fn($query) => $query->withCount('reactions'),
        ]);

        return inertia('Post/View', [
            'post' => new PostResource($post)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        
        if (!empty($data['date']) && !empty($data['time'])) {
            $data['date_time'] = $data['date'] . ' ' . $data['time'];
        }
        unset($data['date'], $data['time']);


        $data['user_id'] = $user->id;

        DB::beginTransaction();
        $allFilePaths = [];

        try {
            $post = Post::create($data);

            $group = Group::create([
                'post_id' => $post->id,
                'name'    => $post->title.' Chat',
            ]);

            $post->postUsers()->create([
                'user_id'     => $user->id,
                'status'      => 'approved',
                'role'        => 'admin',
                'artist_role' => 'Creator',
                'created_by'  => $user->id,
            ]);

            $group->users()->attach($user->id);

            /** @var \Illuminate\Http\UploadedFile[] $files */
            $files = $data['attachments'] ?? [];
            foreach ($files as $file) {
                $path = $file->store('attachments/' . $post->id, 'public');
                $allFilePaths[] = $path;
                PostAttachment::create([
                    'post_id' => $post->id,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'for_profile'=> true, 
                    'created_by' => $user->id
                ]);
            }

            DB::commit();

            $followers = $user->followers;
            Notification::send($followers, new PostCreated($post, $user, null));
        } catch (\Exception $e) {
            foreach ($allFilePaths as $path) {
                Storage::disk('public')->delete($path);
            }
            DB::rollBack();
            throw $e;
        }

        return back();
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $user = $request->user();

        if (!$post->isOwner(Auth::id())) {
            return response("You don't have permission to update this post", 403);
        }

        DB::beginTransaction();
        $allFilePaths = [];

        try {
            $data = $request->validated();

            if (!empty($data['date']) && !empty($data['time'])) {
                $data['date_time'] = $data['date'] . ' ' . $data['time'];
            }
            unset($data['date'], $data['time']);



            $post->update($data);

            $deleted_ids = $data['deleted_file_ids'] ?? [];

            $attachments = PostAttachment::query()
                ->where('post_id', $post->id)
                ->whereIn('id', $deleted_ids)
                ->get();

            foreach ($attachments as $attachment) {
                $attachment->delete();
            }

            /** @var \Illuminate\Http\UploadedFile[] $files */
            $files = $data['attachments'] ?? [];
            foreach ($files as $file) {
                $path = $file->store('attachments/' . $post->id, 'public');
                $allFilePaths[] = $path;
                PostAttachment::create([
                    'post_id' => $post->id,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'for_profile'=> true,
                    'created_by' => $user->id
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            foreach ($allFilePaths as $path) {
                Storage::disk('public')->delete($path);
            }
            DB::rollBack();
            throw $e;
        }

        return back();
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $id = Auth::id();

        if ($post->isOwner($id)) {
            $post->delete();
            return back();
        }

        return response("You don't have permission to delete this post", 403);
    }

    public function downloadAttachment(PostAttachment $attachment)
    {
        

        return Storage::disk('public')->download($attachment->path, $attachment->name);
    }

    public function join(Request $request, Post $post)
    {
        $request->validate([
            'role' => 'required|string|max:255',
        ]);

        $user = $request->user();

        
        $alreadyJoined = $post->postUsers()->where('user_id', $user->id)->exists();
        if ($alreadyJoined) {
            return back()->with('message', 'You already requested to join.');
        }

        $post->postUsers()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'role' => 'user',
            'artist_role' => $request->role, 
            'created_by' => null
        ]);

        return back()->with('message', 'Join request sent.');
    }

    



    public function postReaction(Request $request, Post $post)
    {
        $data = $request->validate([
            'reaction' => [Rule::enum(ReactionEnum::class)]
        ]);

        $userId = Auth::id();
        $reaction = Reaction::where('user_id', $userId)
            ->where('object_id', $post->id)
            ->where('object_type', Post::class)
            ->first();

        if ($reaction) {
            $hasReaction = false;
            $reaction->delete();
        } else {
            $hasReaction = true;
            Reaction::create([
                'object_id' => $post->id,
                'object_type' => Post::class,
                'user_id' => $userId,
                'type' => $data['reaction']
            ]);

            if (!$post->isOwner($userId)) {
                $user = User::where('id', $userId)->first();
                $post->user->notify(new ReactionAddedOnPost($post, $user));
            }
        }

        $reactions = Reaction::where('object_id', $post->id)->where('object_type', Post::class)->count();

        return response([
            'num_of_reactions' => $reactions,
            'current_user_has_reaction' => $hasReaction
        ]);
    }

    public function createComment(Request $request, Post $post)
    {
        $data = $request->validate([
            'comment' => ['required'],
            'parent_id' => ['nullable', 'exists:comments,id']
        ]);

        $comment = Comment::create([
            'post_id' => $post->id,
            'comment' => nl2br($data['comment']),
            'user_id' => Auth::id(),
            'parent_id' => $data['parent_id'] ?: null
        ]);

        $post = $comment->post;
        $post->user->notify(new CommentCreated($comment, $post));

        return response(new CommentResource($comment), 201);
    }

    public function deleteComment(Comment $comment)
    {
        $post = $comment->post;
        $id = Auth::id();
        if ($comment->isOwner($id) || $post->isOwner($id)) {

            $allComments = Comment::getAllChildrenComments($comment);
            $deletedCommentCount = count($allComments);

            $comment->delete();

            if (!$comment->isOwner($id)) {
                $comment->user->notify(new CommentDeleted($comment, $post));
            }


            return response(['deleted' => $deletedCommentCount], 200);
        }

        return response("You don't have permission to delete this comment.", 403);
    }

    public function updateComment(UpdateCommentRequest $request, Comment $comment)
    {
        $data = $request->validated();

        $comment->update([
            'comment' => nl2br($data['comment'])
        ]);

        return new CommentResource($comment);
    }

    public function commentReaction(Request $request, Comment $comment)
    {
        $data = $request->validate([
            'reaction' => [Rule::enum(ReactionEnum::class)]
        ]);

        $userId = Auth::id();
        $reaction = Reaction::where('user_id', $userId)
            ->where('object_id', $comment->id)
            ->where('object_type', Comment::class)
            ->first();

        if ($reaction) {
            $hasReaction = false;
            $reaction->delete();
        } else {
            $hasReaction = true;
            Reaction::create([
                'object_id' => $comment->id,
                'object_type' => Comment::class,
                'user_id' => $userId,
                'type' => $data['reaction']
            ]);

            if (!$comment->isOwner($userId)) {
                $user = User::where('id', $userId)->first();
                $comment->user->notify(new ReactionAddedOnComment($comment->post, $comment, $user));
            }
        }

        $reactions = Reaction::where('object_id', $comment->id)->where('object_type', Comment::class)->count();

        return response([
            'num_of_reactions' => $reactions,
            'current_user_has_reaction' => $hasReaction
        ]);
    }

    public function aiPostContent(Request $request)
    {
        $prompt = $request->get('prompt');

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Please generate social media post content based on the following prompt. Generated formatted content with multiple paragraphs. Put hashtags after 2 lines from the main content" . PHP_EOL . PHP_EOL . "Prompt: " . PHP_EOL
                        . $prompt
                ],
            ],
        ]);

        return response([
            'content' => $result->choices[0]->message->content
            
        ]);
    }

    public function fetchUrlPreview(Request $request)
    {
        $data = $request->validate([
            'url' => 'url'
        ]);
        $url = $data['url'];

        $html = file_get_contents($url);

        $dom = new \DOMDocument();

        
        libxml_use_internal_errors(true);

        
        $dom->loadHTML($html);

        
        libxml_use_internal_errors(false);

        $ogTags = [];
        $metaTags = $dom->getElementsByTagName('meta');
        foreach ($metaTags as $tag) {
            $property = $tag->getAttribute('property');
            if (str_starts_with($property, 'og:')) {
                $ogTags[$property] = $tag->getAttribute('content');
            }
        }

        return $ogTags;
    }

    public function pinUnpin(Request $request, Post $post)
    {
        $user = $request->user();

        $pinned = false;
        if ($user->pinned_post_id === $post->id) {
            $user->pinned_post_id = null;
        } else {
            $pinned = true;
            $user->pinned_post_id = $post->id;
        }

        $user->save();

        return back()->with('success', 'Post was successfully ' . ($pinned ? 'pinned' : 'unpinned'));
    }
}
