<?php

namespace App\Http\Controllers\Webhooks;

use App\Actions\Order\ApplyProviderResultAction;
use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DigiflazzWebhookController extends Controller
{
    public function __invoke(Request $request, ApplyProviderResultAction $applyResult): Response
    {
        $provider = Provider::where('driver', 'digiflazz')->firstOrFail();

        if (! $this->hasValidSignature($request, $provider)) {
            Log::warning('Digiflazz webhook signature mismatch');

            return response('Invalid signature', 401);
        }

        $data = $request->input('data', []);
        $refId = $data['ref_id'] ?? null;

        $transaction = $refId ? Transaction::where('ref_id', $refId)->first() : null;

        if (! $transaction) {
            return response('Transaction not found', 404);
        }

        // Idempotent: a finished transaction ignores late/duplicate webhook deliveries.
        if (in_array($transaction->status, [Transaction::STATUS_SUCCESS, Transaction::STATUS_FAILED], true)) {
            return response('OK');
        }

        $transaction->logs()->create([
            'event' => 'webhook_received',
            'payload_json' => $data,
        ]);

        $applyResult->execute($transaction, $data);

        return response('OK');
    }

    private function hasValidSignature(Request $request, Provider $provider): bool
    {
        if (empty($provider->webhook_secret)) {
            return false;
        }

        $header = $request->header('X-Hub-Signature', '');
        $expected = 'sha1='.hash_hmac('sha1', $request->getContent(), $provider->webhook_secret);

        return hash_equals($expected, $header);
    }
}
