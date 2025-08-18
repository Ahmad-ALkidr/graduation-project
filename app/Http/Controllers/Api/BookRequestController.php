<?php

namespace App\Http\Controllers\Api;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\BookRequest;
use App\Models\Course;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class BookRequestController extends Controller
{
    /**
     * إضافة ملف جديد (من قبل طالب أو أكاديمي)
     */
// in your BookRequestController.php or similar

public function store(Request $request)
{
    // 1. ✨ Improved Validation
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'type' => 'required|string|in:book,summary,image',
        'course_id' => 'required|integer|exists:courses,id',
        // File validation is now more specific based on the 'type'
        'file' => [
            'required',
            'file',
            'max:10240', // 10MB max
            function ($attribute, $value, $fail) use ($request) {
                $type = $request->input('type');
                $extension = strtolower($value->getClientOriginalExtension());
                $allowed = [];
                if ($type === 'image') {
                    $allowed = ['jpg', 'jpeg', 'png'];
                } elseif ($type === 'book' || $type === 'summary') {
                    $allowed = ['pdf', 'doc', 'docx'];
                }
                if (!in_array($extension, $allowed)) {
                    $fail("The uploaded file is not a valid {$type}.");
                }
            },
        ],
    ]);

    $user = $request->user();
    $status = 'pending'; // Default status for students

    // 2. ✨ Simplified Logic for Academics
    if ($user->role === RoleEnum::ACADEMIC) {
        // 3. ✨ Removed redundant database query. We only fetch the course once.
        $course = Course::findOrFail($validated['course_id']);

        // Use a Gate to authorize the action.
        if (Gate::allows('manages-subject', $course->subject)) {
            $status = 'approved';
        } else {
            abort(403, 'You are not authorized to add files for this subject.');
        }
    }

    // 4. ✨ Correct File Storage
    // Store on the 'public' disk and get the correct relative path.
    $filePath = $request->file('file')->store('book_requests', 'public');

    // Create the BookRequest record
    $bookRequest = BookRequest::create([
        'title' => $validated['title'],
        'type' => $validated['type'],
        'course_id' => $validated['course_id'],
        'file_path' => $filePath,
        'user_id' => $user->id,
        'status' => $status,
        'processed_by_user_id' => ($status === 'approved') ? $user->id : null,
    ]);

    return response()->json($bookRequest, 201);
}

    /**
     * حذف ملف (من قبل الأكاديمي المسؤول أو المدير)
     */
    public function destroy(BookRequest $bookRequest)
    {
        $user = auth()->user();
        $subject = $bookRequest->course->subject;

        // اسمح بالحذف فقط إذا كان المستخدم هو المدير أو الأكاديمي المسؤول عن المادة
        if ($user->role !== 'admin' && !Gate::allows('manages-subject', $subject)) {
            abort(403, 'Unauthorized action.');
        }

        // حذف الملف من نظام التخزين
        Storage::delete($bookRequest->file_path);

        // حذف السجل من قاعدة البيانات
        $bookRequest->delete();

        return response()->json(null, 204); // No Content
    }
}
