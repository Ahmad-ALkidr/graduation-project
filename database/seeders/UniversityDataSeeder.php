<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\College;
use App\Models\Department;
use App\Models\Subject;
use App\Models\Course;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\DB;

class UniversityDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تعطيل قيود المفتاح الأجنبي مؤقتًا لتجنب المشاكل عند الحذف
        DB::statement('SET session_replication_role = \'replica\';');

        // حذف البيانات القديمة لضمان عدم التكرار
        College::truncate();
        Department::truncate();
        Subject::truncate();
        Course::truncate();

        // إعادة تفعيل قيود المفتاح الأجنبي
        DB::statement('SET session_replication_role = \'origin\';');

        // جلب مستخدم أكاديمي عشوائي لربط المواد به
        $academic = User::where('role', RoleEnum::ACADEMIC)->inRandomOrder()->first();
        if (!$academic) {
            $academic = User::factory()->create(['role' => RoleEnum::ACADEMIC]);
        }

        $collegeMajorsMap = [
            'كلية الهندسة' => [
                'هندسة معلوماتية', 'هندسة مدنية', 'هندسة ميكاترونكس',
                'هندسة اتصالات', 'هندسة كيميائية', 'هندسة زراعية',
            ],
            'كلية العلوم الصحية' => ['قسم التمريض', 'قسم الطوارئ', 'قسم التخدير'],
            'كلية الاداب والعلوم الإنسانية' => ['قسم اللغة الانكليزية', 'قسم اللغة العربية'],
            'كلية الشريعة والقانون' => [],
            'كلية التربية' => ['معلم صف', 'ارشاد نفسي', 'رياض اطفال'],
            'كلية الاقتصاد والادارة' => [],
            'كلية العلوم السياسية' => []
        ];

        foreach ($collegeMajorsMap as $collegeName => $departments) {
            $college = College::create(['name' => $collegeName]);

            foreach ($departments as $departmentName) {
                $department = Department::create([
                    'name' => $departmentName,
                    'college_id' => $college->id,
                ]);

                // إنشاء مادتين لكل سنة وفصل
                for ($year = 1; $year <= 4; $year++) {
                    // الفصل الأول
                    $subject1 = Subject::firstOrCreate(
                        ['name' => "مادة 1 - {$departmentName} - سنة {$year}"],
                        ['academic_id' => $academic->id]
                    );
                    Course::create([
                        'subject_id' => $subject1->id,
                        'department_id' => $department->id,
                        'year' => $year,
                        'semester' => 1,
                    ]);

                    // الفصل الثاني
                    $subject2 = Subject::firstOrCreate(
                        ['name' => "مادة 2 - {$departmentName} - سنة {$year}"],
                        ['academic_id' => $academic->id]
                    );
                    Course::create([
                        'subject_id' => $subject2->id,
                        'department_id' => $department->id,
                        'year' => $year,
                        'semester' => 2,
                    ]);
                }
            }
        }
    }
}
