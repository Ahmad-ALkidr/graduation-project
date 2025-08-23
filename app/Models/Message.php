<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_group_id',
        'user_id',
        'content',
        'type',
    ];

    /**
     * العلاقة مع المجموعة
     */
    public function group()
    {
        return $this->belongsTo(ChatGroup::class, 'chat_group_id');
    }

    /**
     * العلاقة مع المرسل
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
