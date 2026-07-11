<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promotion;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\AcademicYear;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Faculté de Sciences Informatiques
        $faculty = Faculty::where('code', 'FSI')->first();

        // Année académique active
        $academicYear = AcademicYear::where('status', 'active')->first();





        if (!$faculty || !$academicYear) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Promotions Licence
        |--------------------------------------------------------------------------
        */

        $licencePromotions = [
            [

                'level' => 'L1',
            ],
            [

                'level' => 'L2',
            ],
            [

                'level' => 'L3',
            ],
        ];


        foreach ($licencePromotions as $promotion) {
            Promotion::create([
                'faculty_id' => $faculty->id,
                'program_id' => null,
                'academic_year_id' => $academicYear->id,

                'level' => $promotion['level'],
                'is_active' => true,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Promotions Master
        |--------------------------------------------------------------------------
        */

        $programs = Program::where('faculty_id', $faculty->id)->get();


        foreach ($programs as $program) {

            Promotion::create([
                'faculty_id' => $faculty->id,
                'program_id' => $program->id,
                'academic_year_id' => $academicYear->id,

                'level' => 'M1',
                'is_active' => true,
            ]);


            Promotion::create([
                'faculty_id' => $faculty->id,
                'program_id' => $program->id,
                'academic_year_id' => $academicYear->id,
              
                'level' => 'M2',
                'is_active' => true,
            ]);
        }
    }
}
