<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('section_equipes', function (Blueprint $table) {
            $table->id();
            $table->string('section_id')->default('developpeurs'); // Ex: faculte, gouvernement, cp_cpa, developpeurs
            $table->string('annee');
            $table->string('titre');
            $table->text('description')->nullable();

            // Relation Foreign Key vers la table membres
            $table->foreignId('membre_id')
                  ->constrained('membres')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_equipes');
    }
};
