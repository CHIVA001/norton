<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Summer Tires',
            'Winter Tires',
            'All-Season Tires',
            'Performance Tires',
            'Off-Road Tires',
            'Touring Tires',
            'Sport Tires',
            'Eco-Friendly Tires'
        ];

        return [
            'stock_id' => Stock::factory(),
            'image' => 'https://via.placeholder.com/300x200?text=' . urlencode(fake()->randomElement($categories)),
            'name' => fake()->randomElement($categories),
            'year' => fake()->numberBetween(2020, 2026),
            'count' => fake()->numberBetween(5, 100),
        ];
    }
}
