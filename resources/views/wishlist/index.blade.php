@extends('layouts.app')

@section('title', 'علاقه‌مندی‌ها')
@section('hide-mobile-nav', '1')

@section('mobile-header')
@include('profile.partials.mobile-header', ['title' => 'علاقه‌مندی‌ها'])
@endsection

@section('content')
@php
    $lastRowStart = $products->count() - (($products->count() - 1) % 3 + 1);
@endphp
<section class="px-4 py-4 md:px-20 lg:py-8">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-start">
        <x-account-sidebar active="wishlist" />

        <div class="w-full lg:rounded-3xl lg:bg-white lg:px-6 lg:py-6">
            <h1 class="mb-4 hidden items-center gap-2 text-xl font-bold text-[#2b2620] lg:flex">
                <svg class="size-7 text-[#6b6154]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M12 20s-7.5-4.6-9.2-9.3C1.7 7.5 3.9 4.5 7.1 4.5c2 0 3.6 1.1 4.9 2.9 1.3-1.8 2.9-2.9 4.9-2.9 3.2 0 5.4 3 4.3 6.2C19.5 15.4 12 20 12 20Z"/></svg>
                علاقه‌مندی‌ها
            </h1>

            @if($products->isEmpty())
            <div class="flex min-h-64 flex-col items-center justify-center gap-4 rounded-3xl bg-white text-center">
                <img src="/icons/heart.svg" alt="" class="size-10 opacity-40">
                <p class="text-sm text-[#6b6154]">هنوز محصولی به علاقه‌مندی‌ها اضافه نکرده‌اید.</p>
                <a href="{{ route('products.index') }}" class="press rounded-2xl bg-[#111] px-6 py-3 text-sm text-white">مشاهده محصولات</a>
            </div>
            @else
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 lg:gap-0">
                @foreach($products as $product)
                <div class="lg:border-[#ede6d3] {{ $loop->index % 3 !== 2 && ! $loop->last ? 'lg:border-l' : '' }} {{ $loop->index < $lastRowStart ? 'lg:border-b' : '' }} lg:p-2">
                    <x-product-card :product="$product" variant="listing" />
                </div>
                @endforeach
            </div>
            @if($products->hasPages())<div class="mt-6">{{ $products->links() }}</div>@endif
            @endif
        </div>
    </div>
</section>
@endsection
