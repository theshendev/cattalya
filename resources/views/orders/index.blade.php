@extends('layouts.app')
@section('title', 'سفارش‌های من')
@section('hide-mobile-nav', '1')

@section('mobile-header')
@include('profile.partials.mobile-header', ['title' => 'سفارش‌ها'])
@endsection

@section('content')
@php use App\Support\Jalali; @endphp
<section class="px-4 py-4 md:px-20 lg:py-8">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-start">
        <x-account-sidebar active="orders" />

        <div class="w-full lg:rounded-3xl lg:bg-white lg:px-6 lg:py-6">
            <h1 class="mb-5 hidden items-center gap-2 text-xl font-bold text-[#2b2620] lg:flex">
                <svg class="size-7 text-[#6b6154]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M6 9h12l-1 11H7L6 9Zm3 0V7a3 3 0 0 1 6 0v2"/></svg>
                سفارش‌ها
            </h1>

            @if($orders->isEmpty())
            <div class="flex min-h-64 flex-col items-center justify-center gap-4 rounded-3xl bg-white text-center">
                <img src="/icons/cart.svg" alt="" class="size-10 opacity-40">
                <p class="text-sm text-[#6b6154]">هنوز سفارشی ثبت نکرده‌اید.</p>
                <a href="{{ route('products.index') }}" class="press rounded-2xl bg-[#111] px-6 py-3 text-sm text-white">شروع خرید</a>
            </div>
            @else
            <div class="flex flex-col gap-4 lg:gap-5">
                @foreach($orders as $order)
                <a href="{{ route('orders.show', $order) }}" class="group block rounded-2xl border border-[#f0e6c8] bg-white px-4 py-4 transition-colors hover:border-[#c9a227] lg:px-6">
                    <div class="flex flex-col gap-2 border-b border-[#ede6d3] pb-4 lg:flex-row lg:items-center lg:justify-between">
                        <p class="flex items-center gap-2 text-base font-bold text-[#2b2620] lg:text-lg">
                            <svg class="size-6 shrink-0 text-[#a8a096]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="m3.5 7.5 8.5-4 8.5 4v4M3.5 7.5v9l8.5 4m-8.5-13 8.5 4m0 0v9m0-9 8.5-4M7.5 5.5l8.5 4"/><circle cx="17.5" cy="17.5" r="3" stroke-width="1.4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="m16.3 17.5.8.8 1.6-1.6"/></svg>
                            سفارش {{ $order->id }}
                            @if($order->status === 'pending')
                            <span class="rounded-lg bg-[#fdeee0] px-2 py-0.5 text-xs font-normal text-[#a34d06]">{{ $order->statusLabel() }}</span>
                            @endif
                        </p>
                        <p class="flex items-center gap-2 text-sm text-[#a8a096]">
                            {{ Jalali::formatLong($order->created_at) }}
                            <span class="h-3 w-px bg-[#d9d4cc]" aria-hidden="true"></span>
                            مبلغ
                            <strong class="font-bold text-[#2b2620]">{{ number_format($order->total_amount) }} تومان</strong>
                        </p>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-4">
                        @foreach($order->items as $item)
                        @php $image = $item->product?->getFirstMediaUrl('images', 'thumb') ?: $item->product?->getFirstMediaUrl('images'); @endphp
                        <span class="size-20 overflow-hidden rounded-2xl bg-[#f5f5f5]" title="{{ $item->product_name }}">
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
    </div>
</section>
@endsection
