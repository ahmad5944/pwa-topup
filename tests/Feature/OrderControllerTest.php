<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_place_an_order(): void
    {
        Queue::fake();

        $user = User::factory()->create(['balance' => 50000]);
        $product = Product::factory()->create(['is_active' => true, 'price_jual' => 10000]);

        $response = $this->actingAs($user)->post(route('orders.store'), [
            'product_id' => $product->id,
            'target_number' => '081234567890',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', ['user_id' => $user->id, 'product_id' => $product->id]);
        $this->assertSame(40000.0, (float) $user->fresh()->balance);
    }

    public function test_target_number_is_validated(): void
    {
        $user = User::factory()->create(['balance' => 50000]);
        $product = Product::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->post(route('orders.store'), [
            'product_id' => $product->id,
            'target_number' => 'not-a-number',
        ]);

        $response->assertSessionHasErrors('target_number');
    }

    public function test_insufficient_balance_returns_error_instead_of_500(): void
    {
        $user = User::factory()->create(['balance' => 100]);
        $product = Product::factory()->create(['is_active' => true, 'price_jual' => 10000]);

        $response = $this->actingAs($user)->post(route('orders.store'), [
            'product_id' => $product->id,
            'target_number' => '081234567890',
        ]);

        $response->assertSessionHasErrors('target_number');
        $this->assertDatabaseMissing('transactions', ['user_id' => $user->id]);
    }

    public function test_guests_cannot_place_orders(): void
    {
        $product = Product::factory()->create();

        $this->post(route('orders.store'), [
            'product_id' => $product->id,
            'target_number' => '081234567890',
        ])->assertRedirect(route('login'));
    }
}
