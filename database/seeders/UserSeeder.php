<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'email'    => 'admin@nexaflow.com',
            'password' => Hash::make('password123'), // Password Anda
            'role'     => 'admin',
            'status'   => true,
        ]);
    }
}