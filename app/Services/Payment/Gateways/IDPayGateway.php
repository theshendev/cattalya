<?php

namespace App\Services\Payment\Gateways;

use App\Models\Payment;
use App\Services\Payment\DTOs\PaymentResponse;
use App\Services\Payment\DTOs\VerifyResponse;
use Illuminate\Http\Request;

/**
 * IDPay Payment Gateway — REST API v1.1.
 *
 * @see https://idpay.ir/web-service/v1.1/
 *
 * Flow: POST /payment (201 Created) -> redirect to `link` -> IDPay calls the
 * callback with status/track_id/id/order_id (GET or POST, per the dashboard
 * setting) -> POST /payment/verify within 10 minutes or the money is returned
 * to the payer automatically.
 *
 * Amounts are in Rials, between 1,000 and 500,000,000.
 */
class IDPayGateway extends AbstractGateway
{
    public const MIN_AMOUNT_RIALS = 1_000;

    public const MAX_AMOUNT_RIALS = 500_000_000;

    /** Transaction status codes (وضعیت تراکنش). */
    public const STATUSES = [
        1 => 'پرداخت انجام نشده است.',
        2 => 'پرداخت ناموفق بوده است.',
        3 => 'خطا رخ داده است.',
        4 => 'بلوکه شده.',
        5 => 'برگشت به پرداخت‌کننده.',
        6 => 'برگشت خورده سیستمی.',
        7 => 'انصراف از پرداخت.',
        8 => 'به درگاه پرداخت منتقل شد.',
        10 => 'در انتظار تأیید پرداخت.',
        100 => 'پرداخت تأیید شده است.',
        101 => 'پرداخت قبلاً تأیید شده است.',
        200 => 'به دریافت‌کننده واریز شد.',
    ];

    /**
     * Statuses that mean the payer's money is held and we may call verify.
     * 10 is the normal state on the callback; 100/101 appear when IDPay has
     * already settled (e.g. a callback replay after a successful verify).
     */
    private const VERIFIABLE = [10, 100, 101];

    /** Statuses that mean the money is settled. */
    private const SETTLED = [100, 101, 200];

    /** API error list (لیست خطاها). */
    public const ERRORS = [
        -1 => 'خطای غیرمنتظره در سمت آیدی‌پی.',
        11 => 'کاربر مسدود شده است.',
        12 => 'API Key یافت نشد.',
        13 => 'درخواست از آی‌پی غیرمجاز ارسال شده است.',
        14 => 'وب‌سرویس شما در حال بررسی است و یا تأیید نشده است.',
        15 => 'سرویس مورد نظر در دسترس نمی‌باشد.',
        21 => 'حساب بانکی متصل به وب‌سرویس تأیید نشده است.',
        22 => 'وب‌سرویس یافت نشد.',
        23 => 'اعتبارسنجی وب‌سرویس ناموفق بود.',
        24 => 'حساب بانکی مرتبط با این وب‌سرویس غیرفعال شده است.',
        31 => 'کد تراکنش `id` نباید خالی باشد.',
        32 => 'شماره سفارش `order_id` نباید خالی باشد.',
        33 => 'مبلغ `amount` نباید خالی باشد.',
        34 => 'مبلغ `amount` کمتر از حد مجاز است.',
        35 => 'مبلغ `amount` بیشتر از حد مجاز است.',
        36 => 'مبلغ `amount` بیشتر از حد مجاز است.',
        37 => 'آدرس بازگشت `callback` نباید خالی باشد.',
        38 => 'دامنه آدرس بازگشت با آدرس ثبت‌شده در وب‌سرویس همخوانی ندارد.',
        39 => 'آدرس بازگشت `callback` نامعتبر است.',
        41 => 'فیلتر وضعیت تراکنش‌ها نامعتبر است.',
        42 => 'فیلتر تاریخ پرداخت نامعتبر است.',
        43 => 'فیلتر تاریخ تسویه نامعتبر است.',
        44 => 'فیلتر تراکنش صحیح نمی‌باشد.',
        51 => 'تراکنش ایجاد نشد.',
        52 => 'استعلام نتیجه‌ای نداشت.',
        53 => 'تأیید پرداخت امکان‌پذیر نیست.',
        54 => 'مدت زمان تأیید پرداخت سپری شده است.',
    ];

    public function name(): string
    {
        return 'idpay';
    }

    protected function requiredCredentials(): array
    {
        return ['api_key'];
    }

    private function headers(): array
    {
        return [
            'X-API-KEY' => (string) $this->option('api_key'),
            'X-SANDBOX' => $this->isSandbox() ? '1' : '0',
            'Content-Type' => 'application/json',
        ];
    }

