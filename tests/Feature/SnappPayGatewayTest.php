<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SnappPayGatewayTest extends TestCase
{
    use RefreshDatabase;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'gateways.drivers.snapppay.enabled' => true,
            'gateways.drivers.snapppay.base_url' => 'https://snapp.test',
            'gateways.drivers.snapppay.username' => 'merchant',
            'gateways.drivers.snapppay.password' => 'secret',
            'gateways.drivers.snapppay.client_id' => 'cid',
            'gateways.drivers.snapppay.client_secret' => 'csecret',
        ]);
        Cache::flush();

        $user = User::factory()->create(['phone' => '09121234567']);
        $this->order = Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total_amount' => 1_070_000,
            'subtotal_amount' => 1_000_000,
            'shipping_cost' => 100_000,
            'packaging_cost' => 20_000,
            'discount_amount' => 50_000,
        ]);
        OrderItem::create([
            'order_id' => $this->order->id, 'product_name' => 'گوشواره', 'weight_grams' => 2,
            'unit_price' => 500_000, 'quantity' => 2, 'total_price' => 1_000_000,
        ]);
    }

    private function service(): PaymentService
    {
        return app(PaymentService::class);
    }

    private function fakeSuccessfulRequests(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'ACCESS', 'expires_in' => 3600]),
            '*/payment/v1/token' => Http::response(['successful' => true, 'response' => ['paymentToken' => 'PT-1', 'paymentPageUrl' => 'https://pay.snapp.test/?token=PT-1']]),
            '*/payment/v1/verify' => Http::response(['successful' => true, 'response' => ['transactionId' => 'TX-9']]),
            '*/payment/v1/settle' => Http::response(['successful' => true, 'response' => []]),
            '*/payment/v1/revert' => Http::response(['successful' => true, 'response' => []]),
        ]);
    }

    private function initiate(): ?Payment
    {
        $response = $this->service()->initiate(
            $this->order,
            'snapppay',
            fn ($name, Payment $payment) => route('payment.callback', ['gateway' => $name, 'ref' => $payment->reference]),
        );

        return $response->success ? Payment::first() : null;
    }

    public function test_it_is_hidden_until_credentials_are_present(): void
    {
        $this->assertContains('snapppay', $this->service()->availableGateways());

        config(['gateways.drivers.snapppay.client_secret' => '']);
        $this->assertNotContains('snapppay', $this->service()->availableGateways());

        config(['gateways.drivers.snapppay.client_secret' => 'x', 'gateways.drivers.snapppay.enabled' => false]);
        $this->assertNotContains('snapppay', $this->service()->availableGateways());
    }

    public function test_it_is_hidden_outside_the_contracted_amount_window(): void
    {
        config(['gateways.drivers.snapppay.min_amount' => 1_000_000, 'gateways.drivers.snapppay.max_amount' => 50_000_000]);

        $this->assertArrayHasKey('snapppay', $this->service()->gatewayOptions(5_000_000));
        $this->assertArrayNotHasKey('snapppay', $this->service()->gatewayOptions(500_000));
        $this->assertArrayNotHasKey('snapppay', $this->service()->gatewayOptions(60_000_000));
        $this->assertArrayHasKey('snapppay', $this->service()->gatewayOptions());
    }

    public function test_it_creates_an_instalment_payment_with_the_basket_in_rials(): void
    {
        $this->fakeSuccessfulRequests();

        $response = $this->service()->initiate($this->order, 'snapppay', fn () => 'https://shop.test/back');

        $this->assertTrue($response->success);
        $this->assertSame('https://pay.snapp.test/?token=PT-1', $response->redirectUrl);
        $this->assertSame('PT-1', $response->authority);

        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/api/online/v1/oauth/token')) {
                return false;
            }

            return $request->hasHeader('Authorization', 'Basic '.base64_encode('cid:csecret'))
                && $request['grant_type'] === 'password'
                && $request['scope'] === 'online-merchant'
                && $request['username'] === 'merchant';
        });

        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/api/online/payment/v1/token')) {
                return false;
            }

            $cart = $request['cartList'][0];

            return $request->hasHeader('Authorization', 'Bearer ACCESS')
                && $request['amount'] === 10_700_000
                && $request['discountAmount'] === 500_000
                && $request['paymentMethodTypeDto'] === 'INSTALLMENT'
                && $request['returnURL'] === 'https://shop.test/back'
                && $request['mobile'] === '+989121234567'
                && $cart['cartItems'][0]['amount'] === 5_000_000
                && $cart['cartItems'][0]['count'] === 2
                && $cart['cartItems'][1]['id'] === 'gift-packaging'
                && $cart['shippingAmount'] === 1_000_000
                // items 10,000,000 + packaging 200,000 + shipping 1,000,000
                && $cart['totalAmount'] === 11_200_000;
        });
    }

    public function test_it_reuses_the_cached_access_token(): void
    {
        $this->fakeSuccessfulRequests();

        $this->service()->initiate($this->order, 'snapppay', fn () => 'https://shop.test/back');
        $this->service()->initiate($this->order, 'snapppay', fn () => 'https://shop.test/back');

        $tokenCalls = Http::recorded(fn ($request) => str_ends_with($request->url(), '/oauth/token'))->count();
        $this->assertSame(1, $tokenCalls);
    }

    public function test_it_reports_gateway_errors_in_persian(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'ACCESS', 'expires_in' => 3600]),
            '*/payment/v1/token' => Http::response(['successful' => false, 'errorData' => ['errorCode' => 1001, 'message' => 'مبلغ خارج از محدوده است.']]),
        ]);

        $response = $this->service()->initiate($this->order, 'snapppay', fn () => 'https://shop.test/back');

        $this->assertFalse($response->success);
        $this->assertSame('مبلغ خارج از محدوده است.', $response->message);
        $this->assertSame(Payment::STATUS_FAILED, Payment::first()->status);
    }

    public function test_it_fails_cleanly_when_login_is_rejected(): void
    {
        Http::fake(['*/oauth/token' => Http::response(['error' => 'invalid_client'], 401)]);

        $response = $this->service()->initiate($this->order, 'snapppay', fn () => 'https://shop.test/back');

        $this->assertFalse($response->success);
    }

    public function test_callback_verifies_and_settles_then_marks_the_order_paid(): void
    {
        $this->fakeSuccessfulRequests();
        $payment = $this->initiate();

        $this->get(route('payment.callback', ['gateway' => 'snapppay', 'ref' => $payment->reference, 'state' => 'OK']))
            ->assertRedirect(route('orders.result', $this->order));

        $payment->refresh();
        $this->assertTrue($payment->isPaid());
        $this->assertSame('TX-9', $payment->ref_code);
        $this->assertSame('paid', $this->order->fresh()->status);

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/payment/v1/verify') && $request['paymentToken'] === 'PT-1');
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/payment/v1/settle') && $request['paymentToken'] === 'PT-1');
    }

    public function test_a_failed_settle_reverts_the_payment_and_leaves_the_order_unpaid(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'ACCESS', 'expires_in' => 3600]),
            '*/payment/v1/token' => Http::response(['successful' => true, 'response' => ['paymentToken' => 'PT-1', 'paymentPageUrl' => 'https://pay.snapp.test/?token=PT-1']]),
            '*/payment/v1/verify' => Http::response(['successful' => true, 'response' => []]),
            '*/payment/v1/settle' => Http::response(['successful' => false, 'errorData' => ['message' => 'settle failed']], 500),
            '*/payment/v1/revert' => Http::response(['successful' => true]),
        ]);
        $payment = $this->initiate();

        $this->get(route('payment.callback', ['gateway' => 'snapppay', 'ref' => $payment->reference]));

        $this->assertFalse($payment->fresh()->isPaid());
        $this->assertSame('pending', $this->order->fresh()->status);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/payment/v1/revert') && $request['paymentToken'] === 'PT-1');
    }

    public function test_a_declined_credit_check_fails_verification_without_settling(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'ACCESS', 'expires_in' => 3600]),
            '*/payment/v1/token' => Http::response(['successful' => true, 'response' => ['paymentToken' => 'PT-1', 'paymentPageUrl' => 'https://pay.snapp.test/?token=PT-1']]),
            '*/payment/v1/verify' => Http::response(['successful' => false, 'errorData' => ['message' => 'پرداخت تکمیل نشده است.']], 400),
        ]);
        $payment = $this->initiate();

        $this->get(route('payment.callback', ['gateway' => 'snapppay', 'ref' => $payment->reference, 'state' => 'FAILED']));

        $this->assertSame(Payment::STATUS_FAILED, $payment->fresh()->status);
        $this->assertSame('pending', $this->order->fresh()->status);
        Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/payment/v1/settle'));
    }

    public function test_it_finds_the_payment_from_the_callback_by_either_identifier(): void
    {
        $driver = $this->service()->driver('snapppay');

        $this->assertSame('REF-1', $driver->resolveReference(Request::create('/cb', 'GET', ['ref' => 'REF-1'])));
        $this->assertSame('REF-2', $driver->resolveReference(Request::create('/cb', 'POST', ['transactionId' => 'REF-2'])));
    }
}
