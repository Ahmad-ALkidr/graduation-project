<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\BookRequest;
use App\Models\College;
use App\Models\Department;
use App\Models\Subject;
use App\Models\Course;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class LibraryStructureController extends Controller
{
    /**
     * Display the main library management page.
     */
   // app/Http/Controllers/Admin/LibraryStructureController.php

public function index()
{
    // ✨ 1. تحديث الاستعلام لجلب الملفات الموافق عليها فقط
    $colleges = College::with([
        'departments.courses.subject',
        'departments.courses.bookRequests' => function ($query) {
            $query->where('status', 'approved');
        }
    ])->get();

    // الآن نقوم بمعالجة البيانات لإنشاء البنية الشجرية المطلوبة
    $libraryTree = $colleges->map(function ($college) {
        return [
            'id' => $college->id,
            'name' => $college->name,
            'departments' => $college->departments->map(function ($department) {
                $years = $department->courses->groupBy('year')->map(function ($yearCourses, $year) {
                    return [
                        'year' => $year,
                        'semesters' => $yearCourses->groupBy('semester')->map(function ($semesterCourses, $semester) {

                            // ✨ --- 2. بداية الكود الجديد لمعالجة المواد والملفات --- ✨
                            // سنقوم بتجميع كل الملفات لكل مادة فريدة
                            $subjects = $semesterCourses->mapToGroups(function ($course) {
                                return [$course->subject->id => $course->bookRequests];
                            })->map(function ($materials, $subjectId) use ($semesterCourses) {
                                $subject = $semesterCourses->firstWhere('subject.id', $subjectId)->subject;
                                return [
                                    'id' => $subject->id,
                                    'name' => $subject->name,
                                    // دمج كل الملفات وإزالة التكرار
                                    'materials' => $materials->flatten()->unique('id')->values()
                                ];
                            })->values();

                            return [
                                'semester' => $semester,
                                'subjects' => $subjects,
                            ];
                            // ✨ --- نهاية الكود الجديد --- ✨

                        })->values(),
                    ];
                })->values();

                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'years' => $years,
                ];
            }),
        ];
   });
    return view('admin.library-structure', ['libraryTree' => $libraryTree]);
}

    // --- College Methods ---
    public function storeCollege(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:colleges,name']);
        College::create($request->only('name'));
        return back()->with('success', 'College created successfully.');
    }
    // ... (update/destroy methods for College)

// ✨ --- دالة  لإضافة قسم --- ✨
    public function storeDepartment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:departments,name',
            'college_id' => 'required|exists:colleges,id'
        ]);
        Department::create($request->only('name', 'college_id'));
        return back()->with('success', 'Department created successfully.');
    }

    // ✨ --- NEW: Subject & Course Methods --- ✨
    public function storeSubject(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:subjects,name',
            'academic_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'year' => 'required|integer|min:1|max:6',
            'semester' => 'required|in:first,second',
        ]);

        // Find an academic to assign the subject to
        $academic = User::where('id', $request->academic_id)->where('role', RoleEnum::ACADEMIC)->firstOrFail();

        // Create the subject first
        $subject = Subject::create([
            'name' => $request->name,
            'academic_id' => $academic->id,
        ]);

        // Then, create the course to link it to the department, year, and semester
        Course::create([
            'subject_id' => $subject->id,
            'department_id' => $request->department_id,
            'year' => $request->year,
            'semester' => $request->semester,
        ]);

        return back()->with('success', 'Subject and Course created successfully.');
    }
    // ✨ --- دالة جديدة لإضافة ملف (محتوى) لمادة --- ✨
// in LibraryStructureController.php

public function storeMaterial(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx|max:10240',
        'subject_id' => 'required|exists:subjects,id',
        'department_id' => 'required|exists:departments,id',
        'year' => 'required|integer',
        'semester' => 'required|string',
    ]);

    $course = Course::where('subject_id', $request->subject_id)
                    ->where('department_id', $request->department_id)
                    ->where('year', $request->year)
                    ->where('semester', $request->semester)
                    ->firstOrFail();

    $filePath = $request->file('file')->store('book_requests', 'public');

    // ✨ --- هذا هو التصحيح --- ✨
    // نستخدم Auth::guard('admin')->id() لنكون أكثر تحديدًا
    $adminId = Auth::guard('admin')->id();

    BookRequest::create([
        'title' => $request->title,
        'file_path' => $filePath,
        'course_id' => $course->id,
        'user_id' => $adminId, // نمرر رقم المدير هنا
        'status' => 'approved',
        'processed_by_user_id' => $adminId, // المدير هو من قام بالمعالجة أيضًا
    ]);

    return back()->with('success', 'Material added successfully.');
}

}
