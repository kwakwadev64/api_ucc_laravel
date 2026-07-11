<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicYears = [
            [
                'name' => '2025-2026',
                'start_date' => '2025-09-01',
                'end_date' => '2026-07-31',
                'status' => 'closed',
            ],
            [
                'name' => '2026-2027',
                'start_date' => '2026-09-01',
                'end_date' => '2027-07-31',
                'status' => 'active',
            ],
        ];

        foreach ($academicYears as $year) {
            AcademicYear::create($year);
        }
    }
}
