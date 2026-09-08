<?php

namespace Tests\Feature;

use App\Models\FavoriteProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_a_favorite(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->post(route('favorites.store'), ['product_id' => $product->id])
            ->assertRedirect();

        $this->assertDatabaseHas('favorite_products', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    public function test_user_cannot_delete_others_favorite(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $favorite = FavoriteProduct::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder)->delete(route('favorites.destroy', $favorite))
            ->assertForbidden();

        $this->assertDatabaseHas('favorite_products', ['id' => $favorite->id]);
    }

    public function test_user_can_delete_own_favorite(): void
    {
        $user = User::factory()->create();
        $favorite = FavoriteProduct::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->delete(route('favorites.destroy', $favorite))
            ->assertRedirect();

        $this->assertDatabaseMissing('favorite_products', ['id' => $favorite->id]);
    }
}
