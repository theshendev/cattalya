@extends('layouts.app')
@section('title', 'پرداخت')
@section('hide-mobile-nav', '1')

@section('mobile-header')
<a href="{{ route('checkout.index') }}" class="flex items-center gap-3 text-base text-[#2b2620]" aria-label="بازگشت به اطلاعات گیرنده">
    <svg class="size-6 text-[#6b6154]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 12h16m-6-6 6 6-6 6"/></svg>
    پرداخت
</a>
@endsection

@section('content')
@php
    $cardIcon = fn (bool $isActive) => '<svg class="size-14 shrink-0" viewBox="0 0 56 56" fill="none" aria-hidden="true"><rect x="12" y="10" width="34" height="24" rx="4" transform="rotate(-35 12 10)" fill="'.($isActive ? '#e8d49a' : '#ddd8d0').'"/><rect x="8" y="16" width="34" height="24" rx="4" transform="rotate(-35 8 16)" fill="'.($isActive ? '#b08000' : '#a8a096').'"/><path d="m20 34 6-4M26 36l3-2" stroke="#fff" stroke-width="2" stroke-linecap="round"/><path d="M10 47h34" stroke="'.($isActive ? '#b08000' : '#a8a096').'" stroke-width="3" stroke-linecap="round"/></svg>';
