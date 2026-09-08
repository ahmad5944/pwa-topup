<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\TransactionLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransactionLog>
 */
class TransactionLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'event' => fake()->randomElement(['order_placed', 'provider_request', 'provider_response', 'webhook_received']),
            'payload_json' => ['note' => fake()->sentence()],
        ];
    }
}
