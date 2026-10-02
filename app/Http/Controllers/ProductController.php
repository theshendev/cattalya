<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\GoldPrice;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $goldPrice = GoldPrice::current();
        $goldPricePerGram = (float) ($goldPrice?->price_per_gram ?? 0);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        $query = Product::with(['category', 'media'])
            ->where('is_active', true)
            ->where('stock', '>', 0);

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $calculatedPriceSql = 'CASE WHEN is_fixed_price = 1 THEN fixed_price ELSE ROUND((weight_grams * ?) * (1 + wage_percent / 100) * (1 + tax_percent / 100) + fee, 0) END';

        if ($request->filled('min_price')) {
            $query->whereRaw($calculatedPriceSql.' >= ?', [$goldPricePerGram, max(0, $request->integer('min_price'))]);
        }

        if ($request->filled('max_price')) {
            $query->whereRaw($calculatedPriceSql.' <= ?', [$goldPricePerGram, max(0, $request->integer('max_price'))]);
        }

        $sort = $request->string('sort', 'all')->toString();
        match ($sort) {
            'price_asc' => $query->orderByRaw($calculatedPriceSql.' ASC', [$goldPricePerGram]),
            'price_desc' => $query->orderByRaw($calculatedPriceSql.' DESC', [$goldPricePerGram]),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        return view('products.index', compact('products', 'categories', 'goldPrice', 'sort'));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        $goldPrice = GoldPrice::current();
        $related = Product::with('media')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'goldPrice', 'related'));
    }
}
