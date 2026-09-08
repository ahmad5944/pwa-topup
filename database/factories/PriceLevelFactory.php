<?php

namespace Database\Factories;

use App\Models\PriceLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceLevel>
 */
class PriceLevelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Member', 'Silver Reseller', 'Gold Reseller', 'Platinum Reseller']),
            'markup_percent' => fake()->randomFloat(2, 0, 10),
            'is_reseller_level' => false,
        ];
    }

    public function reseller(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_reseller_level' => true,
        ]);
    }
}
