<?php

namespace Database\Factories;

use App\Models\Deposit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Deposit>
 */
class DepositFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => fake()->numberBetween(10000, 1000000),
            'method' => fake()->randomElement(['midtrans_va', 'midtrans_qris', 'midtrans_ewallet', 'manual_transfer']),
            'status' => fake()->randomElement([
                Deposit::STATUS_SUCCESS,
                Deposit::STATUS_SUCCESS,
                Deposit::STATUS_PENDING,
                Deposit::STATUS_FAILED,
            ]),
            'order_id' => 'DEP-'.fake()->unique()->numerify('########').'-'.strtoupper(Str::random(8)),
            'gateway_ref_id' => fake()->boolean(60) ? fake()->uuid() : null,
        ];
    }
}
