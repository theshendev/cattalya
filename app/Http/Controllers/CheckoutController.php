<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\GoldPrice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CheckoutService $checkout,
        private PaymentService $payments,
    ) {}

    /**
     * Step 2: choose the delivery address.
     */
    public function index(Request $request)
    {
        if ($this->cart->items()->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'سبد خرید شما خالی است.');
        }

        $user = $request->user();

        return view('checkout.address', [
            'addresses' => $user->addresses()->orderByDesc('is_default')->latest()->get(),
            'selectedAddress' => $this->checkout->selectedAddress($user),
            'notes' => $this->checkout->notes(),
            'quote' => $this->checkout->quote(),
        ]);
    }

    public function selectAddress(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'address_id' => ['required', 'integer'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'address_id.required' => 'لطفاً یک آدرس انتخاب کنید.',
        ]);

        $address = $request->user()->addresses()->find($data['address_id']);

        if (! $address) {
            return back()->withErrors(['address_id' => 'آدرس انتخاب‌شده معتبر نیست.']);
        }

        $this->checkout->selectAddress($address, $data['notes'] ?? null);

        return redirect()->route('checkout.payment');
    }

    /**
     * Step 3: payment method, gift options and discount code.
     */
    public function payment(Request $request)
    {
        if ($this->cart->items()->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'سبد خرید شما خالی است.');
        }

        $address = $this->checkout->selectedAddress($request->user());

        if (! $address) {
            return redirect()->route('checkout.index')->withErrors(['address_id' => 'لطفاً یک آدرس انتخاب کنید.']);
        }

        return view('checkout.payment', [
            'items' => $this->cart->itemsWithPrices(),
            'address' => $address,
            'gateways' => $this->payments->gatewayOptions($this->checkout->quote()['total']),
            'upcomingGateways' => collect(config('gateways.drivers'))
                ->reject(fn (array $driver) => $driver['enabled'] ?? true)
                ->map(fn (array $driver, string $name) => $driver['label'] ?? $name)
                ->all(),
            'quote' => $this->checkout->quote(),
            'packagingCost' => $this->checkout->quote(isGift: true)['packaging'],
        ]);
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:50']], [
            'code.required' => 'کد تخفیف را وارد کنید.',
        ]);

        $error = $this->checkout->applyCoupon($request->string('code')->toString());

        return $error
            ? back()->withErrors(['code' => $error])
            : back()->with('success', 'کد تخفیف اعمال شد.');
    }

    public function removeCoupon(): RedirectResponse
    {
        $this->checkout->removeCoupon();

        return back();
    }

    /**
     * Create (or refresh) the pending order and hand the buyer to the gateway.
     */
    public function store(Request $request, PaymentController $paymentController): RedirectResponse
    {
        if ($this->cart->items()->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $user = $request->user();
        $address = $this->checkout->selectedAddress($user);

        if (! $address) {
            return redirect()->route('checkout.index')->withErrors(['address_id' => 'لطفاً یک آدرس انتخاب کنید.']);
        }

        $isGift = $request->boolean('is_gift');
        $quote = $this->checkout->quote($isGift);

        $request->validate([
            'gateway' => ['required', Rule::in(array_keys($this->payments->gatewayOptions($quote['total'])))],
            'is_gift' => ['nullable', 'boolean'],
            'gift_message' => ['nullable', 'string', 'max:300'],
        ], [
            'gateway.required' => 'روش پرداخت را انتخاب کنید.',
            'gateway.in' => 'درگاه پرداخت نامعتبر یا غیرفعال است.',
        ]);

        $items = $this->cart->itemsWithPrices();
        $goldPrice = GoldPrice::current();

        $order = DB::transaction(function () use ($user, $address, $request, $isGift, $quote, $items, $goldPrice) {
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'status' => 'pending',
                'total_amount' => $quote['total'],
                'subtotal_amount' => $quote['subtotal'],
                'shipping_cost' => $quote['shipping'],
                'packaging_cost' => $quote['packaging'],
                'discount_amount' => $quote['discount'],
                'coupon_code' => $quote['coupon']?->code,
                'is_gift' => $isGift,
                'gift_message' => $isGift ? $request->input('gift_message') : null,
                'gold_price_snapshot' => $goldPrice?->price_per_gram ?? 0,
                'shipping_address' => $this->addressSnapshot($address),
                'notes' => $this->checkout->notes(),
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'product_name' => $item['name'],
                    'weight_grams' => $item['weight_grams'],
                    'wage_percent' => $item['wage_percent'],
                    'tax_percent' => $item['tax_percent'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total_price'],
                ]);
            }

            if ($quote['coupon']) {
                Coupon::whereKey($quote['coupon']->id)->increment('used_count');
            }

            return $order;
        });

        $this->cart->clear();
        $this->checkout->forget();

        return $paymentController->pay($request, $order);
    }

    /**
     * @return array<string, string|null>
     */
    private function addressSnapshot(Address $address): array
    {
        return [
            'title' => $address->title,
            'receiver_name' => $address->receiver_name,
            'phone' => $address->phone,
            'province' => $address->province,
            'city' => $address->city,
            'address' => $address->address,
            'plaque' => $address->plaque,
            'unit' => $address->unit,
            'postal_code' => $address->postal_code,
        ];
    }
}
