<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promotion;
use App\Models\Faculty;
use App\Models\Program;
use Illuminate\Support\Facades\DB;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('promotions')->delete();


        // Faculté des Sciences Informatiques
        $faculty = Faculty::where('code', 'FSI')->first();


        if (!$faculty) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Promotions Licence (sans filière)
        |--------------------------------------------------------------------------
        */

        foreach (['L1', 'L2', 'L3'] as $level) {

            Promotion::create([
                'faculty_id' => $faculty->id,
                'program_id' => null,

                'name' => $level . ' ' . $faculty->code,

                'level' => $level,
                'is_active' => true,
            ]);

        }



        /*
        |--------------------------------------------------------------------------
        | Promotions Master (avec filière)
        |--------------------------------------------------------------------------
        */

        $programs = Program::where('faculty_id', $faculty->id)->get();


        foreach ($programs as $program) {

            foreach (['M1', 'M2'] as $level) {

                Promotion::create([
                    'faculty_id' => $faculty->id,
                    'program_id' => $program->id,

                    'name' => $level . ' '
                        . $faculty->code
                        . ' / '
                        . $program->code,

                    'level' => $level,
                    'is_active' => true,
                ]);

            }

        }
    }
}
