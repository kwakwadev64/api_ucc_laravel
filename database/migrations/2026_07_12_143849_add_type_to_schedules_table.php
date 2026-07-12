<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter les migrations.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Type d'horaire
            |--------------------------------------------------------------------------
            | course : Horaire des cours
            | exam   : Horaire des examens
            |--------------------------------------------------------------------------
            */
            $table->enum('type', ['course', 'exam'])
                ->default('course')
                ->after('academic_year_id');

        });
    }

    /**
     * Annuler les migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
