<?php

namespace Database\Factories;

use App\Models\FavoriteProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FavoriteProduct>
 */
class FavoriteProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'target_number' => fake()->boolean(70) ? fake()->numerify('08##########') : null,
        ];
    }
}
