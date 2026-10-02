<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">قیمت لحظه‌ای طلا</x-slot>

        @php $goldPrice = $this->getGoldPrice(); @endphp

        @if($goldPrice)
            <div class="flex items-center gap-6 py-2">
                <div>
                    <p class="text-sm text-gray-500">قیمت هر گرم طلای ۱۸ عیار</p>
                    <p class="text-3xl font-bold text-amber-600">{{ number_format($goldPrice->price_per_gram) }} <span class="text-base font-normal">تومن</span></p>
                </div>
                <div class="text-sm text-gray-400">
                    <p>منبع: {{ $goldPrice->source === 'api' ? 'API خودکار' : 'ورود دستی' }}</p>
                    <p>آخرین بروزرسانی: {{ $goldPrice->fetched_at?->diffForHumans() ?? $goldPrice->created_at->diffForHumans() }}</p>
                </div>
                <div class="mr-auto">
                    <x-filament::button
                        wire:click="$dispatch('open-modal', { id: 'fetch-gold-price' })"
                        color="warning"
                        size="sm"
                        tag="a"
                        href="{{ route('filament.admin.resources.gold-prices.create') }}"
                    >
                        بروزرسانی دستی
                    </x-filament::button>
                </div>
            </div>
        @else
            <p class="text-gray-500">هنوز قیمتی ثبت نشده.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
