<?php

namespace App\Services\Payment\DTOs;

class VerifyResponse
{
    public function __construct(
        public readonly bool $success,
        /** Bank reference: ZarinPal ref_id, IDPay track_id, DigiPay rrn. */
        public readonly ?string $refCode = null,
        public readonly ?string $message = null,
        public readonly array $rawData = [],
        public readonly int|string|null $code = null,
        /** Masked PAN, when the gateway returns one. */
        public readonly ?string $cardPan = null,
        /**
         * True when the gateway says the transaction was already verified
         * (ZarinPal 101, IDPay 101). The money is settled; treat as success
         * but do not double-count it in reporting.
         */
        public readonly bool $alreadyVerified = false,
    ) {}

    public static function success(
        string $refCode,
        array $rawData = [],
        ?string $cardPan = null,
        bool $alreadyVerified = false,
        int|string|null $code = null,
    ): self {
        return new self(
            success: true,
            refCode: $refCode,
            rawData: $rawData,
            code: $code,
            cardPan: $cardPan,
            alreadyVerified: $alreadyVerified,
        );
    }

    public static function failure(string $message, array $rawData = [], int|string|null $code = null): self
    {
        return new self(false, message: $message, rawData: $rawData, code: $code);
    }
}
