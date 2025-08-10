<?php

namespace Database\Seeders;

use App\Models\College;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CollegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        College::create(['name' => 'كلية الهندسة المعلوماتية']);
        College::create(['name' => 'كلية الآداب والعلوم الإنسانية']);
        College::create(['name' => 'كلية العلوم']);
    }
}
