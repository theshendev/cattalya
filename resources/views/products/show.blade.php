@extends('layouts.app')

@section('title', $product->name)

@php
    $images = $product->getMedia('images');
    $isWished = auth()->check() && auth()->user()->wishlists()->where('product_id', $product->id)->exists();
    $goldPricePerGram = \App\Models\GoldPrice::currentPricePerGram();
    $formatWeight = fn ($grams) => rtrim(rtrim(number_format((float) $grams, 2), '0'), '.').' گرم';
    $weightOptions = collect([null])
        ->merge($product->is_fixed_price ? [] : $product->variants)
        ->map(function ($variant) use ($product, $goldPricePerGram, $formatWeight) {
            $grams = (float) ($variant?->weight_grams ?? $product->weight_grams);
            $price = $product->calculatePrice($goldPricePerGram, $product->is_fixed_price ? null : $grams);

            return [
                'variant_id' => $variant?->id,
                'grams' => $grams,
                'weight' => $product->is_fixed_price || ! $grams ? null : $formatWeight($grams),
                'price' => number_format($price),
                'list_price' => ! $product->is_fixed_price && $grams ? number_format($price * 1.04) : null,
                'is_available' => $product->canBeOrdered($variant),
            ];
        })
        ->sortBy('grams')
        ->values();
    $selectedOption = $weightOptions->firstWhere('is_available', true) ?? $weightOptions->first();
    $hasWeightChoice = $weightOptions->count() > 1;
    $isInStock = $selectedOption['is_available'];
    $price = $selectedOption['price'];
    $listPrice = $selectedOption['list_price'];
    $weightLabel = $selectedOption['weight'];
    $specs = array_filter([
        'وزن محصول' => $weightLabel,
        'اجرت' => $product->is_fixed_price ? null : '%'.(float) $product->wage_percent,
        'عیار طلا' => $product->karat ? round($product->karat / 24 * 1000).'/'.$product->karat : null,
        'رنگ طلا' => $product->gold_color,
        'درخشندگی' => $product->shine,
        'ابعاد تقریبی' => $product->dimensions,
    ]);
    $highlights = array_filter([
        ['icon' => 'M4 12h2m12 0h2M7 9v6m10-6v6M9 12a3 3 0 1 0 6 0 3 3 0 0 0-6 0', 'text' => $weightLabel],
        ['icon' => 'M5 20V9l7-5 7 5v11M9 20v-6h6v6M12 4v5', 'text' => $product->karat ? 'طلای '.$product->karat.' عیار' : null],
        ['icon' => 'M5 4h4v16H5zM15 4h4v16h-4zM5 8h2M5 12h2M5 16h2M15 8h2M15 12h2M15 16h2', 'text' => $product->dimensions],
    ], fn ($highlight) => filled($highlight['text']));
    $badges = [
        ['icon' => 'shield', 'text' => 'ضمانت اصالت کالا'],
        ['icon' => 'truck', 'text' => 'ارسال رایگان'],
        ['icon' => 'gift', 'text' => 'بسته‌بندی هدیه‌ای'],
    ];
    $contactIcons = ['sms', 'whatsapp', 'instagram'];
@endphp

