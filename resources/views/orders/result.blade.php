@extends('layouts.app')
@section('title', 'نتیجه پرداخت')

@section('content')
@php
    $isPaid = $order->status !== 'pending' && $order->status !== 'cancelled';
    $quote = [
        'subtotal' => (float) $order->subtotal_amount,
        'discount' => (float) $order->discount_amount,
        'shipping' => (float) $order->shipping_cost,
        'packaging' => (float) $order->packaging_cost,
        'total' => (float) $order->total_amount,
    ];
    $retryGateway = $order->latestPayment?->gateway ?? config('gateways.default');
@endphp
<div class="px-4 py-6 md:px-20 md:py-8">
    <x-checkout-stepper :step="3" :done="true" />

    <div class="mt-5 grid grid-cols-1 items-start gap-5 lg:grid-cols-[1fr_325px]">
        <section class="flex flex-col items-center rounded-3xl bg-white px-6 py-14 text-center md:py-16">
            <svg class="size-16 {{ $isPaid ? 'text-[#2f8f4e]' : 'text-[#d64545]' }}" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="6" y="16" width="48" height="36" rx="8"/>
                <path d="M14 16l24-8 6 8M54 30h-8a5 5 0 0 0 0 10h8"/>
                @if($isPaid)
                <circle cx="20" cy="46" r="9" fill="white"/><path d="m16 46 3 3 6-6"/>
                @else
                <circle cx="20" cy="46" r="9" fill="white"/><path d="m16.5 42.5 7 7m0-7-7 7"/>
                @endif
            </svg>

            @if($isPaid)
            <h1 class="mt-5 text-xl font-bold text-[#2f8f4e] md:text-2xl">پرداخت با موفقیت انجام شد</h1>
            <p class="mt-3 max-w-lg text-sm leading-7 text-[#6b6154]">
                پرداخت شما با کد سفارش <strong dir="ltr">{{ $order->id }}</strong> با موفقیت انجام شد
                @if($order->latestPayment?->ref_code)
                (کد رهگیری: <strong dir="ltr">{{ $order->latestPayment->ref_code }}</strong>)
                @endif
                . می‌توانید جزئیات سفارش خود را از بخش
                <a href="{{ route('orders.index') }}" class="text-[#1f7a8c] underline">سفارش‌های من</a> مشاهده کنید.
            </p>
            <a href="{{ route('home') }}" class="mt-6 text-sm text-[#1f7a8c] hover:underline">بازگشت به صفحه اصلی</a>
            @else
            <h1 class="mt-5 text-xl font-bold text-[#d64545] md:text-2xl">پرداخت انجام نشد.</h1>
            <p class="mt-3 max-w-lg text-sm leading-7 text-[#6b6154]">
                پرداخت شما انجام نشد. در صورت کسر مبلغ از حساب شما تا ۷۲ ساعت آینده مبلغ به حساب شما بازگشت داده می‌شود.
            </p>
            @if($errors->has('payment'))
            <p class="mt-3 rounded-xl bg-[#fbeaea] px-4 py-2 text-sm text-red-600">{{ $errors->first('payment') }}</p>
            @endif
            @if($order->status === 'pending')
            <form method="POST" action="{{ route('payment.pay', $order) }}" class="mt-6">
                @csrf
                <input type="hidden" name="gateway" value="{{ $retryGateway }}">
                <button type="submit" class="text-sm text-[#1f7a8c] hover:underline">تلاش مجدد</button>
            </form>
            @endif
            <a href="{{ route('orders.show', $order) }}" class="mt-3 text-xs text-[#a8a096] hover:text-[#8a6300]">مشاهده سفارش</a>
            @endif
        </section>

        <x-invoice-summary :quote="$quote" :sticky="false" />
    </div>
</div>
@endsection
