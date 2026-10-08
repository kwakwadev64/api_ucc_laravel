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
        $this->call([
            FacultySeeder::class,
            AcademicYearSeeder::class,
            PromotionSeeder::class,
            StudentSeeder::class,
            SuperAdminSeeder::class,
            ClearSchedulesSeeder::class,
            AppealReasonSeeder::class
        ]);



    }
}
