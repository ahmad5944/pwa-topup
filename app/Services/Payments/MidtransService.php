<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;

class MidtransService
{
    private function snapBaseUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';
    }

    /** Core API (status/capture/etc.) lives on a different subdomain than Snap. */
    private function coreApiBaseUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    /**
     * Create a Snap transaction and return the response (contains `token` and `redirect_url`).
     *
     * @return array<string, mixed>
     */
    public function createSnapTransaction(string $orderId, int $grossAmount, array $customer = []): array
    {
        $response = Http::withBasicAuth(config('services.midtrans.server_key'), '')
            ->baseUrl($this->snapBaseUrl())
            ->post('/snap/v1/transactions', [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $grossAmount,
                ],
                'customer_details' => $customer,
            ])
            ->throw();

        return $response->json();
    }

    /** Verify the signature_key Midtrans sends with every notification. */
    public function isValidSignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.config('services.midtrans.server_key'));

        return hash_equals($expected, $signatureKey);
    }

    /** Fetch the current transaction status directly from Midtrans (reconciliation fallback). */
    public function getStatus(string $orderId): array
    {
        $response = Http::withBasicAuth(config('services.midtrans.server_key'), '')
            ->baseUrl($this->coreApiBaseUrl())
            ->get("/v2/{$orderId}/status")
            ->throw();

        return $response->json();
    }
}
