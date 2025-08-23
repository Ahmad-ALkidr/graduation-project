<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\ChatGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatStatusController extends Controller
{
    // لوضع علامة "مقروء" على مجموعة
    public function markGroupAsRead(Request $request, ChatGroup $group)
    {
        $request->user()->chatGroups()->updateExistingPivot($group->id, [
            'last_read_at' => now(),
        ]);
        return response()->json(['message' => 'Group marked as read.']);
    }

    // لجلب العدد الإجمالي للرسائل غير المقروءة
    public function getUnreadCount(Request $request)
    {
        $user = $request->user();
        $totalUnread = 0;

        $groups = $user->chatGroups()->withPivot('last_read_at')->get();

        foreach ($groups as $group) {
            $lastRead = $group->pivot->last_read_at;

            $unreadInGroup = DB::table('messages')
                ->where('chat_group_id', $group->id)
                ->where('user_id', '!=', $user->id) // الرسائل من الآخرين فقط
                ->when($lastRead, function ($query) use ($lastRead) {
                    return $query->where('created_at', '>', $lastRead);
                })
                ->count();

            $totalUnread += $unreadInGroup;
        }

        return response()->json(['total_unread_count' => $totalUnread]);
    }
}
