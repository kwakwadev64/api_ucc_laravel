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
        Schema::create('membres', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('role');
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->text('sujet_memoire')->nullable();
            $table->string('annee')->nullable();
            $table->string('github')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('portfolio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membres');
    }
};