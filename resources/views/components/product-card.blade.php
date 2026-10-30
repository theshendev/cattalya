@props(['product', 'variant' => 'grid'])

@php
    $price = $product->calculatePrice();
    $image = $product->getFirstMediaUrl('images', 'thumb') ?: $product->getFirstMediaUrl('images');
    static $wishlistProductIds;
    $wishlistProductIds ??= auth()->check() ? auth()->user()->wishlists()->pluck('product_id')->all() : [];
    $isWished = in_array($product->id, $wishlistProductIds, true);
@endphp

<article data-reveal class="group relative flex gap-3 rounded-2xl border border-transparent bg-white p-3 transition-[transform,box-shadow,border-color] duration-300 ease-out hover:-translate-y-1 hover:border-[#f0e6c8] hover:shadow-xl hover:shadow-[#8a6300]/5 md:flex-col md:items-end md:gap-4 md:rounded-3xl md:p-4 {{ $variant === 'listing' ? 'min-h-44 flex-row items-stretch' : 'flex-col items-end' }}">
    <a href="{{ route('products.show', $product->slug) }}" class="absolute inset-0 z-10 rounded-2xl md:rounded-3xl" aria-label="مشاهده {{ $product->name }}"></a>

    <div class="relative aspect-square overflow-hidden rounded-xl bg-[#f9f6f3] md:w-full md:rounded-2xl {{ $variant === 'listing' ? 'w-[44%] shrink-0' : 'w-full' }}">
        @if($image)
        <img src="{{ $image }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110">
        @else
        <x-jewelry-placeholder :category="$product->category?->slug" />
        @endif

        @auth
        <form method="POST" action="{{ $isWished ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}" class="absolute right-2 top-2 z-20 md:right-3 md:top-3" data-no-loading>
            @csrf
            @if($isWished) @method('DELETE') @endif
            <button type="submit" aria-label="{{ $isWished ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها' }}" class="flex size-8 items-center justify-center rounded-full border border-[#f0e6c8] bg-white/95 backdrop-blur-sm transition-all hover:scale-110 hover:border-red-200 hover:bg-[#fbeaea] md:size-12 {{ $isWished ? 'border-red-200 bg-[#fbeaea]' : '' }}">
                <img src="{{ $isWished ? '/icons/heart-filled.svg' : '/icons/heart.svg' }}" alt="" class="size-4 md:size-6">
            </button>
        </form>
        @else
        <a href="{{ route('login') }}" aria-label="برای افزودن به علاقه‌مندی‌ها وارد شوید" class="absolute right-2 top-2 z-20 flex size-8 items-center justify-center rounded-full border border-[#f0e6c8] bg-white/95 backdrop-blur-sm transition-all hover:scale-110 hover:border-red-200 hover:bg-[#fbeaea] md:right-3 md:top-3 md:size-12">
            <img src="/icons/heart.svg" alt="" class="size-4 md:size-6">
        </a>
        @endauth
    </div>

    <div class="flex min-w-0 flex-1 flex-col justify-between gap-3 {{ $variant === 'listing' ? 'py-1' : 'w-full' }}">
        <p class="w-full {{ $variant === 'listing' ? 'line-clamp-3' : 'line-clamp-2' }} text-right text-sm font-medium leading-6 text-[#2b2620] md:text-base md:leading-7 transition-colors duration-200 group-hover:text-[#8a6300] md:line-clamp-2">{{ $product->name }}</p>

        <div class="flex w-full items-end gap-2 md:items-center md:gap-4">
            <div class="min-w-0 flex-1 text-right">
                @if(! $product->is_fixed_price && $product->weight_grams)
                <p class="mb-1 text-[10px] text-[#a8a096] line-through decoration-[#d6cbb0] md:text-xs">{{ number_format($price * 1.04) }} ت</p>
                @endif
                <p class="truncate text-base font-bold text-[#2b2620] md:text-xl">{{ number_format($price) }} تومان</p>
            </div>
            <span class="hidden size-8 shrink-0 items-center justify-center rounded-full border border-[#f0e6c8] bg-[#f9f6f3] transition-all duration-300 group-hover:scale-110 group-hover:border-[#8a6300] group-hover:bg-[#8a6300] md:flex md:size-12">
                <img src="/icons/arrow-left.svg" alt="" class="size-4 transition-all duration-300 group-hover:-translate-x-0.5 group-hover:brightness-0 group-hover:invert md:size-5">
            </span>
        </div>
    </div>
</article>
