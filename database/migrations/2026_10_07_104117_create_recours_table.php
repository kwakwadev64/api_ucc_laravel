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
        Schema::create('recours', function (Blueprint $table) {
            $table->id();

            // Étudiant qui introduit le recours
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Contexte académique du recours
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->restrictOnDelete();

            $table->foreignId('promotion_id')
                ->constrained('promotions')
                ->restrictOnDelete();

            // Copie des informations présentes sur le formulaire
            $table->string('last_name');
            $table->string('post_name')->nullable();
            $table->string('first_name');

            // État du recours
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'decided',
            ])->default('submitted');

            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recours');
    }
};
