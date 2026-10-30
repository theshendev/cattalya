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

        $selectedCategories = collect($request->input('categories', []))
            ->push($request->input('category'))
            ->filter(fn ($slug) => is_string($slug) && $slug !== '')
            ->unique()
            ->values()
            ->all();

        if ($selectedCategories !== []) {
            $query->whereHas('category', fn ($q) => $q->whereIn('slug', $selectedCategories));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $calculatedPriceSql = 'CASE WHEN is_fixed_price = 1 THEN fixed_price ELSE ROUND((weight_grams * ?) * (1 + wage_percent / 100) * (1 + tax_percent / 100) + fee, 0) END';

        $priceCeiling = (int) Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->selectRaw('MAX('.$calculatedPriceSql.') as max_price', [$goldPricePerGram])
            ->value('max_price');

        $minPrice = $this->parsePriceInput($request->input('min_price'));
        $maxPrice = $this->parsePriceInput($request->input('max_price'));

        if ($minPrice !== null) {
            $query->whereRaw($calculatedPriceSql.' >= ?', [$goldPricePerGram, $minPrice]);
        }

        if ($maxPrice !== null) {
            $query->whereRaw($calculatedPriceSql.' <= ?', [$goldPricePerGram, $maxPrice]);
        }

        $sort = $request->string('sort', 'all')->toString();
        match ($sort) {
            'price_asc' => $query->orderByRaw($calculatedPriceSql.' ASC', [$goldPricePerGram]),
            'price_desc' => $query->orderByRaw($calculatedPriceSql.' DESC', [$goldPricePerGram]),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        return view('products.index', compact('products', 'categories', 'goldPrice', 'sort', 'selectedCategories', 'priceCeiling', 'minPrice', 'maxPrice'));
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

    /**
     * Turn a formatted price such as "۱.۳۰۰.۰۰۰" or "1,300,000" into an integer amount.
     */
    private function parsePriceInput(mixed $value): ?int
    {
        if (! is_scalar($value)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', strtr((string) $value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]));

        return $digits === '' ? null : (int) $digits;
    }
}
