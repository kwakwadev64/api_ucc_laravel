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
    Schema::table('photo_familles', function (Blueprint $table) {
        if (!Schema::hasColumn('photo_familles', 'faculty_id')) {
            $table->foreignId('faculty_id')
                ->nullable()
                ->constrained('faculties')
                ->nullOnDelete();
        }
    });
}

public function down(): void
{
    Schema::table('photo_familles', function (Blueprint $table) {
        if (Schema::hasColumn('photo_familles', 'faculty_id')) {
            $table->dropForeign(['faculty_id']);
            $table->dropColumn('faculty_id');
        }
    });
}
};
