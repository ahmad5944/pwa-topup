<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_member_sees_member_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('member');

        $this->actingAs($user)->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->has('promoBanners', 2)
                ->has('frequentProducts', 0)
                ->where('loyaltyPointsBalance', 0)
            );
    }

    public function test_member_dashboard_shows_frequent_products(): void
    {
        $user = User::factory()->create();
        $user->assignRole('member');

        $firstProduct = Product::factory()->create();
        $secondProduct = Product::factory()->create();

        Transaction::factory()->count(3)->create([
            'user_id' => $user->id,
            'product_id' => $firstProduct->id,
            'status' => Transaction::STATUS_SUCCESS,
        ]);

        Transaction::factory()->count(1)->create([
            'user_id' => $user->id,
            'product_id' => $secondProduct->id,
            'status' => Transaction::STATUS_SUCCESS,
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->where('frequentProducts.0.product_id', $firstProduct->id)
                ->where('frequentProducts.0.purchase_count', 3)
                ->where('frequentProducts.1.product_id', $secondProduct->id)
                ->where('frequentProducts.1.purchase_count', 1)
            );
    }

    public function test_member_dashboard_shows_recommended_products_from_favorite_category(): void
    {
        $user = User::factory()->create();
        $user->assignRole('member');

        $category = Category::factory()->create();
        $favoriteProduct = Product::factory()->create(['category_id' => $category->id]);
        $recommendedProduct = Product::factory()->create(['category_id' => $category->id, 'name' => 'Rekomendasi A']);
        $otherCategoryProduct = Product::factory()->create();

        $user->favoriteProducts()->create([
            'product_id' => $favoriteProduct->id,
            'target_number' => '081234567890',
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->has('recommendedProducts', 1)
                ->where('recommendedProducts.0.id', $recommendedProduct->id)
            );
    }

    public function test_admin_sees_admin_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page->component('Admin/Dashboard'));
    }
}
