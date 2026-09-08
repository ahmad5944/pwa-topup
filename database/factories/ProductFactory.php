<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $priceBeli = fake()->numberBetween(1000, 500000);

        return [
            'provider_id' => Provider::factory(),
            'category_id' => Category::factory(),
            'buyer_sku_code' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'name' => fake()->words(3, true),
            'brand' => fake()->randomElement(['Telkomsel', 'Indosat', 'XL', 'Axis', 'Smartfren', 'PLN', 'Mobile Legends', 'Free Fire', 'Steam', 'Gopay', 'OVO', 'Dana']),
            'type' => fake()->randomElement(['Prepaid', 'Postpaid', 'Voucher']),
            'price_beli' => $priceBeli,
            'price_jual' => $priceBeli * 1.1,
            'unlimited_stock' => true,
            'stock' => 0,
            'is_active' => true,
        ];
    }
}
