<?php

namespace App\Services\Payment\DTOs;

class PaymentResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $redirectUrl = null,
        /** Gateway-side handle: ZarinPal authority, IDPay id, DigiPay ticket. */
        public readonly ?string $authority = null,
        public readonly ?string $message = null,
        /** Gateway error code, kept for logs and support tickets. */
        public readonly int|string|null $code = null,
        public readonly array $rawData = [],
    ) {}

    public static function success(string $redirectUrl, string $authority, array $rawData = []): self
    {
        return new self(true, $redirectUrl, $authority, rawData: $rawData);
    }

    public static function failure(string $message, int|string|null $code = null, array $rawData = []): self
    {
        return new self(false, message: $message, code: $code, rawData: $rawData);
    }
}
