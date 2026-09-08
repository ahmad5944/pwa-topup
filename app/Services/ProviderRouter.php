<?php

namespace App\Services;

use App\Contracts\TopupProviderContract;
use App\Models\Provider;
use App\Services\Providers\DigiflazzProvider;
use RuntimeException;

class ProviderRouter
{
    /** Map of provider "driver" column values to their contract implementation. */
    private const DRIVERS = [
        'digiflazz' => DigiflazzProvider::class,
    ];

    private const MAX_CONSECUTIVE_FAILURES = 5;

    /** Resolve the highest-priority active provider and its driver instance. */
    public function resolve(): TopupProviderContract
    {
        $provider = Provider::where('is_active', true)
            ->orderByDesc('priority')
            ->firstOrFail();

        return $this->driverFor($provider);
    }

    public function driverFor(Provider $provider): TopupProviderContract
    {
        $driverClass = self::DRIVERS[$provider->driver] ?? null;

        if (! $driverClass) {
            throw new RuntimeException("No driver registered for provider [{$provider->driver}].");
        }

        return new $driverClass($provider);
    }

    public function recordSuccess(Provider $provider): void
    {
        $provider->update(['consecutive_failures' => 0]);
    }

    /** Auto-disable a provider after too many consecutive failures so the next resolve() picks the failover. */
    public function recordFailure(Provider $provider): void
    {
        $provider->increment('consecutive_failures');

        if ($provider->consecutive_failures >= self::MAX_CONSECUTIVE_FAILURES) {
            $provider->update(['is_active' => false]);
        }
    }
}
