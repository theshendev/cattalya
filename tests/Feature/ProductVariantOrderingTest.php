<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoldPrice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_weight_variant_is_added_to_the_cart_at_its_own_weight_and_price(): void
    {
        GoldPrice::create(['price_per_gram' => 1_000_000, 'is_active' => true]);
        $product = $this->createProduct();
        $variant = ProductVariant::factory()->for($product)->create(['weight_grams' => 5, 'stock' => 2]);

        $this->post(route('cart.add', $product), ['variant_id' => $variant->id])->assertRedirect();
        $this->post(route('cart.add', $product))->assertRedirect();

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSeeText('5 گرم')
            ->assertSeeText('2 گرم')
            ->assertSeeText(number_format(5_500_000))
            ->assertSeeText(number_format(2_200_000));
    }

    public function test_a_variant_of_another_product_cannot_be_added(): void
    {
        $product = $this->createProduct();
        $otherVariant = ProductVariant::factory()->for($this->createProduct(['slug' => 'other-earring']))->create();

        $this->post(route('cart.add', $product), ['variant_id' => $otherVariant->id])->assertNotFound();

        $this->assertSame([], session('cart', []));
    }

    public function test_an_out_of_stock_variant_cannot_be_added(): void
    {
        $product = $this->createProduct();
        $variant = ProductVariant::factory()->for($product)->outOfStock()->create();

        $this->post(route('cart.add', $product), ['variant_id' => $variant->id])->assertNotFound();
    }

    public function test_a_variant_can_be_removed_from_the_cart_by_its_item_key(): void
    {
        $product = $this->createProduct();
        $variant = ProductVariant::factory()->for($product)->create();
        $this->post(route('cart.add', $product), ['variant_id' => $variant->id]);
        $this->post(route('cart.add', $product));

        $this->delete(route('cart.remove', $product->id.'-'.$variant->id))->assertRedirect();

        $this->assertSame([(string) $product->id], array_map('strval', array_keys(session('cart'))));
    }

    public function test_a_made_to_order_product_without_stock_is_listed_and_orderable_beyond_stock(): void
    {
        $product = $this->createProduct(['stock' => 0, 'is_made_to_order' => true]);

        $this->get(route('products.index'))->assertSeeText($product->name);
        $this->get(route('products.show', $product->slug))->assertSeeText('ساخت سفارشی');

        $this->post(route('cart.add', $product), ['quantity' => 4])->assertRedirect();

        $this->assertSame(4, session('cart')[$product->id]['quantity']);
    }

    public function test_a_product_whose_only_stock_is_in_a_variant_is_listed(): void
    {
        $product = $this->createProduct(['stock' => 0]);
        ProductVariant::factory()->for($product)->create(['stock' => 1]);
        $soldOut = $this->createProduct(['slug' => 'sold-out', 'name' => 'گوشواره تمام‌شده', 'stock' => 0]);

        $this->get(route('products.index'))
            ->assertSeeText($product->name)
            ->assertDontSeeText($soldOut->name);
    }

    public function test_paying_for_a_variant_takes_it_out_of_the_variant_stock(): void
    {
        $product = $this->createProduct(['stock' => 4]);
        $variant = ProductVariant::factory()->for($product)->create(['stock' => 3]);
        $order = Order::create(['user_id' => User::factory()->create()->id, 'status' => 'pending', 'total_amount' => 1]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'weight_grams' => $variant->weight_grams,
            'unit_price' => 1,
            'quantity' => 2,
        ]);

        $order->markAsPaid();

        $this->assertSame(1, $variant->fresh()->stock);
        $this->assertSame(4, $product->fresh()->stock);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createProduct(array $attributes = []): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'earring'],
            ['name' => 'گوشواره', 'is_active' => true, 'sort_order' => 1],
        );

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'گوشواره حلقه‌ای',
            'slug' => 'hoop-earring',
            'weight_grams' => 2,
            'wage_percent' => 10,
            'tax_percent' => 0,
            'stock' => 3,
            'is_active' => true,
        ], $attributes));
    }
}
