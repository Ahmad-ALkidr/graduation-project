<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'content', 'image_path'];

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

    /**
     * المستخدمون الذين أعجبوا بالمنشور
     */
    public function likers()
    {
        return $this->belongsToMany(User::class, 'likes')->withTimestamps();
    }
}
