@props(['title', 'products', 'link' => null, 'inset' => true, 'perView' => 4])

@php
    $gutterClass = $inset ? 'px-4 md:px-20' : '';
    $slideWidthClass = $perView === 3 ? 'md:w-[calc((100%-2.5rem)/3)]' : 'md:w-[calc((100%-3.75rem)/4)]';
    $prevPositionClass = $inset ? 'right-[60px]' : 'right-0 translate-x-1/2';
    $nextPositionClass = $inset ? 'left-[60px]' : 'left-0 -translate-x-1/2';
@endphp

<section {{ $attributes->merge(['class' => $inset ? 'mt-20' : '']) }}>
    <div class="mb-5 flex items-center justify-between md:mb-6 {{ $gutterClass }}">
        <h2 data-reveal class="text-xl font-bold text-[#2b2620]">{{ $title }}</h2>
        @if($link)
        <a href="{{ $link }}" data-reveal class="group flex items-center gap-2 text-xs font-medium text-[#b08000] hover:underline">
            مشاهده همه
            <img src="/icons/arrow-left.svg" alt="" class="h-4 w-4 transition-transform duration-300 group-hover:-translate-x-1">
        </a>
        @endif
    </div>

    <div class="relative {{ $gutterClass }}" data-carousel>
        <div data-carousel-track class="scrollbar-hide -mx-1 flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth px-1 pb-2 md:gap-5">
            @forelse($products as $product)
            <div class="w-[68%] shrink-0 snap-start sm:w-[44%] {{ $slideWidthClass }}">
                <x-product-card :product="$product" />
            </div>
            @empty
            <p class="w-full py-10 text-center text-[#a8a096]">محصولی یافت نشد.</p>
            @endforelse
        </div>

        @if($products->count() > 1)
        <button type="button" data-carousel-prev aria-label="قبلی"
                class="absolute {{ $prevPositionClass }} top-1/2 z-10 hidden h-24 w-10 -translate-y-1/2 items-center justify-center rounded-2xl border border-[#e3d2a0] bg-white text-[#8a6300] transition-opacity hover:bg-[#fdf1c9] disabled:opacity-0 md:flex">
            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 5 7 7-7 7"/></svg>
        </button>
        <button type="button" data-carousel-next aria-label="بعدی"
                class="absolute {{ $nextPositionClass }} top-1/2 z-10 hidden h-24 w-10 -translate-y-1/2 items-center justify-center rounded-2xl border border-[#e3d2a0] bg-white text-[#8a6300] transition-opacity hover:bg-[#fdf1c9] disabled:opacity-0 md:flex">
            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m15 5-7 7 7 7"/></svg>
        </button>
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
