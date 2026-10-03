<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    /**
     * Initiate payment for an order.
     * POST /payment/{order}/pay
     */
    public function pay(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === Auth::id(), 403);
        abort_unless($order->status === 'pending', 400, 'این سفارش قابل پرداخت نیست');

        $gateway = $request->input('gateway', config('gateways.default'));

        if (! in_array($gateway, $this->paymentService->availableGateways(), true)) {
            return back()->withErrors(['gateway' => 'درگاه پرداخت نامعتبر یا غیرفعال است']);
        }

        $response = $this->paymentService->initiate(
            $order,
            $gateway,
            // Every gateway gets the payment reference in the URL. ZarinPal
            // needs it (it echoes nothing of ours); for the others it is a
            // harmless fallback if their echoed field is ever missing.
            fn (string $name, Payment $payment) => route('payment.callback', [
                'gateway' => $name,
                'ref' => $payment->reference,
            ]),
        );

        if (! $response->success) {
            return redirect()->route('orders.result', $order)
                ->withErrors(['payment' => $response->message]);
        }

        return redirect()->away($response->redirectUrl);
    }

    /**
     * Handle a gateway callback.
     * GET|POST /payment/callback/{gateway}
     *
     * Unauthenticated: the request comes from the gateway, not the buyer's
     * session. Everything is validated against the stored Payment row.
     */
    public function callback(Request $request, string $gateway): RedirectResponse
    {
        if (! in_array($gateway, $this->paymentService->availableGateways(), true)) {
            return redirect()->route('orders.index')
                ->withErrors(['payment' => 'درگاه پرداخت نامعتبر است']);
        }

        $reference = $this->paymentService->driver($gateway)->resolveReference($request)
            ?? $request->query('ref');

        if (blank($reference)) {
            Log::warning('payment.callback.missing_reference', [
                'gateway' => $gateway,
                'query' => $request->query(),
            ]);

            return redirect()->route('orders.index')
                ->withErrors(['payment' => 'اطلاعات پرداخت ناقص است']);
        }

        $payment = Payment::where('reference', $reference)->where('gateway', $gateway)->first();

        if (! $payment) {
            return redirect()->route('orders.index')
                ->withErrors(['payment' => 'پرداخت یافت نشد']);
        }

        $result = $this->paymentService->verify($request, $gateway, $reference);

        $redirect = redirect()->route('orders.result', $payment->order_id);

        return $result->success
            ? $redirect
            : $redirect->withErrors(['payment' => $result->message]);
    }
}
