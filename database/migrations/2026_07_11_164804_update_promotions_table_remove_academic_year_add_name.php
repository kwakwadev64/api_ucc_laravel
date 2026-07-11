<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {

            // Supprimer la relation avec l'année académique
            $table->dropForeign(['academic_year_id']);

            $table->dropColumn('academic_year_id');


            // Ajouter le nom de la promotion
            $table->string('name')->after('id');

        });
    }


    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {

            $table->foreignId('academic_year_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->dropColumn('name');

        });
    }
};
