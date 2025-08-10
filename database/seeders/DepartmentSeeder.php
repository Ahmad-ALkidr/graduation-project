<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $itCollege = College::where('name', 'كلية الهندسة المعلوماتية')->first();
        $artsCollege = College::where('name', 'كلية الآداب والعلوم الإنسانية')->first();

        Department::create(['name' => 'قسم هندسة البرمجيات', 'college_id' => $itCollege->id]);
        Department::create(['name' => 'قسم نظم المعلومات', 'college_id' => $itCollege->id]);
        Department::create(['name' => 'قسم اللغة الإنجليزية', 'college_id' => $artsCollege->id]);
    }
}
