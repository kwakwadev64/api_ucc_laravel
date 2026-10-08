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
        Schema::create('recours_jury_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recours_id')
                ->constrained('recours')
                ->cascadeOnDelete();

            // Membre du jury ayant traité le recours
            $table->foreignId('jury_member_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Présentation de l'affaire
            $table->text('case_presentation')->nullable();

            // Appréciation du titulaire du cours
            $table->text('professor_appreciation')->nullable();

            // Preuves matérielles fournies
            $table->text('material_evidence')->nullable();

            // Décision du jury
            $table->enum('decision', [
                'pending',
                'founded',
                'unfounded',
            ])->default('pending');

            // Explication/commentaire de la décision
            $table->text('decision_comment')->nullable();

            $table->timestamp('decided_at')->nullable();

            $table->timestamps();

            // Un recours possède une seule fiche de décision du jury.
            $table->unique('recours_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recours_jury_reviews');
    }
};
