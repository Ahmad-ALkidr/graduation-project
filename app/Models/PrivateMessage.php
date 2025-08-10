<?php
// app/Models/PrivateMessage.php (ملف جديد)

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivateMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'type',
    ];

    /**
     * العلاقة مع المحادثة
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * العلاقة مع المرسل
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
