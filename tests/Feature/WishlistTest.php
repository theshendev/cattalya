<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_add_and_remove_a_wishlist_item(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'انگشتر',
            'slug' => 'ring',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'انگشتر طلا',
            'slug' => 'gold-ring',
            'weight_grams' => 1.5,
            'wage_percent' => 7,
            'tax_percent' => 9,
            'fee' => 0,
            'stock' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($user)->post(route('wishlist.store', $product))->assertRedirect();
        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user)->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('انگشتر طلا');

        $this->actingAs($user)->delete(route('wishlist.destroy', $product))->assertRedirect();
        $this->assertDatabaseMissing('wishlists', ['user_id' => $user->id, 'product_id' => $product->id]);
    }
}
