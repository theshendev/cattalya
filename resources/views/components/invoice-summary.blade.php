@props(['quote', 'sticky' => true, 'showShipping' => true, 'packaging' => null])

@php
    $rows = [
        ['key' => 'subtotal', 'label' => 'جمع فاکتور', 'value' => $quote['subtotal'], 'visible' => true, 'tone' => 'text-[#2b2620]', 'labelTone' => 'text-[#a8a096]'],
        ['key' => 'discount', 'label' => 'تخفیف', 'value' => $quote['discount'], 'visible' => $quote['discount'] > 0, 'tone' => 'text-[#1f7a8c]', 'labelTone' => 'text-[#1f7a8c]'],
        ['key' => 'shipping', 'label' => 'هزینه ارسال', 'value' => $quote['shipping'], 'visible' => $showShipping, 'tone' => 'text-[#2b2620]', 'labelTone' => 'text-[#a8a096]'],
        ['key' => 'packaging', 'label' => 'هزینه بسته بندی', 'value' => $quote['packaging'], 'visible' => $packaging !== null, 'tone' => 'text-[#2b2620]', 'labelTone' => 'text-[#a8a096]'],
    ];
    $formatAmount = fn (float $amount, string $key) => $key === 'shipping' && $amount <= 0 ? 'رایگان' : number_format($amount).' تومان';
@endphp

<aside data-reveal class="hidden h-fit rounded-3xl border border-[#f0e6c8] bg-white p-5 lg:block {{ $sticky ? 'lg:sticky lg:top-24' : '' }}">
    <h2 class="text-right text-sm text-[#a8a096]">فاکتور خرید</h2>

    <dl class="mt-2 flex flex-col text-sm">
        @foreach($rows as $row)
        @continue(! $row['visible'])
        <div class="flex items-center justify-between gap-3 border-b border-[#ede6d3] py-5 {{ $row['key'] === 'packaging' && $quote['packaging'] <= 0 ? 'hidden' : '' }}" @if($row['key'] === 'packaging') data-invoice-row="packaging" @endif>
            <dt class="{{ $row['labelTone'] }}">{{ $row['label'] }}</dt>
            <dd class="{{ $row['tone'] }}" data-invoice="{{ $row['key'] }}">{{ $formatAmount($row['value'], $row['key']) }}</dd>
        </div>
        @endforeach
        <div class="flex items-center justify-between gap-3 pt-6">
            <dt class="text-[#a8a096]">قیمت نهایی:</dt>
            <dd class="text-xl font-bold text-[#2b2620]" data-invoice="total">{{ number_format($quote['total']) }} تومان</dd>
        </div>
    </dl>

    @if($slot->isNotEmpty())
    <div class="mt-8">
        {{ $slot }}
    </div>
    @endif
</aside>

@push('mobile-dock')
<div data-invoice-dock class="rounded-t-3xl border-t border-[#f0e6c8] bg-white px-4 pb-4 pt-3 shadow-[0_-8px_24px_rgb(0_0_0_/_0.06)]">
    <button type="button" data-invoice-dock-toggle aria-expanded="false" class="mx-auto flex items-center gap-2 py-1 text-xs text-[#b08000]">
        <span data-invoice-dock-label>مشاهده کامل فاکتور</span>
        <svg class="size-4 transition-transform" data-invoice-dock-arrow fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 19V5m-6 6 6-6 6 6"/></svg>
    </button>

    <dl class="text-sm">
        <div data-invoice-dock-rows class="hidden">
            @foreach($rows as $row)
            @continue(! $row['visible'])
            <div class="flex items-center justify-between gap-3 border-b border-[#ede6d3] py-3.5 {{ $row['key'] === 'packaging' && $quote['packaging'] <= 0 ? 'hidden' : '' }}" @if($row['key'] === 'packaging') data-invoice-row="packaging" @endif>
                <dt class="{{ $row['labelTone'] }}">{{ $row['label'] }}</dt>
                <dd class="{{ $row['tone'] }}" data-invoice="{{ $row['key'] }}">{{ $formatAmount($row['value'], $row['key']) }}</dd>
            </div>
            @endforeach
        </div>
        <div class="flex items-center justify-between gap-3 py-3">
            <dt class="text-[#a8a096]">قیمت نهایی:</dt>
            <dd class="text-lg font-bold text-[#2b2620]" data-invoice="total">{{ number_format($quote['total']) }} تومان</dd>
        </div>
    </dl>

    @if($slot->isNotEmpty())
    <div class="border-t border-[#ede6d3] pt-3">
        {{ $slot }}
    </div>
    @endif
</div>
@endpush

@once
@push('mobile-dock')
<script>
document.querySelectorAll('[data-invoice-dock]').forEach((dock) => {
    const toggle = dock.querySelector('[data-invoice-dock-toggle]');
    toggle.addEventListener('click', () => {
        const isOpen = toggle.getAttribute('aria-expanded') !== 'true';
        toggle.setAttribute('aria-expanded', String(isOpen));
        dock.querySelector('[data-invoice-dock-rows]').classList.toggle('hidden', !isOpen);
        dock.querySelector('[data-invoice-dock-arrow]').classList.toggle('rotate-180', isOpen);
        dock.querySelector('[data-invoice-dock-label]').textContent = isOpen ? 'بستن فاکتور' : 'مشاهده کامل فاکتور';
    });
});
</script>
@endpush
@endonce
