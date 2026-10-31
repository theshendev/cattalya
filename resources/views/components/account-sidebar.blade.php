@props(['active', 'showOnMobile' => false])

@php
    $links = [
        'profile' => ['اطلاعات شخصی', route('profile.show', ['details' => 1]), 'M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm-4 5.5c.7-1.8 2.2-2.7 4-2.7s3.3.9 4 2.7'],
        'wishlist' => ['علاقه‌مندی‌ها', route('wishlist.index'), 'M12 20s-7.5-4.6-9.2-9.3C1.7 7.5 3.9 4.5 7.1 4.5c2 0 3.6 1.1 4.9 2.9 1.3-1.8 2.9-2.9 4.9-2.9 3.2 0 5.4 3 4.3 6.2C19.5 15.4 12 20 12 20Z'],
        'orders' => ['سفارش‌ها', route('orders.index'), 'M6 9h12l-1 11H7L6 9Zm3 0V7a3 3 0 0 1 6 0v2'],
    ];
@endphp

<aside class="{{ $showOnMobile ? '' : 'max-lg:hidden' }} w-full shrink-0 bg-white max-lg:-mx-4 max-lg:w-[calc(100%+2rem)] max-lg:px-5 max-lg:pb-8 max-lg:pt-6 lg:w-[325px] lg:rounded-3xl lg:border lg:border-[#f0e6c8] lg:p-5">
    <div class="flex items-center gap-4 border-b border-[#ede6d3] pb-5 lg:pt-3">
        <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#f3efe6] text-[#6b6154]">
            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5" stroke-width="1.4"/><path stroke-linecap="round" stroke-width="1.4" d="M5 20c1-3.5 3.8-5.5 7-5.5s6 2 7 5.5"/></svg>
        </div>
        <p class="min-w-0 truncate text-right text-lg font-bold text-[#2b2620]">{{ auth()->user()->fullName() ?: auth()->user()->phone }}</p>
    </div>

    <nav class="mt-4 flex flex-col gap-2" aria-label="حساب کاربری">
        @foreach($links as $key => [$label, $url, $icon])
        @php $isActive = $active === $key; @endphp
        <a href="{{ $url }}" @if($isActive) aria-current="page" @endif
           class="flex items-center gap-3 rounded-2xl px-6 py-4 text-lg transition-colors {{ $isActive ? 'lg:bg-[#fdf1c9] lg:font-bold lg:text-[#8a6300]' : 'text-[#6b6154] hover:text-[#8a6300]' }} max-lg:px-2 max-lg:text-[#6b6154]">
            <svg class="size-6 shrink-0 {{ $isActive ? 'lg:fill-current lg:text-[#c9a227]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="{{ $icon }}"/></svg>
            <span>{{ $label }}</span>
        </a>
        @endforeach
    </nav>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 border-t border-[#ede6d3] pt-4">
        @csrf
        <button type="submit" class="flex w-full items-center gap-3 rounded-2xl px-6 py-3 text-right text-base text-[#c43c3c] hover:bg-[#fbeaea] max-lg:px-2">
            <svg class="size-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4M10 8l-4 4 4 4m-4-4h10"/></svg>
            خروج از حساب
        </button>
    </form>
</aside>
