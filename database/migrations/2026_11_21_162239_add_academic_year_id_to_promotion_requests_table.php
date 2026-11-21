
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotion_requests', function (Blueprint $table) {
            $table->foreignId('academic_year_id')
                ->nullable()
                ->constrained('academic_years')
                ->restrictOnDelete();

            $table->unique(
                ['user_id', 'academic_year_id'],
                'unique_user_promotion_request_per_year'
            );
        });
    }

    public function down(): void
    {
        Schema::table('promotion_requests', function (Blueprint $table) {
            $table->dropUnique('unique_user_promotion_request_per_year');
            $table->dropConstrainedForeignId('academic_year_id');
        });
    }
};
