<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('faculty_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->enum('level', [
                'L1',
                'L2',
                'L3',
                'M1',
                'M2',
            ]);

            $table->foreignId('academic_year_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
