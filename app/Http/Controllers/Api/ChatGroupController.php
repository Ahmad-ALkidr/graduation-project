<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatGroupResource;
use App\Models\ChatGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Enums\RoleEnum;
use App\Models\Department;
use App\Models\Subject;

class ChatGroupController extends Controller
{
   /**
     * Display a list of chat groups based on the user's role.
     * - Academics see the groups they created.
     * - Students see the groups for their year and major.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $groups = collect(); // Start with an empty collection

        // --- For Academics ---
        if ($user->role === RoleEnum::ACADEMIC) {
            $groups = ChatGroup::where('creator_id', $user->id)
                ->withCount('members')
                ->latest()
                ->get();
        }
        // --- For Students ---
        elseif ($user->role === RoleEnum::STUDENT && $user->major && $user->year) {
            // Find the department that matches the student's major
            $department = Department::where('name', $user->major)->first();

            if ($department) {
                // Get all subject IDs for the student's department and year
                $subjectIds = $department->courses()
                    ->where('year', $user->year)
                    ->pluck('subject_id')
                    ->unique();

                // Get all chat groups linked to those subjects
                $groups = ChatGroup::whereIn('subject_id', $subjectIds)
                    ->withCount('members')
                    ->latest()
                    ->get();
            }
        }

        return ChatGroupResource::collection($groups);
    }

    /**
     * Create a new chat group for a subject.
     * - Only academics can create groups.
     * - An academic can only create a group for a subject they teach.
     * - A subject can only have one group.
     */
    public function store(Request $request)
    {
        // 1. Security: Ensure the user is an academic
        Gate::authorize('is-academic');
        $academic = $request->user();

        // 2. Validate the request
        $validated = $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
        ]);

        $subject = Subject::find($validated['subject_id']);

        // 3. Security: Ensure the academic teaches this subject
        if ($subject->academic_id !== $academic->id) {
            return response()->json(['message' => 'You are not authorized to create a group for this subject.'], 403);
        }

        // 4. Prevent Duplicates: Check if a group already exists for this subject
        if (ChatGroup::where('subject_id', $subject->id)->exists()) {
            return response()->json(['message' => 'A chat group for this subject already exists.'], 422);
        }

        // 5. Create the group
        $group = ChatGroup::create([
            'name' => $subject->name, // The group name is the subject name
            'creator_id' => $academic->id,
            'subject_id' => $subject->id,
        ]);

        // Automatically add the creator as a member
        $group->members()->attach($academic->id);

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

    public function getSubjectsWithoutChatGroup(Request $request)
    {
        $academic = $request->user();

        // Fetch subjects taught by this academic where no chat group exists
        $subjects = $academic->subjects()
            ->whereDoesntHave('chatGroup')
            ->get();
        return response()->json($subjects);
    }
}
