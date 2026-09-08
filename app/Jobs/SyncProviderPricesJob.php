<?php

namespace App\Jobs;

use App\Models\Provider;
use App\Services\ProviderRouter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncProviderPricesJob implements ShouldQueue
{
    use Queueable;

    public function handle(ProviderRouter $router): void
    {
        Provider::where('is_active', true)->each(function (Provider $provider) use ($router) {
            try {
                $driver = $router->driverFor($provider);

                foreach ($driver->checkPriceList() as $item) {
                    if (empty($item['buyer_sku_code'])) {
                        continue;
                    }

                    $provider->products()->updateOrCreate(
                        ['buyer_sku_code' => $item['buyer_sku_code']],
                        [
                            'name' => $item['product_name'] ?? $item['buyer_sku_code'],
                            'brand' => $item['brand'] ?? null,
                            'type' => $item['type'] ?? null,
                            'price_beli' => $item['price'] ?? 0,
                            // Base selling price defaults to cost; real markup is applied per price_level at checkout time.
                            'price_jual' => $item['price'] ?? 0,
                            'unlimited_stock' => $item['unlimited_stock'] ?? true,
                            'stock' => $item['stock'] ?? 0,
                            'is_active' => ($item['buyer_product_status'] ?? false) && ($item['seller_product_status'] ?? false),
                        ]
                    );
                }
            } catch (\Throwable $e) {
                Log::warning('Price sync failed for provider', ['provider_id' => $provider->id, 'error' => $e->getMessage()]);
            }
        });
    }
}
