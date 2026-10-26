@extends('layouts.app')

@section('title', 'صفحه اصلی')

@section('content')

{{-- Hero Banner --}}
<section class="px-4 md:px-20 pt-6 md:pt-8">
    <div class="relative rounded-2xl md:rounded-3xl overflow-hidden h-[300px] sm:h-72 md:h-[405px] bg-[#263a3b] group animate-scale-in">
        @if($heroBanner?->getFirstMediaUrl('image'))
        <img src="{{ $heroBanner->getFirstMediaUrl('image') }}"
             alt="{{ $heroBanner->title }}"
             class="absolute inset-0 w-full h-full object-cover opacity-70 scale-105 group-hover:scale-100 transition-transform duration-[1.2s] ease-out">
        @else
        <img src="/images/cattalya-emblem.png" alt="" aria-hidden="true"
             class="absolute -bottom-4 left-1/2 h-[58%] w-auto max-w-none -translate-x-1/2 select-none object-contain sm:h-[115%] sm:-bottom-6 sm:left-0 sm:translate-x-0 md:left-4 md:h-[130%]">
        @endif
        <div class="absolute inset-0 bg-gradient-to-l from-black/35 via-transparent to-black/10"></div>
        <div class="absolute inset-0 flex items-start pt-6 sm:items-center sm:pt-0">
            <div class="ml-auto mr-4 max-w-xs px-4 text-right text-[#fcfde3] md:mr-20 md:max-w-xl md:px-0">
                <h1 class="text-2xl sm:text-4xl md:text-6xl font-bold leading-tight mb-2 md:mb-4 animate-fade-up" style="animation-delay:.1s">
                    {{ $heroBanner?->title ?? 'به کاتالیا خوش اومدید' }}
                </h1>
                <p class="text-sm sm:text-base md:text-xl font-medium opacity-90 hidden sm:block animate-fade-up" style="animation-delay:.25s">
                    {{ $heroBanner?->subtitle ?? 'بهترین زیورآلات طلا با ضمانت اصالت' }}
                </p>
                @if($heroBanner?->link)
                <a href="{{ $heroBanner->link }}"
                   class="press inline-block mt-4 md:mt-6 bg-[#8a6300] text-white px-6 md:px-8 py-2.5 md:py-3 rounded-2xl text-sm font-medium hover:bg-[#6b4d00] hover:shadow-xl hover:shadow-black/20 animate-fade-up" style="animation-delay:.4s">
                    مشاهده محصولات
                </a>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Service Features Bar --}}
<section class="px-4 md:px-20 mt-4 md:mt-6">
    <div data-reveal-stagger="80" class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-0 md:border-b md:border-[#f0e6c8] md:divide-x md:divide-x-reverse md:divide-[#f0e6c8]">
        @foreach([
            ['icon' => 'gift',    'title' => 'بسته‌بندی هدیه‌ای',        'sub' => 'ارسال با بسته‌بندی مناسب هدیه'],
            ['icon' => 'routing', 'title' => 'ارسال سراسر ایران',         'sub' => 'امکان ثبت سفارش از تمامی نقاط'],
            ['icon' => 'truck',   'title' => 'ارسال رایگان',              'sub' => 'با خرید بالای ۱۲ میلیون تومان'],
            ['icon' => 'shield',  'title' => 'ضمانت اصالت کالا',          'sub' => 'ارائه فاکتور رسمی و قابل استناد'],
        ] as $feature)
        {{-- icon first in DOM → RTL flex places it on the RIGHT --}}
        <div data-reveal class="group flex items-center gap-2 md:gap-3 rounded-2xl p-3 md:p-4 md:m-2 transition-colors duration-300 hover:bg-[#fdeee0]">
            <div class="w-8 h-8 md:w-12 md:h-12 flex items-center justify-center shrink-0">
                <img src="/icons/{{ $feature['icon'] }}.svg" alt="" class="w-6 h-6 md:w-8 md:h-8 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
            </div>
            <div class="text-right flex-1">
                <p class="font-bold text-xs md:text-sm text-[#2b2620]">{{ $feature['title'] }}</p>
                <p class="text-[10px] md:text-xs text-[#6b6154] mt-1 hidden sm:block">{{ $feature['sub'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- Categories --}}
<section class="px-4 md:px-20 mt-10 md:mt-14">
    <div data-reveal-stagger="70" class="flex items-center gap-3 md:gap-5 overflow-x-auto pb-2 md:pb-0 md:justify-between scrollbar-hide">
        @forelse($categories as $cat)
        <a href="{{ route('products.index', ['category' => $cat->slug]) }}"
           data-reveal
           class="flex flex-col items-center gap-2 md:gap-3 shrink-0 md:flex-1 group transition-transform duration-300 hover:-translate-y-1">
            <div class="w-16 h-16 sm:w-20 sm:h-20 md:w-full md:aspect-square rounded-full md:rounded-[100px] overflow-hidden bg-[#f9f6f3] ring-2 ring-transparent ring-offset-2 ring-offset-[#f9f8f4] group-hover:ring-[#8a6300] transition-all duration-300">
                @if($cat->getFirstMediaUrl('image'))
                <img src="{{ $cat->getFirstMediaUrl('image') }}"
                     alt="{{ $cat->name }}"
                     loading="lazy"
                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 ease-out">
                @else
                <x-jewelry-placeholder :category="$cat->slug" />
                @endif
            </div>
            <span class="text-xs md:text-sm text-[#6b6154] font-medium group-hover:text-[#8a6300] transition-colors text-center">{{ $cat->name }}</span>
        </a>
        @empty
        <p class="text-[#a8a096] text-sm w-full text-center">دسته‌بندی‌ای ثبت نشده.</p>
        @endforelse
    </div>
</section>

{{-- Newest Products --}}
<x-product-carousel title="جدیدترین محصولات" :products="$newestProducts" :link="route('products.index', ['sort' => 'newest'])" />

{{-- Promo Banners --}}
<section class="px-4 md:px-20 mt-10 md:mt-14 flex flex-col sm:flex-row gap-4 md:gap-5">
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
