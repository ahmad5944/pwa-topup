<?php

namespace App\Services\Providers;

use App\Contracts\TopupProviderContract;
use App\Models\Provider;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;

class DigiflazzProvider implements TopupProviderContract
{
    private const BASE_URL = 'https://api.digiflazz.com/v1';

    public function __construct(private readonly Provider $provider) {}

    public function checkPriceList(): array
    {
        $response = Http::baseUrl(self::BASE_URL)
            ->post('/price-list', [
                'cmd' => 'prepaid',
                'username' => $this->provider->username,
                'sign' => $this->sign('pricelist'),
            ])
            ->throw();

        return $response->json('data', []);
    }

    public function topup(Transaction $transaction): array
    {
        $response = Http::baseUrl(self::BASE_URL)
            ->post('/transaction', [
                'username' => $this->provider->username,
                'buyer_sku_code' => $transaction->product->buyer_sku_code,
                'customer_no' => $transaction->target_number,
                'ref_id' => $transaction->ref_id,
                'sign' => $this->sign($transaction->ref_id),
            ])
            ->throw();

        return $response->json('data', []);
    }

    public function checkStatus(string $refId): array
    {
        // Digiflazz has no dedicated status endpoint for prepaid; recheck by
        // resubmitting the topup call with the same ref_id.
        $response = Http::baseUrl(self::BASE_URL)
            ->post('/transaction', [
                'username' => $this->provider->username,
                'ref_id' => $refId,
                'sign' => $this->sign($refId),
            ])
            ->throw();

        return $response->json('data', []);
    }

    /** Digiflazz signature formula: md5(username + apiKey + secondaryParam). */
    private function sign(string $secondaryParam): string
    {
        return md5($this->provider->username.$this->provider->api_key.$secondaryParam);
    }
}
