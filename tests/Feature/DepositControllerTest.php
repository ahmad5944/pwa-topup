<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DepositControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_and_create_pages_render_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('deposits.index'))->assertOk();
        $this->actingAs($user)->get(route('deposits.create'))->assertOk();
    }

    public function test_deposit_amount_below_minimum_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('deposits.store'), ['amount' => 5000]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('amount');
    }

    public function test_successful_deposit_creates_pending_deposit_and_returns_snap_token(): void
    {
        Http::fake([
            '*/snap/v1/transactions' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://example.com/snap',
            ], 200),
        ]);

        $user = User::factory()->create(['phone' => '081234567890']);

        $response = $this->actingAs($user)->postJson(route('deposits.store'), ['amount' => 50000]);

        $response->assertOk()->assertJson(['snap_token' => 'snap-token-123']);
        $this->assertDatabaseHas('deposits', [
            'user_id' => $user->id,
            'amount' => 50000,
            'status' => 'pending',
        ]);
    }
}
