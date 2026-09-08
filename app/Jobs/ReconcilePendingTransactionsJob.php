<?php

namespace App\Jobs;

use App\Actions\Order\ApplyProviderResultAction;
use App\Models\Transaction;
use App\Services\ProviderRouter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ReconcilePendingTransactionsJob implements ShouldQueue
{
    use Queueable;

    private const STALE_AFTER_MINUTES = 5;

    public function handle(ProviderRouter $router, ApplyProviderResultAction $applyResult): void
    {
        Transaction::with('product.provider')
            ->where('status', Transaction::STATUS_PROCESSING)
            ->where('updated_at', '<=', now()->subMinutes(self::STALE_AFTER_MINUTES))
            ->chunkById(100, function ($transactions) use ($router, $applyResult) {
                foreach ($transactions as $transaction) {
                    $this->recheck($transaction, $router, $applyResult);
                }
            });
    }

    private function recheck(Transaction $transaction, ProviderRouter $router, ApplyProviderResultAction $applyResult): void
    {
        try {
            $driver = $router->driverFor($transaction->product->provider);
            $data = $driver->checkStatus($transaction->ref_id);

            $transaction->logs()->create([
                'event' => 'reconciliation_check',
                'payload_json' => $data,
            ]);

            $applyResult->execute($transaction, $data);
        } catch (\Throwable $e) {
            Log::warning('Reconciliation recheck failed', ['transaction_id' => $transaction->id, 'error' => $e->getMessage()]);
        }
    }
}
