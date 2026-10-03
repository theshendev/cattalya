<?php

namespace App\Services\Payment\Contracts;

use App\Models\Payment;
use App\Services\Payment\DTOs\PaymentResponse;
use App\Services\Payment\DTOs\VerifyResponse;
use Illuminate\Http\Request;

interface PaymentGateway
{
    /**
     * Machine name used in config, routes and the payments table.
     */
    public function name(): string;

    /**
     * Whether credentials are present and the gateway is switched on.
     */
    public function isConfigured(): bool;

    /**
     * Whether the gateway can take a payment of this size (in Tomans).
     */
    public function supportsAmount(float $toman): bool;

    /**
     * Ask the gateway for a payment session and return where to send the user.
     *
     * The Payment row already exists and carries the reference the gateway
     * must echo back to us on the callback.
     */
    public function pay(Payment $payment, string $callbackUrl): PaymentResponse;

    /**
     * Pull our own reference out of an incoming callback, so the controller
     * can find the Payment row before trusting anything else in the request.
     */
    public function resolveReference(Request $request): ?string;

    /**
     * Confirm the payment with the gateway. Implementations must settle the
     * money — an unverified transaction is auto-reversed by every gateway
     * here — and must reject amount/identifier mismatches.
     */
    public function verify(Payment $payment, Request $request): VerifyResponse;
}
