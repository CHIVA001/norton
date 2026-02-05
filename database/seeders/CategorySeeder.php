<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Stock;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all existing stocks
        $stocks = Stock::all();
        
        if ($stocks->isEmpty()) {
            // If no stocks exist, create categories with new stocks
            Category::factory()->count(50)->create();
        } else {
            // Create categories and randomly assign them to existing stocks
            Category::factory()->count(50)->create([
                'stock_id' => $stocks->random()->id,
            ]);
        }
    }
}
