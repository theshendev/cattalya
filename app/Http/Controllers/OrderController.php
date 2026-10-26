<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product.media', 'items.product.category'])
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        $order->load('items');

        return view('orders.show', compact('order'));
    }

    /**
     * Landing page after the gateway: success or "try again".
     */
    public function result(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        $order->load('latestPayment');

        return view('orders.result', compact('order'));
    }
}
