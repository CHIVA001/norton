<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'degree' => $this->faker->randomElement(['Bachelors', 'Masters', 'PhD']),
            'tel' => $this->faker->phoneNumber(),
        ];
    }
}