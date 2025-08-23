<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. إنشاء مستخدم Admin
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // كلمة مرور افتراضية
            'gender' => 'male',
            'birth_date' => '1990-01-01',
            'university_id' => 'ADMIN001',
            'college' => 'N/A',
            'major' => 'N/A',
            'year' => 0,
            'role' => RoleEnum::ADMIN, // تعيين الدور باستخدام الـ Enum
            'email_verified_at' => Carbon::now(), // الادمن مفعل مباشرة
            'otp_sent_at' => Carbon::now(),
        ]);

        // 2. إنشاء مستخدم Manager
        User::create([
            'first_name' => 'Manager',
            'last_name' => 'User',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'gender' => 'female',
            'birth_date' => '1992-03-15',
            'university_id' => 'MGR001',
            'college' => 'N/A',
            'major' => 'N/A',
            'year' => 0,
            'role' => RoleEnum::ADMIN, // تعيين الدور
            'email_verified_at' => Carbon::now(),
            'otp_sent_at' => Carbon::now(),
        ]);

        // 3. إنشاء مستخدم Teacher
        User::create([
            'first_name' => 'Teacher',
            'last_name' => 'User',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
            'gender' => 'male',
            'birth_date' => '1985-07-20',
            'university_id' => 'TCH001',
            'college' => 'Science',
            'major' => 'Physics',
            'year' => 0,
            'role' => RoleEnum::ACADEMIC, // تعيين الدور
            'email_verified_at' => Carbon::now(),
            'otp_sent_at' => Carbon::now(),
        ]);

        // 4. إنشاء مستخدم Student (مفعل)
        User::create([
            'first_name' => 'Student',
            'last_name' => 'Verified',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'gender' => 'female',
            'birth_date' => '2000-09-10',
            'university_id' => 'STU001',
            'college' => 'Arts',
            'major' => 'History',
            'year' => 4,
            'role' => RoleEnum::STUDENT, // تعيين الدور
            'email_verified_at' => Carbon::now(), // مفعل
            'otp_sent_at' => Carbon::now(),
        ]);

        // 5. إنشاء مستخدم Student (غير مفعل) - لاختبار عملية التحقق
        User::create([
            'first_name' => 'Student',
            'last_name' => 'Unverified',
            'email' => 'unverified@example.com',
            'password' => Hash::make('password'),
            'gender' => 'male',
            'birth_date' => '2001-01-25',
            'university_id' => 'STU002',
            'college' => 'Engineering',
            'major' => 'Software',
            'year' => 3,
            'role' => RoleEnum::STUDENT, // تعيين الدور
            'email_verified_at' => null, // غير مفعل
            'otp_sent_at' => Carbon::now()->subMinutes(10), // أُرسل الـ OTP منذ 10 دقائق (مثلاً)
        ]);

        $this->command->info('Users seeded successfully!');
    }
}
