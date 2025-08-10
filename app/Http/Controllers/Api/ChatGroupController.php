<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatGroupResource;
use App\Models\ChatGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Enums\RoleEnum;

class ChatGroupController extends Controller
{
    /**
     * عرض المجموعات المتاحة للطالب
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // تأكد من أن المستخدم طالب ولديه قسم وسنة محددين
        if ($user->role !== RoleEnum::STUDENT || !$user->major || !$user->year) {
            return response()->json(['data' => []]); // أعد قائمة فارغة إذا لم يكن طالبًا
        }

        // ابحث عن القسم المطابق لاسم تخصص الطالب
        // ملاحظة: هذا يفترض أن 'major' في جدول users يطابق 'name' في جدول departments
        $department = \App\Models\Department::where('name', $user->major)->first();

        if (!$department) {
            return response()->json(['data' => []]);
        }

        // جلب كل المجموعات التي تطابق قسم وسنة الطالب
        $groups = ChatGroup::where('department_id', $department->id)
            ->where('year', $user->year)
            ->withCount('members') // جلب عدد الأعضاء مع كل مجموعة
            ->get();

        return ChatGroupResource::collection($groups);
    }

    /**
     * إنشاء مجموعة جديدة (للأكاديمي فقط)
     */
    public function store(Request $request)
    {
        // تحقق من أن المستخدم هو أكاديمي
        Gate::authorize('is-academic');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|integer|exists:departments,id',
            'year' => 'required|integer',
            'course_id' => 'nullable|integer|exists:courses,id',
        ]);

        $group = ChatGroup::create([
            'name' => $validated['name'],
            'department_id' => $validated['department_id'],
            'year' => $validated['year'],
            'course_id' => $validated['course_id'] ?? null,
            'creator_id' => auth()->id(),
        ]);

        // (اختياري) يمكنك إضافة الطلاب المطابقين تلقائيًا هنا
        // $students = User::where('major', $group->department->name)->where('year', $group->year)->get();
        // $group->members()->attach($students->pluck('id'));

        return new ChatGroupResource($group);
    }

    /**
     * انضمام المستخدم الحالي إلى مجموعة
     */
    public function join(Request $request, ChatGroup $group)
    {
        $user = $request->user();

        // 1. تحقق أولاً مما إذا كان المستخدم عضوًا بالفعل
        $isMember = $user->chatGroups()->where('chat_group_id', $group->id)->exists();

        // 2. إذا لم يكن عضوًا، قم بإضافته
        if (!$isMember) {
            $user->chatGroups()->attach($group->id);
        }

        return response()->json(['message' => 'تم الانضمام إلى المجموعة بنجاح.']);
    }

    /**
     * مغادرة المستخدم الحالي من مجموعة
     */
    public function leave(Request $request, ChatGroup $group)
    {
        $request->user()->chatGroups()->detach($group->id);

        return response()->json(['message' => 'تمت مغادرة المجموعة بنجاح.']);
    }
}
