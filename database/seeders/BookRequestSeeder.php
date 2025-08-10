<?php

namespace Database\Seeders;

use App\Models\BookRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $students = User::where('role', 'student')->get();
        $courses = Course::all();

        if ($students->isEmpty() || $courses->isEmpty()) {
            $this->command->info('No students or courses found. Cannot seed book requests.');
            return;
        }

        // إنشاء طلب معتمد
        $course1 = $courses->first();
        $academic1 = $course1->subject->academic;
        BookRequest::create([
            'title' => 'ملخص شامل للبرمجة 1',
            'file_path' => 'public/seed/summary.pdf',
            'type' => 'summary',
            'status' => 'approved',
            'user_id' => $students->random()->id,
            'course_id' => $course1->id,
            'processed_by_user_id' => $academic1->id,
        ]);

        // إنشاء طلب قيد المراجعة
        BookRequest::create([
            'title' => 'دورة أسئلة قواعد البيانات',
            'file_path' => 'public/seed/questions.pdf',
            'type' => 'book',
            'status' => 'pending',
            'user_id' => $students->random()->id,
            'course_id' => $courses->last()->id,
        ]);
    }
}
