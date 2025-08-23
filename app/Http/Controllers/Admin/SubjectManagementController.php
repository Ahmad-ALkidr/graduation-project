<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Course;
use App\Models\User;
use App\Models\Department;
use App\Enums\RoleEnum;
use App\Models\College;
use Illuminate\Http\Request;

class SubjectManagementController extends Controller
{
    /**
     * Display a listing of all subjects with their details.
     */
public function index()
{
    // جلب كل المواد مع علاقاتها بكفاءة
    $subjects = Subject::with('academic', 'courses.department.college')
                       ->latest()
                       ->paginate(20);

    // بيانات ضرورية لنموذج الإضافة
    $academics = User::where('role', RoleEnum::ACADEMIC)->get();
    $colleges = College::all(); // نحتاج قائمة الكليات
    $departments = Department::all(); // نحتاج قائمة كل الأقسام

    return view('Admin.app-subjects-list', compact('subjects', 'academics', 'colleges', 'departments'));
}

    /**
     * Store a newly created subject and its course.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name',
            'academic_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'year' => 'required|integer|min:1|max:6',
            'semester' => 'required|integer|in:1,2',
        ]);

        // Create the subject
        $subject = Subject::create([
            'name' => $request->name,
            'academic_id' => $request->academic_id,
        ]);

        // Create the course to link it all together
        Course::create([
            'subject_id' => $subject->id,
            'department_id' => $request->department_id,
            'year' => $request->year,
            'semester' => $request->semester,
        ]);

        return redirect()->back()->with('success', 'Subject created successfully!');
    }

    /**
     * Remove the specified subject.
     */
    public function destroy(Subject $subject)
    {
        // Deleting the subject will also delete related courses due to database constraints
        $subject->delete();
        return redirect()->back()->with('success', 'Subject deleted successfully!');
    }
}
