<?php

namespace App\Services\Payment\Gateways;

use App\Models\Payment;
use App\Services\Payment\DTOs\PaymentResponse;
use App\Services\Payment\DTOs\VerifyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * DigiPay UPG (Universal/smart Payment Gateway).
 *
 * @see https://www.mydigipay.com/developers/docs/upg/
 *
 * Flow:
 *   1. POST /oauth/token — OAuth2 *password* grant. client_id:client_secret go
 *      in an HTTP Basic header; username/password/grant_type go in the body as
 *      multipart form-data (not JSON, not urlencoded).
 *   2. POST /businesses/ticket?type=0 — returns `payUrl` and `ticket`.
 *   3. The buyer picks IPG / wallet / credit on DigiPay's page.
 *   4. DigiPay POSTs the result to redirectUrl as x-www-form-urlencoded:
 *      result, providerId, trackingCode, amount.
 *   5. POST /purchases/verify/{trackingCode} within 10 minutes, or the charge
 *      is reversed to the buyer automatically.
 *
 * Amounts are in Rials. `providerId` is our own unique per-transaction id and
 * is the only field DigiPay echoes back that we control — it carries our
 * payment reference.
 */
class DigiPayGateway extends AbstractGateway
{
    /** result.status values (کد نتیجه). */
    public const RESULT_CODES = [
        0 => 'عملیات با موفقیت انجام شد.',
        1054 => 'اطلاعات ورودی اشتباه می‌باشد.',
        9000 => 'اطلاعات خرید یافت نشد.',
        9001 => 'توکن پرداخت معتبر نمی‌باشد.',
        9003 => 'خرید مورد نظر منقضی شده است.',
        9004 => 'خرید مورد نظر در حال انجام است.',
        9005 => 'خرید قابل پرداخت نمی‌باشد.',
        9006 => 'خطا در برقراری ارتباط با درگاه پرداخت.',
        9007 => 'خرید با موفقیت انجام نشده است.',
        9008 => 'این خرید با داده‌های متفاوتی قبلاً ثبت شده است.',
        9009 => 'محدوده زمانی تأیید تراکنش گذشته است.',
        9010 => 'تأیید خرید ناموفق بود.',
        9011 => 'نتیجه تأیید خرید نامشخص است.',
        9012 => 'وضعیت خرید برای این درخواست صحیح نمی‌باشد.',
        9030 => 'ورود شماره همراه برای کاربران ثبت‌نام‌شده الزامی است.',
        9031 => 'اعطای تیکت برای کاربر مورد نظر امکان‌پذیر نمی‌باشد.',
    ];

    /** `result` values on the payment callback. */
    public const CALLBACK_RESULTS = [
        'SUCCESS' => 'تراکنش موفق.',
        'FAILURE' => 'خطا در انجام تراکنش.',
        'IPG_FAILURE' => 'خطا در انجام تراکنش آی‌پی‌جی.',
        'CANCELED' => 'پرداخت لغو شده است.',
        'INTERNAL_ERROR' => 'خطای سیستمی.',
        'INVALID_TICKET' => 'تیکت نامعتبر یا پرداخت در وضعیت نامعتبر.',
    ];

    /** userType on the ticket request. */
    private const USER_IDENTIFIED = 0;  // cellNumber is required

    private const USER_ANONYMOUS = 2;  // guest, IPG only

    private const RESULT_SUCCESS = 0;

    public function name(): string
    {
        return 'digipay';
    }

    protected function requiredCredentials(): array
    {
        return ['client_id', 'client_secret', 'username', 'password'];
    }

    private function baseUrl(): string
    {
        return rtrim($this->option('base_url.'.($this->isSandbox() ? 'sandbox' : 'live')), '/');
    }

    private function ticketType(): int
    {
        return (int) $this->option('ticket_type', 0);
    }

    /**
     * Cached OAuth access token. DigiPay reports expires_in (~3600s); we cache
     * a little short of that so a token never expires mid-request.
     */
    private function accessToken(bool $fresh = false): ?string
    {
        $key = 'digipay:access_token:'.($this->isSandbox() ? 'uat' : 'live');

        if ($fresh) {
            Cache::forget($key);
        }

        if ($cached = Cache::get($key)) {
            return $cached;
        }

        $response = $this->http()
            ->withHeaders([
                'Authorization' => 'Basic '.base64_encode(
                    $this->option('client_id').':'.$this->option('client_secret')
                ),
            ])
            // The token endpoint only accepts multipart/form-data.
            ->asMultipart()
            ->post($this->baseUrl().'/oauth/token', [
                ['name' => 'username',   'contents' => (string) $this->option('username')],
                ['name' => 'password',   'contents' => (string) $this->option('password')],
                ['name' => 'grant_type', 'contents' => 'password'],
            ]);

        if (! $response->successful() || blank($token = $response->json('access_token'))) {
            $this->logError('oauth_failed', [
                'status' => $response->status(),
                'message' => $response->status() === 401
                    ? 'invalid client credentials or username/password'
                    : $response->json('result.message'),
            ]);

            return null;
        }

        $ttl = max(60, (int) $response->json('expires_in', 3600) - 60);
        Cache::put($key, $token, $ttl);

        return $token;
    }