@endphp
<div class="px-4 py-4 md:px-20 md:py-8">
    <x-checkout-stepper :step="3" />

    <form id="payment-form" method="POST" action="{{ route('checkout.store') }}">@csrf</form>

    <div class="mt-5 grid grid-cols-1 items-start gap-5 lg:grid-cols-[1fr_325px]">

        <div class="flex min-w-0 flex-col gap-5">

            {{-- Payment method --}}
            <section class="rounded-3xl bg-white p-5 md:px-6 md:py-6">
                <h1 class="text-lg font-bold text-[#2b2620] md:text-xl">روش پرداخت</h1>

                @error('gateway')<p class="mt-3 rounded-xl bg-[#fbeaea] px-4 py-3 text-sm text-red-600">{{ $message }}</p>@enderror
                @error('payment')<p class="mt-3 rounded-xl bg-[#fbeaea] px-4 py-3 text-sm text-red-600">{{ $message }}</p>@enderror

                @if(empty($gateways))
                <p class="mt-4 text-sm text-red-600">در حال حاضر هیچ درگاه پرداختی فعال نیست.</p>
                @endif

                <div class="mt-3 divide-y divide-[#ede6d3]">
                    @foreach($gateways as $value => $label)
                    <label class="flex cursor-pointer items-center gap-4 py-5">
                        <input form="payment-form" type="radio" name="gateway" value="{{ $value }}" class="size-6 shrink-0 cursor-pointer appearance-none rounded-full border-2 border-[#d9d4cc] checked:border-[#b08000] checked:bg-[radial-gradient(circle,#b08000_0_45%,transparent_50%)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#c9a227]"
                               @checked(old('gateway', array_key_first($gateways)) === $value)>
                        {!! $cardIcon(true) !!}
                        <span class="text-right">
                            <span class="block text-base font-bold text-[#2b2620] md:text-lg">{{ $label }}</span>
                            <span class="mt-1 block text-xs text-[#6b6154] md:text-sm">{{ config("gateways.drivers.{$value}.description", 'پرداخت آنلاین') }}</span>
                        </span>
                    </label>
                    @endforeach
                    @foreach($upcomingGateways as $value => $label)
                    <div class="flex items-center gap-4 py-5 opacity-80" aria-disabled="true">
                        <span class="size-6 shrink-0 rounded-full border-2 border-[#ede6d3]" aria-hidden="true"></span>
                        {!! $cardIcon(false) !!}
                        <span class="text-right">
                            <span class="flex items-center gap-2 text-base font-bold text-[#a8a096] md:text-lg">
                                {{ $label }}
                                <span class="rounded-full bg-[#f3efe8] px-3 py-1 text-xs font-normal text-[#a8a096]">بزودی</span>
                            </span>
                            <span class="mt-1 block text-xs text-[#a8a096] md:text-sm">{{ config("gateways.drivers.{$value}.description") }}</span>
                        </span>
                    </div>
                    @endforeach
                </div>
            </section>

            {{-- Gift --}}
            <section class="rounded-3xl bg-white p-5 md:px-6">
                <label class="flex cursor-pointer items-start justify-between gap-4 md:items-center">
                    <span class="flex flex-col gap-3 text-right md:flex-row md:items-center">
                        <span class="flex items-center gap-2 text-base font-bold text-[#2b2620] md:text-lg">
                            <img src="/icons/gift.svg" alt="" class="size-6">
                            ارسال به عنوان هدیه
                        </span>
                        <span class="text-xs leading-6 text-[#6b6154] md:text-sm">(ارسال با بسته بندی مخصوص هدیه و همچنین امکان ارسال متن نوشته شده روی کارت هدیه مخصوص)</span>
                    </span>
                    <span class="relative inline-flex shrink-0 items-center">
                        <input form="payment-form" type="checkbox" name="is_gift" value="1" id="is-gift" class="peer sr-only" @checked(old('is_gift'))>
                        <span class="h-8 w-16 rounded-full bg-[#d9d4cb] transition-colors peer-checked:bg-[#b08000] peer-focus-visible:ring-2 peer-focus-visible:ring-[#8a6300]/40"></span>
                        <span class="absolute left-1 top-1 size-6 rounded-full bg-white shadow transition-transform peer-checked:translate-x-8"></span>
                    </span>
                </label>
                <textarea form="payment-form" name="gift_message" id="gift-message" rows="1" maxlength="300"
                          class="mt-5 hidden h-14 w-full resize-none rounded-2xl border border-[#f0e6c8] px-5 py-4 text-right text-sm outline-none placeholder:text-[#a8a096] focus:border-[#dfc47d] md:text-base"
                          placeholder="متن مورد نظر خود را بنویسید ...">{{ old('gift_message') }}</textarea>
            </section>

            {{-- Discount code --}}
            <section class="rounded-3xl bg-white p-5 md:px-6">
                <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                    <h2 class="flex items-center gap-2 text-base font-bold text-[#2b2620] md:text-lg">
                        <svg class="size-7 text-[#a8a096]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.3" d="M6 3h12a1 1 0 0 1 1 1v17l-3-2-2 2-2-2-2 2-2-2-3 2V4a1 1 0 0 1 1-1Zm3 11 6-6m-5.5.5h.01m5 5h.01"/></svg>
                        کدتخفیف
                    </h2>
                    @if($quote['coupon'])
                    <form method="POST" action="{{ route('checkout.coupon.remove') }}" class="flex items-center gap-3">
                        @csrf @method('DELETE')
                        <span class="rounded-xl bg-[#e6f6ec] px-4 py-2 text-sm font-medium text-[#1a7f3c]" dir="ltr">{{ $quote['coupon']->code }}</span>
                        <button type="submit" class="text-xs text-red-500 hover:underline">حذف کد</button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('checkout.coupon') }}" class="flex flex-col gap-4 md:flex-row md:items-center">
                        @csrf
                        <input type="text" name="code" value="{{ old('code') }}" dir="ltr" placeholder="کد تخفیف" aria-label="کد تخفیف"
                               class="h-12 w-full rounded-2xl border border-[#f0e6c8] px-5 text-right text-base font-bold uppercase text-[#2b2620] outline-none placeholder:font-normal placeholder:text-[#a8a096] focus:border-[#dfc47d] md:w-[330px]">
                        <button type="submit" class="press h-12 rounded-2xl border border-[#111] text-base text-[#2b2620] md:w-[136px]">ثبت</button>
                    </form>
                    @endif
                </div>
                @error('code')<p class="mt-3 text-sm text-red-500">{{ $message }}</p>@enderror
            </section>

            {{-- Shipping address + items --}}
            <section class="rounded-3xl bg-white p-5 md:px-6">
                <div class="flex flex-col gap-2 md:flex-row-reverse md:items-center md:justify-between md:gap-6">
                    <a href="{{ route('checkout.index') }}" class="order-first shrink-0 self-end text-sm text-[#1f7a8c] hover:underline md:order-none md:self-auto">تغییر آدرس</a>
                    <p class="text-right text-sm leading-7 text-[#6b6154] md:text-base">
                        ارسال به:
                        <strong class="font-bold text-[#2b2620]">{{ $address->title ? $address->title.' : ' : '' }}{{ $address->fullAddress() }}</strong>
                    </p>
                </div>
                <div class="mt-5 flex flex-wrap gap-4 border-t border-[#ede6d3] pt-5">
                    @foreach($items as $item)
                    <a href="{{ route('products.show', $item['slug']) }}" class="size-20 overflow-hidden rounded-2xl bg-[#f5f5f5]" title="{{ $item['name'] }}">
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
    const baseTotal = {{ (int) $quote['total'] }} - {{ (int) $quote['packaging'] }};
    const format = (value) => new Intl.NumberFormat('en-US').format(value) + ' تومان';

    function sync() {
        message.classList.toggle('hidden', !gift.checked);
        const extra = gift.checked ? packagingCost : 0;
        document.querySelectorAll('[data-invoice-row="packaging"]').forEach((row) => {
            row.classList.toggle('hidden', extra === 0);
            row.querySelector('dd').textContent = format(extra);
        });
        document.querySelectorAll('[data-invoice="total"]').forEach((total) => total.textContent = format(baseTotal + extra));
    }
    gift.addEventListener('change', sync);
    sync();
})();
</script>
@endsection
