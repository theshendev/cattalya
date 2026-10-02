@props(['active'])

<aside class="w-full lg:w-[325px] shrink-0 rounded-3xl border border-[#f0e6c8] bg-white p-5">
    <div class="flex items-center gap-3 border-b border-[#f0e6c8] pb-5">
        <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#f9f6f3] text-lg font-bold text-[#8a6300]">
            {{ mb_substr(auth()->user()->name ?: 'ک', 0, 1) }}
        </div>
        <div class="min-w-0 text-right">
            <p class="truncate text-sm font-bold text-[#2b2620]">{{ trim(auth()->user()->name.' '.auth()->user()->last_name) }}</p>
            <p class="mt-1 text-xs text-[#a8a096]" dir="ltr">{{ auth()->user()->phone }}</p>
        </div>
    </div>

    <nav class="mt-4 flex flex-col gap-2" aria-label="حساب کاربری">
        <a href="{{ route('profile.show') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium transition-colors {{ $active === 'profile' ? 'bg-[#fdeee0] text-[#8a6300]' : 'text-[#6b6154] hover:bg-[#f9f6f3] hover:text-[#8a6300]' }}">
            <span>اطلاعات شخصی</span><span aria-hidden="true">‹</span>
        </a>
        <a href="{{ route('wishlist.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium transition-colors {{ $active === 'wishlist' ? 'bg-[#fdeee0] text-[#8a6300]' : 'text-[#6b6154] hover:bg-[#f9f6f3] hover:text-[#8a6300]' }}">
            <span>علاقه‌مندی‌ها</span><span aria-hidden="true">‹</span>
        </a>
        <a href="{{ route('orders.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium transition-colors {{ $active === 'orders' ? 'bg-[#fdeee0] text-[#8a6300]' : 'text-[#6b6154] hover:bg-[#f9f6f3] hover:text-[#8a6300]' }}">
            <span>سفارش‌ها</span><span aria-hidden="true">‹</span>
        </a>
    </nav>
</aside>
