<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\Faculty;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer la Faculté de Sciences Informatiques
        $faculty = Faculty::where('code', 'FSI')->first();

        if (!$faculty) {
            return;
        }

        $programs = [
            [
                'name' => 'Conception des systemes d’information',
                'code' => 'CSI',
                'cycle' => 'master',
              
            ],
            [
                'name' => 'Réseaux informatiques',
                'code' => 'RXI',
                'cycle' => 'master',

            ],

        ];

        foreach ($programs as $program) {
            Program::create([
                'faculty_id' => $faculty->id,
                ...$program
            ]);
        }
    }
}
