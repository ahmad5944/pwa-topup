<?php

namespace Database\Factories;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppNotification>
 */
class AppNotificationFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['transaction_success', 'transaction_failed', 'deposit_success', 'deposit_failed', 'info']);

        $messages = [
            'transaction_success' => 'Transaksi kamu berhasil diproses.',
            'transaction_failed' => 'Transaksi kamu gagal, saldo sudah dikembalikan.',
            'deposit_success' => 'Deposit kamu berhasil, saldo sudah ditambahkan.',
            'deposit_failed' => 'Deposit kamu gagal diproses.',
            'info' => fake()->sentence(),
        ];

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'message' => $messages[$type],
            'is_read' => fake()->boolean(40),
        ];
    }
}
