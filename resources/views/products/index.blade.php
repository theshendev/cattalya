@extends('layouts.app')

@section('title', 'محصولات')

@section('content')
@php
    $sortOptions = [
        'all' => 'همه محصولات',
        'newest' => 'جدیدترین‌ها',
        'price_asc' => 'ارزان‌ترین‌ها',
        'price_desc' => 'گران‌ترین‌ها',
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
        <button type="button" data-open-product-sheet="filters" class="press flex h-12 items-center justify-center gap-3 rounded-2xl border border-[#dfc47d] bg-white text-sm text-[#2b2620]">
            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 4h18l-7 8v6l-4 2v-8L3 4Z"/></svg>
            فیلترها
        </button>
        <button type="button" data-open-product-sheet="sort" class="press flex h-12 items-center justify-center gap-3 rounded-2xl border border-[#dfc47d] bg-white text-sm text-[#2b2620]">
            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.7" d="M4 7h16M7 12h10m-7 5h4"/></svg>
            مرتب‌سازی براساس
        </button>
    </div>

    <div class="flex items-start gap-6 md:gap-8">
        <aside class="hidden w-72 shrink-0 flex-col gap-4 md:flex">
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-col gap-4">
                <input type="hidden" name="sort" value="{{ $sort }}">

                <section class="rounded-3xl bg-white p-6">
                    <h2 class="mb-5 text-right text-sm text-[#a8a096]">دسته‌بندی‌ها</h2>
                    <div class="flex flex-col gap-3">
                        <label class="flex cursor-pointer items-center justify-between text-sm text-[#6b6154]">
                            <span>همه</span>
                            <input type="radio" name="category" value="" class="accent-[#c8960c]" {{ request('category') ? '' : 'checked' }}>
                        </label>
                        @foreach($categories as $category)
                        <label class="flex cursor-pointer items-center justify-between text-sm text-[#6b6154]">
                            <span>{{ $category->name }}</span>
                            <input type="radio" name="category" value="{{ $category->slug }}" class="accent-[#c8960c]" {{ request('category') === $category->slug ? 'checked' : '' }}>
                        </label>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-3xl bg-white p-6">
                    <h2 class="mb-5 text-right text-sm text-[#a8a096]">محدوده قیمت</h2>
                    <div class="flex flex-col gap-3">
                        <label class="flex items-center rounded-2xl border border-[#ead69e] px-4 py-3 text-xs text-[#a8a096]">
                            <span>از قیمت</span>
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="۰" class="min-w-0 flex-1 border-0 bg-transparent text-left text-sm text-[#2b2620] outline-none" min="0">
                            <span>تومان</span>
                        </label>
                        <label class="flex items-center rounded-2xl border border-[#ead69e] px-4 py-3 text-xs text-[#a8a096]">
                            <span>تا قیمت</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="۱۰۰,۰۰۰,۰۰۰" class="min-w-0 flex-1 border-0 bg-transparent text-left text-sm text-[#2b2620] outline-none" min="0">
                            <span>تومان</span>
                        </label>
                    </div>
                </section>

                <button type="submit" class="press rounded-2xl bg-[#111] py-3 text-sm font-medium text-white">اعمال فیلترها</button>
            </form>
        </aside>

        <div class="min-w-0 flex-1">
            <div class="mb-5 hidden items-center justify-between rounded-3xl bg-white px-6 md:flex">
                @foreach($sortOptions as $value => $label)
                <a href="{{ route('products.index', array_merge($sortQuery, ['sort' => $value])) }}" class="relative px-5 py-5 text-sm transition-colors {{ $sort === $value ? 'font-bold text-[#8a6300]' : 'text-[#6b6154] hover:text-[#8a6300]' }}">
                    {{ $label }}
                    @if($sort === $value)<span class="absolute inset-x-5 bottom-0 h-0.5 rounded-full bg-[#c8960c]"></span>@endif
                </a>
                @endforeach
            </div>

            <div class="mb-4 flex items-center justify-between md:hidden">
                <p class="text-xs text-[#a8a096]">{{ $products->total() }} محصول</p>
                <p class="text-sm font-medium text-[#8a6300]">{{ $sortOptions[$sort] ?? $sortOptions['all'] }}</p>
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

<div id="product-sheet" class="fixed inset-0 z-[70] hidden md:hidden" aria-hidden="true">
    <button type="button" data-close-product-sheet class="absolute inset-0 bg-black/45" aria-label="بستن فیلترها"></button>
    <div id="product-sheet-panel" class="absolute inset-x-0 bottom-0 max-h-[88vh] translate-y-full overflow-y-auto rounded-t-[28px] bg-[#f9f8f4] transition-transform duration-300">
        <div class="sticky top-0 z-10 bg-white px-4 pb-4 pt-3">
            <span class="mx-auto mb-4 block h-1 w-10 rounded-full bg-[#aaa197]"></span>
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold">فیلتر و مرتب‌سازی</h2>
                <button type="button" data-close-product-sheet class="flex size-9 items-center justify-center rounded-full border border-[#e3d2a0]" aria-label="بستن">×</button>
            </div>
        </div>

        <section data-product-sheet-section="sort" class="bg-white px-4 pb-5">
            <h3 class="mb-3 text-sm font-medium text-[#6b6154]">مرتب‌سازی براساس</h3>
            <div class="flex flex-col gap-2">
                @foreach($sortOptions as $value => $label)
                <a href="{{ route('products.index', array_merge($sortQuery, ['sort' => $value])) }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm {{ $sort === $value ? 'bg-[#fff3cf] font-bold text-[#8a6300]' : 'text-[#2b2620]' }}">
                    <span>{{ $label }}</span>
                    <span aria-hidden="true">{{ $sort === $value ? '●' : '○' }}</span>
                </a>
                @endforeach
            </div>
        </section>

        <form method="GET" action="{{ route('products.index') }}" data-product-sheet-section="filters" class="flex flex-col gap-3 px-4 py-4">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <section class="rounded-3xl bg-white p-5">
                <h3 class="mb-4 text-sm text-[#6b6154]">دسته‌بندی</h3>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 text-sm"><input type="radio" name="category" value="" class="accent-[#c8960c]" {{ request('category') ? '' : 'checked' }}>همه</label>
                    @foreach($categories as $category)
                    <label class="flex items-center gap-2 text-sm"><input type="radio" name="category" value="{{ $category->slug }}" class="accent-[#c8960c]" {{ request('category') === $category->slug ? 'checked' : '' }}>{{ $category->name }}</label>
                    @endforeach
                </div>
            </section>
            <section class="rounded-3xl bg-white p-5">
                <h3 class="mb-4 text-sm text-[#6b6154]">محدوده قیمت</h3>
                <div class="flex flex-col gap-3">
                    <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="از قیمت" class="rounded-2xl border border-[#ead69e] bg-white px-4 py-3 text-sm outline-none" min="0">
                    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="تا قیمت" class="rounded-2xl border border-[#ead69e] bg-white px-4 py-3 text-sm outline-none" min="0">
                </div>
            </section>
            <div class="sticky bottom-0 grid grid-cols-2 gap-2 bg-white py-4">
                <a href="{{ route('products.index') }}" class="flex items-center justify-center rounded-2xl border border-red-400 py-3 text-sm text-red-500">حذف فیلترها</a>
                <button type="submit" class="rounded-2xl bg-[#111] py-3 text-sm text-white">اعمال فیلترها</button>
            </div>
        </form>
    </div>
</div>

<script>
    (() => {
        const sheet = document.getElementById('product-sheet');
        const panel = document.getElementById('product-sheet-panel');
        const openButtons = document.querySelectorAll('[data-open-product-sheet]');
        const closeButtons = document.querySelectorAll('[data-close-product-sheet]');

        const openSheet = (section) => {
            sheet.classList.remove('hidden');
            sheet.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(() => {
                panel.scrollTop = section === 'sort'
                    ? 0
                    : sheet.querySelector('[data-product-sheet-section="filters"]')?.offsetTop ?? 0;
                panel.classList.remove('translate-y-full');
            });
        };

        const closeSheet = () => {
            panel.classList.add('translate-y-full');
            window.setTimeout(() => {
                sheet.classList.add('hidden');
                sheet.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }, 300);
        };

        openButtons.forEach((button) => button.addEventListener('click', () => openSheet(button.dataset.openProductSheet)));
        closeButtons.forEach((button) => button.addEventListener('click', closeSheet));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !sheet.classList.contains('hidden')) closeSheet();
        });
    })();
</script>
@endsection
