<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'content', 'image_path'];
    // protected $appends = ['is_liked_by_user'];

    /**
     * المستخدم الذي كتب المنشور
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * التعليقات على المنشور
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
// This defines the users who have liked a post.
public function likers()
{
    return $this->belongsToMany(User::class, 'likes', 'post_id', 'user_id');
}
}
