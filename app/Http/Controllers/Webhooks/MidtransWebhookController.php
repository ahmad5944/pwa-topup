<?php

namespace App\Http\Controllers\Webhooks;

use App\Actions\Deposit\CreditBalanceAction;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Services\Payments\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, MidtransService $midtrans, CreditBalanceAction $creditBalance): Response
    {
        $orderId = (string) $request->input('order_id');
        $statusCode = (string) $request->input('status_code');
        $grossAmount = (string) $request->input('gross_amount');
        $signatureKey = (string) $request->input('signature_key');

        if (! $midtrans->isValidSignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning('Midtrans webhook signature mismatch', ['order_id' => $orderId]);

            return response('Invalid signature', 401);
        }

        $deposit = Deposit::where('order_id', $orderId)->first();

        if (! $deposit) {
            return response('Deposit not found', 404);
        }

        $transactionStatus = $request->input('transaction_status');
        $fraudStatus = $request->input('fraud_status');

        // Treat capture+accept and settlement as paid; deny/cancel/expire as failed; pending stays pending.
        if (in_array($transactionStatus, ['settlement', 'capture'], true) && ($fraudStatus === null || $fraudStatus === 'accept')) {
            $creditBalance->execute($deposit);
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'], true)) {
            $deposit->update(['status' => Deposit::STATUS_FAILED]);
        }

        $deposit->update(['gateway_ref_id' => $request->input('transaction_id')]);

        return response('OK');
    }
}
