<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('transactions.show', $transaction))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Transactions/Show'));
    }

    public function test_user_cannot_view_others_transaction(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder)->get(route('transactions.show', $transaction))
            ->assertForbidden();
    }

    public function test_index_only_lists_own_transactions(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Transaction::factory()->count(2)->create(['user_id' => $user->id]);
        Transaction::factory()->count(3)->create(['user_id' => $other->id]);

        $this->actingAs($user)->get(route('transactions.index'))
            ->assertInertia(fn ($page) => $page->has('transactions.data', 2));
    }

    public function test_index_can_render_transaction_target_data_for_reorder(): void
    {
        $user = User::factory()->create();
        Transaction::factory()->create([
            'user_id' => $user->id,
            'target_number' => '081234567890',
        ]);

        $this->actingAs($user)->get(route('transactions.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Transactions/Index')
                ->where('transactions.data.0.target_number', '081234567890')
            );
    }
}
