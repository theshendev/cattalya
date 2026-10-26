<?php

namespace App\Services\Payment\Gateways;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\DTOs\PaymentResponse;
use App\Services\Payment\DTOs\VerifyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * SnappPay (اسنپ‌پی) — buy-now-pay-later / instalment gateway.
 *
 * SnappPay hands the full technical spec to merchants after the contract is
 * signed, so this driver follows the public client libraries
 * (backendprogramer/laravel-snapp-pay, amiranmanesh/payvand) and the endpoint
 * list they share. Anything not confirmed by those is marked "unconfirmed" in
 * docs/payment-gateways.md and handled defensively here.
 *
 * Flow:
 *   1. POST api/online/v1/oauth/token — OAuth2 password grant. client_id:secret
 *      go in an HTTP Basic header; grant_type/scope/username/password in a
 *      urlencoded form body. Returns access_token + expires_in.
 *   2. POST api/online/payment/v1/token (Bearer, JSON) — creates the payment
 *      with the basket; returns paymentToken and the hosted page URL.
 *   3. Redirect the buyer to the hosted page; SnappPay runs the credit check
 *      and sends the buyer back to returnURL.
 *   4. POST api/online/payment/v1/verify {paymentToken}.
 *   5. POST api/online/payment/v1/settle {paymentToken}. SnappPay reverts a
 *      payment that is verified but never settled, so a failed settle is
 *      followed by an explicit revert.
 *
 * Amounts are in Rials. The callback outcome is advisory only — the payment is
 * final once verify and settle both succeed.
 */
class SnappPayGateway extends AbstractGateway
{
    private const TOKEN_ENDPOINT = 'api/online/v1/oauth/token';

    private const PAYMENT_TOKEN_ENDPOINT = 'api/online/payment/v1/token';

    private const VERIFY_ENDPOINT = 'api/online/payment/v1/verify';

    private const SETTLE_ENDPOINT = 'api/online/payment/v1/settle';

    private const REVERT_ENDPOINT = 'api/online/payment/v1/revert';

    private const CANCEL_ENDPOINT = 'api/online/payment/v1/cancel';

    private const STATUS_ENDPOINT = 'api/online/payment/v1/status';

    public function name(): string
    {
        return 'snapppay';
    }

    protected function requiredCredentials(): array
    {
        return ['username', 'password', 'client_id', 'client_secret', 'base_url'];
    }

    /**
     * Instalment products only make sense inside the merchant's contracted
     * amount window, so the checkout hides SnappPay outside it.
     */
    public function supportsAmount(float $toman): bool
    {
        $min = (float) $this->option('min_amount', 0);
        $max = (float) $this->option('max_amount', 0);

        return $toman >= $min && ($max <= 0 || $toman <= $max);
    }

    private function url(string $path): string
    {
        return rtrim((string) $this->option('base_url'), '/').'/'.ltrim($path, '/');
    }

    private function accessToken(bool $fresh = false): ?string
    {
        $key = 'snapppay:access_token:'.md5((string) $this->option('base_url').$this->option('client_id'));

        if ($fresh) {
            Cache::forget($key);
        }

        if ($cached = Cache::get($key)) {
            return $cached;
        }

        $response = $this->http()
            ->withBasicAuth((string) $this->option('client_id'), (string) $this->option('client_secret'))
            ->asForm()
            ->post($this->url(self::TOKEN_ENDPOINT), [
                'grant_type' => 'password',
                'scope' => $this->option('scope', 'online-merchant'),
                'username' => $this->option('username'),
                'password' => $this->option('password'),
            ]);

        if (! $response->successful() || blank($token = $response->json('access_token'))) {
            $this->logError('oauth_failed', ['status' => $response->status()]);

            return null;
        }

        Cache::put($key, $token, max(60, (int) $response->json('expires_in', 3600) - 120));

        return $token;
    }

    /**
     * Authenticated JSON call. One retry with a fresh token when the cached
     * one was rejected.
     *
     * @return array{status: int, json: array<string, mixed>}|null Null when no token could be obtained.
     */
    private function call(string $method, string $path, array $payload = []): ?array
    {
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $token = $this->accessToken(fresh: $attempt === 1);

            if (! $token) {
                return null;
            }

            $request = $this->http()->withToken($token);
            $response = $method === 'get'
                ? $request->get($this->url($path), $payload)
                : $request->post($this->url($path), $payload);

            if ($response->status() !== 401) {
                return ['status' => $response->status(), 'json' => $response->json() ?? []];
            }
        }

