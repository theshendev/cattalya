@props(['title', 'products', 'link' => null])

<section {{ $attributes->merge(['class' => 'mt-10 md:mt-14']) }}>
    <div class="mb-5 flex items-center justify-between px-4 md:mb-6 md:px-20">
        <h2 data-reveal class="text-lg font-bold text-[#2b2620] md:text-xl">{{ $title }}</h2>
        @if($link)
        <a href="{{ $link }}" data-reveal class="group flex items-center gap-2 text-xs font-medium text-[#b08000] hover:underline">
            <img src="/icons/arrow-left.svg" alt="" class="h-4 w-4 transition-transform duration-300 group-hover:-translate-x-1">
            مشاهده همه
        </a>
        @endif
    </div>

    <div class="relative px-4 md:px-20" data-carousel>
        <div data-carousel-track class="scrollbar-hide -mx-1 flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth px-1 pb-2 md:gap-5">
            @forelse($products as $product)
            <div class="w-[68%] shrink-0 snap-start sm:w-[44%] md:w-[calc((100%-3.75rem)/4)]">
                <x-product-card :product="$product" />
            </div>
            @empty
            <p class="w-full py-10 text-center text-[#a8a096]">محصولی یافت نشد.</p>
            @endforelse
        </div>

        @if($products->count() > 1)
        <button type="button" data-carousel-prev aria-label="قبلی"
                class="absolute right-2 top-1/3 z-10 hidden size-10 items-center justify-center rounded-xl border border-[#f0e6c8] bg-white text-[#8a6300] shadow-md transition-opacity hover:bg-[#fdf1c9] disabled:opacity-0 md:right-14 md:flex">›</button>
        <button type="button" data-carousel-next aria-label="بعدی"
                class="absolute left-2 top-1/3 z-10 hidden size-10 items-center justify-center rounded-xl border border-[#f0e6c8] bg-white text-[#8a6300] shadow-md transition-opacity hover:bg-[#fdf1c9] disabled:opacity-0 md:left-14 md:flex">‹</button>
        @endif
    </div>
</section>

@once
<script>
document.querySelectorAll('[data-carousel]').forEach((root) => {
    const track = root.querySelector('[data-carousel-track]');
    const prev = root.querySelector('[data-carousel-prev]');
    const next = root.querySelector('[data-carousel-next]');
    if (!prev || !next) return;

    const step = () => track.clientWidth * 0.8;
    // In RTL, scrollLeft runs from 0 (start, on the right) to a negative maximum.
    const update = () => {
        const max = track.scrollWidth - track.clientWidth;
        const position = Math.abs(track.scrollLeft);
        prev.disabled = position < 4;
        next.disabled = position > max - 4;
    };
    prev.addEventListener('click', () => track.scrollBy({ left: step() }));
    next.addEventListener('click', () => track.scrollBy({ left: -step() }));
    track.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    update();
});
</script>
@endonce
