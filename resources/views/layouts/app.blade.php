<!DOCTYPE html>
<html lang="fa" dir="rtl" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'کاتالیا') — فروشگاه زیورآلات طلا</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f8f7f3] font-persian text-[#2b2620]">

@php
    $flashes = collect(['success', 'error', 'warning', 'info', 'status'])
        ->filter(fn ($key) => filled(session($key)))
        ->map(fn ($key) => [
            'type' => $key === 'status' ? 'info' : $key,
            'message' => (string) session($key),
        ])
        ->values()
        ->merge($errors->all() ? [['type' => 'error', 'message' => $errors->first()]] : []);
    $announcement = \App\Models\Announcement::where('is_active', true)->latest()->first();
    $goldPrice = \App\Models\GoldPrice::current();
    $cartCount = count(session('cart', []));
@endphp

@if($flashes->isNotEmpty())
<script type="application/json" id="flash-messages">@json($flashes)</script>
@endif

<header id="site-header" class="sticky top-0 z-50 border-b border-[#f0e6c8] bg-white transition-shadow duration-300">
    <div class="hidden h-[72px] items-center justify-between px-20 md:flex">
        <div class="flex items-center gap-20">
        <a href="{{ route('home') }}" class="shrink-0 transition-transform duration-300 hover:scale-105" aria-label="کاتالیا">
            <x-cattalya-logo />
        </a>

        <nav class="flex items-center gap-8 text-sm text-[#6b6154]">
            <a href="{{ route('home') }}" class="nav-link transition-colors hover:text-[#8a6300] {{ request()->routeIs('home') ? 'font-bold text-[#8a6300]' : '' }}" @if(request()->routeIs('home')) data-active @endif>صفحه اصلی</a>
            <a href="{{ route('products.index') }}" class="nav-link transition-colors hover:text-[#8a6300] {{ request()->routeIs('products.*') ? 'font-bold text-[#8a6300]' : '' }}" @if(request()->routeIs('products.*')) data-active @endif>محصولات</a>
            <a href="{{ route('about') }}" class="nav-link transition-colors hover:text-[#8a6300] {{ request()->routeIs('about') ? 'font-bold text-[#8a6300]' : '' }}" @if(request()->routeIs('about')) data-active @endif>درباره ما</a>
            <a href="{{ route('contact') }}" class="nav-link transition-colors hover:text-[#8a6300] {{ request()->routeIs('contact*') ? 'font-bold text-[#8a6300]' : '' }}" @if(request()->routeIs('contact*')) data-active @endif>تماس با ما</a>
        </nav>
        </div>

        <div class="flex items-center gap-4">
            <a href="{{ route('products.index') }}" class="flex h-10 w-[220px] items-center gap-2 rounded-2xl border border-[#e8d49a] px-4 text-[#a8a096] transition-colors hover:border-[#8a6300]">
                <span class="flex-1 text-right text-xs">جستجو کنید...</span>
                <img src="/icons/search.svg" alt="" class="h-5 w-5">
            </a>
            <span class="h-8 w-px bg-[#ede6d3]"></span>
            <a href="{{ auth()->check() ? route('profile.show') : route('login') }}" class="press flex h-10 w-10 items-center justify-center rounded-2xl border border-[#e3d2a0]" aria-label="حساب کاربری">
                <img src="/icons/user.svg" alt="" class="h-5 w-5">
            </a>
            <a href="{{ route('cart.drawer') }}" class="press relative flex h-10 w-10 items-center justify-center rounded-2xl border border-[#e3d2a0]" aria-label="سبد خرید">
                <img src="/icons/cart.svg" alt="" class="h-5 w-5">
                @if($cartCount > 0)
                    <span id="cart-count" data-count="{{ $cartCount }}" class="absolute -left-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-[#8a6300] text-[10px] text-white">{{ $cartCount }}</span>
                @endif
            </a>
        </div>
    </div>

    @hasSection('mobile-header')
    <div class="flex h-16 items-center justify-between px-4 md:hidden">
        @yield('mobile-header')
    </div>
    @else
    <div class="grid h-16 grid-cols-3 items-center px-4 md:hidden">
        <button id="mobile-menu-btn" aria-expanded="false" aria-controls="mobile-nav" class="press flex h-10 w-10 items-center justify-center justify-self-start rounded-2xl border border-[#dfc47d] bg-white" aria-label="باز کردن منو">
            <svg id="menu-icon-open" class="h-5 w-5 text-[#4d4942]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.6" d="M5 7h14M5 12h14M5 17h14"/></svg>
            <svg id="menu-icon-close" class="hidden h-5 w-5 text-[#4d4942]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.6" d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
        <a href="{{ route('home') }}" class="justify-self-center" aria-label="کاتالیا"><x-cattalya-logo size="sm" /></a>
        <a href="{{ route('products.index') }}" class="press flex h-10 w-10 items-center justify-center justify-self-end rounded-2xl border border-[#dfc47d] bg-white" aria-label="جستجو">
            <img src="/icons/search.svg" alt="" class="h-5 w-5">
        </a>
    </div>
    @endif

    <nav id="mobile-nav" class="max-h-0 overflow-hidden border-[#f0e6c8] bg-white opacity-0 transition-[max-height,opacity] duration-300 md:hidden">
        <div class="flex flex-col gap-1 border-t border-[#f0e6c8] px-5 py-3">
            <a href="{{ route('home') }}" class="py-2 text-sm">صفحه اصلی</a>
            <a href="{{ route('products.index') }}" class="py-2 text-sm">محصولات</a>
            <a href="{{ route('about') }}" class="py-2 text-sm">درباره ما</a>
            <a href="{{ route('contact') }}" class="py-2 text-sm">تماس با ما</a>
        </div>
    </nav>
</header>

<div class="h-10 items-center justify-between gap-3 bg-[#fdf0e4] px-4 text-xs md:flex md:px-20 @hasSection('mobile-header') hidden @else flex @endif">
    <p class="hidden truncate text-[#a66d00] md:block">{{ $announcement?->text ?? 'تمامی محصولات باتوجه به قیمت روز طلا قیمت‌گذاری شده‌اند.' }}</p>
    <p class="mx-auto whitespace-nowrap text-[#a34d06] md:mx-0">
        قیمت لحظه‌ای طلا:
        <strong class="text-sm">{{ number_format($goldPrice?->price_per_gram ?? 18_465_000) }}</strong>
        تومان
    </p>
</div>

<script>
    (() => {
        const button = document.getElementById('mobile-menu-btn');
        const nav = document.getElementById('mobile-nav');
        const openIcon = document.getElementById('menu-icon-open');
        const closeIcon = document.getElementById('menu-icon-close');

        button?.addEventListener('click', () => {
            const isOpen = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', String(!isOpen));
            nav.style.maxHeight = isOpen ? '0px' : `${nav.scrollHeight}px`;
            nav.classList.toggle('opacity-0', isOpen);
            openIcon.classList.toggle('hidden', !isOpen);
            closeIcon.classList.toggle('hidden', isOpen);
        });
    })();
</script>

<main class="page-enter {{ request()->routeIs('products.show') ? '' : 'pb-20 md:pb-0' }}">
    @yield('content')
</main>

@php
    $siteDescription = \App\Models\Setting::get('site_description') ?: 'گالری کاتالیا در سال ۱۴۰۲ فعالیت خودش رو در حوزه فروش زیورآلات طلا شروع کرده.';
    $footerCopyright = \App\Models\Setting::get('footer_copyright') ?: '© ۱۴۰۵ کاتالیا | تمامی حقوق مادی و معنوی این وب‌سایت محفوظ است.';
    $whatsappNumber = preg_replace('/\D+/', '', (string) \App\Models\Setting::get('whatsapp_number'));
    $socialLinks = array_filter([
        'instagram' => \App\Models\Setting::get('instagram_url'),
        'whatsapp' => $whatsappNumber ? 'https://wa.me/'.preg_replace('/^0/', '98', $whatsappNumber) : null,
        'sms' => \App\Models\Setting::get('contact_email') ? 'mailto:'.\App\Models\Setting::get('contact_email') : null,
    ]);
@endphp
<footer class="relative mt-20 border-t border-[#e5e7eb] bg-white">
    <button id="back-to-top" type="button" aria-label="بازگشت به بالا" class="press absolute left-1/2 top-0 flex size-10 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-2xl border border-[#e3d2a0] bg-white">
        <img src="/icons/arrow-up.svg" alt="" class="size-5">
    </button>
    <div class="px-4 pb-24 pt-10 md:px-20 md:pb-5">
        <div class="flex flex-col items-start gap-8 md:flex-row md:items-center md:gap-20">
            <div class="flex flex-col items-start gap-3 text-right md:w-[404px] md:shrink-0">
                <x-cattalya-logo size="sm" />
                <p class="max-w-sm text-sm leading-7 text-[#6b6154]">{{ $siteDescription }}</p>
            </div>
            <span class="hidden h-16 w-px bg-[#f0e6c8] md:block"></span>
            <div class="flex flex-col items-start gap-4 md:flex-1">
                <p class="text-xs text-[#a8a096]">لینک‌ها</p>
                <div class="flex flex-col items-start gap-4 text-sm text-[#6b6154] md:flex-row md:gap-8">
                    <a href="{{ route('home') }}">صفحه اصلی</a>
                    <a href="{{ route('products.index') }}">محصولات</a>
                    <a href="{{ route('about') }}">درباره ما</a>
                    <a href="{{ route('contact') }}">تماس با ما</a>
                </div>
            </div>
            <span class="hidden h-16 w-px bg-[#f0e6c8] md:block"></span>
            <div class="flex flex-col items-start gap-3 md:w-[209px]">
                <p class="text-xs text-[#a8a096]">راه‌های ارتباطی</p>
                <div class="flex gap-3">
                    @forelse($socialLinks as $social => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $social }}" class="press flex h-10 w-10 items-center justify-center rounded-2xl border border-[#e3d2a0]"><img src="/icons/{{ $social }}.svg" alt="" class="h-5 w-5"></a>
                    @empty
                        <a href="{{ route('contact') }}" aria-label="تماس با ما" class="press flex h-10 w-10 items-center justify-center rounded-2xl border border-[#e3d2a0]"><img src="/icons/sms.svg" alt="" class="h-5 w-5"></a>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="mt-8 border-t border-[#ede6d3] pt-5 text-right text-xs">{{ $footerCopyright }}</div>
    </div>
</footer>

@hasSection('mobile-bottom-bar')
@yield('mobile-bottom-bar')
@else
<nav class="fixed inset-x-0 bottom-0 z-40 flex h-16 items-center justify-around border-t border-[#e5dcc7] bg-white md:hidden" aria-label="دسترسی سریع">
    @foreach([
        ['label' => 'خانه', 'url' => route('home'), 'active' => request()->routeIs('home'), 'icon' => 'home'],
        ['label' => 'سبد خرید', 'url' => route('cart.drawer'), 'active' => request()->routeIs('cart.*', 'checkout.*'), 'icon' => 'cart'],
        ['label' => 'حساب', 'url' => auth()->check() ? route('profile.show') : route('login'), 'active' => request()->routeIs('profile.*', 'orders.*', 'wishlist.*', 'login', 'register*', 'otp.*'), 'icon' => 'user'],
    ] as $tab)
    <a href="{{ $tab['url'] }}" aria-label="{{ $tab['label'] }}" @if($tab['active']) aria-current="page" @endif
       class="flex h-10 items-center justify-center gap-1.5 rounded-2xl {{ $tab['active'] ? 'border border-[#c9a227] bg-[#fff6d9] px-4 text-sm font-bold text-[#8a6300]' : 'w-12' }}">
        @if($tab['icon'] === 'home')
        <svg class="h-5 w-5 {{ $tab['active'] ? 'text-[#c9a227]' : '' }}" fill="{{ $tab['active'] ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m3 11 9-7 9 7v8a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>
        @else
        <img src="/icons/{{ $tab['icon'] }}.svg" alt="" class="h-5 w-5">
        @endif
        @if($tab['active'])<span>{{ $tab['label'] }}</span>@endif
    </a>
    @endforeach
</nav>
@endif

</body>
</html>