@section('mobile-header')
<a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('products.index') }}" class="flex items-center gap-3 text-base text-[#2b2620]" aria-label="بازگشت">
    <svg class="size-6 text-[#6b6154]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 12h16m-6-6 6 6-6 6"/></svg>
    صفحه محصول
</a>
<a href="{{ route('cart.drawer') }}" class="press relative flex size-10 items-center justify-center rounded-2xl border border-[#e3d2a0]" aria-label="سبد خرید">
    <img src="/icons/cart.svg" alt="" class="size-5">
    @if(count(session('cart', [])) > 0)
    <span class="absolute -right-1 -top-1 flex size-4 items-center justify-center rounded-full bg-[#fdf1c9] text-[10px] text-[#8a6300]">{{ count(session('cart', [])) }}</span>
    @endif
</a>
@endsection

@section('content')
<div class="px-4 py-4 md:px-20 md:py-8">

    <nav class="mb-4 flex flex-wrap items-center gap-2 text-[10px] text-[#a8a096] animate-fade-in md:mb-5 md:gap-3 md:text-sm" aria-label="breadcrumb">
        <a href="{{ route('home') }}" class="transition-colors hover:text-[#8a6300]">صفحه اصلی</a>
        <span>/</span>
        <a href="{{ route('products.index') }}" class="transition-colors hover:text-[#8a6300]">لیست محصولات</a>
        <span>/</span>
        <span class="max-w-[180px] truncate md:max-w-none">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-[1fr_325px]">

        <div class="flex min-w-0 flex-col gap-5 md:gap-10">

            <section class="rounded-3xl bg-white p-4 md:p-5">
                <div class="flex flex-col gap-5 md:flex-row md:gap-10">

                    {{-- Gallery --}}
                    <div class="flex shrink-0 gap-3 md:gap-4">
                        <div class="relative flex w-full flex-col gap-4 md:w-[380px]">
                            <div class="group aspect-square overflow-hidden rounded-2xl bg-[#f5f5f5] animate-scale-in">
                                @if($images->count())
                                <img id="main-img" src="{{ $images->first()->getUrl() }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition-[transform,opacity] duration-500 ease-out group-hover:scale-105">
                                @else
                                <x-jewelry-placeholder :category="$product->category?->slug" />
                                @endif
                            </div>

                            @if($images->count() > 1)
                            <div data-reveal-stagger="70" class="flex justify-end gap-3 md:justify-start">
                                @foreach($images as $media)
                                <button type="button" data-reveal onclick="selectImage(this, '{{ $media->getUrl() }}')" aria-label="تصویر {{ $loop->iteration }}"
                                        class="thumb size-[60px] overflow-hidden rounded-xl border bg-white transition-all duration-300 hover:scale-105 hover:border-[#8a6300] {{ $loop->first ? 'border-[#c9a227]' : 'border-[#f0e6c8]' }}">
                                    <img src="{{ $media->getUrl('thumb') ?: $media->getUrl() }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                </button>
                                @endforeach
                            </div>
                            @endif

                            {{-- Mobile: actions float over the image --}}
                            <div class="absolute right-3 top-3 flex flex-col items-center gap-3 md:hidden">
                                @include('products.partials.actions')
                            </div>
                        </div>

                        <div class="order-first hidden flex-col items-center gap-4 pt-2 md:flex">
                            @include('products.partials.actions')
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="flex min-w-0 flex-1 flex-col text-right">
                        <div data-reveal-stagger="70" class="flex flex-col gap-4 md:pt-10">
                            <h1 data-reveal class="text-lg font-bold leading-8 text-[#2b2620] md:text-[22px] md:leading-10">{{ $product->name }}</h1>

                            @if($product->description)
                            <p data-reveal class="text-sm leading-7 text-[#6b6154] md:text-base">{{ $product->description }}</p>
                            @endif

                            @if($hasWeightChoice)
                            <fieldset data-reveal class="md:mt-2">
                                <legend class="sr-only">انتخاب وزن</legend>
                                <div class="grid grid-cols-3 gap-2 md:flex md:gap-4">
                                    @foreach($weightOptions as $option)
                                    <label class="relative flex h-11 cursor-pointer items-center gap-1.5 whitespace-nowrap rounded-2xl border border-[#ead69e] bg-white px-2.5 text-sm text-[#6b6154] transition-colors has-checked:border-[#e3c46d] has-checked:bg-[#fdf1c9] has-checked:text-[#8a6300] has-disabled:cursor-not-allowed has-disabled:opacity-40 md:w-[150px] md:px-4">
                                        <input type="radio" name="weight_option" class="peer sr-only" data-weight-option="{{ json_encode($option) }}" @checked($option === $selectedOption) @disabled(! $option['is_available'])>
                                        <span class="flex size-5 shrink-0 items-center justify-center rounded-full border border-[#a8a096] peer-checked:border-[#8a6300] peer-focus-visible:ring-2 peer-focus-visible:ring-[#c9a227] peer-checked:[&>span]:block" aria-hidden="true">
                                            <span class="hidden size-2.5 rounded-full bg-[#8a6300]"></span>
                                        </span>
                                        {{ $option['weight'] }}
                                    </label>
                                    @endforeach
                                </div>
                            </fieldset>

                            <p data-reveal class="flex items-center gap-2 border-t border-[#ede6d3] pt-5 text-xs text-[#6b6154] md:mt-2 md:text-sm">
                                <img src="/icons/gift.svg" alt="" class="size-5">
                                امکان ارسال به عنوان هدیه، همراه با بسته‌بندی مخصوص.
                            </p>
                            @elseif($highlights)
                            <ul data-reveal class="flex flex-wrap gap-3 md:mt-2">
                                @foreach($highlights as $highlight)
                                <li class="flex h-11 items-center gap-2 rounded-2xl bg-[#f9f8f4] px-4 text-sm text-[#2b2620]">
                                    <svg class="size-6 text-[#6b6154]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="{{ $highlight['icon'] }}"/></svg>
                                    {{ $highlight['text'] }}
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </div>

                        <ul class="mt-6 flex items-center justify-between gap-2 border-t border-[#ede6d3] pt-5 md:mt-auto md:justify-start md:gap-x-12">
                            @foreach($badges as $badge)
                            <li class="flex items-center gap-1.5 whitespace-nowrap text-[11px] text-[#6b6154] md:gap-2 md:text-sm">
                                <img src="/icons/{{ $badge['icon'] }}.svg" alt="" class="size-5 md:size-6">
                                <span>{{ $badge['text'] }}</span>
                            </li>
                            @endforeach
                        </ul>

                        <div class="mt-5 flex items-center justify-between border-t border-[#ede6d3] pt-5 lg:hidden">
                            <p class="text-sm text-[#a8a096]">با ما در تماس باشید.</p>
                            <div class="flex items-center gap-3">
                                @foreach($contactIcons as $icon)
                                <span class="flex size-10 items-center justify-center rounded-full border border-[#e3d2a0] bg-white"><img src="/icons/{{ $icon }}.svg" alt="" class="size-5"></span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            @if($specs)
            <section data-reveal class="grid grid-cols-1 gap-6 rounded-3xl bg-white p-5 md:grid-cols-[1fr_1.15fr] md:gap-16 md:p-10">
                <div class="text-right md:order-last">
                    <h2 class="text-lg font-bold text-[#2b2620] md:text-[22px]">جزئیات محصول</h2>
                    <p class="mt-3 text-sm leading-7 text-[#6b6154] md:mt-6 md:text-base md:leading-8">در این قسمت جزئیات فیزیکی و ظاهری محصول قابل مشاهده می‌باشد.</p>
                </div>
                <dl class="flex flex-col md:order-first">
                    @foreach($specs as $label => $value)
                    <div class="flex items-center justify-between gap-4 border-b border-[#ede6d3] py-3.5 first:pt-0 last:border-b-0 last:pb-0">
                        <dt class="text-sm text-[#a8a096]">{{ $label }}:</dt>
                        <dd class="text-sm font-bold text-[#2b2620]" @if($label === 'وزن محصول') data-option-weight @endif>{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </section>
            @endif

            @if($related->count())
            <x-product-carousel title="محصولات مشابه" :products="$related" :link="route('products.index', ['category' => $product->category?->slug])" :inset="false" :per-view="3" />
            @endif
        </div>

        {{-- Invoice sidebar --}}
        <aside data-reveal class="hidden rounded-3xl border border-[#f0e6c8] bg-white p-5 lg:block">
            <h2 class="text-right text-sm text-[#a8a096]">فاکتور خرید</h2>

            <dl class="mt-4 flex flex-col text-sm">
                <div class="flex items-center justify-between gap-3 pb-4">
                    <dt class="text-[#a8a096]">وضعیت محصول:</dt>
                    <dd>
                        @if($product->is_made_to_order)
                        <span class="rounded-lg bg-[#fdeee0] px-4 py-1.5 text-xs font-medium text-[#a34d06]">ساخت سفارشی</span>
                        @elseif($isInStock)
                        <span class="rounded-lg bg-[#e6f6ec] px-4 py-1.5 text-xs font-medium text-[#1a7f3c]">آماده ارسال</span>
                        @else
                        <span class="rounded-lg bg-[#fbeaea] px-4 py-1.5 text-xs font-medium text-[#c0392b]">ناموجود</span>
                        @endif
                    </dd>
                </div>
                @if(! $product->is_fixed_price)
                <div class="flex items-center justify-between gap-3 border-t border-[#ede6d3] py-5">
                    <dt class="text-[#a8a096]">وزن:</dt>
                    <dd class="text-[#2b2620]" data-option-weight>{{ $weightLabel }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3 border-t border-[#ede6d3] py-5">
                    <dt class="text-[#a8a096]">اجرت:</dt>
                    <dd class="text-[#2b2620]">%{{ (float) $product->wage_percent }}</dd>
                </div>
                @endif
                @if($product->karat)
                <div class="flex items-center justify-between gap-3 border-t border-[#ede6d3] py-5">
                    <dt class="text-[#a8a096]">عیار طلا:</dt>
                    <dd class="text-[#2b2620]">{{ $product->karat }} عیار</dd>
                </div>
                @endif
                <div class="flex items-end justify-between gap-3 border-t border-[#ede6d3] pt-5">
                    <dt class="pb-1 text-[#a8a096]">قیمت نهایی:</dt>
                    <dd class="text-left">
                        @if($listPrice)
                        <p class="text-xs text-[#a8a096] line-through"><span data-option-list-price>{{ $listPrice }}</span> تومان</p>
                        @endif
                        <p class="mt-1 text-lg font-bold text-[#2b2620]"><span data-option-price>{{ $price }}</span> <span class="text-base">تومان</span></p>
                    </dd>
                </div>
            </dl>

            <div class="mt-8">
                @include('products.partials.add-to-cart')
            </div>

            <div class="mt-5 border-t border-[#ede6d3] pt-5 text-center">
                <p class="text-sm text-[#a8a096]">با ما در تماس باشید.</p>
                <div class="mt-3 flex items-center justify-center gap-3">
                    @foreach($contactIcons as $icon)
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
        t.classList.remove('border-[#c9a227]');
        t.classList.add('border-[#f0e6c8]');
    });
    button.classList.remove('border-[#f0e6c8]');
    button.classList.add('border-[#c9a227]');

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

document.querySelectorAll('[data-weight-option]').forEach((radio) => {
    radio.addEventListener('change', () => {
        const option = JSON.parse(radio.dataset.weightOption);
        document.querySelectorAll('[data-option-price]').forEach((el) => el.textContent = option.price);
        document.querySelectorAll('[data-option-list-price]').forEach((el) => el.textContent = option.list_price);
        document.querySelectorAll('[data-option-weight]').forEach((el) => el.textContent = option.weight);
        document.querySelectorAll('[data-variant-input]').forEach((input) => input.value = option.variant_id ?? '');
    });
});

document.querySelectorAll('[data-share-product]').forEach((button) => {
    button.addEventListener('click', async () => {
        const shareData = { title: document.title, url: window.location.href };
        if (navigator.share) {
            await navigator.share(shareData).catch(() => {});
            return;
        }
        await navigator.clipboard?.writeText(shareData.url);
        button.setAttribute('aria-label', 'لینک کپی شد');
    });
});
</script>
@endsection

@section('mobile-bottom-bar')
<div class="h-12 lg:hidden" aria-hidden="true"></div>
<div class="fixed inset-x-0 bottom-0 z-40 rounded-t-3xl border-t border-[#f0e6c8] bg-white px-4 pb-4 pt-4 shadow-[0_-8px_24px_rgb(0_0_0_/_0.06)] lg:hidden">
    <div class="mb-3 flex items-end justify-between border-b border-[#ede6d3] pb-3">
        <span class="pb-1 text-sm text-[#a8a096]">قیمت نهایی:</span>
        <div class="text-left">
            @if($listPrice)
            <p class="text-xs text-[#a8a096] line-through"><span data-option-list-price>{{ $listPrice }}</span> تومان</p>
            @endif
            <p class="text-lg font-bold text-[#2b2620]"><span data-option-price>{{ $price }}</span> <span class="text-base">تومان</span></p>
        </div>
    </div>
    @include('products.partials.add-to-cart')
</div>
@endsection
