<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\GoldPrice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'gateways.drivers.zarinpal.merchant_id' => str_repeat('a', 36),
            'gateways.drivers.zarinpal.sandbox' => true,
        ]);

        GoldPrice::create(['price_per_gram' => 1_000_000, 'is_active' => true]);

        $this->product = Product::create([
            'category_id' => Category::create(['name' => 'گوشواره', 'slug' => 'earring', 'is_active' => true, 'sort_order' => 1])->id,
            'name' => 'گوشواره پروانه',
            'slug' => 'butterfly-earring',
            'weight_grams' => 2,
            'wage_percent' => 10,
            'tax_percent' => 0,
            'stock' => 5,
            'is_active' => true,
        ]);
    }

    public function test_guest_can_manage_the_cart_but_must_log_in_to_check_out(): void
    {
        $this->post(route('cart.add', $this->product), ['quantity' => 2])->assertRedirect();

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSeeText('خلاصه سبد خرید')
            ->assertSeeText($this->product->name);

        $this->patch(route('cart.update', $this->product->id), ['quantity' => 3])->assertRedirect();
        $this->get(route('cart.index'))->assertSeeText('3');

        $this->get(route('checkout.index'))->assertRedirect(route('login'));

        $this->delete(route('cart.remove', $this->product->id))->assertRedirect();
        $this->get(route('cart.index'))->assertSeeText('سبد خرید شما خالی است');
    }

    public function test_checkout_without_an_address_asks_for_one(): void
    {
        $user = User::factory()->create(['phone' => '09121234567']);
        $this->post(route('cart.add', $this->product));

        $this->actingAs($user)->get(route('checkout.index'))
            ->assertOk()
            ->assertSeeText('هنوز آدرسی ثبت نکرده‌اید');

        $this->actingAs($user)->get(route('checkout.payment'))
            ->assertRedirect(route('checkout.index'));
    }

    public function test_user_can_add_edit_and_delete_addresses(): void
    {
        $user = User::factory()->create(['phone' => '09121234567']);

        $this->actingAs($user)->post(route('addresses.store'), $this->addressPayload())->assertRedirect();

        $address = $user->addresses()->firstOrFail();
        $this->assertTrue($address->is_default, 'the first address becomes the default');
        $this->assertSame($user->phone, $address->phone);

        $this->actingAs($user)->patch(route('addresses.update', $address), $this->addressPayload(['city' => 'کرج']))->assertRedirect();
        $this->assertSame('کرج', $address->fresh()->city);

        $this->actingAs($user)->post(route('addresses.store'), $this->addressPayload(['title' => 'شرکت', 'is_default' => 1]));
        $this->assertSame(1, $user->addresses()->where('is_default', true)->count());

        $this->actingAs($user)->delete(route('addresses.destroy', $user->addresses()->where('is_default', true)->first()))->assertRedirect();
        $this->assertTrue($user->addresses()->firstOrFail()->is_default, 'the remaining address is promoted to default');
    }

    public function test_an_address_for_someone_else_needs_their_name_and_phone(): void
    {
        $user = User::factory()->create(['phone' => '09121234567']);

        $this->actingAs($user)->post(route('addresses.store'), $this->addressPayload(['receiver_is_self' => '0']))
            ->assertSessionHasErrors([
                'receiver_name' => 'نام و نام خانوادگی گیرنده الزامی است.',
                'phone' => 'شماره همراه گیرنده الزامی است.',
            ]);

        $this->actingAs($user)->post(route('addresses.store'), $this->addressPayload([
            'receiver_is_self' => '0',
            'receiver_name' => 'پروانه موسوی',
            'phone' => '09362236405',
        ]))->assertSessionHasNoErrors();

        $address = $user->addresses()->firstOrFail();
        $this->assertSame('پروانه موسوی', $address->receiver_name);
        $this->assertSame('09362236405', $address->phone);
    }

    public function test_marking_yourself_as_the_receiver_replaces_the_receiver_details(): void
    {
        $user = User::factory()->create(['name' => 'علی', 'last_name' => 'رضایی', 'phone' => '09121234567']);
        $address = Address::create($this->addressPayload() + ['user_id' => $user->id, 'receiver_name' => 'پروانه موسوی', 'phone' => '09362236405']);

        $this->actingAs($user)->patch(route('addresses.update', $address), $this->addressPayload([
            'receiver_is_self' => '1',
            'receiver_name' => 'پروانه موسوی',
            'phone' => '09362236405',
        ]))->assertSessionHasNoErrors();

        $this->assertSame('علی رضایی', $address->fresh()->receiver_name);
        $this->assertSame('09121234567', $address->fresh()->phone);
    }

    public function test_cart_explains_that_made_to_order_items_ship_once_made(): void
    {
        $this->post(route('cart.add', $this->product));
        $this->get(route('cart.index'))->assertDontSeeText('ارسال پس از تکمیل ساخت سفارش انجام خواهد شد.');

        $this->product->update(['is_made_to_order' => true]);
        $this->post(route('cart.add', Product::create([
            'category_id' => $this->product->category_id,
            'name' => 'انگشتر سفارشی',
            'slug' => 'custom-ring',
            'weight_grams' => 3,
            'stock' => 0,
            'is_made_to_order' => true,
            'is_active' => true,
        ])));

        $this->get(route('cart.index'))->assertSeeText('ارسال پس از تکمیل ساخت سفارش انجام خواهد شد.');
    }

    public function test_payment_page_lists_switched_off_gateways_as_coming_soon(): void
    {
        config(['gateways.drivers.snapppay.enabled' => false]);
        $user = User::factory()->create(['phone' => '09121234567']);
        $user->addresses()->create($this->addressPayload() + ['receiver_name' => 'علی', 'phone' => '09121234567', 'is_default' => true]);
        $this->post(route('cart.add', $this->product));

        $this->actingAs($user)->get(route('checkout.payment'))
            ->assertOk()
            ->assertSeeTextInOrder([config('gateways.drivers.snapppay.label'), 'بزودی'])
            ->assertDontSee('name="gateway" value="snapppay"', false);
    }

    public function test_invalid_address_is_rejected_and_other_users_addresses_are_protected(): void
    {
        $user = User::factory()->create(['phone' => '09121234567']);
        $other = User::factory()->create(['phone' => '09127654321']);
        $othersAddress = Address::create($this->addressPayload() + ['user_id' => $other->id, 'receiver_name' => 'x', 'phone' => '09127654321']);

        $this->actingAs($user)->post(route('addresses.store'), $this->addressPayload(['postal_code' => '123']))
            ->assertSessionHasErrors('postal_code');

        $this->actingAs($user)->patch(route('addresses.update', $othersAddress), $this->addressPayload())->assertForbidden();
        $this->actingAs($user)->delete(route('addresses.destroy', $othersAddress))->assertForbidden();
    }

    public function test_full_purchase_flow_from_cart_to_paid_order(): void
    {
        Http::fake([
            '*/request.json' => Http::response(['data' => ['code' => 100, 'authority' => 'AUTH123'], 'errors' => []]),
            '*/verify.json' => Http::response(['data' => ['code' => 100, 'ref_id' => 555, 'card_pan' => '6037******1234'], 'errors' => []]),
        ]);
        Setting::set('shipping_cost', '50000');
        Setting::set('gift_packaging_cost', '20000');
        Setting::set('free_shipping_threshold', '0');
        $coupon = Coupon::factory()->create(['code' => 'TS42CK', 'type' => 'percent', 'value' => 10]);

        $user = User::factory()->create(['phone' => '09121234567']);
        $address = $user->addresses()->create($this->addressPayload() + ['receiver_name' => 'علی', 'phone' => '09121234567', 'is_default' => true]);

        // Step 1: cart (2 g × 1,000,000 + 10% wage = 2,200,000 each)
        $this->post(route('cart.add', $this->product), ['quantity' => 2]);
        $this->actingAs($user)->get(route('cart.index'))->assertOk()->assertSeeText('4,400,000');

        // Step 2: address
        $this->get(route('checkout.index'))->assertOk()->assertSeeText($address->city);
        $this->post(route('checkout.address'), ['address_id' => $address->id, 'notes' => 'لطفا زنگ نزنید'])
            ->assertRedirect(route('checkout.payment'));

        // Step 3: payment page, coupon, then pay as a gift
        $this->post(route('checkout.coupon'), ['code' => 'nope'])->assertSessionHasErrors('code');
        $this->post(route('checkout.coupon'), ['code' => 'ts42ck'])->assertSessionHasNoErrors();
        $this->get(route('checkout.payment'))->assertOk()->assertSeeText('TS42CK')->assertSeeText('4,010,000');

        $response = $this->post(route('checkout.store'), [
            'gateway' => 'zarinpal',
            'is_gift' => 1,
            'gift_message' => 'تولدت مبارک',
        ]);
        $response->assertRedirect('https://sandbox.zarinpal.com/pg/StartPay/AUTH123');

        $order = Order::with('items')->firstOrFail();
        $this->assertSame('pending', $order->status);
        $this->assertEquals(4_400_000, $order->subtotal_amount);
        $this->assertEquals(440_000, $order->discount_amount);
        $this->assertEquals(50_000, $order->shipping_cost);
        $this->assertEquals(20_000, $order->packaging_cost);
        $this->assertEquals(4_030_000, $order->total_amount);
        $this->assertSame('TS42CK', $order->coupon_code);
        $this->assertTrue($order->is_gift);
        $this->assertSame('تولدت مبارک', $order->gift_message);
        $this->assertSame('لطفا زنگ نزنید', $order->notes);
        $this->assertSame($address->city, $order->shipping_address['city']);
        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertEquals(4_400_000, $order->items->first()->total_price);
        $this->assertSame(0, $this->app->make(CartService::class)->count(), 'cart is emptied once the order exists');

        // Gateway sends the buyer back.
        $payment = Payment::firstOrFail();
        $this->get(route('payment.callback', ['gateway' => 'zarinpal', 'ref' => $payment->reference, 'Authority' => 'AUTH123', 'Status' => 'OK']))
            ->assertRedirect(route('orders.result', $order));

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(3, $this->product->fresh()->stock, 'stock is reduced when the order is paid');

        $this->get(route('orders.result', $order))
            ->assertOk()
            ->assertSeeText('پرداخت با موفقیت انجام شد')
            ->assertSeeText('555');
        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSeeText('علی')
            ->assertSeeText('تولدت مبارک');
        $this->get(route('orders.index'))->assertOk();

        // A replayed callback must not take stock a second time.
        $this->get(route('payment.callback', ['gateway' => 'zarinpal', 'ref' => $payment->reference, 'Authority' => 'AUTH123', 'Status' => 'OK']));
        $this->assertSame(3, $this->product->fresh()->stock);
    }

    public function test_failed_payment_shows_retry_and_retry_restarts_the_payment(): void
    {
        Http::fake([
            '*/request.json' => Http::response(['data' => ['code' => 100, 'authority' => 'AUTH123'], 'errors' => []]),
        ]);

        $user = User::factory()->create(['phone' => '09121234567']);
        $user->addresses()->create($this->addressPayload() + ['receiver_name' => 'علی', 'phone' => '09121234567', 'is_default' => true]);
        $this->actingAs($user)->post(route('cart.add', $this->product));
        $this->post(route('checkout.store'), ['gateway' => 'zarinpal']);

        $order = Order::firstOrFail();
        $payment = Payment::firstOrFail();

        $this->get(route('payment.callback', ['gateway' => 'zarinpal', 'ref' => $payment->reference, 'Authority' => 'AUTH123', 'Status' => 'NOK']))
            ->assertRedirect(route('orders.result', $order));

        $this->get(route('orders.result', $order))
            ->assertOk()
            ->assertSeeText('پرداخت انجام نشد')
            ->assertSeeText('تلاش مجدد');

        $this->post(route('payment.pay', $order), ['gateway' => 'zarinpal'])
            ->assertRedirect('https://sandbox.zarinpal.com/pg/StartPay/AUTH123');
        $this->assertSame(2, Payment::count());
    }

    public function test_coupon_rules_are_enforced(): void
    {
        $user = User::factory()->create(['phone' => '09121234567']);
        $this->actingAs($user)->post(route('cart.add', $this->product));

        Coupon::factory()->create(['code' => 'EXPIRED', 'expires_at' => now()->subDay()]);
        Coupon::factory()->create(['code' => 'USEDUP', 'usage_limit' => 1, 'used_count' => 1]);
        Coupon::factory()->create(['code' => 'BIGONLY', 'min_order' => 99_000_000]);
        Coupon::factory()->create(['code' => 'OFF', 'is_active' => false]);

        foreach (['EXPIRED', 'USEDUP', 'BIGONLY', 'OFF', 'MISSING'] as $code) {
            $this->post(route('checkout.coupon'), ['code' => $code])->assertSessionHasErrors('code');
        }
    }

    public function test_only_the_owner_can_see_an_order_result(): void
    {
        $order = Order::create(['user_id' => User::factory()->create(['phone' => '09121234567'])->id, 'status' => 'pending', 'total_amount' => 1]);

        $this->actingAs(User::factory()->create(['phone' => '09127654321']))
            ->get(route('orders.result', $order))
            ->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function addressPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'خونه',
            'province' => 'تهران',
            'city' => 'تهران',
            'address' => 'خیابان ولیعصر',
            'plaque' => '70',
            'unit' => '7',
            'postal_code' => '1234567890',
        ], $overrides);
    }
}
