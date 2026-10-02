<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->whereHas('wishlists', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with(['category', 'media'])
            ->withMax([
                'wishlists as wished_at' => fn ($query) => $query->where('user_id', $request->user()->id),
            ], 'created_at')
            ->latest('wished_at')
            ->paginate(12);

        return view('wishlist.index', compact('products'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $request->user()->wishlists()->firstOrCreate(['product_id' => $product->id]);

        return back()->with('success', 'محصول به علاقه‌مندی‌ها اضافه شد.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $request->user()->wishlists()->where('product_id', $product->id)->delete();

        return back()->with('success', 'محصول از علاقه‌مندی‌ها حذف شد.');
    }
}
