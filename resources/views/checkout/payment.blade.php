@extends('layouts.app')
@section('title', 'پرداخت')

@section('content')
<div class="px-4 py-6 md:px-20 md:py-8">
    <x-checkout-stepper :step="3" />

    <form id="payment-form" method="POST" action="{{ route('checkout.store') }}">@csrf</form>

    <div class="mt-5 grid grid-cols-1 items-start gap-5 lg:grid-cols-[1fr_325px]">

        <div class="flex min-w-0 flex-col gap-5">

            {{-- Payment method --}}
            <section class="rounded-3xl bg-white p-5 md:p-6">
                <h1 class="text-lg font-bold text-[#2b2620] md:text-xl">روش پرداخت</h1>

                @error('gateway')<p class="mt-3 rounded-xl bg-[#fbeaea] px-4 py-3 text-sm text-red-600">{{ $message }}</p>@enderror
                @error('payment')<p class="mt-3 rounded-xl bg-[#fbeaea] px-4 py-3 text-sm text-red-600">{{ $message }}</p>@enderror

                @if(empty($gateways))
                <p class="mt-4 text-sm text-red-600">در حال حاضر هیچ درگاه پرداختی فعال نیست.</p>
                @else
                <div class="mt-4 divide-y divide-[#f0e6c8]">
                    @foreach($gateways as $value => $label)
                    <label class="flex cursor-pointer items-center justify-between gap-4 py-4">
                        <input form="payment-form" type="radio" name="gateway" value="{{ $value }}" class="size-5 accent-[#8a6300]"
                               @checked(old('gateway', array_key_first($gateways)) === $value)>
                        <span class="flex items-center gap-3 text-right">
                            <span>
                                <span class="block font-bold text-[#2b2620]">{{ $label }}</span>
                                <span class="block text-xs text-[#a8a096]">{{ config("gateways.drivers.{$value}.description", 'پرداخت آنلاین') }}</span>
                            </span>
                            <img src="/icons/cart.svg" alt="" class="size-8 opacity-70">
                        </span>
                    </label>
                    @endforeach
                </div>
                @endif
            </section>

            {{-- Gift --}}
            <section class="rounded-3xl bg-white p-5 md:p-6">
                <label class="flex cursor-pointer items-center justify-between gap-4">
                    <span class="relative inline-flex shrink-0 items-center">
                        <input form="payment-form" type="checkbox" name="is_gift" value="1" id="is-gift" class="peer sr-only" @checked(old('is_gift'))>
                        <span class="h-6 w-11 rounded-full bg-[#d9d4cb] transition-colors peer-checked:bg-[#8a6300] peer-focus-visible:ring-2 peer-focus-visible:ring-[#8a6300]/40"></span>
                        <span class="absolute right-0.5 top-0.5 size-5 rounded-full bg-white shadow transition-transform peer-checked:-translate-x-5"></span>
                    </span>
                    <span class="flex items-center gap-2 text-right">
                        <span class="text-xs text-[#6b6154]">(ارسال با بسته بندی مخصوص هدیه و همچنین امکان ارسال متن نوشته شده روی کارت هدیه مخصوص)</span>
                        <span class="font-bold text-[#2b2620]">ارسال به عنوان هدیه</span>
                        <img src="/icons/gift.svg" alt="" class="size-5">
                    </span>
                </label>
                <textarea form="payment-form" name="gift_message" id="gift-message" rows="2" maxlength="300"
                          class="mt-4 hidden w-full resize-none rounded-2xl border border-[#f0e6c8] px-4 py-3 text-right text-sm outline-none focus:border-[#8a6300] focus:ring-4 focus:ring-[#8a6300]/10"
                          placeholder="متن مورد نظر خود را بنویسید ...">{{ old('gift_message') }}</textarea>
            </section>

        <section class="rounded-3xl bg-white p-5 md:p-6">
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="ml-auto text-lg font-bold text-[#2b2620] md:order-last">کدتخفیف</h2>
                @if($quote['coupon'])
                <form method="POST" action="{{ route('checkout.coupon.remove') }}" class="flex items-center gap-3">
                    @csrf @method('DELETE')
                    <span class="rounded-xl bg-[#e6f6ec] px-4 py-2 text-sm font-medium text-[#1a7f3c]" dir="ltr">{{ $quote['coupon']->code }}</span>
                    <button type="submit" class="text-xs text-red-500 hover:underline">حذف کد</button>
                </form>
                @else
                <form method="POST" action="{{ route('checkout.coupon') }}" class="flex flex-1 items-center gap-3 md:max-w-md">
                    @csrf
                    <input type="text" name="code" value="{{ old('code') }}" dir="ltr" placeholder="کد تخفیف را وارد کنید"
                           class="h-12 min-w-0 flex-1 rounded-2xl border border-[#f0e6c8] px-4 text-sm uppercase outline-none focus:border-[#8a6300] focus:ring-4 focus:ring-[#8a6300]/10">
                    <button type="submit" class="press h-12 rounded-2xl border border-[#111] px-6 text-sm">ثبت</button>
                </form>
                @endif
            </div>
            @error('code')<p class="mt-3 text-sm text-red-500">{{ $message }}</p>@enderror
        </section>

        {{-- Shipping address + items --}}
        <section class="rounded-3xl bg-white p-5 md:p-6">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('checkout.index') }}" class="shrink-0 text-xs text-[#1f7a8c] hover:underline">تغییر آدرس</a>
                <p class="text-right text-sm text-[#6b6154]">
                    ارسال به: <strong class="text-[#2b2620]">{{ $address->title ? $address->title.' : ' : '' }}{{ $address->fullAddress() }}</strong>
                </p>
            </div>
            <div class="mt-4 flex flex-wrap gap-3 border-t border-[#f0e6c8] pt-4">
                @foreach($items as $item)
                <a href="{{ route('products.show', $item['slug']) }}" class="size-20 overflow-hidden rounded-2xl bg-[#f9f6f3]" title="{{ $item['name'] }}">
                    @if($item['image'])
                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
                    @else
                    <x-jewelry-placeholder />
                    @endif
                </a>
                @endforeach
            </div>
        </section>
        </div>

        {{-- Invoice --}}
        <x-invoice-summary :quote="$quote" :packaging="$quote['packaging']">
            <button form="payment-form" type="submit" @disabled(empty($gateways)) class="press flex h-12 w-full items-center justify-center rounded-2xl bg-[#111] text-sm font-medium text-white hover:bg-[#2b2620] disabled:cursor-not-allowed disabled:opacity-40">پرداخت</button>
        </x-invoice-summary>
    </div>
</div>

<script>
(function () {
    const gift = document.getElementById('is-gift');
    const message = document.getElementById('gift-message');
    const packagingCost = {{ (int) $packagingCost }};
    const row = document.querySelector('[data-invoice-row="packaging"]');
    const total = document.querySelector('[data-invoice="total"]');
    const baseTotal = {{ (int) $quote['total'] }} - {{ (int) $quote['packaging'] }};
    const format = (value) => new Intl.NumberFormat('en-US').format(value) + ' تومان';

    function sync() {
        message.classList.toggle('hidden', !gift.checked);
        const extra = gift.checked ? packagingCost : 0;
        if (row) {
            row.classList.toggle('hidden', extra === 0);
            row.querySelector('dd').textContent = format(extra);
        }
        total.textContent = format(baseTotal + extra);
    }
    gift.addEventListener('change', sync);
    sync();
})();
</script>
@endsection
