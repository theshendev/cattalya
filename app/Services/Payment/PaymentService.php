<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\Contracts\PaymentGateway;
use App\Services\Payment\DTOs\PaymentResponse;
use App\Services\Payment\DTOs\VerifyResponse;
use App\Services\Payment\Gateways\DigiPayGateway;
use App\Services\Payment\Gateways\IDPayGateway;
use App\Services\Payment\Gateways\ZarinPalGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PaymentService
{
    /** @var array<string, class-string<PaymentGateway>> */
    private array $drivers = [
        'zarinpal' => ZarinPalGateway::class,
        'idpay' => IDPayGateway::class,
        'digipay' => DigiPayGateway::class,
    ];

    public function driver(string $gateway): PaymentGateway
    {
        if (! isset($this->drivers[$gateway])) {
            throw new InvalidArgumentException("درگاه پرداخت '{$gateway}' پشتیبانی نمی‌شود.");
        }

        return app($this->drivers[$gateway]);
    }

    public function defaultDriver(): PaymentGateway
    {
        return $this->driver(config('gateways.default', 'zarinpal'));
    }

    /**
     * Gateways that are switched on *and* have credentials.
     *
     * @return list<string>
     */
    public function availableGateways(): array
    {
        return array_values(array_filter(
            array_keys($this->drivers),
            fn (string $name) => $this->driver($name)->isConfigured(),
        ));
    }

    /**
     * Names plus display labels, for rendering the checkout selector.
     *
     * @return array<string, string>
     */
    public function gatewayOptions(): array
    {
        $options = [];

        foreach ($this->availableGateways() as $name) {
            $options[$name] = config("gateways.drivers.{$name}.label", $name);
        }

        return $options;
    }

    /**
     * Create the Payment row, then ask the gateway where to send the buyer.
     *
     * The row is written *before* the API call so a callback can always be
     * matched, even if the response is lost in transit.
     */
    public function initiate(Order $order, string $gatewayName, callable $callbackUrlBuilder): PaymentResponse
    {
        $gateway = $this->driver($gatewayName);

        if (! $gateway->isConfigured()) {
            return PaymentResponse::failure('این درگاه پرداخت در حال حاضر در دسترس نیست.');
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'gateway' => $gatewayName,
            'amount' => $order->total_amount,
            'reference' => Payment::generateReference($order->id),
            'status' => Payment::STATUS_PENDING,
        ]);

        // The callback URL carries the reference so ZarinPal — which echoes
        // nothing of ours — can still be matched back to this row.
        $callbackUrl = $callbackUrlBuilder($gatewayName, $payment);

        $response = $gateway->pay($payment, $callbackUrl);

        if ($response->success) {
            $payment->authority = $response->authority;
            $payment->recordGatewayData('initiate', $response->rawData);
            $payment->save();

            return $response;
        }

        $payment->status = Payment::STATUS_FAILED;
        $payment->recordGatewayData('initiate', $response->rawData + ['error' => $response->message]);
        $payment->save();

        return $response;
    }

    /**
     * Verify a callback and settle the order.
     *
     * Locks the payment row so two concurrent callbacks (browser redirect plus
     * a server-to-server retry) cannot both mark the order paid.
     */
    public function verify(Request $request, string $gatewayName, string $reference): VerifyResponse
    {
        $gateway = $this->driver($gatewayName);

        return DB::transaction(function () use ($request, $gateway, $gatewayName, $reference) {
            $payment = Payment::where('reference', $reference)
                ->where('gateway', $gatewayName)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                Log::warning('payment.callback.unknown_reference', [
                    'gateway' => $gatewayName,
                    'reference' => $reference,
                ]);

                return VerifyResponse::failure('پرداخت یافت نشد.');
            }

            // Idempotency: a replayed callback must not re-verify.
            if ($payment->isPaid()) {
                return VerifyResponse::success(
                    refCode: (string) $payment->ref_code,
                    cardPan: $payment->card_pan,
                    alreadyVerified: true,
                );
            }

            $response = $gateway->verify($payment, $request);

            if ($response->success) {
                $payment->fill([
                    'status' => Payment::STATUS_PAID,
                    'ref_code' => $response->refCode,
                    'card_pan' => $response->cardPan,
                    'paid_at' => now(),
                ]);
                $payment->recordGatewayData('verify', $response->rawData);
                $payment->save();

                // Only advance an order that is still awaiting payment.
                if ($payment->order->status === 'pending') {
                    $payment->order->update(['status' => 'paid']);
                }
            } else {
                $payment->status = Payment::STATUS_FAILED;
                $payment->recordGatewayData('verify', $response->rawData + ['error' => $response->message]);
                $payment->save();
            }

            return $response;
        });
    }
}
