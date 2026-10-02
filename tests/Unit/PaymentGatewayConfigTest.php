<?php

namespace Tests\Unit;

use App\Models\Payment;
use App\Services\Payment\Gateways\DigiPayGateway;
use App\Services\Payment\Gateways\IDPayGateway;
use App\Services\Payment\Gateways\ZarinPalGateway;
use App\Services\Payment\PaymentService;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Database-free checks: config wiring, credential gating, amount conversion
 * and the gateway response-code tables.
 */
class PaymentGatewayConfigTest extends TestCase
{
    public function test_toman_amounts_are_converted_to_rials_once(): void
    {
        $payment = new Payment(['amount' => 150_000]);

        $this->assertSame(1_500_000, $payment->amountInRials());
    }

    public function test_references_are_unique_per_attempt_and_carry_the_order_id(): void
    {
        $a = Payment::generateReference(42);
        $b = Payment::generateReference(42);

        $this->assertStringStartsWith('CTL-42-', $a);
        $this->assertNotSame($a, $b);
        $this->assertLessThanOrEqual(64, strlen($a));
        // IDPay caps order_id at 50 characters.
        $this->assertLessThanOrEqual(50, strlen($a));
    }

    public function test_a_gateway_without_credentials_is_not_configured(): void
    {
        config(['gateways.drivers.zarinpal.merchant_id' => '']);
        $this->assertFalse(app(ZarinPalGateway::class)->isConfigured());

        config(['gateways.drivers.zarinpal.merchant_id' => str_repeat('a', 36)]);
        $this->assertTrue(app(ZarinPalGateway::class)->isConfigured());
    }

    public function test_digipay_requires_all_four_oauth_credentials(): void
    {
        config([
            'gateways.drivers.digipay.enabled' => true,
            'gateways.drivers.digipay.client_id' => 'cid',
            'gateways.drivers.digipay.client_secret' => 'secret',
            'gateways.drivers.digipay.username' => 'user',
            'gateways.drivers.digipay.password' => '',
        ]);

        $this->assertFalse(app(DigiPayGateway::class)->isConfigured());

        config(['gateways.drivers.digipay.password' => 'pass']);
        $this->assertTrue(app(DigiPayGateway::class)->isConfigured());
    }

    public function test_a_disabled_gateway_is_never_offered(): void
    {
        config([
            'gateways.drivers.idpay.enabled' => false,
            'gateways.drivers.idpay.api_key' => str_repeat('b', 36),
        ]);

        $this->assertFalse(app(IDPayGateway::class)->isConfigured());
    }

    public function test_unknown_driver_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(PaymentService::class)->driver('sadad');
    }

    public function test_sandbox_and_live_endpoints_are_distinct(): void
    {
        $live = config('gateways.drivers.zarinpal.endpoints.live');
        $sandbox = config('gateways.drivers.zarinpal.endpoints.sandbox');

        $this->assertSame('https://payment.zarinpal.com/pg/v4/payment/request.json', $live['request']);
        $this->assertSame('https://sandbox.zarinpal.com/pg/v4/payment/request.json', $sandbox['request']);
    }

    public function test_documented_response_codes_are_mapped(): void
    {
        // A few anchors from each official table.
        $this->assertSame('پرداخت ناموفق بود.', ZarinPalGateway::ERRORS[-51]);
        $this->assertArrayHasKey(-54, ZarinPalGateway::ERRORS);   // invalid authority
        $this->assertArrayHasKey(101, ZarinPalGateway::ERRORS);   // already verified

        $this->assertArrayHasKey(10, IDPayGateway::STATUSES);     // awaiting verification
        $this->assertArrayHasKey(54, IDPayGateway::ERRORS);       // verify window expired

        $this->assertArrayHasKey(9009, DigiPayGateway::RESULT_CODES); // verify window expired
        $this->assertArrayHasKey('CANCELED', DigiPayGateway::CALLBACK_RESULTS);
    }
}
