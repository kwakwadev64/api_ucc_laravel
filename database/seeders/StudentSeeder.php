<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([

            'first_name' => 'Jean',
            'last_name' => 'Luma',

            'email' => 'jean@gmail.com',

            'phone' => '0990000000',

            'password' => Hash::make('12345678'),

            'role' => 'student',

            'faculty_id' => 4,

            'promotion_id' => 3,

            'academic_year_id' => 1,

            'is_active' => true,

        ]);
    }
}