        return ['status' => 401, 'json' => []];
    }

    /**
     * SnappPay wraps results as {successful, response, errorData}.
     *
     * @param  array<string, mixed>  $json
     */
    private function isSuccessful(array $json, int $status): bool
    {
        return $status >= 200 && $status < 300 && (bool) data_get($json, 'successful', false);
    }

    /**
     * @param  array<string, mixed>  $json
     */
    private function errorMessage(array $json, string $fallback): string
    {
        return (string) (data_get($json, 'errorData.message') ?: data_get($json, 'message') ?: $fallback);
    }

    public function pay(Payment $payment, string $callbackUrl): PaymentResponse
    {
        $order = $payment->order()->with(['items', 'user'])->first();
        $rial = (int) config('gateways.toman_to_rial', 10);

        $items = $order->items->map(fn ($item) => [
            'id' => (string) ($item->product_id ?? $item->id),
            'name' => $item->product_name,
            'count' => (int) $item->quantity,
            'amount' => (int) round((float) $item->unit_price * $rial),
            'category' => 'جواهر و زیورآلات',
            'commissionType' => 100,
        ])->all();

        if ($order->packaging_cost > 0) {
            $items[] = [
                'id' => 'gift-packaging',
                'name' => 'بسته‌بندی هدیه',
                'count' => 1,
                'amount' => (int) round((float) $order->packaging_cost * $rial),
                'category' => 'خدمات',
                'commissionType' => 100,
            ];
        }

        $itemsTotal = array_sum(array_map(fn ($item) => $item['amount'] * $item['count'], $items));
        $shipping = (int) round((float) $order->shipping_cost * $rial);

        $body = array_filter([
            'amount' => $payment->amountInRials(),
            'discountAmount' => (int) round((float) $order->discount_amount * $rial),
            'externalSourceAmount' => 0,
            'paymentMethodTypeDto' => 'INSTALLMENT',
            'returnURL' => $callbackUrl,
            'transactionId' => $payment->reference,
            'mobile' => $this->mobile($order),
            'cartList' => [[
                'cartId' => (string) $order->id,
                'cartItems' => $items,
                'isShipmentIncluded' => false,
                'isTaxIncluded' => true,
                'shippingAmount' => $shipping,
                'taxAmount' => 0,
                'totalAmount' => $itemsTotal + $shipping,
            ]],
        ], fn ($value) => $value !== null);

        $result = $this->call('post', self::PAYMENT_TOKEN_ENDPOINT, $body);

        if ($result === null) {
            return PaymentResponse::failure('ارتباط با اسنپ‌پی برقرار نشد. اطلاعات کاربری درگاه را بررسی کنید.', 'oauth');
        }

        $json = $result['json'];
        $paymentToken = data_get($json, 'response.paymentToken');
        $pageUrl = data_get($json, 'response.paymentPageUrl');

        if ($this->isSuccessful($json, $result['status']) && filled($paymentToken) && filled($pageUrl)) {
            return PaymentResponse::success($pageUrl, $paymentToken, ['request' => $this->scrub($body), 'response' => $json]);
        }

        $this->logError('token_failed', ['status' => $result['status'], 'error' => data_get($json, 'errorData')]);

        return PaymentResponse::failure(
            $this->errorMessage($json, 'ایجاد پرداخت اقساطی با اسنپ‌پی ناموفق بود.'),
            data_get($json, 'errorData.errorCode', $result['status']),
            ['request' => $this->scrub($body), 'response' => $json],
        );
    }

    public function resolveReference(Request $request): ?string
    {
        return $request->input('transactionId') ?? $request->query('ref');
    }

    public function verify(Payment $payment, Request $request): VerifyResponse
    {
        $paymentToken = (string) $payment->authority;

        if (blank($paymentToken)) {
            return VerifyResponse::failure('توکن پرداخت اسنپ‌پی یافت نشد.');
        }

        // The callback outcome is advisory; the verify call is authoritative.
        $verify = $this->call('post', self::VERIFY_ENDPOINT, ['paymentToken' => $paymentToken]);

        if ($verify === null) {
            return VerifyResponse::failure('ارتباط با اسنپ‌پی برقرار نشد. لطفاً دوباره تلاش کنید.');
        }

        if (! $this->isSuccessful($verify['json'], $verify['status'])) {
            return VerifyResponse::failure(
                $this->errorMessage($verify['json'], 'پرداخت اقساطی تایید نشد.'),
                ['verify' => $verify['json']],
                data_get($verify['json'], 'errorData.errorCode', $verify['status']),
            );
        }

        // A verified-but-unsettled payment is reverted by SnappPay, so settle now.
        if ($this->option('auto_settle', true)) {
            $settle = $this->call('post', self::SETTLE_ENDPOINT, ['paymentToken' => $paymentToken]);

            if ($settle === null || ! $this->isSuccessful($settle['json'], $settle['status'])) {
                $this->logError('settle_failed', ['payment' => $payment->reference, 'status' => $settle['status'] ?? null]);
                $this->revert($paymentToken);

                return VerifyResponse::failure(
                    'تسویه پرداخت اقساطی با اسنپ‌پی انجام نشد و مبلغ به مشتری بازگردانده می‌شود.',
                    ['verify' => $verify['json'], 'settle' => $settle['json'] ?? []],
                );
            }
        }

        return VerifyResponse::success(
            refCode: (string) (data_get($verify['json'], 'response.transactionId') ?: $payment->reference),
            rawData: ['verify' => $verify['json']],
        );
    }

    /**
     * Undo a verified payment (full amount only — SnappPay ignores partial amounts).
     */
    public function revert(string $paymentToken): bool
    {
        $result = $this->call('post', self::REVERT_ENDPOINT, ['paymentToken' => $paymentToken]);

        return $result !== null && $this->isSuccessful($result['json'], $result['status']);
    }

    /**
     * Drop a payment the buyer never finished, releasing their reserved credit.
     */
    public function cancel(string $paymentToken): bool
    {
        $result = $this->call('post', self::CANCEL_ENDPOINT, ['paymentToken' => $paymentToken]);

        return $result !== null && $this->isSuccessful($result['json'], $result['status']);
    }

    /**
     * Current state at SnappPay — use it to recover from a lost callback.
     *
     * @return array<string, mixed>|null
     */
    public function status(string $paymentToken): ?array
    {
        $result = $this->call('get', self::STATUS_ENDPOINT, ['paymentToken' => $paymentToken]);

        return $result['json'] ?? null;
    }

    private function mobile(Order $order): ?string
    {
        $phone = $order->user?->phone;

        return $phone ? preg_replace('/^0/', '+98', $phone) : null;
    }
}