    public function pay(Payment $payment, string $callbackUrl): PaymentResponse
    {
        if (! $token = $this->accessToken()) {
            return PaymentResponse::failure('خطا در احراز هویت درگاه دیجی‌پی.');
        }

        $cellNumber = $payment->order->user?->phone;

        $body = array_filter([
            'amount' => $payment->amountInRials(),
            // Required when userType is 0; without it DigiPay shows IPG only.
            'cellNumber' => $cellNumber,
            'providerId' => $payment->reference,
            'redirectUrl' => $callbackUrl,
            'userType' => filled($cellNumber) ? self::USER_IDENTIFIED : self::USER_ANONYMOUS,
        ], fn ($value) => $value !== null && $value !== '');

        $response = $this->http()
            ->withToken($token)
            ->withHeaders([
                'Agent' => (string) $this->option('agent', 'WEB'),
                'Digipay-Version' => (string) $this->option('api_version'),
                'Content-Type' => 'application/json',
            ])
            ->post($this->baseUrl().'/businesses/ticket?type='.$this->ticketType(), $body);

        $data = $response->json() ?? [];

        if ($response->successful()
            && (int) data_get($data, 'result.status', -1) === self::RESULT_SUCCESS
            && filled($payUrl = $data['payUrl'] ?? null)
        ) {
            return PaymentResponse::success(
                $payUrl,
                (string) $data['ticket'],
                ['request' => $this->scrub($body), 'response' => $data],
            );
        }

        $status = data_get($data, 'result.status');

        $this->logError('ticket_failed', [
            'reference' => $payment->reference,
            'http' => $response->status(),
            'body' => $data,
        ]);

        return PaymentResponse::failure(
            self::RESULT_CODES[$status] ?? data_get($data, 'result.message', 'خطا در اتصال به درگاه دیجی‌پی.'),
            $status,
            ['request' => $this->scrub($body), 'response' => $data],
        );
    }

    public function resolveReference(Request $request): ?string
    {
        // DigiPay returns providerId exactly as we sent it.
        return $request->input('providerId');
    }

    public function verify(Payment $payment, Request $request): VerifyResponse
    {
        $result = (string) $request->input('result');
        $trackingCode = $request->input('trackingCode');

        if ($result !== 'SUCCESS') {
            return VerifyResponse::failure(
                self::CALLBACK_RESULTS[$result] ?? 'پرداخت ناموفق بود.',
                ['callback' => $request->all()],
                $result,
            );
        }

        if (blank($trackingCode)) {
            return VerifyResponse::failure('کد رهگیری در پاسخ درگاه وجود ندارد.');
        }

        // The callback is unauthenticated and carries the amount in Rials.
        if ((int) $request->input('amount') !== $payment->amountInRials()) {
            $this->logError('amount_mismatch', [
                'reference' => $payment->reference,
                'expected' => $payment->amountInRials(),
                'received' => $request->input('amount'),
            ]);

            return VerifyResponse::failure('مبلغ بازگشتی با مبلغ سفارش مطابقت ندارد.');
        }

        if (! $token = $this->accessToken()) {
            return VerifyResponse::failure('خطا در احراز هویت درگاه دیجی‌پی.');
        }

        // This endpoint takes no body — the tracking code is in the path.
        $response = $this->http()
            ->withToken($token)
            ->withHeaders(['Digipay-Version' => (string) $this->option('api_version')])
            ->post($this->baseUrl().'/purchases/verify/'.rawurlencode($trackingCode));

        $data = $response->json() ?? [];
        $status = (int) data_get($data, 'result.status', -1);

        if ($response->successful() && $status === self::RESULT_SUCCESS) {
            // DigiPay echoes providerId; it must still be ours.
            if (($data['providerId'] ?? $payment->reference) !== $payment->reference) {
                $this->logError('provider_id_mismatch', [
                    'reference' => $payment->reference,
                    'received' => $data['providerId'] ?? null,
                ]);

                return VerifyResponse::failure('اطلاعات بازگشتی درگاه با تراکنش مطابقت ندارد.');
            }

            return VerifyResponse::success(
                refCode: (string) ($data['rrn'] ?? $trackingCode),
                rawData: ['verify' => $data],
                cardPan: $data['maskedPan'] ?? null,
                code: $status,
            );
        }

        $this->logError('verify_failed', [
            'reference' => $payment->reference,
            'http' => $response->status(),
            'body' => $data,
            // 9011 means "outcome unknown" — DigiPay asks that verify be retried.
            'retryable' => $status === 9011,
        ]);

        return VerifyResponse::failure(
            self::RESULT_CODES[$status] ?? data_get($data, 'result.message', 'تأیید پرداخت ناموفق بود.'),
            ['verify' => $data],
            $status,
        );
    }
}
