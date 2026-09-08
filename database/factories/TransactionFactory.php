<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'target_number' => fake()->numerify('08##########'),
            'status' => fake()->randomElement([
                Transaction::STATUS_SUCCESS,
                Transaction::STATUS_SUCCESS,
                Transaction::STATUS_SUCCESS,
                Transaction::STATUS_PENDING,
                Transaction::STATUS_PROCESSING,
                Transaction::STATUS_FAILED,
                Transaction::STATUS_REFUND,
            ]),
            'price' => fake()->numberBetween(1000, 500000),
            'ref_id' => (string) Str::uuid(),
            'provider_ref_id' => fake()->boolean(70) ? fake()->bothify('SN########') : null,
            'invoice_no' => 'INV-'.fake()->unique()->numerify('########').'-'.strtoupper(Str::random(8)),
        ];
    }
}
