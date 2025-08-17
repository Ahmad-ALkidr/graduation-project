<?php

namespace App\Policies;

use App\Models\PrivateMessage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PrivateMessagePolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PrivateMessage $privateMessage): bool
    {
        // Only allow a user to delete a message if they are the sender.
        return $user->id === $privateMessage->sender_id;
    }
}
