<?php

namespace App\Actions\Order;

use App\Events\TransactionCompleted;
use App\Events\TransactionFailed;
use App\Models\Commission;
use App\Models\ResellerDownline;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApplyProviderResultAction
{
    /** Map a Digiflazz "Sukses"/"Gagal"/"Pending" response onto a transaction. */
    public function execute(Transaction $transaction, array $data): void
    {
        $status = $data['status'] ?? null;

        $transaction->update([
            'provider_ref_id' => $data['sn'] ?? $transaction->provider_ref_id,
        ]);

        match ($status) {
            'Sukses' => $this->markSuccess($transaction),
            'Gagal' => $this->markFailed($transaction),
            // "Pending" is left as-is; the reconciliation job will recheck it later.
            default => null,
        };
    }

    private function markSuccess(Transaction $transaction): void
    {
        $transaction->update(['status' => Transaction::STATUS_SUCCESS]);

        $this->creditResellerCommission($transaction);

        TransactionCompleted::dispatch($transaction);
    }

    private function markFailed(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $transaction->update(['status' => Transaction::STATUS_FAILED]);

            if ($transaction->user_id) {
                User::query()->lockForUpdate()->find($transaction->user_id)
                    ?->increment('balance', $transaction->price);
            }
        });

        TransactionFailed::dispatch($transaction);
    }

    /** Credit the upline reseller a flat margin (price_jual - price_beli) when a downline's order succeeds. */
    private function creditResellerCommission(Transaction $transaction): void
    {
        if (! $transaction->user_id) {
            return;
        }

        $reseller = ResellerDownline::where('downline_user_id', $transaction->user_id)->first()?->reseller;

        if (! $reseller) {
            return;
        }

        $margin = $transaction->product->price_jual - $transaction->product->price_beli;

        if ($margin <= 0) {
            return;
        }

        Commission::create([
            'reseller_id' => $reseller->id,
            'transaction_id' => $transaction->id,
            'amount' => $margin,
            'status' => Commission::STATUS_PENDING,
        ]);
    }
}
