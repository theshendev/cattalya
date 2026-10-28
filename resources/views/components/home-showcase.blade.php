@props(['heroBanner' => null])

{{-- Hero Banner --}}
<section class="px-4 md:px-20 pt-5 md:pt-20">
    <div class="relative rounded-2xl md:rounded-3xl overflow-hidden h-[320px] sm:h-72 md:h-[405px] bg-[#263a3b] group animate-scale-in">
        @if($heroBanner?->getFirstMediaUrl('image'))
        <img src="{{ $heroBanner->getFirstMediaUrl('image') }}"
             alt="{{ $heroBanner->title }}"
             class="absolute inset-0 w-full h-full object-cover opacity-70 scale-105 group-hover:scale-100 transition-transform duration-[1.2s] ease-out">
        @else
        <img src="/images/cattalya-emblem.png" alt="" aria-hidden="true"
             class="absolute left-1/2 top-[164px] h-[215px] w-auto max-w-none -translate-x-1/2 select-none object-contain sm:top-auto sm:-bottom-6 sm:left-0 sm:h-[115%] sm:translate-x-0 md:-left-[66px] md:-top-[71px] md:bottom-auto md:h-[140%]">
        @endif
        <div class="absolute inset-0 bg-gradient-to-l from-black/35 via-transparent to-black/10"></div>
        <div class="absolute inset-0 flex items-start pt-14 sm:items-center sm:pt-0">
            <div class="mx-auto px-4 text-center text-[#fcfde3] sm:ml-auto sm:mr-4 sm:max-w-xs sm:text-right md:mr-16 md:max-w-xl md:px-0">
                <h1 class="text-3xl sm:text-4xl md:text-[64px] font-bold leading-tight mb-2 md:mb-4 animate-fade-up" style="animation-delay:.1s">
                    {{ $heroBanner?->title ?? 'به کاتالیا خوش اومدید' }}
                </h1>
                <p class="text-xs sm:text-base md:text-xl font-medium opacity-90 animate-fade-up" style="animation-delay:.25s">
                    {{ $heroBanner?->subtitle ?? 'بهترین زیورآلات طلا با ضمانت اصالت' }}
                </p>
                @if($heroBanner?->link)
                <a href="{{ $heroBanner->link }}"
                   class="press inline-block mt-4 md:mt-6 bg-[#8a6300] text-white px-6 md:px-8 py-2.5 md:py-3 rounded-2xl text-sm font-medium hover:bg-[#6b4d00] hover:shadow-xl hover:shadow-black/20 animate-fade-up" style="animation-delay:.4s">
                    مشاهده محصولات
                </a>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Service Features Bar: desktop runs right to left in this order; mobile moves the first two to the second row --}}
<section class="px-4 md:px-20 mt-6 md:mt-[60px]">
    <div data-reveal-stagger="80" class="grid grid-cols-2 gap-4 md:flex md:items-center md:justify-between md:border-b md:border-[#f0e6c8] md:px-5 md:pb-5">
        @foreach([
            ['icon' => 'shield',  'title' => 'ضمانت اصالت کالا',          'sub' => 'ارائه فاکتور رسمی و قابل استناد',      'mobileTitle' => 'ضمانت اصالت',       'mobileSub' => 'ارائه فاکتور معتبر'],
            ['icon' => 'truck',   'title' => 'ارسال رایگان',              'sub' => 'با خرید بالای ۱۲ میلیون تومان',        'mobileTitle' => 'ارسال رایگان',      'mobileSub' => 'با خرید بالای ۱۲ میلیون تومان'],
            ['icon' => 'routing', 'title' => 'ارسال به تمامی نقاط ایران',  'sub' => 'امکان ثبت سفارش از تمامی نقاط ایران', 'mobileTitle' => 'ارسال سراسری',      'mobileSub' => 'پوشش کامل تمام ایران'],
            ['icon' => 'gift',    'title' => 'بسته‌بندی هدیه‌ای',        'sub' => 'ارسال با بسته‌بندی مناسب هدیه',        'mobileTitle' => 'بسته‌بندی هدیه‌ای', 'mobileSub' => 'ارسال با بسته‌بندی مناسب'],
        ] as $feature)
        @if(! $loop->first)<span class="hidden h-10 w-px shrink-0 bg-[#e3d2a0] md:block" aria-hidden="true"></span>@endif
        {{-- icon first in DOM → RTL flex places it on the RIGHT --}}
        <div data-reveal class="group flex flex-col items-center gap-2 rounded-2xl bg-[#f9f6f3] px-2 py-3 text-center md:h-20 md:min-w-[267px] md:flex-row md:whitespace-nowrap md:gap-4 md:px-5 md:py-0 md:text-right {{ $loop->index < 2 ? 'order-last md:order-none' : '' }}">
            <img src="/icons/{{ $feature['icon'] }}.svg" alt="" class="size-10 shrink-0 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
            <div class="flex-1">
                <p class="text-sm font-bold text-[#2b2620] md:text-base"><span class="md:hidden">{{ $feature['mobileTitle'] }}</span><span class="hidden md:inline">{{ $feature['title'] }}</span></p>
                <p class="mt-1 text-xs text-[#6b6154]"><span class="md:hidden">{{ $feature['mobileSub'] }}</span><span class="hidden md:inline">{{ $feature['sub'] }}</span></p>
            </div>
        </div>
        @endforeach
    </div>
</section>
