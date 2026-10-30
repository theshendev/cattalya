@extends('layouts.app')
@section('title', 'سبد خرید')

@section('content')
<div class="px-4 py-6 md:px-20 md:py-8">
    <x-checkout-stepper :step="1" />

    @if($items->isEmpty())
    <div class="mt-5 flex flex-col items-center gap-5 rounded-3xl bg-white px-6 py-20 text-center animate-fade-up">
        <div class="flex size-20 items-center justify-center rounded-full bg-[#f9f6f3]">
            <img src="/icons/cart.svg" alt="" class="size-9 opacity-50">
        </div>
        <div>
            <p class="text-lg font-bold text-[#2b2620]">سبد خرید شما خالی است</p>
            <p class="mt-2 text-sm text-[#a8a096]">محصول مورد علاقه‌تان را به سبد اضافه کنید.</p>
        </div>
        <a href="{{ route('products.index') }}" class="press rounded-2xl bg-[#111] px-8 py-3 text-sm text-white">مشاهده محصولات</a>
    </div>
    @else
    <div class="mt-5 grid grid-cols-1 items-start gap-5 lg:grid-cols-[1fr_325px]">
        <section class="rounded-3xl bg-white p-5 md:px-8 md:py-6">
            <div class="flex items-center gap-2 text-right">
                <h1 class="text-lg font-bold text-[#2b2620] md:text-xl">خلاصه سبد خرید</h1>
                <span class="text-xs text-[#a8a096]">({{ $items->sum('quantity') }} کالا)</span>
            </div>

            <div class="mt-2 divide-y divide-[#ede6d3] border-b border-[#ede6d3]">
                @foreach($items as $item)
                <article class="grid grid-cols-[auto_1fr] items-center gap-x-4 gap-y-5 py-6 md:flex md:gap-6 md:py-6">
                    <a href="{{ route('products.show', $item['slug']) }}" class="size-24 shrink-0 overflow-hidden rounded-2xl bg-[#f5f5f5] md:size-20">
                        @if($item['image'])
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
                        @else
                        <x-jewelry-placeholder />
                        @endif
                    </a>

                    <div class="flex min-w-0 flex-col items-start gap-3 text-right md:flex-1 md:flex-row md:items-center md:justify-between md:gap-6">
                        <div class="min-w-0">
                            <a href="{{ route('products.show', $item['slug']) }}" class="line-clamp-2 text-sm leading-7 text-[#2b2620] md:line-clamp-1 md:text-base">{{ $item['name'] }}</a>
                            @if(! $item['is_fixed_price'] && $item['weight_grams'])
                            <p class="text-xs text-[#a8a096]">{{ (float) $item['weight_grams'] }} گرم</p>
                            @endif
                        </div>

                        @if($item['is_made_to_order'] ?? false)
                        <span class="shrink-0 rounded-xl bg-[#fdeee0] px-5 py-1.5 text-sm text-[#a34d06]">ساخت سفارشی</span>
                        @else
                        <span class="shrink-0 rounded-xl bg-[#e6f6ec] px-5 py-1.5 text-sm text-[#1a7f3c]">آماده ارسال</span>
                        @endif
                    </div>

                    <div class="col-span-2 flex items-center justify-between gap-6 md:col-span-1 md:shrink-0 md:flex-row-reverse md:justify-start">
                        <form method="POST" action="{{ route('cart.update', $item['key']) }}" class="flex h-10 w-[120px] items-center justify-between rounded-full border border-[#f0e6c8] px-3" data-no-loading>
                            @csrf @method('PATCH')
                            <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" class="flex size-6 items-center justify-center text-[#b08000]" aria-label="افزایش تعداد">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="M12 5v14M5 12h14"/></svg>
                            </button>
                            <span class="text-sm text-[#2b2620]">{{ $item['quantity'] }}</span>
                            <button type="submit" name="quantity" value="{{ $item['quantity'] - 1 }}" class="flex size-6 items-center justify-center text-[#d64545]" aria-label="{{ $item['quantity'] > 1 ? 'کاهش تعداد' : 'حذف از سبد خرید' }}">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="M5 12h14"/></svg>
                            </button>
                        </form>

                        <span class="hidden h-10 w-px bg-[#ede6d3] md:block" aria-hidden="true"></span>

                        <div class="text-left md:min-w-36 md:text-right">
                            @if(! $item['is_fixed_price'] && $item['weight_grams'])
                            <p class="text-[10px] text-[#a8a096] line-through">{{ number_format($item['total_price'] * 1.04) }}</p>
                            @endif
                            <p class="text-sm font-bold text-[#2b2620]">{{ number_format($item['total_price']) }} تومان</p>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>

            @if($items->contains(fn ($item) => $item['is_made_to_order'] ?? false))
            <p class="mt-6 flex items-center gap-2 text-sm text-[#6b6154] md:text-base">
                <span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-[#a8a096] text-xs font-bold text-white" aria-hidden="true">!</span>
                ارسال پس از تکمیل ساخت سفارش انجام خواهد شد.
            </p>
            @endif
        </section>

        <x-invoice-summary :quote="$quote" :show-shipping="false">
            <a href="{{ route('checkout.index') }}" class="press flex h-12 w-full items-center justify-center rounded-2xl bg-[#111] text-sm font-medium text-white hover:bg-[#2b2620]">تایید و ادامه</a>
        </x-invoice-summary>
    </div>
    @endif
</div>
@endsection
