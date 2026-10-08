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
        Schema::create('appeal_reasons', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->unique();

            // Le motif nécessite-t-il une pièce justificative ?
            $table->boolean('requires_attachment')->default(false);

            // Le motif nécessite-t-il une explication ?
            $table->boolean('requires_description')->default(false);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appeal_reasons');
    }
};
