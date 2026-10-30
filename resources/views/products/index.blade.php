@extends('layouts.app')

@section('title', 'محصولات')

@section('content')
@php
    $sortOptions = [
        'all' => ['label' => 'همه', 'mobileLabel' => 'همه محصولات', 'icon' => 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z'],
        'newest' => ['label' => 'جدیدترین‌ها', 'mobileLabel' => 'جدیدترین‌ها', 'icon' => 'm14 4 1.8 3.7 4 .6-2.9 2.8.7 4-3.6-1.9-3.6 1.9.7-4-2.9-2.8 4-.6L14 4ZM3 8h4M3 12h3m-3 4h5'],
        'price_desc' => ['label' => 'گران‌ترین‌ها', 'mobileLabel' => 'گران‌ترین‌ها', 'icon' => 'M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Zm4 6 6 6m0-4v4h-4'],
        'price_asc' => ['label' => 'ارزان‌ترین‌ها', 'mobileLabel' => 'ارزان‌ترین‌ها', 'icon' => 'M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Zm4 10 6-6m-4 0h4v4'],
    ];
    $sortQuery = request()->except(['page', 'sort']);
@endphp

<div class="px-4 py-5 md:px-20 md:py-8">
    <nav class="mb-6 hidden items-center gap-3 text-xs text-[#a8a096] md:flex" aria-label="مسیر صفحه">
        <a href="{{ route('home') }}" class="hover:text-[#8a6300]">صفحه اصلی</a>
        <span>/</span>
        <span>لیست محصولات</span>
    </nav>

    <div class="mb-5 grid grid-cols-2 gap-2 md:hidden">
        <button type="button" data-open-product-panel="sort" class="press flex h-12 items-center justify-center gap-3 rounded-2xl border border-[#ead69e] bg-white text-sm text-[#2b2620]">
            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.7" d="M4 7h16M7 12h10m-7 5h4"/></svg>
            مرتب‌سازی براساس
        </button>
        <button type="button" data-open-product-panel="filters" class="press relative flex h-12 items-center justify-center gap-3 rounded-2xl border border-[#ead69e] bg-white text-sm text-[#2b2620]">
            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 4h16l-6 8v7l-4-2v-5L4 4Z"/></svg>
            فیلترها
            @if($selectedCategories !== [] || $minPrice !== null || $maxPrice !== null)
            <span class="absolute left-3 top-3 size-2 rounded-full bg-[#c9a227]" aria-label="فیلتر فعال"></span>
            @endif
        </button>
    </div>

    <div class="flex items-start gap-5">
        <aside class="hidden w-80 shrink-0 md:block">
            <form method="GET" action="{{ route('products.index') }}" data-product-filters data-auto-submit-form class="flex flex-col gap-4">
                <input type="hidden" name="sort" value="{{ $sort }}">
                @include('products.partials.filters', ['context' => 'desktop'])
            </form>
        </aside>

        <div class="min-w-0 flex-1">
            <div class="mb-5 hidden items-center gap-2 rounded-2xl bg-white px-4 md:flex">
                @foreach($sortOptions as $value => $option)
                <a href="{{ route('products.index', array_merge($sortQuery, ['sort' => $value])) }}" class="relative px-4 py-4 text-sm transition-colors {{ $sort === $value ? 'font-bold text-[#8a6300]' : 'text-[#6b6154] hover:text-[#8a6300]' }}" @if($sort === $value) aria-current="page" @endif>
                    {{ $option['label'] }}
                    @if($sort === $value)<span class="absolute inset-x-4 bottom-0 h-0.5 rounded-full bg-[#c8960c]"></span>@endif
                </a>
                @endforeach
            </div>

            @if($products->isEmpty())
            <div class="rounded-3xl bg-white py-20 text-center text-[#a8a096]">
                <p class="text-lg font-medium">محصولی یافت نشد</p>
                <p class="mt-2 text-sm">فیلترها را تغییر دهید و دوباره تلاش کنید.</p>
            </div>
            @else
            <div data-reveal-stagger="60" class="grid grid-cols-1 gap-4 md:grid-cols-3 md:gap-5">
                @foreach($products as $product)
                <x-product-card :product="$product" variant="listing" />
                @endforeach
            </div>

            @if($products->hasPages())
            <div class="mt-10 flex justify-center">{{ $products->links() }}</div>
            @endif
            @endif
        </div>
    </div>
</div>

{{-- Mobile: sort bottom sheet --}}
<div id="product-sort-sheet" class="fixed inset-0 z-[70] hidden md:hidden" role="dialog" aria-modal="true" aria-labelledby="product-sort-title" aria-hidden="true">
    <button type="button" data-close-product-panel class="absolute inset-0 bg-black/40" aria-label="بستن"></button>
    <div data-product-panel-body class="absolute inset-x-0 bottom-0 translate-y-full rounded-t-[28px] bg-white px-4 pb-8 pt-4 transition-transform duration-300">
        <span class="mx-auto block h-1 w-9 rounded-full bg-[#aaa197]"></span>
        <div class="mb-5 mt-8 flex items-center justify-between px-2">
            <h2 id="product-sort-title" class="text-lg font-bold text-[#2b2620]">مرتب‌سازی براساس</h2>
            <button type="button" data-close-product-panel class="flex size-6 items-center justify-center rounded-full border-[1.5px] border-[#2b2620] text-[#2b2620]" aria-label="بستن">
                <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-width="3" d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </div>
        <div class="flex flex-col gap-1">
            @foreach($sortOptions as $value => $option)
            <a href="{{ route('products.index', array_merge($sortQuery, ['sort' => $value])) }}" class="flex h-14 items-center gap-3 rounded-3xl px-6 text-base {{ $sort === $value ? 'bg-[#fff3d6] font-medium text-[#8a6300]' : 'text-[#2b2620]' }}" @if($sort === $value) aria-current="page" @endif>
                <svg class="size-5 {{ $sort === $value ? 'text-[#c9a227]' : 'text-[#2b2620]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $option['icon'] }}"/></svg>
                {{ $option['mobileLabel'] }}
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- Mobile: full-screen filters page --}}
<div id="product-filter-sheet" class="fixed inset-0 z-[70] hidden md:hidden" role="dialog" aria-modal="true" aria-labelledby="product-filter-title" aria-hidden="true">
    <form method="GET" action="{{ route('products.index') }}" data-product-filters data-product-panel-body class="flex h-full translate-x-full flex-col bg-[#f9f8f4] transition-transform duration-300">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <header class="flex h-[104px] shrink-0 items-end bg-white px-4 pb-6">
            <button type="button" data-close-product-panel class="flex items-center gap-4 text-lg text-[#2b2620]" aria-label="بازگشت">
                <svg class="size-6 text-[#6b6154]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 12h16m-6-6 6 6-6 6"/></svg>
                <span id="product-filter-title">فیلترها</span>
            </button>
        </header>

        <div class="flex flex-1 flex-col gap-3 overflow-y-auto px-4 py-4">
            @include('products.partials.filters', ['context' => 'mobile'])
        </div>

        <div class="grid shrink-0 grid-cols-2 gap-2 bg-white px-4 py-5">
            <a href="{{ route('products.index', ['sort' => $sort]) }}" class="flex h-12 items-center justify-center rounded-2xl border border-[#d64545] text-base text-[#c43c3c]">حذف فیلترها</a>
            <button type="submit" class="flex h-12 items-center justify-center rounded-2xl bg-[#111] text-base text-white">اعمال فیلترها</button>
        </div>
    </form>
