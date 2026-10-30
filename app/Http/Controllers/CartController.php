<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart, private CheckoutService $checkout) {}

    /**
     * Step 1 of checkout: the full cart page.
     */
    public function index()
    {
        return view('cart.index', [
            'items' => $this->cart->itemsWithPrices(),
            'quote' => $this->checkout->quote(),
        ]);
    }

    /**
     * The slide-over mini cart opened from the header.
     */
    public function drawer()
    {
        return view('cart.drawer', [
            'items' => $this->cart->itemsWithPrices(),
            'total' => $this->cart->total(),
        ]);
    }

    public function add(Request $request, Product $product)
    {
        $variant = $request->filled('variant_id')
            ? $product->variants()->findOrFail($request->integer('variant_id'))
            : null;

        abort_unless($product->canBeOrdered($variant), 404);

        $this->cart->add($product, max(1, (int) $request->input('quantity', 1)), $variant);

        return back()->with('success', 'محصول به سبد خرید اضافه شد.');
    }

    public function update(Request $request, string $itemKey)
    {
        $this->cart->update($itemKey, (int) $request->input('quantity', 1));

        return back();
    }

    public function remove(string $itemKey)
    {
        $this->cart->remove($itemKey);

        return back()->with('success', 'محصول از سبد خرید حذف شد.');
    }
}
