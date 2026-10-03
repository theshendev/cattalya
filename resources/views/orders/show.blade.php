@extends('layouts.app')
@section('title', 'سفارش #' . $order->id)

@section('content')
@php
$statusMap = [
    'pending'    => ['label' => 'در انتظار پرداخت', 'color' => 'text-amber-600 bg-amber-50  border-amber-200'],
    'paid'       => ['label' => 'پرداخت شده',        'color' => 'text-blue-600  bg-blue-50   border-blue-200'],
    'processing' => ['label' => 'در حال پردازش',     'color' => 'text-purple-600 bg-purple-50 border-purple-200'],
    'shipped'    => ['label' => 'ارسال شده',          'color' => 'text-indigo-600 bg-indigo-50 border-indigo-200'],
    'delivered'  => ['label' => 'تحویل داده شده',    'color' => 'text-green-600 bg-green-50  border-green-200'],
    'cancelled'  => ['label' => 'لغو شده',            'color' => 'text-red-600   bg-red-50    border-red-200'],
];
$st = $statusMap[$order->status] ?? ['label' => $order->status, 'color' => 'text-gray-600 bg-gray-50 border-gray-200'];
@endphp

<div class="px-4 md:px-20 py-6 md:py-10 max-w-4xl mr-auto ml-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6 md:mb-8 animate-fade-up">
        <span class="text-xs md:text-sm font-medium px-3 md:px-4 py-1.5 md:py-2 rounded-full border {{ $st['color'] }} {{ $order->status === 'pending' ? 'animate-pulse' : '' }}">{{ $st['label'] }}</span>
        <div class="text-right">
            <h1 class="text-xl md:text-2xl font-bold text-[#2b2620]">سفارش #{{ $order->id }}</h1>
            <p class="text-xs md:text-sm text-[#a8a096] mt-1">{{ \App\Support\Jalali::formatLong($order->created_at) }} — {{ $order->created_at->format('H:i') }}</p>
        </div>
    </div>

    <div data-reveal-stagger="80" class="flex flex-col gap-4 md:gap-5">

        {{-- Items --}}
        <div data-reveal class="bg-white rounded-2xl md:rounded-3xl p-4 md:p-6">
            <h2 class="font-bold text-[#2b2620] text-right mb-4 md:mb-5">اقلام سفارش</h2>
            <div class="flex flex-col divide-y divide-[#f0e6c8]">
                @foreach($order->items as $item)
                <div class="flex items-center justify-between py-3 md:py-4 first:pt-0 last:pb-0 gap-3 rounded-xl -mx-2 px-2 transition-colors duration-200 hover:bg-[#f9f6f3]">
                    <div class="text-xs md:text-sm font-bold text-[#2b2620] shrink-0">{{ number_format($item->total_price) }} ت</div>
                    <div class="text-right flex-1 min-w-0">
                        <p class="font-medium text-[#2b2620] text-sm truncate">{{ $item->product_name }}</p>
                        <p class="text-xs text-[#a8a096] mt-0.5">
                            {{ number_format($item->unit_price) }} × {{ $item->quantity }}
                            @if($item->weight_grams) · {{ $item->weight_grams }}g @endif
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
            <dl class="mt-3 flex flex-col gap-2 border-t border-[#f0e6c8] pt-3 text-sm text-[#6b6154] md:mt-4 md:pt-4">
                <div class="flex justify-between"><dt>جمع کالاها</dt><dd>{{ number_format($order->subtotal_amount) }} تومان</dd></div>
                @if($order->discount_amount > 0)<div class="flex justify-between text-[#1f7a8c]"><dt>تخفیف{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</dt><dd>{{ number_format($order->discount_amount) }} تومان</dd></div>@endif
                <div class="flex justify-between"><dt>هزینه ارسال</dt><dd>{{ $order->shipping_cost > 0 ? number_format($order->shipping_cost).' تومان' : 'رایگان' }}</dd></div>
                @if($order->packaging_cost > 0)<div class="flex justify-between"><dt>هزینه بسته‌بندی</dt><dd>{{ number_format($order->packaging_cost) }} تومان</dd></div>@endif
            </dl>
            <div class="border-t border-[#f0e6c8] mt-3 md:mt-4 pt-3 md:pt-4 flex items-center justify-between">
                <span class="text-lg md:text-xl font-bold text-[#2b2620]">{{ number_format($order->total_amount) }} تومان</span>
                <span class="font-bold text-[#6b6154]">جمع کل</span>
            </div>
        </div>

        {{-- Delivery Address --}}
        @php $shipTo = $order->shipping_address ?? []; @endphp
        <div data-reveal class="bg-white rounded-2xl md:rounded-3xl p-4 md:p-6">
            <h2 class="font-bold text-[#2b2620] text-right mb-4">آدرس تحویل</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 md:gap-3 text-sm text-right">
                <div><span class="text-[#a8a096]">گیرنده: </span><span class="text-[#2b2620] font-medium">{{ $shipTo['receiver_name'] ?? '—' }}</span></div>
                <div><span class="text-[#a8a096]">تلفن: </span><span class="text-[#2b2620] font-medium" dir="ltr">{{ $shipTo['phone'] ?? '—' }}</span></div>
                <div><span class="text-[#a8a096]">استان: </span><span class="text-[#2b2620] font-medium">{{ $shipTo['province'] ?? '—' }}</span></div>
                <div><span class="text-[#a8a096]">شهر: </span><span class="text-[#2b2620] font-medium">{{ $shipTo['city'] ?? '—' }}</span></div>
                <div class="col-span-1 sm:col-span-2"><span class="text-[#a8a096]">آدرس: </span><span class="text-[#2b2620] font-medium">{{ $shipTo['address'] ?? '—' }}@if(! empty($shipTo['plaque']))، پلاک {{ $shipTo['plaque'] }}@endif @if(! empty($shipTo['unit']))، واحد {{ $shipTo['unit'] }}@endif</span></div>
                <div><span class="text-[#a8a096]">کد پستی: </span><span class="text-[#2b2620] font-medium" dir="ltr">{{ $shipTo['postal_code'] ?? '—' }}</span></div>
            </div>
            @if($order->is_gift)
            <p class="mt-4 rounded-xl bg-[#fdeee0] px-4 py-3 text-right text-sm text-[#a34d06]">ارسال به عنوان هدیه@if($order->gift_message): «{{ $order->gift_message }}»@endif</p>
            @endif
        </div>

        {{-- Payment Info --}}
        @php
            $payment  = $order->payments()->latest()->first();
            $statuses = ['pending' => 'در انتظار پرداخت', 'paid' => 'موفق', 'failed' => 'ناموفق', 'refunded' => 'برگشت خورده'];
        @endphp
        @if($payment)
        <div data-reveal class="bg-white rounded-2xl md:rounded-3xl p-4 md:p-6">
            <h2 class="font-bold text-[#2b2620] text-right mb-4">اطلاعات پرداخت</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 md:gap-3 text-sm text-right">
                <div><span class="text-[#a8a096]">درگاه: </span><span class="font-medium">{{ config("gateways.drivers.{$payment->gateway}.label", $payment->gateway) }}</span></div>
                <div><span class="text-[#a8a096]">وضعیت: </span><span class="font-medium">{{ $statuses[$payment->status] ?? $payment->status }}</span></div>
                @if($payment->ref_code)
                <div><span class="text-[#a8a096]">کد رهگیری: </span><span class="font-bold text-[#8a6300]" dir="ltr">{{ $payment->ref_code }}</span></div>
                @endif
                @if($payment->card_pan)
                <div><span class="text-[#a8a096]">شماره کارت: </span><span class="font-medium" dir="ltr">{{ $payment->card_pan }}</span></div>
                @endif
                @if($payment->paid_at)
                <div class="col-span-1 sm:col-span-2"><span class="text-[#a8a096]">زمان پرداخت: </span><span class="font-medium">{{ $payment->paid_at->format('Y/m/d H:i') }}</span></div>
                @endif
            </div>
        </div>
        @endif

        {{-- Pay Now (if pending) --}}
        @if($order->status === 'pending')
        <div data-reveal class="bg-[#fdeee0] rounded-2xl md:rounded-3xl p-5 md:p-6 text-center">
            <p class="text-[#a34d06] font-medium mb-4">این سفارش هنوز پرداخت نشده است.</p>
            @php $gateways = app(\App\Services\Payment\PaymentService::class)->gatewayOptions(); @endphp
            @if(empty($gateways))
                <p class="text-sm text-red-600">در حال حاضر هیچ درگاه پرداختی فعال نیست.</p>
            @else
            <form method="POST" action="{{ route('payment.pay', $order) }}">
                @csrf
                {{-- Preselect the gateway chosen at checkout, if it is still available. --}}
                @php $selected = in_array(session('gateway'), array_keys($gateways), true) ? session('gateway') : array_key_first($gateways); @endphp
                <div class="flex flex-wrap justify-center gap-2 mb-4">
                    @foreach($gateways as $value => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="gateway" value="{{ $value }}" {{ $selected === $value ? 'checked' : '' }} class="sr-only peer">
                        <span class="inline-block border-2 border-[#f0e6c8] bg-white rounded-xl px-4 py-2 text-xs font-bold text-[#2b2620] peer-checked:border-[#8a6300] peer-checked:bg-[#fdeee0] transition-all">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
                <button type="submit"
                        class="press bg-[#8a6300] text-white font-bold px-8 md:px-10 py-3 rounded-2xl hover:bg-[#6b4d00] hover:shadow-xl hover:shadow-[#8a6300]/25">
                    پرداخت آنلاین
                </button>
            </form>
            @endif
        </div>
        @endif

        <a href="{{ route('orders.index') }}"
           data-reveal
           class="group text-sm text-[#6b6154] hover:text-[#8a6300] text-center transition-colors py-2">
            <span class="inline-block transition-transform duration-300 group-hover:translate-x-1">←</span> بازگشت به لیست سفارش‌ها
        </a>
    </div>
</div>
@endsection
