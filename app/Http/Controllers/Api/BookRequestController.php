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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:book,summary,image',
            'course_id' => 'required|integer|exists:courses,id',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,png,jpeg|max:10240', // 10MB max
        ]);

        $course = Course::find($validated['course_id']);
        $user = auth()->user();
        $status = 'pending'; // الحالة الافتراضية للطلاب

        // إذا كان المستخدم أكاديمي، تحقق من صلاحياته
        if ($user->role === RoleEnum::ACADEMIC) {
            $course = Course::findOrFail($validated['course_id']);
            if (Gate::allows('manages-subject', $course->subject)) {
                $status = 'approved';
            } else {
                abort(403, 'You are not authorized to add files to this subject.');
            }
        }

        // تخزين الملف
        $filePath = $request->file('file')->store('public/book_requests');

        // إنشاء الطلب
        $bookRequest = BookRequest::create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'course_id' => $validated['course_id'],
            'file_path' => $filePath,
            'user_id' => $user->id,
            'status' => $status,
            // إذا تمت الموافقة تلقائياً، سجل الأكاديمي كمعالج للطلب
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
