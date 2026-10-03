@extends('layouts.app')

@section('title', $product->name)

@section('content')
@php
    $images = $product->getMedia('images');
    $isInStock = $product->stock > 0;
    $isWished = auth()->check() && auth()->user()->wishlists()->where('product_id', $product->id)->exists();
    $specs = array_filter([
        'وزن محصول' => $product->is_fixed_price ? null : number_format((float) $product->weight_grams, 2).' گرم',
        'اجرت' => $product->is_fixed_price ? null : '%'.(float) $product->wage_percent,
        'عیار طلا' => $product->karat ? $product->karat.' عیار' : null,
        'رنگ طلا' => $product->gold_color,
        'درخشندگی' => $product->shine,
        'ابعاد تقریبی' => $product->dimensions,
    ]);
@endphp
<div class="px-4 py-6 md:px-20 md:py-8">

    {{-- Breadcrumb --}}
    <nav class="mb-4 flex flex-wrap items-center gap-2 text-xs text-[#a8a096] animate-fade-in md:mb-5 md:text-sm" aria-label="breadcrumb">
        <a href="{{ route('home') }}" class="transition-colors hover:text-[#8a6300]">صفحه اصلی</a>
        <span>/</span>
        <a href="{{ route('products.index') }}" class="transition-colors hover:text-[#8a6300]">لیست محصولات</a>
        <span>/</span>
        <span class="max-w-[180px] truncate md:max-w-none">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-[1fr_325px]">

        {{-- Main column --}}
        <div class="flex min-w-0 flex-col gap-5 md:gap-8">

            <section class="rounded-3xl bg-white p-4 md:p-5">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-[auto_1fr] md:gap-8">

                    {{-- Gallery --}}
                    <div class="flex gap-3 md:gap-4">
                        <div class="flex flex-col items-center gap-3 pt-1">
                            @auth
                            <form method="POST" action="{{ $isWished ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}" data-no-loading>
                                @csrf
                                @if($isWished) @method('DELETE') @endif
                                <button type="submit" aria-label="{{ $isWished ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها' }}" class="flex size-9 items-center justify-center rounded-full transition-transform hover:scale-110">
                                    <img src="{{ $isWished ? '/icons/heart-filled.svg' : '/icons/heart.svg' }}" alt="" class="size-5">
                                </button>
                            </form>
                            @else
                            <a href="{{ route('login') }}" aria-label="برای افزودن به علاقه‌مندی‌ها وارد شوید" class="flex size-9 items-center justify-center rounded-full transition-transform hover:scale-110">
                                <img src="/icons/heart.svg" alt="" class="size-5">
                            </a>
                            @endauth
                        </div>

                        <div class="flex w-full flex-col gap-4 md:w-[380px]">
                            <div class="group aspect-square overflow-hidden rounded-2xl bg-[#f9f6f3] animate-scale-in">
                                @if($images->count())
                                <img id="main-img"
                                     src="{{ $images->first()->getUrl() }}"
                                     alt="{{ $product->name }}"
                                     class="h-full w-full object-cover transition-[transform,opacity] duration-500 ease-out group-hover:scale-105">
                                @else
                                <x-jewelry-placeholder :category="$product->category?->slug" />
                                @endif
                            </div>

                            @if($images->count() > 1)
                            <div data-reveal-stagger="70" class="flex gap-3">
                                @foreach($images as $media)
                                <button type="button" data-reveal
                                        onclick="selectImage(this, '{{ $media->getUrl() }}')"
                                        class="thumb size-[62px] overflow-hidden rounded-xl border bg-[#f9f6f3] transition-all duration-300 hover:scale-105 hover:border-[#8a6300] {{ $loop->first ? 'border-[#8a6300]' : 'border-[#f0e6c8]' }}">
                                    <img src="{{ $media->getUrl('thumb') ?: $media->getUrl() }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                </button>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div data-reveal-stagger="70" class="flex flex-col gap-4 text-right">
                        <h1 data-reveal class="text-xl font-bold leading-9 text-[#2b2620] md:text-2xl">{{ $product->name }}</h1>

                        @if($product->description)
                        <p data-reveal class="text-sm leading-7 text-[#6b6154] md:text-base">{{ $product->description }}</p>
                        @endif

                        @if(!$product->is_fixed_price && $goldPrice)
                        <div data-reveal class="rounded-2xl bg-[#fdeee0] p-3 text-sm md:p-4">
                            <p class="font-medium text-[#a34d06]">قیمت‌گذاری بر اساس قیمت لحظه‌ای طلا</p>
                            <p class="mt-1 text-[#6b6154]">
                                قیمت طلای ۱۸ عیار:
                                <strong class="text-[#a34d06]">{{ number_format($goldPrice->price_per_gram) }} تومان/گرم</strong>
                            </p>
                        </div>
                        @endif

                        <div data-reveal class="flex items-center gap-2 border-t border-[#f0e6c8] pt-4 text-xs text-[#6b6154] md:text-sm">
                            <img src="/icons/gift.svg" alt="" class="size-5">
                            <span>امکان ارسال به عنوان هدیه، همراه با بسته‌بندی مخصوص.</span>
                        </div>
                    </div>
                </div>

                {{-- Trust badges --}}
                <div class="mt-5 flex flex-wrap items-center justify-start gap-x-8 gap-y-3 border-t border-[#f0e6c8] pt-5 md:mt-6">
                    @foreach([
                        ['icon' => 'shield', 'text' => 'ضمانت اصالت کالا'],
                        ['icon' => 'truck', 'text' => 'ارسال رایگان'],
                        ['icon' => 'gift', 'text' => 'بسته‌بندی هدیه‌ای'],
                    ] as $badge)
                    <div class="flex items-center gap-2 text-xs text-[#6b6154] md:text-sm">
                        <img src="/icons/{{ $badge['icon'] }}.svg" alt="" class="size-6">
                        <span>{{ $badge['text'] }}</span>
                    </div>
                    @endforeach
                </div>
            </section>

            {{-- Details --}}
            @if($specs)
            <section data-reveal class="grid grid-cols-1 gap-5 rounded-3xl bg-white p-5 md:grid-cols-[1fr_2fr] md:gap-8 md:p-8">
                <div class="text-right">
                    <h2 class="text-lg font-bold text-[#2b2620] md:text-xl">جزئیات محصول</h2>
                    <p class="mt-3 text-sm leading-7 text-[#6b6154]">در این قسمت جزئیات فیزیکی و ظاهری محصول قابل مشاهده می‌باشد.</p>
                </div>
                <dl class="flex flex-col">
                    @foreach($specs as $label => $value)
                    <div class="flex items-center justify-between gap-4 border-b border-[#f0e6c8] py-3 last:border-b-0">
                        <dt class="text-sm text-[#a8a096]">{{ $label }}:</dt>
                        <dd class="text-sm font-medium text-[#2b2620]">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </section>
            @endif

            {{-- Similar products --}}
            @if($related->count())
            <section class="mt-2">
                <h2 data-reveal class="mb-5 text-right text-lg font-bold text-[#2b2620] md:text-xl">محصولات مشابه</h2>
                <div data-reveal-stagger="60" class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-5">
                    @foreach($related->take(3) as $relProduct)
                    <x-product-card :product="$relProduct" />
                    @endforeach
                </div>
            </section>
            @endif
        </div>

        {{-- Invoice sidebar --}}
        <aside data-reveal class="rounded-3xl border border-[#f0e6c8] bg-white p-5 lg:sticky lg:top-6">
            <h2 class="text-right text-sm text-[#a8a096]">فاکتور خرید</h2>

            <dl class="mt-4 flex flex-col text-sm">
                <div class="flex items-center justify-between gap-3 py-3">
                    <dt class="text-[#a8a096]">وضعیت محصول:</dt>
                    <dd>
                        @if($isInStock)
                        <span class="rounded-lg bg-[#e6f6ec] px-3 py-1 text-xs font-medium text-[#1a7f3c]">آماده ارسال</span>
                        @else
                        <span class="rounded-lg bg-[#fbeaea] px-3 py-1 text-xs font-medium text-[#c0392b]">ناموجود</span>
                        @endif
                    </dd>
                </div>
                @if(!$product->is_fixed_price)
                <div class="flex items-center justify-between gap-3 border-t border-[#f0e6c8] py-4">
                    <dt class="text-[#a8a096]">وزن:</dt>
                    <dd class="text-[#2b2620]">{{ number_format((float) $product->weight_grams, 2) }} گرم</dd>
                </div>
                <div class="flex items-center justify-between gap-3 border-t border-[#f0e6c8] py-4">
                    <dt class="text-[#a8a096]">اجرت:</dt>
                    <dd class="text-[#2b2620]">%{{ (float) $product->wage_percent }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3 border-t border-[#f0e6c8] py-4">
                    <dt class="text-[#a8a096]">مالیات:</dt>
                    <dd class="text-[#2b2620]">%{{ (float) $product->tax_percent }}</dd>
                </div>
                @endif
                <div class="flex items-center justify-between gap-3 border-t border-[#f0e6c8] py-4">
                    <dt class="text-[#a8a096]">عیار طلا:</dt>
                    <dd class="text-[#2b2620]">{{ $product->karat }} عیار</dd>
                </div>
                <div class="flex items-center justify-between gap-3 border-t border-[#f0e6c8] pt-4">
                    <dt class="text-[#a8a096]">قیمت نهایی:</dt>
                    <dd class="text-lg font-bold text-[#2b2620]">
                        {{ number_format($product->calculatePrice()) }}
                        <span class="text-sm font-normal">تومان</span>
                    </dd>
                </div>
            </dl>

            <div class="mt-5">
                @if($isInStock)
                <form method="POST" action="{{ route('cart.add', $product) }}">
                    @csrf
                    <button type="submit" class="press flex h-12 w-full items-center justify-center gap-2 rounded-2xl bg-[#111] font-medium text-white hover:bg-[#2b2620]">
                        <img src="/icons/cart.svg" alt="" class="size-5 invert">
                        افزودن به سبد خرید
                    </button>
                </form>
                @else
                <div class="flex h-12 w-full items-center justify-center rounded-2xl bg-[#f0e6c8] font-medium text-[#a8a096]">ناموجود</div>
                @endif
            </div>

            <div class="mt-5 border-t border-[#f0e6c8] pt-4 text-center">
                <p class="text-sm text-[#a8a096]">با ما در تماس باشید.</p>
                <div class="mt-3 flex items-center justify-center gap-3">
                    @foreach(['sms', 'whatsapp', 'instagram'] as $icon)
                    <span class="flex size-10 items-center justify-center rounded-full border border-[#e3d2a0] bg-white">
                        <img src="/icons/{{ $icon }}.svg" alt="" class="size-5">
                    </span>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</div>

<script>
function selectImage(button, url) {
    const img = document.getElementById('main-img');
    if (!img) return;
    document.querySelectorAll('.thumb').forEach((t) => {
        t.classList.remove('border-[#8a6300]');
        t.classList.add('border-[#f0e6c8]');
    });
    button.classList.remove('border-[#f0e6c8]');
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
