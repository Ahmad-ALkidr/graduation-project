<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AcademicUserSeeder::class);
        $this->call(UserSeeder::class);

        $this->call([
            CollegeSeeder::class,
            DepartmentSeeder::class,
            SubjectSeeder::class, // يعتمد على وجود الأكاديميين
            CourseSeeder::class, // يعتمد على وجود المواد
            BookRequestSeeder::class,
            PostSeeder::class,
        ]);
    }
}
