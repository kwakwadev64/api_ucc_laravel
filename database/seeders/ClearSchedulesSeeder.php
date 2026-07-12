<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClearSchedulesSeeder extends Seeder
{
    /**
     * Supprimer tous les horaires existants.
     */
    public function run(): void
    {

        /*
        |--------------------------------------------------------------------------
        | Désactiver les contraintes FK
        |--------------------------------------------------------------------------
        */

        DB::statement('PRAGMA foreign_keys = OFF');



        /*
        |--------------------------------------------------------------------------
        | Vider la table schedules
        |--------------------------------------------------------------------------
        */

        DB::table('schedules')->truncate();



        /*
        |--------------------------------------------------------------------------
        | Réactiver les contraintes FK
        |--------------------------------------------------------------------------
        */

        DB::statement('PRAGMA foreign_keys = ON');

    }
}
