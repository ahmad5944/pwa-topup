<?php

namespace Database\Factories;

use App\Models\ResellerDownline;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResellerDownline>
 */
class ResellerDownlineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reseller_id' => User::factory(),
            'downline_user_id' => User::factory(),
        ];
    }
}
