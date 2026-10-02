<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_range_filters_products_using_the_displayed_price(): void
    {
        [$lowerPricedProduct, $higherPricedProduct] = $this->createProducts();

        $response = $this->get(route('products.index', [
            'min_price' => 10_000_000,
            'max_price' => 20_000_000,
        ]));

        $response
            ->assertOk()
            ->assertSeeText($higherPricedProduct->name)
            ->assertDontSeeText($lowerPricedProduct->name);
    }

    public function test_products_can_be_sorted_by_displayed_price(): void
    {
        [$lowerPricedProduct, $higherPricedProduct] = $this->createProducts();

        $response = $this->get(route('products.index', ['sort' => 'price_desc']));

        $response
            ->assertOk()
            ->assertSeeInOrder([$higherPricedProduct->name, $lowerPricedProduct->name]);
    }

    /**
     * @return array{Product, Product}
     */
    private function createProducts(): array
    {
        $category = Category::create([
            'name' => 'گردنبند',
            'slug' => 'necklace',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $lowerPricedProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'گردنبند اقتصادی',
            'slug' => 'affordable-necklace',
            'is_fixed_price' => true,
            'fixed_price' => 5_000_000,
            'stock' => 1,
            'is_active' => true,
        ]);

        $higherPricedProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'گردنبند ویژه',
            'slug' => 'premium-necklace',
            'is_fixed_price' => true,
            'fixed_price' => 15_000_000,
            'stock' => 1,
            'is_active' => true,
        ]);

        return [$lowerPricedProduct, $higherPricedProduct];
    }
}
