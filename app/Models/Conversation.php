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
    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_user')->withTimestamps();
    }

    /**
     * العلاقة مع الرسائل الخاصة
     */
    public function messages()
    {
        return $this->hasMany(PrivateMessage::class);
    }
}
