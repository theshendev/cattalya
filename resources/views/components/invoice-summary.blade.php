@props(['quote', 'sticky' => true, 'showShipping' => true, 'packaging' => null])

<aside data-reveal class="h-fit rounded-3xl border border-[#f0e6c8] bg-white p-5 {{ $sticky ? 'lg:sticky lg:top-24' : '' }}">
    <h2 class="text-right text-sm text-[#a8a096]">فاکتور خرید</h2>

    <dl class="mt-4 flex flex-col text-sm">
        <div class="flex items-center justify-between gap-3 py-3">
            <dt class="text-[#a8a096]">جمع فاکتور</dt>
            <dd class="text-[#2b2620]" data-invoice="subtotal" data-value="{{ (int) $quote['subtotal'] }}">{{ number_format($quote['subtotal']) }} تومان</dd>
        </div>
        @if($quote['discount'] > 0)
        <div class="flex items-center justify-between gap-3 border-t border-[#f0e6c8] py-3">
            <dt class="text-[#1f7a8c]">تخفیف</dt>
            <dd class="text-[#1f7a8c]" data-invoice="discount" data-value="{{ (int) $quote['discount'] }}">{{ number_format($quote['discount']) }} تومان</dd>
        </div>
        @endif
        @if($showShipping)
        <div class="flex items-center justify-between gap-3 border-t border-[#f0e6c8] py-3">
            <dt class="text-[#a8a096]">هزینه ارسال</dt>
            <dd class="text-[#2b2620]" data-invoice="shipping" data-value="{{ (int) $quote['shipping'] }}">
                {{ $quote['shipping'] > 0 ? number_format($quote['shipping']).' تومان' : 'رایگان' }}
            </dd>
        </div>
        @endif
        @if($packaging !== null)
        <div class="flex items-center justify-between gap-3 border-t border-[#f0e6c8] py-3 {{ $quote['packaging'] > 0 ? '' : 'hidden' }}" data-invoice-row="packaging">
            <dt class="text-[#a8a096]">هزینه بسته بندی</dt>
            <dd class="text-[#2b2620]" data-invoice="packaging" data-value="{{ (int) $quote['packaging'] }}">{{ number_format($quote['packaging']) }} تومان</dd>
        </div>
        @endif
        <div class="flex items-center justify-between gap-3 border-t border-[#f0e6c8] pt-4">
            <dt class="text-[#a8a096]">قیمت نهایی:</dt>
            <dd class="text-lg font-bold text-[#2b2620]" data-invoice="total" data-value="{{ (int) $quote['total'] }}">{{ number_format($quote['total']) }} تومان</dd>
        </div>
    </dl>

    <div class="mt-5">
        {{ $slot }}
    </div>
</aside>
