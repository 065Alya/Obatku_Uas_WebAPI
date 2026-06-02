<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@obatku.com'],
            [
                'name' => 'Alya',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // User
        User::updateOrCreate(
            ['email' => 'user@obatku.com'],
            [
                'name' => 'Michi',
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
            ]
        );
    }
}
