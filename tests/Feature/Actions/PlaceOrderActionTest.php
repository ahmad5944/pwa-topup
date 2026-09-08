<?php

namespace Tests\Feature\Actions;

use App\Actions\Order\PlaceOrderAction;
use App\Jobs\ProcessTopupOrderJob;
use App\Models\PriceLevel;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class PlaceOrderActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deducts_balance_creates_transaction_and_dispatches_job(): void
    {
        Queue::fake();

        $user = User::factory()->create(['balance' => 50000]);
        $product = Product::factory()->create(['price_jual' => 10000]);

        $transaction = (new PlaceOrderAction())->execute($user, $product, '081234567890');

        $this->assertSame(40000.0, (float) $user->fresh()->balance);
        $this->assertSame(Transaction::STATUS_PENDING, $transaction->status);
        $this->assertDatabaseHas('transaction_logs', [
            'transaction_id' => $transaction->id,
            'event' => 'order_placed',
        ]);
        Queue::assertPushed(ProcessTopupOrderJob::class, fn ($job) => $job->transactionId === $transaction->id);
    }

    public function test_throws_when_balance_is_insufficient(): void
    {
        Queue::fake();

        $user = User::factory()->create(['balance' => 1000]);
        $product = Product::factory()->create(['price_jual' => 10000]);

        $this->expectException(RuntimeException::class);

        (new PlaceOrderAction())->execute($user, $product, '081234567890');
    }

    public function test_applies_price_level_markup(): void
    {
        Queue::fake();

        $level = PriceLevel::factory()->create(['markup_percent' => 10]);
        $user = User::factory()->create(['balance' => 50000, 'price_level_id' => $level->id]);
        $product = Product::factory()->create(['price_jual' => 10000]);

        $transaction = (new PlaceOrderAction())->execute($user, $product, '081234567890');

        $this->assertSame(11000.0, (float) $transaction->price);
    }
}
