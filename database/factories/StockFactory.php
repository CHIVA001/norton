<?php

namespace Database\Factories;

use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
{
    protected $model = Stock::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $brands = ['Michelin', 'Bridgestone', 'Goodyear', 'Continental', 'Pirelli', 'Dunlop', 'Yokohama', 'Hankook'];
        $sizes = ['195/65R15', '205/55R16', '225/45R17', '235/40R18', '245/35R19', '255/30R20'];
        
        return [
            'name' => fake()->words(2, true) . ' Tire',
            'brand' => fake()->randomElement($brands),
            'code_tire' => strtoupper(fake()->bothify('??###??')),
            'qty' => fake()->numberBetween(10, 500),
            'price' => fake()->numberBetween(5000, 50000) * 100, // Price in cents
        ];
    }
}
