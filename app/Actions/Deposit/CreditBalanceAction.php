<?php

namespace App\Actions\Deposit;

use App\Models\Deposit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditBalanceAction
{
    /** Shared by the Midtrans webhook and manual admin approval so balance crediting is idempotent either way. */
    public function execute(Deposit $deposit, ?User $approver = null): void
    {
        DB::transaction(function () use ($deposit, $approver) {
            $locked = Deposit::query()->lockForUpdate()->find($deposit->id);

            if ($locked->status === Deposit::STATUS_SUCCESS) {
                return; // already credited, avoid double-crediting on duplicate notifications
            }

            User::query()->lockForUpdate()->find($locked->user_id)
                ?->increment('balance', $locked->amount);

            $locked->update([
                'status' => Deposit::STATUS_SUCCESS,
                'approved_by' => $approver?->id,
            ]);
        });
    }
}
