<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_page_renders_in_customer_layout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('account.show'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Account/Show')
                ->where('loyaltyPointsBalance', 0)
            );
    }
}
