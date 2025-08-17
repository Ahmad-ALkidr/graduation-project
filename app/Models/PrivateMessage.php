<?php
// app/Models/PrivateMessage.php (ملف جديد)

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PrivateMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'type',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
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

    /**
     * Scope للرسائل غير المقروءة
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope للرسائل في محادثة معينة
     */
    public function scopeInConversation(Builder $query, int $conversationId): Builder
    {
        return $query->where('conversation_id', $conversationId);
    }

    /**
     * Scope للرسائل المرسلة من مستخدم معين
     */
    public function scopeFromUser(Builder $query, int $userId): Builder
    {
        return $query->where('sender_id', $userId);
    }

    /**
     * Scope للرسائل المرسلة إلى مستخدم معين (غير المرسل)
     */
    public function scopeToUser(Builder $query, int $userId): Builder
    {
        return $query->where('sender_id', '!=', $userId);
    }
}
