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
        <section class="rounded-3xl bg-white p-5 md:p-6">
            <div class="flex items-center gap-2 text-right">
                <h1 class="text-lg font-bold text-[#2b2620] md:text-xl">خلاصه سبد خرید</h1>
                <span class="text-xs text-[#a8a096]">({{ $items->sum('quantity') }} کالا)</span>
            </div>

            <div class="mt-4 divide-y divide-[#f0e6c8]">
                @foreach($items as $item)
                <article class="flex flex-wrap items-center gap-4 py-5 md:flex-nowrap">
                    <a href="{{ route('products.show', $item['slug']) }}" class="size-20 shrink-0 overflow-hidden rounded-2xl bg-[#f9f6f3]">
                        @if($item['image'])
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
                        @else
                        <x-jewelry-placeholder />
                        @endif
                    </a>

                    <div class="min-w-0 flex-1 text-right">
                        <a href="{{ route('products.show', $item['slug']) }}" class="line-clamp-2 text-sm font-medium leading-6 text-[#2b2620] md:text-base">{{ $item['name'] }}</a>
                    </div>

                    <span class="rounded-lg bg-[#e6f6ec] px-3 py-1.5 text-xs font-medium text-[#1a7f3c]">آماده ارسال</span>

                    <div class="min-w-28 text-right md:text-left">
                        <p class="text-sm font-bold text-[#2b2620]">{{ number_format($item['unit_price']) }} <span class="text-xs font-normal">تومان</span></p>
                        @if($item['quantity'] > 1)
                        <p class="text-xs text-[#a8a096]">مجموع: {{ number_format($item['total_price']) }}</p>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        <form method="POST" action="{{ route('cart.update', $item['product_id']) }}" class="flex items-center overflow-hidden rounded-2xl border border-[#ead69e]" data-no-loading>
                            @csrf @method('PATCH')
                            <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" class="flex size-9 items-center justify-center text-xl text-[#c8960c]" aria-label="افزایش تعداد">+</button>
                            <span class="flex size-9 items-center justify-center border-x border-[#f0e6c8] text-sm">{{ $item['quantity'] }}</span>
                            <button type="submit" name="quantity" value="{{ $item['quantity'] - 1 }}" class="flex size-9 items-center justify-center text-xl text-red-400" aria-label="کاهش تعداد">−</button>
                        </form>
                        <form method="POST" action="{{ route('cart.remove', $item['product_id']) }}" data-no-loading>
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:underline">حذف</button>
                        </form>
                    </div>
                </article>
                @endforeach
            </div>
        </section>

        <x-invoice-summary :quote="$quote" :show-shipping="false">
            <a href="{{ route('checkout.index') }}" class="press flex h-12 w-full items-center justify-center rounded-2xl bg-[#111] text-sm font-medium text-white hover:bg-[#2b2620]">تایید و ادامه</a>
        </x-invoice-summary>
    </div>
    @endif
</div>
@endsection
