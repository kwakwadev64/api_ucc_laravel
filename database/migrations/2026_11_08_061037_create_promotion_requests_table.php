<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('current_promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->foreignId('target_promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->string('proof_file'); // Chemin du fichier / capture du relevé
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_comment')->nullable(); // Commentaire optionnel en cas de refus
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_requests');
    }
};