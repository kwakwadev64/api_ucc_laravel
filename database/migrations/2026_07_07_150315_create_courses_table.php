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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();

            // Enseignant qui publie le cours
            $table->foreignId('teacher_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Promotion concernée
            $table->foreignId('promotion_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Année académique
            $table->foreignId('academic_year_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('title');

            $table->text('description')
                  ->nullable();

            
            $table->string('file_path')
                  ->nullable();

            $table->string('file_type')
                  ->nullable();

            $table->boolean('is_published')
                  ->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
