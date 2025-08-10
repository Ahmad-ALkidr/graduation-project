<?php
namespace App\Policies;
use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    // هل يمكن للمستخدم عرض هذه المحادثة؟
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->participants()->where('user_id', $user->id)->exists();
    }
}
