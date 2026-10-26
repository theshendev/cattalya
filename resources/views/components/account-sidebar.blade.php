@props(['active'])

@php
    $links = [
        'profile' => ['اطلاعات شخصی', route('profile.show'), '/icons/user.svg'],
        'wishlist' => ['علاقه‌مندی‌ها', route('wishlist.index'), '/icons/heart.svg'],
        'orders' => ['سفارش‌ها', route('orders.index'), '/icons/cart.svg'],
    ];
@endphp

<aside class="w-full shrink-0 rounded-3xl border border-[#f0e6c8] bg-white p-5 lg:w-[325px]">
    <div class="flex items-center gap-3 border-b border-[#f0e6c8] pb-5">
        <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#f3efe6]">
            <img src="/icons/user.svg" alt="" class="size-6 opacity-60">
        </div>
        <div class="min-w-0 text-right">
            <p class="truncate text-sm font-bold text-[#2b2620]">{{ auth()->user()->fullName() }}</p>
            <p class="mt-1 text-xs text-[#a8a096]" dir="ltr">{{ auth()->user()->phone }}</p>
        </div>
    </div>

    <nav class="mt-4 flex flex-col gap-2" aria-label="حساب کاربری">
        @foreach($links as $key => [$label, $url, $icon])
        <a href="{{ $url }}" @if($active === $key) aria-current="page" @endif
           class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium transition-colors {{ $active === $key ? 'bg-[#fdf1c9] text-[#8a6300]' : 'text-[#6b6154] hover:bg-[#f9f6f3] hover:text-[#8a6300]' }}">
            <span>{{ $label }}</span>
            <img src="{{ $icon }}" alt="" class="size-5">
        </a>
        @endforeach
    </nav>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 border-t border-[#f0e6c8] pt-4">
        @csrf
        <button type="submit" class="w-full rounded-2xl px-4 py-2 text-right text-sm text-red-500 hover:bg-[#fbeaea]">خروج از حساب</button>
    </form>
</aside>
