<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogSearchDebounceTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_page_renders_with_searchable_products(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Pulsa Telkomsel 10K']);

        $this->actingAs($user)->get(route('catalog.index', ['search' => 'Pulsa']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Catalog/Index'));
    }
}
