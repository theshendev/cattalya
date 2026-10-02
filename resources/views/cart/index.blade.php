@extends('layouts.app')
@section('title', 'سبد خرید')

@section('content')
<div class="hidden min-h-[calc(100vh-112px)] px-20 py-16 md:block" aria-hidden="true">
    <div class="relative h-[500px] overflow-hidden rounded-3xl bg-[#1c3536]">
        <div class="absolute -bottom-44 -left-10 size-[480px] rounded-full border-[34px] border-[#c8960c]"></div>
        <div class="absolute bottom-28 left-48 size-32 rotate-45 border-4 border-[#b9f1ff] bg-[#60c4e7]"></div>
        <div class="absolute inset-y-0 right-24 flex items-center text-right text-[#fcfde3]"><p class="text-6xl font-bold leading-tight">درخشش ابدی،<br>زیبایی جاودان.</p></div>
    </div>
</div>

<div class="fixed inset-0 z-[70] bg-black/45" role="dialog" aria-modal="true" aria-labelledby="cart-title">
    <a href="{{ route('home') }}" class="absolute inset-0" aria-label="بستن سبد خرید"></a>

    <aside class="absolute inset-x-0 bottom-0 flex max-h-[88vh] flex-col rounded-t-[30px] bg-white shadow-2xl md:inset-y-0 md:left-0 md:right-auto md:max-h-none md:w-[430px] md:rounded-none">
        <span class="mx-auto mt-3 block h-1 w-10 shrink-0 rounded-full bg-[#aaa197] md:hidden"></span>
        <header class="flex shrink-0 items-center justify-between border-b border-[#f0e6c8] px-5 py-5 md:px-7">
            <div class="flex items-center gap-2">
                <h1 id="cart-title" class="text-xl font-bold">خلاصه سبد خرید</h1>
                <span class="text-xs text-[#a8a096]">({{ $items->sum('quantity') }} کالا)</span>
            </div>
            <a href="{{ route('home') }}" class="flex size-8 items-center justify-center rounded-full border border-[#2b2620] text-lg" aria-label="بستن">×</a>
        </header>

        @if($items->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center gap-5 px-6 py-16 text-center">
            <div class="flex size-20 items-center justify-center rounded-full bg-[#f9f6f3]">
                <img src="/icons/cart.svg" alt="" class="size-9 opacity-50">
            </div>
            <div><p class="text-lg font-bold">سبد خرید شما خالی است</p><p class="mt-2 text-sm text-[#a8a096]">محصول مورد علاقه‌تان را به سبد اضافه کنید.</p></div>
            <a href="{{ route('products.index') }}" class="press rounded-2xl bg-[#111] px-8 py-3 text-sm text-white">مشاهده محصولات</a>
        </div>
        @else
        <div class="flex-1 overflow-y-auto px-5 py-3 md:px-7">
            @foreach($items as $item)
            <article class="border-b border-[#f0e6c8] py-5 last:border-0">
                <div class="flex items-start gap-3">
                    <a href="{{ route('products.show', $item['slug']) }}" class="size-20 shrink-0 overflow-hidden rounded-2xl bg-[#f9f6f3]">
                        @if($item['image'])
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
                        @else
                        <x-jewelry-placeholder />
                        @endif
                    </a>
                    <div class="min-w-0 flex-1 text-right">
                        <a href="{{ route('products.show', $item['slug']) }}" class="line-clamp-2 text-sm font-medium leading-6">{{ $item['name'] }}</a>
                        <p class="mt-2 text-xs text-[#a8a096] line-through">{{ number_format($item['unit_price'] * 1.04) }} تومان</p>
                        <p class="text-sm font-bold">{{ number_format($item['unit_price']) }} تومان</p>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <form method="POST" action="{{ route('cart.remove', $item['product_id']) }}" data-no-loading>
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400">حذف</button>
                    </form>
                    <form method="POST" action="{{ route('cart.update', $item['product_id']) }}" class="flex items-center overflow-hidden rounded-2xl border border-[#ead69e]" data-no-loading>
                        @csrf @method('PATCH')
                        <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" class="flex size-9 items-center justify-center text-xl text-[#c8960c]">+</button>
                        <span class="flex size-9 items-center justify-center border-x border-[#f0e6c8] text-sm">{{ $item['quantity'] }}</span>
                        <button type="submit" name="quantity" value="{{ $item['quantity'] - 1 }}" class="flex size-9 items-center justify-center text-xl text-red-400">−</button>
                    </form>
                </div>
            </article>
            @endforeach
        </div>

        <footer class="shrink-0 border-t border-[#f0e6c8] bg-white px-5 pb-6 pt-5 md:px-7">
            <div class="mb-5 flex items-end justify-between">
                <span class="text-sm text-[#6b6154]">قیمت نهایی:</span>
                <div class="text-left"><p class="text-xs text-[#a8a096] line-through">{{ number_format($total * 1.04) }} تومان</p><p class="text-lg font-black">{{ number_format($total) }} تومان</p></div>
            </div>
            <div class="grid gap-2">
                <a href="{{ route('cart.index') }}" class="press flex h-12 items-center justify-center rounded-2xl border border-[#111] text-sm">مشاهده سبد خرید</a>
                <a href="{{ route('checkout.index') }}" class="press flex h-12 items-center justify-center rounded-2xl bg-[#111] text-sm text-white">تسویه حساب</a>
            </div>
        </footer>
        @endif
    </aside>
</div>
@endsection
