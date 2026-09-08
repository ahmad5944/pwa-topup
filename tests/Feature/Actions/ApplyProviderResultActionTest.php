<?php

namespace Tests\Feature\Actions;

use App\Actions\Order\ApplyProviderResultAction;
use App\Models\Product;
use App\Models\LoyaltyPoint;
use App\Models\ResellerDownline;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplyProviderResultActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_transaction_successful_and_credits_reseller_commission(): void
    {
        $reseller = User::factory()->create();
        $member = User::factory()->create();
        ResellerDownline::factory()->create([
            'reseller_id' => $reseller->id,
            'downline_user_id' => $member->id,
        ]);

        $product = Product::factory()->create(['price_beli' => 8000, 'price_jual' => 10000]);
        $transaction = Transaction::factory()->create([
            'user_id' => $member->id,
            'product_id' => $product->id,
            'status' => Transaction::STATUS_PENDING,
            'price' => 10000,
        ]);

        (new ApplyProviderResultAction())->execute($transaction, ['status' => 'Sukses', 'sn' => 'SN123']);

        $this->assertSame(Transaction::STATUS_SUCCESS, $transaction->fresh()->status);
        $this->assertSame('SN123', $transaction->fresh()->provider_ref_id);
        $this->assertDatabaseHas('commissions', [
            'reseller_id' => $reseller->id,
            'transaction_id' => $transaction->id,
            'amount' => 2000,
        ]);
        $this->assertDatabaseHas('loyalty_points', [
            'user_id' => $member->id,
            'transaction_id' => $transaction->id,
            'points' => 100,
        ]);
    }

    public function test_success_callback_is_idempotent_for_commission_and_points(): void
    {
        $reseller = User::factory()->create();
        $member = User::factory()->create();
        ResellerDownline::factory()->create([
            'reseller_id' => $reseller->id,
            'downline_user_id' => $member->id,
        ]);

        $product = Product::factory()->create(['price_beli' => 8000, 'price_jual' => 10000]);
        $transaction = Transaction::factory()->create([
            'user_id' => $member->id,
            'product_id' => $product->id,
            'status' => Transaction::STATUS_PENDING,
            'price' => 10000,
        ]);

        $action = new ApplyProviderResultAction();
        $action->execute($transaction, ['status' => 'Sukses', 'sn' => 'SN123']);
        $action->execute($transaction, ['status' => 'Sukses', 'sn' => 'SN124']);

        $this->assertSame(Transaction::STATUS_SUCCESS, $transaction->fresh()->status);
        $this->assertDatabaseCount('commissions', 1);
        $this->assertDatabaseCount('loyalty_points', 1);
        $this->assertSame('SN124', $transaction->fresh()->provider_ref_id);
    }

    public function test_marks_transaction_failed_and_refunds_balance(): void
    {
        $user = User::factory()->create(['balance' => 5000]);
        $product = Product::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => Transaction::STATUS_PENDING,
            'price' => 10000,
        ]);

        (new ApplyProviderResultAction())->execute($transaction, ['status' => 'Gagal']);

        $this->assertSame(Transaction::STATUS_FAILED, $transaction->fresh()->status);
        $this->assertSame(15000.0, (float) $user->fresh()->balance);
    }
}
