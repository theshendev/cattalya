@extends('layouts.app')
@section('title', 'سفارش‌های من')

@section('content')
@php use App\Support\Jalali; @endphp
<section class="px-4 py-6 md:px-20 md:py-8">
    <div class="flex flex-col-reverse gap-5 lg:flex-row-reverse lg:items-start">
        <div class="w-full rounded-3xl bg-white p-5 md:p-7">
            <h1 class="mb-5 flex items-center justify-start gap-2 text-lg font-bold text-[#2b2620]">
                <img src="/icons/cart.svg" alt="" class="size-6"> سفارش‌ها
            </h1>

            @if($orders->isEmpty())
            <div class="flex min-h-64 flex-col items-center justify-center gap-4 text-center">
                <img src="/icons/cart.svg" alt="" class="size-10 opacity-40">
                <p class="text-sm text-[#6b6154]">هنوز سفارشی ثبت نکرده‌اید.</p>
                <a href="{{ route('products.index') }}" class="press rounded-2xl bg-[#111] px-6 py-3 text-sm text-white">شروع خرید</a>
            </div>
            @else
            <div class="flex flex-col gap-4">
                @foreach($orders as $order)
                <a href="{{ route('orders.show', $order) }}" class="group block rounded-2xl border border-[#f0e6c8] p-4 transition-colors hover:border-[#8a6300]">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#f0e6c8] pb-3">
                        <p class="flex items-center gap-2 font-bold text-[#2b2620]">
                            <img src="/icons/gift.svg" alt="" class="size-5"> سفارش {{ $order->id }}
                            <span class="rounded-lg px-2 py-0.5 text-xs font-normal {{ $order->status === 'pending' ? 'bg-[#fdeee0] text-[#a34d06]' : 'bg-[#e6f6ec] text-[#1a7f3c]' }}">{{ $order->statusLabel() }}</span>
                        </p>
                        <p class="text-xs text-[#a8a096]">
                            {{ Jalali::formatLong($order->created_at) }} <span class="mx-1">|</span> مبلغ
                            <strong class="text-[#2b2620]">{{ number_format($order->total_amount) }} تومان</strong>
                        </p>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-3">
                        @foreach($order->items as $item)
                        @php $image = $item->product?->getFirstMediaUrl('images', 'thumb') ?: $item->product?->getFirstMediaUrl('images'); @endphp
                        <span class="size-20 overflow-hidden rounded-2xl bg-[#f9f6f3]" title="{{ $item->product_name }}">
                            @if($image)
                            <img src="{{ $image }}" alt="{{ $item->product_name }}" class="h-full w-full object-cover">
                            @else
                            <x-jewelry-placeholder :category="$item->product?->category?->slug" />
                            @endif
                        </span>
                        @endforeach
                    </div>
                </a>
                @endforeach
            </div>
            @if($orders->hasPages())<div class="mt-6">{{ $orders->links() }}</div>@endif
            @endif
        </div>

        <x-account-sidebar active="orders" />
    </div>
</section>
@endsection
