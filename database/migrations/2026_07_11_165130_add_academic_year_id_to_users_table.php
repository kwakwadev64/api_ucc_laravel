<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->foreignId('academic_year_id')
                ->nullable()
                ->after('promotion_id')
                ->constrained()
                ->cascadeOnDelete();

        });
    }


    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropForeign(['academic_year_id']);

            $table->dropColumn('academic_year_id');

        });
    }
};
