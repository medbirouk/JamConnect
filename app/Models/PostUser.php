<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostUser extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'status',            
        'role',              
        'artist_role',
        'user_id',
        'post_id',
        'created_by',        
        'token',
        'token_expire_date',
    ];

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
