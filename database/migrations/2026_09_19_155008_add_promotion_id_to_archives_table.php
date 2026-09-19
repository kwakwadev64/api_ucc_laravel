<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('archives', function (Blueprint $table) {
            $table->foreignId('promotion_id')
                ->after('name')
                ->constrained('promotions')
                ->cascadeOnDelete();

            $table->dropColumn('promotion');
        });
    }

    public function down(): void
    {
        Schema::table('archives', function (Blueprint $table) {
            $table->string('promotion')
                ->after('name');

            $table->dropForeign([
                'promotion_id'
            ]);

            $table->dropColumn('promotion_id');
        });
    }
};
