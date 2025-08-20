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
        return response()->json(
            cache()->remember('colleges', 3600, function () {
                return College::all();
            }),
        );
    }

    /**
     * الخطوة 2: جلب أقسام كلية معينة
     */
    public function getDepartments(College $college)
    {
        return response()->json(
            cache()->remember("college_{$college->id}_departments", 3600, function () use ($college) {
                return $college->departments;
            }),
        );
    }

    /**
     * الخطوة 3: جلب خيارات السنوات والفصول المتاحة لقسم معين
     */
    public function getCourseOptions(Department $department)
    {
        return response()->json(
            cache()->remember("department_{$department->id}_options", 1800, function () use ($department) {
                return Course::where('department_id', $department->id)->select('year', 'semester')->distinct()->get();
            }),
        );
    }

    /**
     * الخطوة 4: جلب المواد بناءً على الاختيارات
     * -- تم تحديث هذه الدالة بالكامل --
     */
    public function getSubjects(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|integer|exists:departments,id',
            'year' => 'required|integer',
            'semester' => 'required|in:first,second',
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
    /**
     * ✨ --- الدالة الجديدة: جلب شجرة المكتبة الكاملة --- ✨
     * تجلب كل الكليات والأقسام والسنوات والفصول والمواد في طلب واحد.
     */
    // app/Http/Controllers/Api/LibraryController.php

    public function getLibraryTree()
    {
        // ✨ 1. تحديث الاستعلام لجلب الملفات الموافق عليها فقط
        $colleges = College::with([
            'departments.courses.subject',
            'departments.courses.bookRequests' => function ($query) {
                $query->where('status', 'approved');
            },
        ])->get();

        // الآن نقوم بمعالجة البيانات لإنشاء البنية الشجرية المطلوبة
        $structuredData = $colleges->map(function ($college) {
            return [
                'id' => $college->id,
                'name' => $college->name,
                'departments' => $college->departments->map(function ($department) {
                    $years = $department->courses
                        ->groupBy('year')
                        ->map(function ($yearCourses, $year) {
                            return [
                                'year' => $year,
                                'semesters' => $yearCourses
                                    ->groupBy('semester')
                                    ->map(function ($semesterCourses, $semester) {
                                        // ✨ --- 2. بداية الكود الجديد لمعالجة المواد والملفات --- ✨
                                        // سنقوم بتجميع كل الملفات لكل مادة فريدة
                                        $subjects = $semesterCourses
                                            ->mapToGroups(function ($course) {
                                                return [$course->subject->id => $course->bookRequests];
                                            })
                                            ->map(function ($materials, $subjectId) use ($semesterCourses) {
                                                $subject = $semesterCourses->firstWhere('subject.id', $subjectId)->subject;
                                                return [
                                                    'id' => $subject->id,
                                                    'name' => $subject->name,
                                                    // دمج كل الملفات وإزالة التكرار
                                                    'materials' => $materials
                                                        ->flatten()
                                                        ->unique('id')
                                                        ->map(function ($material) {
                                                            return [
                                                                'id' => $material->id,
                                                                'title' => $material->title,
                                                                'file_url' => asset('storage/' . $material->file_path),
                                                                'status' => $material->status,  
                                                                'user_id' => $material->user_id,
                                                                'course_id' => $material->course_id,
                                                                'processed_by_user_id' => $material->processed_by_user_id,
                                                                'created_at' => $material->created_at,
                                                                'updated_at' => $material->updated_at,
                                                            ];
                                                        })
                                                        ->values(),
                                                ];
                                            })
                                            ->values();

                                        return [
                                            'semester' => $semester,
                                            'subjects' => $subjects,
                                        ];
                                        // ✨ --- نهاية الكود الجديد --- ✨
                                    })
                                    ->values(),
                            ];
                        })
                        ->values();

                    return [
                        'id' => $department->id,
                        'name' => $department->name,
                        'years' => $years,
                    ];
                }),
            ];
        });

        return response()->json(['data' => $structuredData]);
    }
}
