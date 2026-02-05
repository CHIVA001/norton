<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 20);
        $price = fake()->numberBetween(5000, 50000) * 100; // Price in cents
        $total = $qty * $price;

        return [
            'stock_id' => Stock::factory(),
            'qty' => $qty,
            'price' => $price,
            'total' => $total,
            'sale_date' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
