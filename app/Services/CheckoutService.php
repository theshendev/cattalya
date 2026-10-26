<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Setting;
use App\Models\User;

class CheckoutService
{
    private const ADDRESS_KEY = 'checkout.address_id';

    private const NOTES_KEY = 'checkout.notes';

    private const COUPON_KEY = 'checkout.coupon';

    public function __construct(private CartService $cart) {}

    public function selectedAddress(User $user): ?Address
    {
        $addresses = $user->addresses()->get();

        return $addresses->firstWhere('id', session(self::ADDRESS_KEY))
            ?? $addresses->firstWhere('is_default', true)
            ?? $addresses->first();
    }

    public function selectAddress(Address $address, ?string $notes): void
    {
        session([self::ADDRESS_KEY => $address->id, self::NOTES_KEY => $notes]);
    }

    public function notes(): ?string
    {
        return session(self::NOTES_KEY);
    }

    /**
     * Try to apply a coupon code to the current cart.
     *
     * @return string|null An error message, or null on success.
     */
    public function applyCoupon(string $code): ?string
    {
        $coupon = Coupon::where('code', strtoupper(trim($code)))->first();

        if (! $coupon) {
            return 'کد تخفیف نامعتبر است.';
        }

        if ($reason = $coupon->rejectionReason((float) $this->cart->total())) {
            return $reason;
        }

        session([self::COUPON_KEY => $coupon->code]);

        return null;
    }

    public function removeCoupon(): void
    {
        session()->forget(self::COUPON_KEY);
    }

    /**
     * The applied coupon, if it is still valid for the current cart.
     */
    public function coupon(): ?Coupon
    {
        $code = session(self::COUPON_KEY);

        if (! $code) {
            return null;
        }

        $coupon = Coupon::where('code', $code)->first();

        return $coupon && $coupon->rejectionReason((float) $this->cart->total()) === null ? $coupon : null;
    }

    /**
     * Price the current cart.
     *
     * @return array{subtotal: float, discount: float, shipping: float, packaging: float, total: float, coupon: Coupon|null}
     */
    public function quote(bool $isGift = false): array
    {
        $subtotal = (float) $this->cart->total();
        $coupon = $this->coupon();
        $discount = $coupon?->discountFor($subtotal) ?? 0.0;

        $freeShippingThreshold = (float) Setting::get('free_shipping_threshold', 0);
        $isFreeShipping = $freeShippingThreshold > 0 && $subtotal >= $freeShippingThreshold;
        $shipping = $isFreeShipping ? 0.0 : (float) Setting::get('shipping_cost', 0);
        $packaging = $isGift ? (float) Setting::get('gift_packaging_cost', 0) : 0.0;

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'packaging' => $packaging,
            'total' => max(0, $subtotal - $discount + $shipping + $packaging),
            'coupon' => $coupon,
        ];
    }

    public function forget(): void
    {
        session()->forget([self::ADDRESS_KEY, self::NOTES_KEY, self::COUPON_KEY]);
    }
}