    public function pay(Payment $payment, string $callbackUrl): PaymentResponse
    {
        $amount = $payment->amountInRials();

        if ($amount < self::MIN_AMOUNT_RIALS || $amount > self::MAX_AMOUNT_RIALS) {
            return PaymentResponse::failure(
                'مبلغ سفارش خارج از محدوده مجاز درگاه آیدی‌پی است (۱٬۰۰۰ تا ۵۰۰٬۰۰۰٬۰۰۰ ریال).'
            );
        }

        $order = $payment->order;

        $body = array_filter([
            'order_id' => $payment->reference,   // max 50 chars
            'amount' => $amount,
            'name' => $order->user?->name,
            'phone' => $order->user?->phone,
            'mail' => $order->user?->email,
            'desc' => "سفارش #{$order->id} — کاتالیا",
            'callback' => $callbackUrl,
        ], fn ($value) => filled($value));

        $response = $this->http()->withHeaders($this->headers())
            ->post($this->option('endpoints.request'), $body);

        $data = $response->json() ?? [];

        // A successful creation is 201 Created, not 200.
        if ($response->created() && filled($data['link'] ?? null)) {
            return PaymentResponse::success(
                $data['link'],
                (string) $data['id'],
                ['request' => $this->scrub($body), 'response' => $data],
            );
        }

        $errorCode = $data['error_code'] ?? null;

        $this->logError('purchase_failed', [
            'reference' => $payment->reference,
            'status' => $response->status(),
            'body' => $data,
        ]);

        return PaymentResponse::failure(
            self::ERRORS[$errorCode] ?? ($data['error_message'] ?? 'خطا در اتصال به درگاه آیدی‌پی.'),
            $errorCode,
            ['request' => $this->scrub($body), 'response' => $data],
        );
    }

    public function resolveReference(Request $request): ?string
    {
        // We send our reference as IDPay's order_id, and it comes back on both
        // the GET and the POST callback shapes.
        return $request->input('order_id');
    }

    public function verify(Payment $payment, Request $request): VerifyResponse
    {
        $status = (int) $request->input('status');

        if (! in_array($status, self::VERIFIABLE, true)) {
            return VerifyResponse::failure(
                self::STATUSES[$status] ?? "پرداخت ناموفق بود (کد وضعیت {$status}).",
                ['callback' => $request->all()],
                $status,
            );
        }

        // Guard against a replayed receipt from a different transaction:
        // IDPay explicitly makes double-spend detection the merchant's job.
        $callbackId = $request->input('id');

        if (blank($callbackId) || $callbackId !== $payment->authority) {
            $this->logError('transaction_id_mismatch', [
                'reference' => $payment->reference,
                'expected' => $payment->authority,
                'received' => $callbackId,
            ]);

            return VerifyResponse::failure('اطلاعات بازگشتی درگاه با تراکنش مطابقت ندارد.');
        }

        $response = $this->http()->withHeaders($this->headers())
            ->post($this->option('endpoints.verify'), [
                'id' => $payment->authority,
                'order_id' => $payment->reference,
            ]);

        $data = $response->json() ?? [];
        $verifyStatus = (int) ($data['status'] ?? 0);

        if ($response->successful() && in_array($verifyStatus, self::SETTLED, true)) {
            // The amount IDPay confirms must match what we asked for.
            $confirmed = (int) ($data['amount'] ?? 0);

            if ($confirmed !== $payment->amountInRials()) {
                $this->logError('amount_mismatch', [
                    'reference' => $payment->reference,
                    'expected' => $payment->amountInRials(),
                    'received' => $confirmed,
                ]);

                return VerifyResponse::failure(
                    'مبلغ تأییدشده با مبلغ سفارش مطابقت ندارد.',
                    ['verify' => $data],
                );
            }

            return VerifyResponse::success(
                refCode: (string) ($data['track_id'] ?? $payment->authority),
                rawData: ['verify' => $data],
                cardPan: data_get($data, 'payment.card_no'),
                alreadyVerified: $verifyStatus === 101,
                code: $verifyStatus,
            );
        }

        $errorCode = $data['error_code'] ?? null;

        $this->logError('verify_failed', [
            'reference' => $payment->reference,
            'status' => $response->status(),
            'body' => $data,
        ]);

        return VerifyResponse::failure(
            self::ERRORS[$errorCode]
                ?? self::STATUSES[$verifyStatus]
                ?? ($data['error_message'] ?? 'تأیید پرداخت ناموفق بود.'),
            ['verify' => $data],
            $errorCode ?? $verifyStatus,
        );
    }

    /**
     * Latest known state of a transaction — use it to reconcile payments whose
     * callback never arrived, before the 10-minute verify window closes.
     */
    public function inquiry(Payment $payment): array
    {
        $response = $this->http()->withHeaders($this->headers())
            ->post($this->option('endpoints.inquiry'), [
                'id' => $payment->authority,
                'order_id' => $payment->reference,
            ]);

        return $response->json() ?? [];
    }
}
