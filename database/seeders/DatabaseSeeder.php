<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Promotion;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FacultySeeder::class,
            ProgramSeeder::class,
            AcademicYearSeeder::class,
            PromotionSeeder::class,
            StudentSeeder::class,
            SuperAdminSeeder::class,
            ClearSchedulesSeeder::class,
        ]);



    }
}
