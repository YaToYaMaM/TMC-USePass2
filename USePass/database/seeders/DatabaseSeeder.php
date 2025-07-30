<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'first_name' => 'Admin',
                'last_name' => 'Account',
                'middle_initial' => 'A',
                'role' => 'admin',
                'contact_number' => '09123456789',
                'profile_image' => null,
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'),
            ]);
        }

        if (!User::where('email', 'guard@example.com')->exists()) {
            User::create([
                'first_name' => 'Guard',
                'last_name' => 'User',
                'middle_initial' => 'G',
                'role' => 'guard',
                'contact_number' => '09987654321',
                'profile_image' => null,
                'email' => 'guard@example.com',
                'password' => Hash::make('guard123'),
            ]);
        }
    }
}
