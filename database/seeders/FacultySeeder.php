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
        Faculty::query()->delete();

        Faculty::create([
            'name' => 'Faculté des Sciences Informatiques',
            'code' => 'FSI',
        ]);
    }
}
