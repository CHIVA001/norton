<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all existing stocks
        $stocks = Stock::all();
        
        if ($stocks->isEmpty()) {
            // If no stocks exist, create sales with new stocks
            Sale::factory()->count(50)->create();
        } else {
            // Create sales and randomly assign them to existing stocks
            for ($i = 0; $i < 50; $i++) {
                $stock = $stocks->random();
                $qty = fake()->numberBetween(1, 20);
                $price = $stock->price;
                
                Sale::factory()->create([
                    'stock_id' => $stock->id,
                    'qty' => $qty,
                    'price' => $price,
                    'total' => $qty * $price,
                ]);
            }
        }
    }
}
