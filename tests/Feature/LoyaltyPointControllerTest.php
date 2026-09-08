<?php

namespace Tests\Feature;

use App\Models\LoyaltyPoint;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyPointControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_loyalty_points(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        LoyaltyPoint::create([
            'user_id' => $user->id,
            'transaction_id' => $transaction->id,
            'points' => 125,
            'source' => 'cashback',
            'description' => 'Cashback transaksi '.$transaction->invoice_no,
        ]);

        $this->actingAs($user)->get(route('loyalty-points.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('LoyaltyPoints/Index')
                ->where('balance', 125)
                ->where('points.data.0.points', 125)
            );
    }
}
