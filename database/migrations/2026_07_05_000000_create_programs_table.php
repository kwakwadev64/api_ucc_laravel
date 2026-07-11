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
        Schema::create('programs', function (Blueprint $table) {
    $table->id();

    $table->foreignId('faculty_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->string('name');
    $table->string('code')->unique();

    $table->enum('cycle', [
        'licence',
        'master',
        'doctorat'
    ])->default('master');


    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
