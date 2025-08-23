<?php
// app/Models/Conversation.php (ملف جديد)

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Conversation extends Model
{
    use HasFactory;

    /**
     * العلاقة مع المشاركين في المحادثة (متعدد لمتعدد)
     */
    public function participants()
    {
        return $this->belongsToMany(
            User::class,
            'conversation_user',
            'conversation_id', // اسم عمود المحادثة في الجدول الوسيط
            'user_id', // اسم عمود المستخدم في الجدول الوسيط
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

    public function scopeWithDetailsForUser(Builder $query, User $user)
    {
        return $query
            ->with(['participants', 'latestMessage.sender'])
            ->addSelect([
                'unread_count' => DB::table('private_messages as pm')
                    ->selectRaw('count(*)')
                    ->whereColumn('pm.conversation_id', 'conversations.id')
                    ->where('pm.sender_id', '!=', $user->id)
                    ->where(function ($subQuery) use ($user) {
                        $lastReadQuery = DB::table('conversation_user as cu')->select('cu.last_read_at')->where('cu.user_id', $user->id)->whereColumn('cu.conversation_id', 'conversations.id');

                        // ✨ --- This is the definitive fix --- ✨
                        // Get the raw SQL and bindings from the subquery
                        $subQuerySql = $lastReadQuery->toSql();
                        $subQueryBindings = $lastReadQuery->getBindings();

                        // Use the raw SQL directly in the where clauses
                        $subQuery->whereRaw("({$subQuerySql}) IS NULL", $subQueryBindings)->orWhereRaw("pm.created_at > ({$subQuerySql})", $subQueryBindings);
                    }),
            ])
            ->withMax('messages', 'created_at')
            ->orderBy('messages_max_created_at');
    }
}
