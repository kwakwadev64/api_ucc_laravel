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
        Schema::create('recours_reasons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recours_id')
                ->constrained('recours')
                ->cascadeOnDelete();

            $table->foreignId('reason_id')
                ->constrained('appeal_reasons')
                ->restrictOnDelete();

            // Explication complémentaire si nécessaire
            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(['recours_id', 'reason_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recours_reasons');
    }
};
