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
        Schema::create('tutorials', function (Blueprint $table) {
    $table->id();

    $table->string('title');
    $table->text('description')->nullable();

    $table->string('youtube_video_id')->unique();
    $table->string('youtube_url');

    $table->string('thumbnail')->nullable();

    // NULL si la vidéo a été ajoutée individuellement
    $table->string('youtube_playlist_id')->nullable();

    // Ordre de la vidéo lorsqu'elle vient d'une playlist
    $table->unsignedInteger('position')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutorials');
    }
};
