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
        Schema::create('recours_courses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recours_id')
                ->constrained('recours')
                ->cascadeOnDelete();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->restrictOnDelete();

            // Nom du titulaire indiqué sur le formulaire
            $table->string('professor_name');

            $table->timestamps();

            // Un même cours ne peut être ajouté deux fois
            // au même recours.
            $table->unique(['recours_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recours_courses');
    }
};
