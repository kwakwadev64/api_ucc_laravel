
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photo_familles', function (Blueprint $table) {

            if (!Schema::hasColumn('photo_familles', 'promotion_id')) {
                $table->foreignId('promotion_id')
                    ->nullable()
                    ->constrained('promotions')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('photo_familles', 'academic_year_id')) {
                $table->foreignId('academic_year_id')
                    ->nullable()
                    ->constrained('academic_years')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('photo_familles', function (Blueprint $table) {
            if (Schema::hasColumn('photo_familles', 'promotion_id')) {
                $table->dropForeign(['promotion_id']);
                $table->dropColumn('promotion_id');
            }

            if (Schema::hasColumn('photo_familles', 'academic_year_id')) {
                $table->dropColumn('academic_year_id');
            }
        });
    }
};
