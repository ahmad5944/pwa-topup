<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_member_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('member');

        $this->actingAs($user)->get(route('admin.providers.index'))->assertForbidden();
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)->get(route('admin.providers.index'))->assertOk();
    }

    public function test_cs_can_access_admin_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('cs');

        $this->actingAs($user)->get(route('admin.deposits.index'))->assertOk();
    }

    public function test_member_cannot_access_reseller_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('member');

        $this->actingAs($user)->get(route('reseller.dashboard'))->assertForbidden();
    }

    public function test_reseller_can_access_reseller_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('reseller');

        $this->actingAs($user)->get(route('reseller.dashboard'))->assertOk();
    }

    public function test_provider_secrets_are_never_exposed_to_the_frontend(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Provider::factory()->create(['api_key' => 'super-secret-key', 'api_secret' => 'super-secret-value']);

        $response = $this->actingAs($user)->get(route('admin.providers.index'));

        $response->assertOk();
        $response->assertDontSee('super-secret-key');
        $response->assertDontSee('super-secret-value');
    }
}
