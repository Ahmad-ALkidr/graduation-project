<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Department;
use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $sweDept = Department::where('name', 'قسم هندسة البرمجيات')->first();
        $prog1 = Subject::where('name', 'البرمجة 1')->first();
        $db = Subject::where('name', 'قواعد البيانات')->first();

        // إنشاء مقررات لقسم هندسة البرمجيات
        Course::create(['subject_id' => $prog1->id, 'department_id' => $sweDept->id, 'year' => 1, 'semester' => 'first']);
        Course::create(['subject_id' => $db->id, 'department_id' => $sweDept->id, 'year' => 2, 'semester' => 'second']);
    }
}
