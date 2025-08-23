<?php

namespace App\Policies;

use App\Models\ChatGroup;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChatGroupPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ChatGroup $group): bool
    {
        // --- التصحيح الرئيسي هنا ---
        // نستخدم علاقة المستخدم مباشرة للتحقق من العضوية
        return $user->chatGroups()->where('chat_group_id', $group->id)->exists();
    }

    /**
     * Determine whether the user can send a message in the group.
     */
    public function sendMessage(User $user, ChatGroup $group): bool
    {
        // نستخدم نفس منطق التحقق
        return $this->view($user, $group);
    }
}
