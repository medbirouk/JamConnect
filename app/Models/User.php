<?php

namespace App\Models;


use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use \App\Models\Post;
use \App\Models\PostUser;
use \App\Models\PostAttachment;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasSlug;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'cover_path',
        'avatar_path',
        'pinned_post_id',
        'roles',
        'demo_path',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'roles' => 'array',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('username')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }

    public function followings(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function postUsers()
    {
        return $this->hasMany(PostUser::class);
    }

    public function postAttachments()
    {
        return $this->hasMany(PostAttachment::class, 'created_by');
    }

    public function deletedPosts()
    {
        return $this->hasMany(Post::class, 'deleted_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (User $user) {

            Reaction::where('user_id', $user->id)->delete();

            Comment::where('user_id', $user->id)->each(function (Comment $c) {
                $c->delete();                  
            });

            
            Post::where('deleted_by', $user->id)->update(['deleted_by' => null]);
            PostAttachment::where('created_by', $user->id)->update(['created_by' => null]);
            PostUser::where('created_by', $user->id)->update(['created_by' => null]);

            
            $user->posts()->each(function ($post) {
                $post->delete();
            });

            
            $user->postUsers()->delete();

            
            $user->followers()->detach();
            $user->followings()->detach();
            $user->groups()->detach();

            
            ChatMessage::where('user_id', $user->id)->delete();
        });
    }
}
