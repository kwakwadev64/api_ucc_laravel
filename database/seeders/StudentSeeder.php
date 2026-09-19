<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Promotion;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faculty = Faculty::where('code', 'FSI')->firstOrFail();

        $promotion = Promotion::where('name', 'L1')
            ->where('faculty_id', $faculty->id)
            ->firstOrFail();

        $academicYear = AcademicYear::where('name', '2026-2027')
            ->firstOrFail();

        User::create([
            'first_name' => 'Jean',
            'last_name' => 'Luma',
            'email' => 'jean@gmail.com',
            'phone' => '0990000000',
            'password' => Hash::make('12345678'),
            'role' => 'student',
            'faculty_id' => $faculty->id,
            'promotion_id' => $promotion->id,
            'academic_year_id' => $academicYear->id,
            'is_active' => true,
        ]);
    }
}
