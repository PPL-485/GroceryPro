<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
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
        // Users — seed factory users only if less than 10 exist
        if (User::count() < 10) {
            User::factory(10 - User::count())->create();
        }

        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'     => 'Admin',
                'phone'    => '08123456789',
                'password' => Hash::make('password'),
                'status'   => 'active',
                'role'     => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'cashier@gmail.com'],
            [
                'name'     => 'Cashier',
                'phone'    => '08987654321',
                'password' => Hash::make('password'),
                'status'   => 'active',
                'role'     => 'cashier',
            ]
        );

        // Categories — seed only if table is empty
        if (Category::count() === 0) {
            Category::factory(10)->create();
        }

        // Products — seed only if table is empty
        if (Product::count() === 0 && Category::count() > 0) {
            $this->call(ProductCatalogSeeder::class);
        }

        // Transactions — seed only if table is empty
        if (\App\Models\Transaction::count() === 0) {
            $this->call(TransactionSeeder::class);
        }
    }
}
