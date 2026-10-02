<?php

namespace App\Services\Payment\Gateways;

use App\Models\Payment;
use App\Services\Payment\DTOs\PaymentResponse;
use App\Services\Payment\DTOs\VerifyResponse;
use Illuminate\Http\Request;

/**
 * ZarinPal Payment Gateway — REST API v4.
 *
 * @see https://www.zarinpal.com/docs/paymentGateway/
 *
 * Flow: POST request.json -> redirect to StartPay/{authority} -> the buyer
 * returns to callback_url with ?Authority=&Status=OK|NOK -> POST verify.json.
 *
 * Amounts are sent in Rials (currency IRR). ZarinPal also accepts IRT with
 * Toman amounts, but mixing the two is the classic 10x bug, so we normalise
 * on Rials everywhere.
 */
class ZarinPalGateway extends AbstractGateway
{
    /** data.code values that mean the money is settled. */
    private const VERIFIED = 100;

    private const ALREADY_VERIFIED = 101;

    /**
     * Official error list — https://www.zarinpal.com/docs/paymentGateway/errorList
     */
    public const ERRORS = [
        -9 => 'خطای اعتبارسنجی — یکی از پارامترهای ارسالی نامعتبر است.',
        -10 => 'آی‌پی یا مرچنت کد پذیرنده صحیح نیست.',
        -11 => 'مرچنت کد فعال نیست.',
        -12 => 'تلاش بیش از دفعات مجاز در یک بازه زمانی کوتاه.',
        -13 => 'خطای مربوط به محدودیت تراکنش.',
        -14 => 'کال‌بک URL با دامنه ثبت‌شده درگاه مغایرت دارد.',
        -15 => 'درگاه پرداخت به حالت تعلیق درآمده است.',
        -16 => 'سطح تأیید پذیرنده پایین‌تر از سطح نقره‌ای است.',
        -17 => 'محدودیت پذیرنده در سطح آبی.',
        -18 => 'امکان استفاده از کد درگاه بر روی سایت دیگر را ندارید.',
        -19 => 'امکان ایجاد تراکنش برای این ترمینال وجود ندارد.',
        -30 => 'پذیرنده اجازه دسترسی به سرویس تسویه اشتراکی شناور را ندارد.',
        -31 => 'حساب بانکی تسویه را به پنل اضافه کنید.',
        -32 => 'مبلغ وارد شده از مبلغ کل تراکنش بیشتر است.',
        -33 => 'درصدهای وارد شده صحیح نیست.',
        -34 => 'مبلغ وارد شده از مبلغ کل تراکنش بیشتر است.',
        -35 => 'تعداد افراد دریافت‌کننده تسهیم بیش از حد مجاز است.',
        -36 => 'حداقل مبلغ جهت تسهیم باید ۱۰۰۰۰ ریال باشد.',
        -37 => 'یک یا چند شماره شبای وارد شده برای تسهیم غیرفعال است.',
        -38 => 'عدم تعریف صحیح شبا.',
        -39 => 'خطایی رخ داده است.',
        -40 => 'پارامتر اضافی نامعتبر است.',
        -41 => 'حداکثر مبلغ پرداختی ۱۰۰ میلیون تومان است.',
        -50 => 'مبلغ پرداخت‌شده با مبلغ تراکنش متفاوت است.',
        -51 => 'پرداخت ناموفق بود.',
        -52 => 'خطای غیرمنتظره‌ای رخ داده است.',
        -53 => 'پرداخت متعلق به این مرچنت کد نیست.',
        -54 => 'اتوریتی نامعتبر است.',
        -55 => 'تراکنش مورد نظر یافت نشد.',
        -60 => 'امکان ریورس کردن تراکنش وجود ندارد.',
        -61 => 'تراکنش موفق نیست یا قبلاً ریورس شده است.',
        -62 => 'آی‌پی درگاه ست نشده است.',
        -63 => 'حداکثر زمان برای ریورس کردن تراکنش منقضی شده است.',
        100 => 'عملیات موفق.',
        101 => 'تراکنش قبلاً وریفای شده است.',
    ];

    public function name(): string
    {
        return 'zarinpal';
    }

    protected function requiredCredentials(): array
    {
        return ['merchant_id'];
    }

    private function endpoint(string $key): string
    {
        return $this->option('endpoints.'.($this->isSandbox() ? 'sandbox' : 'live').'.'.$key);
    }

