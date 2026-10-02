<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\DTOs\PaymentResponse;
use App\Services\Payment\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'gateways.drivers.zarinpal.merchant_id' => str_repeat('a', 36),
            'gateways.drivers.zarinpal.sandbox' => true,
            'gateways.drivers.idpay.api_key' => str_repeat('b', 36),
            'gateways.drivers.idpay.sandbox' => true,
            'gateways.drivers.digipay.enabled' => true,
            'gateways.drivers.digipay.client_id' => 'cid',
            'gateways.drivers.digipay.client_secret' => 'csecret',
            'gateways.drivers.digipay.username' => 'user',
            'gateways.drivers.digipay.password' => 'pass',
            'gateways.drivers.digipay.sandbox' => true,
        ]);

        $this->user = User::factory()->create(['phone' => '09121234567']);
        $this->order = Order::create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'total_amount' => 150_000,   // Tomans
        ]);
    }

    private function service(): PaymentService
    {
        return app(PaymentService::class);
    }

    private function initiate(string $gateway): PaymentResponse
    {
        return $this->service()->initiate(
            $this->order,
            $gateway,
            fn ($name, Payment $payment) => route('payment.callback', ['gateway' => $name, 'ref' => $payment->reference]),
        );
    }

    public function test_only_configured_gateways_are_offered(): void
    {
        config(['gateways.drivers.idpay.api_key' => '']);

        $this->assertSame(['zarinpal', 'digipay'], $this->service()->availableGateways());
    }

    /* --------------------------------------------------------------- ZarinPal */

    public function test_zarinpal_sends_rials_and_redirects_to_start_pay(): void
    {
        Http::fake([
            '*/request.json' => Http::response(['data' => ['code' => 100, 'authority' => 'A0000000000000000000000000000123'], 'errors' => []]),
        ]);

        $response = $this->initiate('zarinpal');

        $this->assertTrue($response->success);
        $this->assertSame('https://sandbox.zarinpal.com/pg/StartPay/A0000000000000000000000000000123', $response->redirectUrl);

        Http::assertSent(function ($request) {
            // 150,000 Tomans must go out as 1,500,000 Rials — exactly once.
            return $request['amount'] === 1_500_000 && $request['currency'] === 'IRR';
        });
    }

    public function test_zarinpal_maps_error_codes_to_persian_messages(): void
    {
        Http::fake([
            '*/request.json' => Http::response(['data' => [], 'errors' => ['code' => -11, 'message' => 'Merchant not active']]),
        ]);

        $response = $this->initiate('zarinpal');

        $this->assertFalse($response->success);
        $this->assertSame('مرچنت کد فعال نیست.', $response->message);
        $this->assertSame(Payment::STATUS_FAILED, Payment::first()->status);
    }

    public function test_zarinpal_callback_verifies_and_marks_order_paid(): void
    {
        Http::fake([
            '*/request.json' => Http::response(['data' => ['code' => 100, 'authority' => 'AUTH123'], 'errors' => []]),
            '*/verify.json' => Http::response(['data' => ['code' => 100, 'ref_id' => 987654, 'card_pan' => '502229******7467'], 'errors' => []]),
        ]);

        $this->initiate('zarinpal');
        $payment = Payment::first();

        $this->get(route('payment.callback', [
            'gateway' => 'zarinpal', 'ref' => $payment->reference,
            'Authority' => 'AUTH123', 'Status' => 'OK',
        ]))->assertRedirect(route('orders.show', $this->order->id));

        $payment->refresh();
        $this->assertTrue($payment->isPaid());
        $this->assertSame('987654', $payment->ref_code);
        $this->assertSame('502229******7467', $payment->card_pan);
        $this->assertSame('paid', $this->order->fresh()->status);
    }

    public function test_zarinpal_rejects_a_callback_whose_authority_does_not_match(): void
    {
        Http::fake(['*/request.json' => Http::response(['data' => ['code' => 100, 'authority' => 'AUTH123'], 'errors' => []])]);

        $this->initiate('zarinpal');
        $payment = Payment::first();

        $this->get(route('payment.callback', [
            'gateway' => 'zarinpal', 'ref' => $payment->reference,
            'Authority' => 'SOMEONE-ELSE', 'Status' => 'OK',
        ]));

        $this->assertFalse($payment->fresh()->isPaid());
        // No verify call should ever have gone out.
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'verify.json'));
    }

    public function test_zarinpal_cancelled_payment_fails_without_calling_verify(): void
    {
        Http::fake(['*/request.json' => Http::response(['data' => ['code' => 100, 'authority' => 'AUTH123'], 'errors' => []])]);

        $this->initiate('zarinpal');
        $payment = Payment::first();

        $this->get(route('payment.callback', [
            'gateway' => 'zarinpal', 'ref' => $payment->reference, 'Status' => 'NOK',
        ]));

        $this->assertSame(Payment::STATUS_FAILED, $payment->fresh()->status);
    }

    /* ------------------------------------------------------------------ IDPay */

    public function test_idpay_treats_201_as_success_and_sends_the_reference_as_order_id(): void
    {
        Http::fake([
            'api.idpay.ir/v1.1/payment' => Http::response(['id' => 'abc123', 'link' => 'https://idpay.ir/p/ws-sandbox/abc123'], 201),
        ]);

        $response = $this->initiate('idpay');
        $payment = Payment::first();

        $this->assertTrue($response->success);
        $this->assertSame('abc123', $payment->authority);

        Http::assertSent(fn ($request) => $request['order_id'] === $payment->reference
            && $request['amount'] === 1_500_000
            && $request->hasHeader('X-SANDBOX', '1'));
    }

    public function test_idpay_verifies_on_callback_status_10(): void
    {
        // status=10 ("awaiting confirmation") is the normal state on the
        // callback — it must not be treated as a failure.
        Http::fake([
            'api.idpay.ir/v1.1/payment' => Http::response(['id' => 'abc123', 'link' => 'https://idpay.ir/p/x'], 201),
            'api.idpay.ir/v1.1/payment/verify' => Http::response([
                'status' => '100', 'track_id' => '10012', 'id' => 'abc123',
                'amount' => '1500000', 'payment' => ['card_no' => '123456******1234'],
            ]),
        ]);

        $this->initiate('idpay');
        $payment = Payment::first();

        $this->post(route('payment.callback', ['gateway' => 'idpay']), [
            'status' => '10', 'track_id' => '10012',
            'id' => 'abc123', 'order_id' => $payment->reference,
        ]);

        $payment->refresh();
        $this->assertTrue($payment->isPaid());
        $this->assertSame('10012', $payment->ref_code);
    }

    public function test_idpay_rejects_verify_when_the_confirmed_amount_differs(): void
    {
        Http::fake([
            'api.idpay.ir/v1.1/payment' => Http::response(['id' => 'abc123', 'link' => 'https://idpay.ir/p/x'], 201),
            'api.idpay.ir/v1.1/payment/verify' => Http::response([
                'status' => '100', 'track_id' => '10012', 'id' => 'abc123', 'amount' => '10000',
            ]),
        ]);

        $this->initiate('idpay');
        $payment = Payment::first();

        $this->post(route('payment.callback', ['gateway' => 'idpay']), [
            'status' => '10', 'id' => 'abc123', 'order_id' => $payment->reference,
        ]);

        $this->assertFalse($payment->fresh()->isPaid());
        $this->assertSame('pending', $this->order->fresh()->status);
    }

    public function test_idpay_cancelled_status_is_reported_in_persian(): void
    {
        Http::fake(['api.idpay.ir/v1.1/payment' => Http::response(['id' => 'abc123', 'link' => 'https://idpay.ir/p/x'], 201)]);

        $this->initiate('idpay');
        $payment = Payment::first();

        $result = $this->service()->verify(
            request()->merge(['status' => 7, 'id' => 'abc123']),
            'idpay',
            $payment->reference,
        );

        $this->assertFalse($result->success);
        $this->assertSame('انصراف از پرداخت.', $result->message);
    }

    /* ---------------------------------------------------------------- DigiPay */

    public function test_digipay_authenticates_with_password_grant_and_requests_a_ticket(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3599]),
            '*/businesses/ticket*' => Http::response([
                'result' => ['status' => 0, 'message' => 'Success'],
                'payUrl' => 'https://uatweb.mydigipay.info/web-pay/upg/T1',
                'ticket' => 'T1',
            ]),
        ]);

        $response = $this->initiate('digipay');

        $this->assertTrue($response->success);
        $this->assertSame('https://uatweb.mydigipay.info/web-pay/upg/T1', $response->redirectUrl);
        $this->assertSame('T1', Payment::first()->authority);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/oauth/token')
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('cid:csecret')));

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/businesses/ticket')) {
                return false;
            }

            return str_contains($request->url(), 'type=0')
                && $request['amount'] === 1_500_000
                && $request['providerId'] === Payment::first()->reference
                && $request['userType'] === 0
                && $request->hasHeader('Digipay-Version', '2022-02-02');
        });
    }

    public function test_digipay_verifies_by_tracking_code_in_the_path(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3599]),
            '*/businesses/ticket*' => Http::response(['result' => ['status' => 0], 'payUrl' => 'https://pay/T1', 'ticket' => 'T1']),
            '*/purchases/verify/*' => Http::response([
                'result' => ['status' => 0, 'message' => 'Success'],
                'trackingCode' => 'TRK99', 'rrn' => '724101640673',
                'maskedPan' => '502229******7467', 'amount' => 1_500_000,
            ]),
        ]);

        $this->initiate('digipay');
        $payment = Payment::first();

        $this->post(route('payment.callback', ['gateway' => 'digipay']), [
            'result' => 'SUCCESS', 'providerId' => $payment->reference,
            'trackingCode' => 'TRK99', 'amount' => 1_500_000,
        ]);

        $payment->refresh();
        $this->assertTrue($payment->isPaid());
        $this->assertSame('724101640673', $payment->ref_code);

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/purchases/verify/TRK99'));
    }

    public function test_digipay_rejects_a_callback_with_a_tampered_amount(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3599]),
            '*/businesses/ticket*' => Http::response(['result' => ['status' => 0], 'payUrl' => 'https://pay/T1', 'ticket' => 'T1']),
        ]);

        $this->initiate('digipay');
        $payment = Payment::first();

        $this->post(route('payment.callback', ['gateway' => 'digipay']), [
            'result' => 'SUCCESS', 'providerId' => $payment->reference,
            'trackingCode' => 'TRK99', 'amount' => 1_000,
        ]);

        $this->assertFalse($payment->fresh()->isPaid());
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/purchases/verify/'));
    }

    public function test_digipay_callback_failure_result_is_mapped(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3599]),
            '*/businesses/ticket*' => Http::response(['result' => ['status' => 0], 'payUrl' => 'https://pay/T1', 'ticket' => 'T1']),
        ]);

        $this->initiate('digipay');
        $payment = Payment::first();

        $result = $this->service()->verify(
            request()->merge(['result' => 'CANCELED']),
            'digipay',
            $payment->reference,
        );

        $this->assertFalse($result->success);
        $this->assertSame('پرداخت لغو شده است.', $result->message);
    }

    /* --------------------------------------------------------------- Shared */

    public function test_a_replayed_callback_does_not_verify_twice(): void
    {
        Http::fake([
            '*/request.json' => Http::response(['data' => ['code' => 100, 'authority' => 'AUTH123'], 'errors' => []]),
            '*/verify.json' => Http::response(['data' => ['code' => 100, 'ref_id' => 987654], 'errors' => []]),
        ]);

        $this->initiate('zarinpal');
        $payment = Payment::first();

        $url = route('payment.callback', [
            'gateway' => 'zarinpal', 'ref' => $payment->reference,
            'Authority' => 'AUTH123', 'Status' => 'OK',
        ]);

        $this->get($url);
        $this->get($url);

        Http::assertSentCount(2); // one request.json + exactly one verify.json
        $this->assertSame(1, Payment::where('status', Payment::STATUS_PAID)->count());
    }

    public function test_an_unknown_reference_is_rejected(): void
    {
        $this->get(route('payment.callback', [
            'gateway' => 'zarinpal', 'ref' => 'CTL-999-NOPE', 'Status' => 'OK',
        ]))->assertRedirect(route('orders.index'));
    }
}
