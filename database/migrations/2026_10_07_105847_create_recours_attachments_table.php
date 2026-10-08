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
        Schema::create('recours_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recours_id')
                ->constrained('recours')
                ->cascadeOnDelete();

            // Motif auquel la pièce est associée.
            // Nullable car une pièce peut éventuellement
            // être générale au recours.
            $table->foreignId('reason_id')
                ->nullable()
                ->constrained('appeal_reasons')
                ->nullOnDelete();

            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recours_attachments');
    }
};
