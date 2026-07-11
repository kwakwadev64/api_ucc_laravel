<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([

            'first_name' => 'Admin',
            'last_name' => 'UCC',

            'email' => 'admin@ucc.cd',

            'phone' => '0991111111',

            'password' => Hash::make('12345678'),

            'role' => 'super_admin',

            'faculty_id' => null,

            'promotion_id' => null,

            'academic_year_id' => null,

            'profile_photo' => null,

            'is_active' => true,

        ]);
    }
}
