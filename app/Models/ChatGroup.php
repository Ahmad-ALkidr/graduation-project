<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'creator_id',
        'department_id',
        'year',
        'course_id',
    ];

    /**
     * العلاقة مع الأكاديمي الذي أنشأ المجموعة
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * العلاقة مع القسم
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * العلاقة مع المقرر (إذا كانت مجموعة خاصة)
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * العلاقة مع الأعضاء (متعدد لمتعدد)
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'chat_group_user');
    }

    /**
     * العلاقة مع الرسائل
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
