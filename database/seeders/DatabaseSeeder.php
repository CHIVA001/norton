<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed in order: Users and Stocks first (independent)
        // Then Categories and Sales (dependent on Stocks)
        $this->call([
            UserSeeder::class,
            StockSeeder::class,
            CategorySeeder::class,
            SaleSeeder::class,
        ]);
    }
}
