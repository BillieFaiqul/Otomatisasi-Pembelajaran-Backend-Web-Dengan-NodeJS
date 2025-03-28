<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'name' => 'Omar',
            'email' => 'omar.yem1111@gmail.com',
            'password' => Hash::make('123456789'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
