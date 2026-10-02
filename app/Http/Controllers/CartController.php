<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index()
    {
        return view('cart.index', [
            'items' => $this->cart->itemsWithPrices(),
            'total' => $this->cart->total(),
        ]);
    }

    public function add(Request $request, Product $product)
    {
        abort_unless($product->is_active && $product->stock > 0, 404);

        $this->cart->add($product, max(1, (int) $request->input('quantity', 1)));

        return back()->with('success', 'محصول به سبد خرید اضافه شد.');
    }

    public function update(Request $request, int $productId)
    {
        $this->cart->update($productId, (int) $request->input('quantity', 1));

        return back();
    }

    public function remove(int $productId)
    {
        $this->cart->remove($productId);

        return back()->with('success', 'محصول از سبد خرید حذف شد.');
    }
}
