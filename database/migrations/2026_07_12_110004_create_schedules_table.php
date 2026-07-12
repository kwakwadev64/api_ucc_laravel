<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute les migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {

            $table->id();

            /*
             |----------------------------------------------------------
             | Faculté concernée (obligatoire)
             |----------------------------------------------------------
             */
            $table->foreignId('faculty_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
             |----------------------------------------------------------
             | Promotion concernée (optionnelle)
             |----------------------------------------------------------
             */
            $table->foreignId('promotion_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
             |----------------------------------------------------------
             | Filière / Programme (optionnel)
             |----------------------------------------------------------
             */
            $table->foreignId('program_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
             |----------------------------------------------------------
             | Année académique
             |----------------------------------------------------------
             */
            $table->foreignId('academic_year_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
             |----------------------------------------------------------
             | Informations sur le document
             |----------------------------------------------------------
             */

            $table->string('title');

            $table->string('file_path');

            $table->string('file_type');

            /*
             |----------------------------------------------------------
             | Horaire actuellement valide ?
             |----------------------------------------------------------
             */

            $table->boolean('is_active')
                ->default(true);

            /*
             |----------------------------------------------------------
             | Administrateur ayant publié
             |----------------------------------------------------------
             */

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Annule les migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
