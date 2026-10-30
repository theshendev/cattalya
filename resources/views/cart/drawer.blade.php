@extends('layouts.app')
@section('title', 'خلاصه سبد خرید')

@section('content')
<div inert aria-hidden="true">
    <x-home-showcase :hero-banner="\App\Models\Banner::where('type', 'hero')->where('is_active', true)->first()" />
</div>

<div class="fixed inset-0 z-[70] bg-black/40" role="dialog" aria-modal="true" aria-labelledby="cart-title">
    <a href="{{ route('home') }}" class="absolute inset-0" aria-label="بستن سبد خرید"></a>

    <aside class="absolute inset-x-0 bottom-0 flex max-h-[88vh] flex-col rounded-t-[30px] bg-white shadow-2xl md:inset-y-0 md:left-0 md:right-auto md:max-h-none md:w-[329px] md:rounded-none">
        <span class="mx-auto mt-3 block h-1 w-10 shrink-0 rounded-full bg-[#aaa197] md:hidden"></span>
        <header class="flex shrink-0 items-center justify-between px-4 pb-3 pt-5 md:pt-4">
            <div class="flex items-center gap-2">
                <h1 id="cart-title" class="text-xl font-bold">خلاصه سبد خرید</h1>
                <span class="text-xs text-[#a8a096]">({{ $items->sum('quantity') }} کالا)</span>
            </div>
            <a href="{{ route('home') }}" class="-m-1 flex size-8 items-center justify-center text-[#2b2620]" aria-label="بستن">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9.25" stroke-width="1.5"/><path stroke-linecap="round" stroke-width="1.5" d="m9.2 9.2 5.6 5.6m0-5.6-5.6 5.6"/></svg>
            </a>
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
        <div class="flex-1 overflow-y-auto px-4">
            @foreach($items as $item)
            <article class="border-b-2 border-[#f0e6c8] py-5 last:border-0">
                <div class="flex items-start gap-3">
                    <a href="{{ route('products.show', $item['slug']) }}" class="size-20 shrink-0 overflow-hidden rounded-2xl bg-[#f5f5f5]">
                        @if($item['image'])
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
                        @else
                        <x-jewelry-placeholder />
                        @endif
                    </a>
                    <div class="min-w-0 flex-1 text-right">
                        <a href="{{ route('products.show', $item['slug']) }}" class="line-clamp-2 text-sm font-medium leading-6">{{ $item['name'] }}</a>
                    </div>
                </div>

                <div class="mt-4 flex items-end justify-between">
                    <form method="POST" action="{{ route('cart.update', $item['product_id']) }}" class="flex h-8 w-[98px] items-center justify-between rounded-full border border-[#f0e6c8] px-2.5" data-no-loading>
                        @csrf @method('PATCH')
                        <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" class="text-[#c8960c]" aria-label="افزایش تعداد">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.5" d="M12 5v14M5 12h14"/></svg>
                        </button>
                        <span class="text-sm">{{ $item['quantity'] }}</span>
                        <button type="submit" name="quantity" value="{{ $item['quantity'] - 1 }}" class="text-red-500" aria-label="{{ $item['quantity'] > 1 ? 'کاهش تعداد' : 'حذف از سبد' }}">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.5" d="M5 12h14"/></svg>
                        </button>
                    </form>
                    <div class="text-left">
                        <p class="text-xs text-[#a8a096] line-through">{{ number_format($item['unit_price'] * 1.04) }} تومان</p>
                        <p class="text-base font-bold">{{ number_format($item['unit_price']) }} تومان</p>
                    </div>
                </div>
            </article>
            @endforeach
        </div>

        <footer class="shrink-0 border-t-2 border-[#f0e6c8] bg-white mx-4 pb-8 pt-5">
            <div class="mb-5 flex items-end justify-between">
                <span class="text-sm text-[#a8a096]">قیمت نهایی:</span>
                <div class="text-left"><p class="text-xs text-[#a8a096] line-through">{{ number_format($total * 1.04) }} تومان</p><p class="text-xl font-black">{{ number_format($total) }} تومان</p></div>
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
