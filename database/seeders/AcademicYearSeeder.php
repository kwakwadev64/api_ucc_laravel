<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $academicYears = [
            [
                'name' => '2025-2026',
                'start_date' => '2025-09-01',
                'end_date' => '2026-07-31',
                'status' => 'closed',
            ],
        ];

        for ($year = 2026; $year <= 2035; $year++) {
            $academicYears[] = [
                'name' => $year . '-' . ($year + 1),
                'start_date' => $year . '-09-01',
                'end_date' => ($year + 1) . '-07-31',
                'status' => $year === 2026 ? 'active' : 'closed',
            ];
        }

        foreach ($academicYears as $academicYear) {
            AcademicYear::create($academicYear);
        }
    }
}