</div>

<script>
    (() => {
        const toLatinDigits = (value) => value
            .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
            .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));
        const parsePrice = (value) => {
            const digits = toLatinDigits(value).replace(/\D/g, '');
            return digits === '' ? null : Number(digits);
        };
        const formatPrice = (value) => value === null ? '' : value.toLocaleString('en-US');

        document.querySelectorAll('[data-price-range]').forEach((range) => {
            const inputs = { from: range.querySelector('[data-price-input="from"]'), to: range.querySelector('[data-price-input="to"]') };
            const sliders = { from: range.querySelector('[data-price-slider="from"]'), to: range.querySelector('[data-price-slider="to"]') };
            const track = range.querySelector('[data-price-track]');
            const max = Number(sliders.to.max);
            const step = Number(range.dataset.step);

            const paintTrack = () => {
                const from = Number(sliders.from.value) / max * 100;
                const to = Number(sliders.to.value) / max * 100;
                track.style.left = `${from}%`;
                track.style.width = `${Math.max(0, to - from)}%`;
            };

            Object.entries(sliders).forEach(([edge, slider]) => {
                slider.addEventListener('input', () => {
                    if (Number(sliders.from.value) > Number(sliders.to.value) - step) {
                        slider.value = edge === 'from' ? Number(sliders.to.value) - step : Number(sliders.from.value) + step;
                    }
                    const value = Number(slider.value);
                    const isOpenEnded = (edge === 'from' && value === 0) || (edge === 'to' && value === max);
                    inputs[edge].value = isOpenEnded ? '' : formatPrice(value);
                    paintTrack();
                });
            });

            Object.entries(inputs).forEach(([edge, input]) => {
                input.addEventListener('input', () => {
                    const value = parsePrice(input.value);
                    input.value = formatPrice(value);
                    sliders[edge].value = value ?? (edge === 'from' ? 0 : max);
                    paintTrack();
                });
            });

            paintTrack();
        });

        document.querySelectorAll('[data-auto-submit-form]').forEach((form) => {
            let submitTimer;
            form.addEventListener('change', () => {
                window.clearTimeout(submitTimer);
                submitTimer = window.setTimeout(() => form.requestSubmit(), 250);
            });
        });

        const panels = { sort: document.getElementById('product-sort-sheet'), filters: document.getElementById('product-filter-sheet') };
        const hiddenClass = { sort: 'translate-y-full', filters: 'translate-x-full' };
        let openPanel = null;

        const showPanel = (name) => {
            const panel = panels[name];
            openPanel = name;
            panel.classList.remove('hidden');
            panel.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(() => requestAnimationFrame(() => panel.querySelector('[data-product-panel-body]').classList.remove(hiddenClass[name])));
        };

        const hidePanel = () => {
            if (! openPanel) {
                return;
            }
            const name = openPanel;
            const panel = panels[name];
            openPanel = null;
            panel.querySelector('[data-product-panel-body]').classList.add(hiddenClass[name]);
            window.setTimeout(() => {
                panel.classList.add('hidden');
                panel.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }, 300);
        };

        document.querySelectorAll('[data-open-product-panel]').forEach((button) => button.addEventListener('click', () => showPanel(button.dataset.openProductPanel)));
        document.querySelectorAll('[data-close-product-panel]').forEach((button) => button.addEventListener('click', hidePanel));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                hidePanel();
            }
        });
    })();
</script>
@endsection
