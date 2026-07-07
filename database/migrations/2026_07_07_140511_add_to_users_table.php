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
    Schema::table('users', function (Blueprint $table) {
        $table->string('first_name')->nullable()->after('id');
        $table->string('last_name')->nullable()->after('first_name');
        $table->string('phone')->nullable()->after('email');

        $table->enum('role', [
            'student',
            'cp',
            'teacher',
            'faculty_admin',
            'super_admin'
        ])->default('student')->after('password');

        $table->foreignId('faculty_id')
              ->nullable()
              ->constrained()
              ->nullOnDelete()
              ->after('role');

        $table->foreignId('promotion_id')
              ->nullable()
              ->constrained()
              ->nullOnDelete()
              ->after('faculty_id');

        $table->string('profile_photo')
              ->nullable()
              ->after('promotion_id');

        $table->boolean('is_active')
              ->default(true)
              ->after('profile_photo');

        $table->dropColumn('name');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
