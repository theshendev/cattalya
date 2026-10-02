@extends('layouts.app')
@section('title', 'سفارش‌های من')

@section('content')
<div class="px-4 md:px-20 py-6 md:py-10">
    <h1 class="text-xl md:text-2xl font-bold text-[#2b2620] text-right mb-6 md:mb-8 animate-fade-up">سفارش‌های من</h1>

    @if($orders->isEmpty())
    <div class="text-center py-16 md:py-24 text-[#a8a096] animate-fade-up">
        <svg class="w-16 h-16 md:w-20 md:h-20 mx-auto mb-4 opacity-30 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-lg md:text-xl font-medium">هنوز سفارشی ثبت نکرده‌اید</p>
        <a href="{{ route('products.index') }}"
           class="press inline-block mt-4 bg-[#8a6300] text-white px-8 py-3 rounded-2xl font-medium hover:bg-[#6b4d00] hover:shadow-xl hover:shadow-[#8a6300]/25">
            شروع خرید
        </a>
    </div>
    @else
    <div data-reveal-stagger="70" class="flex flex-col gap-3 md:gap-4">
        @foreach($orders as $order)
        @php
        $statusMap = [
            'pending'    => ['label' => 'در انتظار پرداخت', 'color' => 'text-amber-600 bg-amber-50'],
            'paid'       => ['label' => 'پرداخت شده',       'color' => 'text-blue-600 bg-blue-50'],
            'processing' => ['label' => 'در حال پردازش',    'color' => 'text-purple-600 bg-purple-50'],
            'shipped'    => ['label' => 'ارسال شده',         'color' => 'text-indigo-600 bg-indigo-50'],
            'delivered'  => ['label' => 'تحویل داده شده',   'color' => 'text-green-600 bg-green-50'],
            'cancelled'  => ['label' => 'لغو شده',           'color' => 'text-red-600 bg-red-50'],
        ];
        $st = $statusMap[$order->status] ?? ['label' => $order->status, 'color' => 'text-gray-600 bg-gray-50'];
        @endphp
        <a href="{{ route('orders.show', $order) }}"
           data-reveal
           class="group bg-white rounded-2xl md:rounded-3xl p-4 md:p-6 flex items-center justify-between gap-3 border border-transparent hover:border-[#f0e6c8] hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-center gap-3 text-xs md:text-sm text-[#6b6154]">
                <span class="hidden sm:inline">{{ $order->created_at->diffForHumans() }}</span>
                <span>{{ $order->items->count() }} کالا</span>
            </div>
            <div class="flex items-center gap-3 md:gap-6 text-right">
                <span class="text-xs font-medium px-2 md:px-3 py-1 rounded-full {{ $st['color'] }} whitespace-nowrap">{{ $st['label'] }}</span>
                <div>
                    <p class="font-bold text-[#2b2620] text-sm md:text-base transition-colors duration-200 group-hover:text-[#8a6300]">{{ number_format($order->total_amount) }} <span class="text-xs font-normal text-[#a8a096]">تومان</span></p>
                    <p class="text-xs text-[#a8a096]">سفارش #{{ $order->id }}</p>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @if($orders->hasPages())
    <div data-reveal class="mt-8 flex justify-center">{{ $orders->links() }}</div>
    @endif
    @endif
</div>
@endsection
