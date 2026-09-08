<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('catalog.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_sees_only_active_products(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'is_active' => true, 'name' => 'Aktif Produk']);
        Product::factory()->create(['category_id' => $category->id, 'is_active' => false, 'name' => 'Nonaktif Produk']);

        $response = $this->actingAs($user)->get(route('catalog.index'));

        $response->assertInertia(fn ($page) => $page
            ->component('Catalog/Index')
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Aktif Produk')
        );
    }

    public function test_can_filter_products_by_category_slug(): void
    {
        $user = User::factory()->create();
        $categoryA = Category::factory()->create(['slug' => 'pulsa']);
        $categoryB = Category::factory()->create(['slug' => 'data']);
        Product::factory()->create(['category_id' => $categoryA->id]);
        Product::factory()->create(['category_id' => $categoryB->id]);

        $response = $this->actingAs($user)->get(route('catalog.index', ['category' => 'pulsa']));

        $response->assertInertia(fn ($page) => $page->has('products.data', 1));
    }

    public function test_inactive_product_detail_returns_404(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['is_active' => false]);

        $this->actingAs($user)->get(route('catalog.show', $product))->assertNotFound();
    }

    public function test_product_detail_prefills_saved_target_number(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['is_active' => true]);
        $user->favoriteProducts()->create([
            'product_id' => $product->id,
            'target_number' => '081234567890',
        ]);

        $this->actingAs($user)->get(route('catalog.show', $product))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Show')
                ->where('suggestedTargetNumber', '081234567890')
                ->where('favoriteTargetNumbers.0', '081234567890')
            );
    }
}
