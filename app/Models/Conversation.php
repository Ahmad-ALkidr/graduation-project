<?php
// app/Models/Conversation.php (ملف جديد)

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    /**
     * العلاقة مع المشاركين في المحادثة (متعدد لمتعدد)
     */
public function participants() {
    return $this->belongsToMany(
        User::class,
        'conversation_user',
        'conversation_id', // اسم عمود المحادثة في الجدول الوسيط
        'user_id'         // اسم عمود المستخدم في الجدول الوسيط
    )->withTimestamps();
}

    /**
     * العلاقة مع الرسائل الخاصة
     */
    public function messages()
    {
        return $this->hasMany(PrivateMessage::class);
    }
    public function latestMessage()
    {
        return $this->hasOne(PrivateMessage::class)->latestOfMany();
    }
}
