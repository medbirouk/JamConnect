<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'name',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function pinnedMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'pinned_message_id');
    }
}
