<?php

namespace App\Http\Controllers;

use App\Models\GoldPrice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private PaymentService $payments,
    ) {}

    public function index()
    {
        if ($this->cart->items()->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'سبد خرید شما خالی است.');
        }

        return view('checkout.index', [
            'items' => $this->cart->itemsWithPrices(),
            'total' => $this->cart->total(),
            'user' => Auth::user(),
            // Only gateways that are enabled and hold credentials.
            'gateways' => $this->payments->gatewayOptions(),
        ]);
    }

    public function store(Request $request)
    {
        if ($this->cart->items()->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $request->validate([
            'receiver_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^09[0-9]{9}$/'],
            'province' => ['required', 'string', 'max:50'],
            'city' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:500'],
            'postal_code' => ['required', 'digits:10'],
            'gateway' => ['required', Rule::in($this->payments->availableGateways())],
        ], [
            'receiver_name.required' => 'نام گیرنده الزامی است.',
            'phone.required' => 'شماره موبایل الزامی است.',
            'phone.regex' => 'فرمت شماره موبایل صحیح نیست.',
            'province.required' => 'استان الزامی است.',
            'city.required' => 'شهر الزامی است.',
            'address.required' => 'آدرس الزامی است.',
            'postal_code.required' => 'کد پستی الزامی است.',
            'postal_code.digits' => 'کد پستی باید ۱۰ رقم باشد.',
        ]);

        $goldPrice = GoldPrice::current();
        $items = $this->cart->itemsWithPrices();
        $total = $this->cart->total();

        $order = DB::transaction(function () use ($request, $goldPrice, $items, $total) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'status' => 'pending',
                'total_amount' => $total,
                'gold_price_snapshot' => $goldPrice?->price_per_gram,
                'address' => [
                    'receiver_name' => $request->receiver_name,
                    'phone' => $request->phone,
                    'province' => $request->province,
                    'city' => $request->city,
                    'address' => $request->address,
                    'postal_code' => $request->postal_code,
                ],
                'notes' => $request->notes,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'weight_grams' => $item['weight_grams'],
                    'wage_percent' => $item['wage_percent'],
                    'tax_percent' => $item['tax_percent'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total_price'],
                ]);
            }

            return $order;
        });

        $this->cart->clear();

        // payment.pay is POST-only, so hand off to the order page, which posts
        // to it with the gateway the customer picked here.
        return redirect()->route('orders.show', $order)
            ->with('gateway', $request->gateway);
    }
}
