<?php

namespace App\Jobs;

use App\Actions\Order\ApplyProviderResultAction;
use App\Models\Transaction;
use App\Services\ProviderRouter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessTopupOrderJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(public readonly int $transactionId) {}

    public function handle(ProviderRouter $router, ApplyProviderResultAction $applyResult): void
    {
        $transaction = Transaction::with('product.provider')->findOrFail($this->transactionId);

        // Idempotency guard: a retried/duplicated job must not resubmit a finished order.
        if (in_array($transaction->status, [Transaction::STATUS_SUCCESS, Transaction::STATUS_FAILED], true)) {
            return;
        }

        $transaction->update(['status' => Transaction::STATUS_PROCESSING]);

        $provider = $transaction->product->provider;
        $driver = $router->driverFor($provider);

        try {
            $data = $driver->topup($transaction);
        } catch (\Throwable $e) {
            $router->recordFailure($provider);

            $transaction->logs()->create([
                'event' => 'provider_request_failed',
                'payload_json' => ['error' => $e->getMessage()],
            ]);

            Log::warning('Digiflazz topup request failed', ['transaction_id' => $transaction->id, 'error' => $e->getMessage()]);

            throw $e; // let the queue retry with backoff
        }

        $router->recordSuccess($provider);

        $transaction->logs()->create([
            'event' => 'provider_response',
            'payload_json' => $data,
        ]);

        $applyResult->execute($transaction, $data);
    }
}
