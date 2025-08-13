<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

Broadcast::channel('chat.private.{conversationId}', function ($user, $conversationId) {
    // ابحث عن المحادثة
    $conversation = Conversation::find($conversationId);

    // إذا كانت المحادثة موجودة والمستخدم الحالي هو أحد المشاركين فيها،
    // قم بإرجاع `true` للسماح له بالاستماع إلى القناة.
    if ($conversation && $conversation->participants()->where('user_id', $user->id)->exists()) {
        return true; 
    }

    // وإلا، امنعه من الاستماع
    return false;
});

// إذا كان لديك قنوات أخرى، اتركها كما هي
