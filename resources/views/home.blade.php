@extends('layouts.app')

@section('title', 'صفحه اصلی')

@section('content')

<x-home-showcase :hero-banner="$heroBanner" />

{{-- Categories --}}
<section class="px-4 md:px-20 mt-20">
    <div data-reveal-stagger="70" class="flex items-center gap-5 md:gap-7 overflow-x-auto pb-2 md:pb-0 md:justify-between scrollbar-hide">
        @forelse($categories as $cat)
        <a href="{{ route('products.index', ['category' => $cat->slug]) }}"
           data-reveal
           class="flex flex-col items-center gap-3 shrink-0 md:flex-1 md:shrink group transition-transform duration-300 hover:-translate-y-1">
            <div class="size-[118px] md:size-auto md:w-full md:aspect-square rounded-full overflow-hidden bg-[#f6f0eb] ring-2 ring-transparent ring-offset-2 ring-offset-[#f9f8f4] group-hover:ring-[#8a6300] transition-all duration-300">
                @if($cat->getFirstMediaUrl('image'))
                <img src="{{ $cat->getFirstMediaUrl('image') }}"
                     alt="{{ $cat->name }}"
                     loading="lazy"
                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 ease-out">
                @else
                <x-jewelry-placeholder :category="$cat->slug" />
                @endif
            </div>
            <span class="text-base text-[#6b6154] font-medium group-hover:text-[#8a6300] transition-colors text-center">{{ $cat->name }}</span>
        </a>
        @empty
        <p class="text-[#a8a096] text-sm w-full text-center">دسته‌بندی‌ای ثبت نشده.</p>
        @endforelse
    </div>
</section>

{{-- Newest Products --}}
<x-product-carousel title="جدیدترین محصولات" :products="$newestProducts" :link="route('products.index', ['sort' => 'newest'])" />

{{-- Promo Banners --}}
<section class="px-4 md:px-20 mt-20 flex flex-col sm:flex-row-reverse gap-4 md:gap-5">
    @if($promoLeft)
    <a href="{{ $promoLeft->link ?? '#' }}" data-reveal class="flex-1 rounded-2xl overflow-hidden h-44 sm:h-[263px] relative block group shadow-sm hover:shadow-2xl transition-shadow duration-500">
        <img src="{{ $promoLeft->getFirstMediaUrl('image') }}"
             alt="{{ $promoLeft->title }}"
             loading="lazy"
             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
        @if($promoLeft->title)
        <div class="absolute inset-0 flex items-center justify-end pr-6 md:pr-8 bg-black/20 group-hover:bg-black/35 transition-colors duration-500">
            <div class="text-white text-right transition-transform duration-500 group-hover:-translate-y-1">
                <h3 class="text-xl md:text-2xl font-bold">{{ $promoLeft->title }}</h3>
                @if($promoLeft->subtitle)<p class="text-xs md:text-sm mt-1 opacity-90">{{ $promoLeft->subtitle }}</p>@endif
            </div>
        </div>
        @endif
    </a>
    @else
    <a href="{{ route('products.index') }}" data-reveal class="group relative block h-44 flex-1 overflow-hidden rounded-2xl shadow-sm transition-shadow duration-500 hover:shadow-2xl sm:h-[263px]">
        <img src="/images/promo-silver-men.png" alt="کالکشن نقره مردانه" loading="lazy" class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">
    </a>
    @endif
    @if($promoRight)
    <a href="{{ $promoRight->link ?? '#' }}" data-reveal class="flex-1 rounded-2xl overflow-hidden h-44 sm:h-[263px] relative block group shadow-sm hover:shadow-2xl transition-shadow duration-500">
        <img src="{{ $promoRight->getFirstMediaUrl('image') }}"
             alt="{{ $promoRight->title }}"
             loading="lazy"
             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
        @if($promoRight->title)
        <div class="absolute inset-0 flex items-center justify-end pr-6 md:pr-8 bg-black/20 group-hover:bg-black/35 transition-colors duration-500">
            <div class="text-white text-right transition-transform duration-500 group-hover:-translate-y-1">
                <h3 class="text-xl md:text-2xl font-bold">{{ $promoRight->title }}</h3>
                @if($promoRight->subtitle)<p class="text-xs md:text-sm mt-1 opacity-90">{{ $promoRight->subtitle }}</p>@endif
            </div>
        </div>
        @endif
    </a>
    @else
    <a href="{{ route('products.index') }}" data-reveal class="group relative block h-44 flex-1 overflow-hidden rounded-2xl shadow-sm transition-shadow duration-500 hover:shadow-2xl sm:h-[263px]">
        <img src="/images/promo-silver-women.png" alt="کالکشن نقره زنانه" loading="lazy" class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">
    </a>
    @endif
</section>

{{-- Featured Products --}}
<x-product-carousel title="محصولات پیشنهادی" :products="$featuredProducts" :link="route('products.index')" />

@endsection
