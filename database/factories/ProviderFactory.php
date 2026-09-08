<?php

namespace Database\Factories;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'driver' => 'digiflazz',
            'username' => fake()->userName(),
            'api_key' => Str::random(32),
            'api_secret' => Str::random(32),
            'webhook_secret' => Str::random(32),
            'base_url' => 'https://api.digiflazz.com/v1',
            'priority' => fake()->numberBetween(1, 10),
            'is_active' => true,
            'consecutive_failures' => 0,
        ];
    }
}
