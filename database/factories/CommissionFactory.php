<?php

namespace Database\Factories;

use App\Models\Commission;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commission>
 */
class CommissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reseller_id' => User::factory(),
            'transaction_id' => Transaction::factory(),
            'amount' => fake()->numberBetween(500, 20000),
            'status' => fake()->randomElement([Commission::STATUS_PENDING, Commission::STATUS_PAID]),
        ];
    }
}
