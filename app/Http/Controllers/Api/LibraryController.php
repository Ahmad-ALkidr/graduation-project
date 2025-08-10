<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Course;
use App\Models\Department;
use App\Models\Subject;
use App\Models\BookRequest; // تم إضافة هذا
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    /**
     * الخطوة 1: جلب كل الكليات
     */
    public function getColleges()
    {
        return response()->json(College::all());
    }

    /**
     * الخطوة 2: جلب أقسام كلية معينة
     */
    public function getDepartments(College $college)
    {
        return response()->json($college->departments);
    }

    /**
     * الخطوة 3: جلب خيارات السنوات والفصول المتاحة لقسم معين
     */
    public function getCourseOptions(Department $department)
    {
        $options = Course::where('department_id', $department->id)
            ->select('year', 'semester')
            ->distinct()
            ->get();

        return response()->json($options);
    }

    /**
     * الخطوة 4: جلب المواد بناءً على الاختيارات
     * -- تم تحديث هذه الدالة بالكامل --
     */
    public function getSubjects(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|integer|exists:departments,id',
            'year'          => 'required|integer',
            'semester'      => 'required|in:first,second',
        ]);

        // ابحث عن معرفات المواد الفريدة للمقررات التي تطابق المعايير
        $subjectIds = Course::where($validated)->pluck('subject_id')->unique();

        // جلب نماذج المواد لهذه المعرفات (فقط الاسم والمعرف)
        $subjects = Subject::whereIn('id', $subjectIds)->get(['id', 'name']);

        return response()->json($subjects);
    }

    /**
     * الخطوة 5: جلب محتوى مادة معينة (كل الملفات المعتمدة)
     * -- تم تصحيح منطق هذه الدالة --
     */
    public function getSubjectContent(Subject $subject)
    {
        // ابحث عن كل المقررات (courses) المرتبطة بهذه المادة
        $courseIds = $subject->courses()->pluck('id');

        // جلب كل طلبات الكتب المعتمدة لهذه المقررات
        $content = BookRequest::whereIn('course_id', $courseIds)
            ->where('status', 'approved')
            ->with('user:id,first_name,last_name', 'course:id,year,semester') // جلب بيانات مفيدة
            ->latest()
            ->get();

        return response()->json($content);
    }
}
