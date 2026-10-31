<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_shows_invoice_and_specifications(): void
    {
        $product = $this->createProduct([
            'karat' => 18,
            'gold_color' => 'طلای زرد',
            'shine' => 'براق',
            'dimensions' => '12*12 سانتی‌متر',
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSeeText('فاکتور خرید')
            ->assertSeeText('آماده ارسال')
            ->assertSeeText('جزئیات محصول')
            ->assertSeeText('طلای زرد')
            ->assertSeeText('براق')
            ->assertSeeText('12*12 سانتی‌متر');
    }

    public function test_karat_is_shown_with_its_millesimal_fineness(): void
    {
        $product = $this->createProduct(['karat' => 18]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSeeText('750/18')
            ->assertSeeText('طلای 18 عیار');
    }

    public function test_weight_variants_are_offered_with_sold_out_weights_disabled(): void
    {
        $product = $this->createProduct(['weight_grams' => 2.51]);
        ProductVariant::factory()->for($product)->create(['weight_grams' => 3.51, 'stock' => 2]);
        ProductVariant::factory()->for($product)->outOfStock()->create(['weight_grams' => 5.51]);

        $response = $this->get(route('products.show', $product->slug))->assertOk();

        $response->assertSeeInOrder(['2.51 گرم', '3.51 گرم', '5.51 گرم']);
        $this->assertSame(1, preg_match_all('/<input type="radio" name="weight_option"[^>]*\sdisabled>/', $response->getContent()));
    }

    public function test_out_of_stock_product_cannot_be_added_to_cart(): void
    {
        $product = $this->createProduct(['stock' => 0]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSeeText('ناموجود')
            ->assertDontSeeText('افزودن به سبد خرید');
    }

    public function test_optional_specifications_are_hidden_when_empty(): void
    {
        $product = $this->createProduct(['is_fixed_price' => true, 'fixed_price' => 1_000_000]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertDontSeeText('رنگ طلا')
            ->assertDontSeeText('درخشندگی');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createProduct(array $attributes = []): Product
    {
        $category = Category::create([
            'name' => 'گوشواره',
            'slug' => 'earring',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'گوشواره پروانه',
            'slug' => 'butterfly-earring',
            'weight_grams' => 2.5,
            'wage_percent' => 7,
            'tax_percent' => 9,
            'stock' => 3,
            'is_active' => true,
        ], $attributes));
    }
}
