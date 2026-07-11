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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('faculty_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('program_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

          

            $table->enum('level', [
                'L1',
                'L2',
                'L3',
                'M1',
                'M2',
                'D1',
                'D2',
                'D3'
            ]);

            $table->foreignId('academic_year_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->boolean('is_active')
                  ->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
