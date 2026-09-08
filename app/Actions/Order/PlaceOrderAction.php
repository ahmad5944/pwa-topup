<?php

namespace App\Actions\Order;

use App\Jobs\ProcessTopupOrderJob;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PlaceOrderAction
{
    /**
     * Validate balance, deduct it under a row lock, create the transaction,
     * and dispatch the async job that talks to the provider.
     */
    public function execute(?User $user, Product $product, string $targetNumber): Transaction
    {
        return DB::transaction(function () use ($user, $product, $targetNumber) {
            $price = $product->priceForLevel($user?->priceLevel);

            if ($user) {
                // Lock the row to prevent a double-spend from two concurrent orders.
                $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

                if ($lockedUser->balance < $price) {
                    throw new RuntimeException('Insufficient balance.');
                }

                $lockedUser->decrement('balance', $price);
            }

            $refId = (string) Str::uuid();

            $transaction = Transaction::create([
                'user_id' => $user?->id,
                'product_id' => $product->id,
                'target_number' => $targetNumber,
                'status' => Transaction::STATUS_PENDING,
                'price' => $price,
                'ref_id' => $refId,
                'invoice_no' => 'INV-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)),
            ]);

            $transaction->logs()->create([
                'event' => 'order_placed',
                'payload_json' => ['price' => $price, 'target_number' => $targetNumber],
            ]);

            ProcessTopupOrderJob::dispatch($transaction->id)->onQueue('topup');

            return $transaction;
        });
    }
}
