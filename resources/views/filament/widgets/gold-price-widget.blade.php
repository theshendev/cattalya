<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">قیمت لحظه‌ای طلا</x-slot>

        @php $goldPrice = $this->getGoldPrice(); @endphp

        @if($goldPrice)
            <div class="cat-gold">
                <p class="cat-gold__label">قیمت هر گرم طلای ۱۸ عیار</p>
                <p class="cat-gold__price">{{ number_format($goldPrice->price_per_gram) }} <span>تومن</span></p>
                <p class="cat-gold__meta">
                    {{ $goldPrice->source === 'api' ? 'به‌روزرسانی خودکار' : 'ثبت دستی' }}
                    · {{ ($goldPrice->fetched_at ?? $goldPrice->created_at)->diffForHumans() }}
                </p>
                <x-filament::button
                    color="primary"
                    size="sm"
                    icon="heroicon-o-pencil-square"
                    tag="a"
                    href="{{ route('filament.admin.resources.gold-prices.create') }}"
                >
                    ثبت قیمت جدید
                </x-filament::button>
            </div>
        @else
            <p class="text-gray-500">هنوز قیمتی ثبت نشده.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
