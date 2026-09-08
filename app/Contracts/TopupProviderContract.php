<?php

namespace App\Contracts;

use App\Models\Transaction;

interface TopupProviderContract
{
    /**
     * Fetch the provider's current price list (prepaid products).
     *
     * @return array<int, array<string, mixed>>
     */
    public function checkPriceList(): array;

    /**
     * Submit a topup order. This call is synchronous on Digiflazz's side and
     * returns Sukses/Gagal/Pending immediately.
     *
     * @return array<string, mixed> raw provider response data
     */
    public function topup(Transaction $transaction): array;

    /**
     * Recheck the status of a previously submitted order using the same ref_id.
     *
     * @return array<string, mixed> raw provider response data
     */
    public function checkStatus(string $refId): array;
}
