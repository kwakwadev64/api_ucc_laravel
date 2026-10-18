<?php

namespace Database\Seeders;

use App\Models\Identifiant;
use Illuminate\Database\Seeder;

class IdentifiantSeeder extends Seeder
{
    public function run(): void
    {
        Identifiant::updateOrCreate(
            ['code' => 'uccFSI2627@']
        );
    }
}
