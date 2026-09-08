<?php

namespace Tests\Feature\Actions;

use App\Actions\Deposit\CreditBalanceAction;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditBalanceActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_credits_user_balance_and_marks_deposit_successful(): void
    {
        $user = User::factory()->create(['balance' => 0]);
        $deposit = Deposit::factory()->create([
            'user_id' => $user->id,
            'amount' => 50000,
            'status' => Deposit::STATUS_PENDING,
        ]);

        (new CreditBalanceAction())->execute($deposit);

        $this->assertSame(50000.0, (float) $user->fresh()->balance);
        $this->assertSame(Deposit::STATUS_SUCCESS, $deposit->fresh()->status);
    }

    public function test_does_not_credit_twice_for_an_already_successful_deposit(): void
    {
        $user = User::factory()->create(['balance' => 0]);
        $deposit = Deposit::factory()->create([
            'user_id' => $user->id,
            'amount' => 50000,
            'status' => Deposit::STATUS_SUCCESS,
        ]);

        (new CreditBalanceAction())->execute($deposit);
        (new CreditBalanceAction())->execute($deposit);

        $this->assertSame(0.0, (float) $user->fresh()->balance);
    }
}
