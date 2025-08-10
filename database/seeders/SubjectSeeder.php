<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Subject;
use App\Models\Department;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        // جلب الأكاديمي الحقيقي
        $realAcademic = User::where('email', 'ahmadalkhder2002g.2@gmail.com')->first();

        // --- التصحيح الرئيسي هنا ---
        // تحقق أولاً من أننا وجدنا المستخدم قبل المتابعة
        if (!$realAcademic) {
            $this->command->error('The real academic user with email ahmadalkhder2002g.2@gmail.com was not found. Please ensure AcademicUserSeeder runs first.');
            return; // أوقف التنفيذ إذا لم يتم العثور عليه
        }

        // الآن يمكننا المتابعة بأمان
        $otherAcademics = User::where('role', 'academic')->where('id', '!=', $realAcademic->id)->get();

        // إنشاء المواد وربطها
        $prog1 = Subject::create(['name' => 'البرمجة 1', 'academic_id' => $realAcademic->id]);

        // تأكد من وجود أكاديميين آخرين قبل استخدامهم
        if ($otherAcademics->isNotEmpty()) {
            $db = Subject::create(['name' => 'قواعد البيانات', 'academic_id' => $otherAcademics->random()->id]);
            $networks = Subject::create(['name' => 'شبكات الحاسوب', 'academic_id' => $otherAcademics->random()->id]);
            $engLit = Subject::create(['name' => 'الأدب الإنجليزي الحديث', 'academic_id' => $otherAcademics->random()->id]);
        } else {
            // في حال عدم وجود أكاديميين آخرين، قم بربط كل المواد بالحساب الحقيقي
            $db = Subject::create(['name' => 'قواعد البيانات', 'academic_id' => $realAcademic->id]);
            $networks = Subject::create(['name' => 'شبكات الحاسوب', 'academic_id' => $realAcademic->id]);
            $engLit = Subject::create(['name' => 'الأدب الإنجليزي الحديث', 'academic_id' => $realAcademic->id]);
        }


        // ربط المواد مع الأقسام
        $sweDept = Department::where('name', 'قسم هندسة البرمجيات')->first();
        $isDept = Department::where('name', 'قسم نظم المعلومات')->first();
        $engDept = Department::where('name', 'قسم اللغة الإنجليزية')->first();

        $sweDept->subjects()->attach([$prog1->id, $db->id]);
        $isDept->subjects()->attach([$db->id, $networks->id]);
        $engDept->subjects()->attach($engLit->id);
    }
}