    public function pay(Payment $payment, string $callbackUrl): PaymentResponse
    {
        $order = $payment->order;

        $body = [
            'merchant_id' => $this->option('merchant_id'),
            'amount' => $payment->amountInRials(),
            'currency' => 'IRR',
            'callback_url' => $callbackUrl,
            'description' => "سفارش #{$order->id} — کاتالیا",
            'metadata' => array_filter([
                'order_id' => $payment->reference,
                'mobile' => $order->user?->phone,
                'email' => $order->user?->email,
            ]),
        ];

        if (filled($referrer = $this->option('referrer_id'))) {
            $body['referrer_id'] = $referrer;
        }

        $response = $this->http()->post($this->endpoint('request'), $body);
        $data = $response->json() ?? [];

        $code = data_get($data, 'data.code');

        if ($code === self::VERIFIED && filled($authority = data_get($data, 'data.authority'))) {
            return PaymentResponse::success(
                $this->endpoint('start_pay').$authority,
                $authority,
                ['request' => $this->scrub($body), 'response' => $data],
            );
        }

        // v4 returns errors as an object on failure and an empty array on success.
        $errorCode = data_get($data, 'errors.code', $code);

        $this->logError('purchase_failed', [
            'reference' => $payment->reference,
            'status' => $response->status(),
            'body' => $data,
        ]);

        return PaymentResponse::failure(
            $this->describe($errorCode) ?? data_get($data, 'errors.message', 'خطا در اتصال به درگاه زرین‌پال.'),
            $errorCode,
            ['request' => $this->scrub($body), 'response' => $data],
        );
    }

    public function resolveReference(Request $request): ?string
    {
        // ZarinPal echoes only Authority and Status, so the reference travels
        // in our own callback URL query string.
        return $request->query('ref');
    }

    public function verify(Payment $payment, Request $request): VerifyResponse
    {
        if ($request->query('Status') !== 'OK') {
            return VerifyResponse::failure('پرداخت توسط کاربر لغو شد.', ['callback' => $request->query()]);
        }

        $authority = $request->query('Authority');

        // The callback is unauthenticated; never verify an authority that does
        // not belong to the payment we looked up by reference.
        if (blank($authority) || $authority !== $payment->authority) {
            $this->logError('authority_mismatch', [
                'reference' => $payment->reference,
                'expected' => $payment->authority,
                'received' => $authority,
            ]);

            return VerifyResponse::failure('اطلاعات بازگشتی درگاه با تراکنش مطابقت ندارد.');
        }

        $body = [
            'merchant_id' => $this->option('merchant_id'),
            // Must equal the requested amount to the Rial, or ZarinPal returns -50.
            'amount' => $payment->amountInRials(),
            'authority' => $authority,
        ];

        $response = $this->http()->post($this->endpoint('verify'), $body);
        $data = $response->json() ?? [];
        $code = data_get($data, 'data.code');

        if (in_array($code, [self::VERIFIED, self::ALREADY_VERIFIED], true)) {
            return VerifyResponse::success(
                refCode: (string) data_get($data, 'data.ref_id', $authority),
                rawData: ['verify' => $data],
                cardPan: data_get($data, 'data.card_pan'),
                alreadyVerified: $code === self::ALREADY_VERIFIED,
                code: $code,
            );
        }

        $errorCode = data_get($data, 'errors.code', $code);

        $this->logError('verify_failed', [
            'reference' => $payment->reference,
            'status' => $response->status(),
            'body' => $data,
        ]);

        return VerifyResponse::failure(
            $this->describe($errorCode) ?? data_get($data, 'errors.message', 'تأیید پرداخت ناموفق بود.'),
            ['verify' => $data],
            $errorCode,
        );
    }

    /**
     * Transactions that were paid but never verified — ZarinPal keeps them for
     * a short window. Run this from a scheduled reconciliation command when a
     * callback is lost (browser closed mid-redirect).
     *
     * @return array<int, array<string, mixed>> authority/amount/callback_url rows
     */
    public function unverified(): array
    {
        $response = $this->http()->post($this->endpoint('unverified'), [
            'merchant_id' => $this->option('merchant_id'),
        ]);

        return data_get($response->json(), 'data.authorities', []);
    }

    private function describe(int|string|null $code): ?string
    {
        return is_numeric($code) ? (self::ERRORS[(int) $code] ?? null) : null;
    }
}
