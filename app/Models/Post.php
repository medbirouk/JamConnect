<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * Class Post
 *
 * @package App\Models
 */
class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'body',
        'title',
        'roles',
        'preview',
        'preview_url',
        'city',
        'date_time',
        'deleted_by',
    ];

    protected $casts = [
        'preview' => 'json',
        'roles' => 'json',
        'date_time' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): HasOne
    {
        return $this->hasOne(Group::class, 'post_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(PostAttachment::class)->latest();
    }

    public function postUsers()
    {
        return $this->hasMany(PostUser::class);
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'object');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function latest5Comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedUsers(): HasMany
    {
        return $this->hasMany(PostUser::class)->where('status', 'approved');
    }

    public function pendingUsers(): HasMany
    {
        return $this->hasMany(PostUser::class)->where('status', 'pending');
    }

    public function currentUserStatus(): HasOne
    {
        return $this->hasOne(PostUser::class)->where('user_id', auth()->id());
    }

    public function isAdmin($userId)
    {
        return $this->postUsers()->where('user_id', $userId)->where('role', 'admin')->exists();
    }

    public function isOwner($userId)
    {
        return $this->user_id == $userId;
    }


    public static function postsForTimeline($userId, $getLatest = true): Builder
    {
        $query = Post::query() 
            ->withCount('reactions') 
            ->with([
                'user',
                'group',
                'attachments',
                'comments' => function ($query) {
                    $query->withCount('reactions'); 
                    
                },
                'comments.user',
                'comments.reactions' => function ($query) use ($userId) {
                    $query->where('user_id', $userId); 
                },
                'reactions' => function ($query) use ($userId) {
                    $query->where('user_id', $userId); 
                }
            ]);
        if ($getLatest) {
            $query->latest();
        }

        return $query;
    }



    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($post) {

            Reaction::where('object_type', Post::class)
                ->where('object_id', $post->id)
                ->delete();


            $commentIds = $post->comments()->pluck('id');

            Reaction::where('object_type', Comment::class)
                ->whereIn('object_id', $commentIds)
                ->delete();


            $post->comments()->delete();

            $post->load('group');

            if ($post->group) {
                $post->group->delete();
            }

            $deleterId = auth()->id();               
            if ($deleterId && User::whereKey($deleterId)->exists()) {
                $post->deleted_by = $deleterId;      
            } else {
                $post->deleted_by = null;          
            }
            $post->save();
        });
    }
}
