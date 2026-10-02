@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="px-4 md:px-20 py-6 md:py-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs md:text-sm text-[#a8a096] mb-6 md:mb-8 justify-end flex-wrap animate-fade-in">
        <span class="text-[#2b2620] font-medium truncate max-w-[120px] md:max-w-none">{{ $product->name }}</span>
        <span>/</span>
        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}"
           class="hover:text-[#8a6300] transition-colors">{{ $product->category->name }}</a>
        <span>/</span>
        <a href="{{ route('products.index') }}" class="hover:text-[#8a6300] transition-colors">محصولات</a>
        <span>/</span>
        <a href="{{ route('home') }}" class="hover:text-[#8a6300] transition-colors">خانه</a>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-12">

        {{-- Images --}}
        <div class="flex gap-3 md:gap-4">
            {{-- Thumbnails --}}
            @php $images = $product->getMedia('images'); @endphp
            @if($images->count() > 1)
            <div data-reveal-stagger="70" class="flex flex-col gap-2 md:gap-3 w-14 md:w-20">
                @foreach($images as $media)
                <button type="button" data-reveal
                        onclick="selectImage(this, '{{ $media->getUrl() }}')"
                        class="thumb w-14 h-14 md:w-20 md:h-20 rounded-xl md:rounded-2xl overflow-hidden border-2 border-transparent hover:border-[#8a6300] hover:scale-105 transition-all duration-300 {{ $loop->first ? 'border-[#8a6300]' : '' }}">
                    <img src="{{ $media->getUrl('thumb') ?: $media->getUrl() }}"
                         alt="" loading="lazy" class="w-full h-full object-cover">
                </button>
                @endforeach
            </div>
            @endif

            {{-- Main Image --}}
            <div class="flex-1 aspect-square rounded-2xl md:rounded-3xl overflow-hidden bg-[#f9f6f3] group animate-scale-in">
                @if($images->count())
                <img id="main-img"
                     src="{{ $images->first()->getUrl() }}"
                     alt="{{ $product->name }}"
                     class="w-full h-full object-cover transition-[transform,opacity] duration-500 ease-out group-hover:scale-105">
                @else
                <div class="w-full h-full flex items-center justify-center text-[#a8a096]">
                    <svg class="w-16 h-16 md:w-24 md:h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                @endif
            </div>
        </div>

        {{-- Product Info --}}
        <div data-reveal-stagger="70" class="flex flex-col gap-4 md:gap-6 text-right">

            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}"
               data-reveal
               class="press self-end text-xs bg-[#fdeee0] text-[#a34d06] px-3 py-1 rounded-full font-medium hover:bg-[#f0e6c8]">
                {{ $product->category->name }}
            </a>

            <h1 data-reveal class="text-xl md:text-2xl font-bold text-[#2b2620] leading-8 md:leading-9">{{ $product->name }}</h1>

            @if(!$product->is_fixed_price && $goldPrice)
            <div data-reveal class="bg-[#fdeee0] rounded-2xl p-3 md:p-4 text-sm">
                <p class="text-[#a34d06] font-medium">قیمت‌گذاری بر اساس قیمت لحظه‌ای طلا</p>
                <p class="text-[#6b6154] mt-1">
                    قیمت طلای ۱۸ عیار:
                    <strong class="text-[#a34d06]">{{ number_format($goldPrice->price_per_gram) }} تومان/گرم</strong>
                </p>
            </div>
            @endif

            {{-- Price breakdown --}}
            <div data-reveal class="bg-[#f9f6f3] rounded-2xl p-4 md:p-5 flex flex-col gap-3">
                @if(!$product->is_fixed_price)
                <div class="flex items-center justify-between text-xs md:text-sm text-[#6b6154]">
                    <span dir="ltr" class="text-left">{{ number_format($product->weight_grams, 3) }}g × {{ number_format($goldPrice?->price_per_gram ?? 0) }}</span>
                    <span>وزن × قیمت طلا</span>
                </div>
                <div class="flex items-center justify-between text-xs md:text-sm text-[#6b6154]">
                    <span>{{ $product->wage_percent }}%</span>
                    <span>اجرت ساخت</span>
                </div>
                <div class="flex items-center justify-between text-xs md:text-sm text-[#6b6154]">
                    <span>{{ $product->tax_percent }}%</span>
                    <span>مالیات</span>
                </div>
                <div class="h-px bg-[#e3d2a0]"></div>
                @endif
                <div class="flex items-center justify-between">
                    <span class="text-xl md:text-2xl font-bold text-[#2b2620]">
                        {{ number_format($product->calculatePrice()) }}
                        <span class="text-sm md:text-base font-normal text-[#6b6154]">تومان</span>
                    </span>
                    <span class="text-xs md:text-sm text-[#6b6154] font-medium">قیمت نهایی</span>
                </div>
            </div>

            {{-- Stock --}}
            <div data-reveal class="flex items-center justify-end gap-2">
                @if($product->stock > 0)
                <span class="text-sm text-green-600 font-medium">موجود در انبار</span>
                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse-ring"></div>
                @else
                <span class="text-sm text-red-500 font-medium">ناموجود</span>
                <div class="w-2 h-2 rounded-full bg-red-400"></div>
                @endif
            </div>

            {{-- Add to Cart --}}
            <div data-reveal class="flex items-center gap-3">
                @if($product->stock > 0)
                <form method="POST" action="{{ route('cart.add', $product) }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="press w-full bg-[#8a6300] text-white font-bold py-3 md:py-4 rounded-2xl hover:bg-[#6b4d00] hover:shadow-xl hover:shadow-[#8a6300]/25">
                        افزودن به سبد خرید
                    </button>
                </form>
                @else
                <div class="flex-1 bg-[#f0e6c8] text-[#a8a096] font-bold py-3 md:py-4 rounded-2xl text-center">ناموجود</div>
                @endif
                @php $isWished = auth()->check() && auth()->user()->wishlists()->where('product_id', $product->id)->exists(); @endphp
                @auth
                <form method="POST" action="{{ $isWished ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}" data-no-loading>
                    @csrf
                    @if($isWished) @method('DELETE') @endif
                    <button type="submit" aria-label="{{ $isWished ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها' }}" class="flex size-12 shrink-0 items-center justify-center rounded-2xl border transition-all hover:scale-105 md:size-14 {{ $isWished ? 'border-red-200 bg-[#fbeaea]' : 'border-[#f0e6c8] bg-[#f9f6f3] hover:border-red-200 hover:bg-[#fbeaea]' }}">
                        <img src="{{ $isWished ? '/icons/heart-filled.svg' : '/icons/heart.svg' }}" alt="" class="size-5 md:size-6">
                    </button>
                </form>
                @else
                <a href="{{ route('login') }}" aria-label="برای افزودن به علاقه‌مندی‌ها وارد شوید" class="flex size-12 shrink-0 items-center justify-center rounded-2xl border border-[#f0e6c8] bg-[#f9f6f3] transition-all hover:scale-105 hover:border-red-200 hover:bg-[#fbeaea] md:size-14">
                    <img src="/icons/heart.svg" alt="" class="size-5 md:size-6">
                </a>
                @endauth
            </div>

            {{-- Guarantees --}}
            <div data-reveal data-reveal-stagger="50" class="grid grid-cols-2 gap-2 md:gap-3">
                @foreach([
                    ['text' => 'ضمانت اصالت کالا'],
                    ['text' => 'ارسال سریع و مطمئن'],
                    ['text' => 'امکان مرجوع کردن'],
                    ['text' => 'بسته‌بندی هدیه‌ای'],
                ] as $g)
                <div class="flex items-center justify-end gap-2 bg-[#f9f6f3] rounded-xl px-3 py-2 transition-colors duration-300 hover:bg-[#fdeee0]">
                    <span class="text-xs text-[#6b6154]">{{ $g['text'] }}</span>
                    <svg class="w-4 h-4 text-[#8a6300] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                @endforeach
            </div>

            {{-- Description --}}
            @if($product->description)
            <div data-reveal class="border-t border-[#f0e6c8] pt-4 md:pt-5">
                <h3 class="font-bold text-[#2b2620] mb-3">توضیحات محصول</h3>
                <p class="text-sm text-[#6b6154] leading-7">{{ $product->description }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Related Products --}}
    @if($related->count())
    <section class="mt-12 md:mt-16">
        <h2 data-reveal class="text-lg md:text-xl font-bold text-[#2b2620] text-right mb-5 md:mb-6">محصولات مرتبط</h2>
        <div data-reveal-stagger="60" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 md:gap-5">
            @foreach($related as $relProduct)
            <x-product-card :product="$relProduct" />
            @endforeach
        </div>
    </section>
    @endif

</div>

<script>
function selectImage(button, url) {
    const img = document.getElementById('main-img');
    if (!img) return;
    document.querySelectorAll('.thumb').forEach((t) => t.classList.remove('border-[#8a6300]'));
    button.classList.add('border-[#8a6300]');

    // Cross-fade to the new image once it has loaded.
    const next = new Image();
    next.onload = () => {
        img.style.opacity = '0';
        setTimeout(() => {
            img.src = url;
            img.style.opacity = '1';
        }, 180);
    };
    next.src = url;
}
</script>
@endsection
