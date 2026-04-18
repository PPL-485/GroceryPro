<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'phone' => '08123456789',
            'password' => Hash::make('password'),
            'status' => 'active',
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Cashier',
            'email' => 'cashier@gmail.com',
            'phone' => '08987654321',
            'password' => Hash::make('password'),
            'status' => 'active',
            'role' => 'cashier',
        ]);
    }
}
