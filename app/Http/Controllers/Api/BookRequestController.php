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

    // in your BookRequestController.php or similar

    public function store(Request $request)
    {
        // 1. ✨ Simplified Validation
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'course_id' => 'required|integer|exists:courses,id',
            // The 'file' validation now only accepts document types
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:10240'], // 10MB max
        ]);

        $user = $request->user();
        $status = 'pending';

        if ($user->role === RoleEnum::ACADEMIC) {
            $course = Course::findOrFail($validated['course_id']);
            if (Gate::allows('manages-subject', $course->subject)) {
                $status = 'approved';
            } else {
                abort(403, 'You are not authorized to add files for this subject.');
            }
        }

        // 2. ✨ Correct File Storage
        $filePath = $request->file('file')->store('book_requests', 'public');

        // 3. ✨ Simplified Record Creation (no 'type')
        $bookRequest = BookRequest::create([
            'title' => $validated['title'],
            'course_id' => $validated['course_id'],
            'file_path' => $filePath,
            'user_id' => $user->id,
            'status' => $status,
            'processed_by_user_id' => $user->id,
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
