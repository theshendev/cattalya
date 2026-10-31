@php
    $isMobile = $context === 'mobile';
    $sliderStep = 100_000;
    $sliderMax = max($sliderStep, (int) (ceil($priceCeiling / $sliderStep) * $sliderStep));
    $sliderFrom = min($minPrice ?? 0, $sliderMax);
    $sliderTo = min($maxPrice ?? $sliderMax, $sliderMax);
    $cardClass = $isMobile ? 'rounded-[28px] bg-white px-5 py-6' : 'rounded-3xl bg-white p-4';
    $titleClass = $isMobile ? 'text-lg font-medium text-[#6b6154]' : 'text-sm text-[#a8a096]';
@endphp

<details open class="group {{ $cardClass }}">
    <summary class="flex cursor-pointer list-none items-center justify-between [&::-webkit-details-marker]:hidden">
        <span class="flex items-center gap-3 {{ $titleClass }}">
            @if($isMobile)
            <svg class="size-7 text-[#a8a096]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="7" cy="7" r="3" stroke-width="1.5"/><circle cx="17" cy="7" r="3" stroke-width="1.5"/><circle cx="7" cy="17" r="3" stroke-width="1.5"/><circle cx="17" cy="17" r="3" stroke-width="1.5"/></svg>
            @endif
            دسته‌بندی‌ها
        </span>
        <svg class="size-4 rotate-180 text-[#b08000] transition-transform group-open:rotate-0 {{ $isMobile ? 'ml-6' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m6 15 6-6 6 6"/></svg>
    </summary>

    <div class="flex flex-col {{ $isMobile ? 'mt-8 gap-5' : 'mt-5 gap-3' }}">
        @foreach($categories as $category)
        <label class="flex cursor-pointer items-center gap-3 {{ $isMobile ? 'text-lg text-[#2b2620]' : 'text-sm text-[#6b6154]' }}">
            <input type="checkbox" name="categories[]" value="{{ $category->slug }}"
                class="shrink-0 cursor-pointer appearance-none rounded-md border border-[#d9d4cc] bg-white bg-center bg-no-repeat transition-colors checked:border-[#c9a227] checked:bg-[#c9a227] checked:bg-[url('data:image/svg+xml;utf8,<svg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%2024%2024%22%20fill=%22none%22%20stroke=%22white%22%20stroke-width=%223%22%20stroke-linecap=%22round%22%20stroke-linejoin=%22round%22><path%20d=%22m5%2012%205%205L20%207%22/></svg>')] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#c9a227] {{ $isMobile ? 'size-5 bg-[length:14px]' : 'size-4 bg-[length:11px]' }}"
                @checked(in_array($category->slug, $selectedCategories, true))>
            <span>{{ $category->name }}</span>
        </label>
        @endforeach
    </div>
</details>

<details open class="group {{ $cardClass }}" data-price-range data-step="{{ $sliderStep }}">
    <summary class="flex cursor-pointer list-none items-center justify-between [&::-webkit-details-marker]:hidden">
        <span class="flex items-center gap-3 {{ $titleClass }}">
            @if($isMobile)
            <svg class="size-7 text-[#a8a096]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 3h12a1 1 0 0 1 1 1v17l-3-2-2 2-2-2-2 2-2-2-3 2V4a1 1 0 0 1 1-1Zm3 6h6m-6 4h4"/></svg>
            @endif
            محدوده قیمت
        </span>
        <svg class="size-4 rotate-180 text-[#b08000] transition-transform group-open:rotate-0 {{ $isMobile ? 'ml-6' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m6 15 6-6 6 6"/></svg>
    </summary>

    <div class="flex flex-col {{ $isMobile ? 'mt-8 gap-4' : 'mt-5 gap-3' }}">
        @foreach([['min_price', 'از قیمت', $minPrice], ['max_price', 'تا قیمت', $maxPrice]] as [$field, $label, $value])
        <label class="flex items-center gap-3 rounded-2xl border border-[#f0e6c8] px-4 text-[#a8a096] focus-within:border-[#dfc47d] {{ $isMobile ? 'h-14 text-sm' : 'h-12 text-[11px]' }}">
            <span class="shrink-0">{{ $label }}</span>
            <input type="text" inputmode="numeric" name="{{ $field }}" value="{{ $value !== null ? number_format($value) : '' }}" placeholder="—" data-price-input="{{ $field === 'min_price' ? 'from' : 'to' }}"
                class="min-w-0 flex-1 border-0 bg-transparent text-left font-medium text-[#2b2620] outline-none placeholder:text-[#d9d4cc] {{ $isMobile ? 'text-lg' : 'text-sm' }}" dir="ltr">
            <span class="shrink-0">تومان</span>
        </label>
        @endforeach

        <div class="mt-2 px-1">
            <div class="relative h-4" dir="ltr">
                <div class="absolute inset-x-0 top-1/2 h-1 -translate-y-1/2 rounded-full bg-[#ede6d3]"></div>
                <div data-price-track class="absolute top-1/2 h-1 -translate-y-1/2 rounded-full bg-[#c9a227]"></div>
                <input type="range" min="0" max="{{ $sliderMax }}" step="{{ $sliderStep }}" value="{{ $sliderFrom }}" data-price-slider="from" class="price-range" aria-label="حداقل قیمت">
                <input type="range" min="0" max="{{ $sliderMax }}" step="{{ $sliderStep }}" value="{{ $sliderTo }}" data-price-slider="to" class="price-range" aria-label="حداکثر قیمت">
            </div>
            <div class="mt-3 flex justify-between text-[#a8a096] {{ $isMobile ? 'text-sm' : 'text-[10px]' }}">
                <span>{{ number_format($sliderMax) }}</span>
                <span>۰</span>
            </div>
        </div>
    </div>
</details>
