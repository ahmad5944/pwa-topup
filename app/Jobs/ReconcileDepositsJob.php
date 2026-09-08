<?php

namespace App\Jobs;

use App\Actions\Deposit\CreditBalanceAction;
use App\Models\Deposit;
use App\Services\Payments\MidtransService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ReconcileDepositsJob implements ShouldQueue
{
    use Queueable;

    private const STALE_AFTER_MINUTES = 15;

    public function handle(MidtransService $midtrans, CreditBalanceAction $creditBalance): void
    {
        Deposit::where('status', Deposit::STATUS_PENDING)
            ->where('method', 'midtrans')
            ->where('updated_at', '<=', now()->subMinutes(self::STALE_AFTER_MINUTES))
            ->chunkById(100, function ($deposits) use ($midtrans, $creditBalance) {
                foreach ($deposits as $deposit) {
                    $this->recheck($deposit, $midtrans, $creditBalance);
                }
            });
    }

    private function recheck(Deposit $deposit, MidtransService $midtrans, CreditBalanceAction $creditBalance): void
    {
        try {
            $status = $midtrans->getStatus($deposit->order_id);
            $transactionStatus = $status['transaction_status'] ?? null;
            $fraudStatus = $status['fraud_status'] ?? null;

            if (in_array($transactionStatus, ['settlement', 'capture'], true) && ($fraudStatus === null || $fraudStatus === 'accept')) {
                $creditBalance->execute($deposit);
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'], true)) {
                $deposit->update(['status' => Deposit::STATUS_FAILED]);
            }
        } catch (\Throwable $e) {
            Log::warning('Deposit reconciliation failed', ['deposit_id' => $deposit->id, 'error' => $e->getMessage()]);
        }
    }
}
