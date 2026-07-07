<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faculty;

class FacultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faculties = [
            [
                'name' => 'Faculté d’Économie et Développement',
                'code' => 'FED'
            ],
            [
                'name' => 'Faculté de Droit',
                'code' => 'FDR'
            ],
            [
                'name' => 'Faculté de Médecine',
                'code' => 'FME'
            ],
            [
                'name' => 'Faculté de Sciences Informatiques',
                'code' => 'FSI'
            ],
            [
                'name' => 'Faculté des Sciences Politiques',
                'code' => 'FSP'
            ],
            [
                'name' => 'Faculté des Communications Sociales',
                'code' => 'FCS'
            ],
            [
                'name' => 'Faculté de Théologie',
                'code' => 'FTH'
            ],
            [
                'name' => 'Faculté de Droit Canonique',
                'code' => 'FDC'
            ],
            [
                'name' => 'Faculté de Philosophie',
                'code' => 'FPH'
            ],
        ];

        foreach ($faculties as $faculty) {
            Faculty::create($faculty);
        }
    }
}
