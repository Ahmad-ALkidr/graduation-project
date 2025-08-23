<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookRequest extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'file_path', 'type', 'status', 'user_id', 'course_id', 'processed_by_user_id'];

    /**
     * A book request belongs to a user (student).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * A book request belongs to a course.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * A book request is processed by a user (academic/admin).
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }
}
