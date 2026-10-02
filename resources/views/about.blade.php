@extends('layouts.app')

@section('title', 'درباره ما')

@section('content')
<section class="px-4 pt-5 md:px-20 md:pt-5">
    <div class="relative overflow-hidden rounded-[24px] bg-[#213738] px-6 pb-5 pt-7 text-[#fffde9] md:h-[405px] md:px-0 md:py-0">
        <img src="/images/cattalya-emblem.png" alt="نشان کاتالیا" class="mx-auto h-auto w-[190px] md:absolute md:-right-10 md:top-1/2 md:w-[600px] md:-translate-y-1/2">
        <div class="relative z-10 mt-1 text-center md:absolute md:left-[9%] md:top-1/2 md:mt-0 md:w-[44%] md:-translate-y-1/2 md:text-right">
            <h1 class="text-[26px] font-bold md:text-[28px]">کاتالیا یک خانواده ست</h1>
            <p class="mt-3 text-[14px] leading-6 md:text-[14px] md:leading-6">
                کاتالیا با هدف ساختن تجربه‌هایی ساده، کاربردی و متفاوت شکل گرفته است.<br>
                ما با ترکیب طراحی، تکنولوژی و شناخت نیازهای کاربران، محصولاتی می‌سازیم که استفاده از آن‌ها فقط آسان نباشد، بلکه حس خوبی هم ایجاد کند.<br>
                در کاتالیا همیشه در حال یادگیری و بهتر شدن هستیم؛ چون باور داریم بهترین تجربه‌ها از توجه به جزئیات و شناخت واقعی کاربران ساخته می‌شوند.
            </p>
        </div>
    </div>
</section>

<section class="px-5 pt-10 md:px-20 md:pt-5">
    <div class="rounded-[24px] bg-white px-5 py-7 text-right md:px-8 md:py-6">
        <h2 class="mb-3 text-xl font-bold md:text-2xl">درباره ما</h2>
        <div class="space-y-2 text-[13px] leading-[22px] text-[#6b6154] md:text-sm md:leading-6">
            <p>کاتالیا با یک ایده ساده شکل گرفت: ساختن تجربه‌هایی که زندگی روزمره را کمی ساده‌تر، جذاب‌تر و لذت‌بخش‌تر کنند.<br>ما باور داریم یک محصول خوب فقط مجموعه‌ای از قابلیت‌ها نیست. چیزی که یک محصول را ماندگار می‌کند، تجربه‌ای است که برای کاربر می‌سازد؛ از اولین برخورد و نحوه استفاده گرفته تا کوچک‌ترین جزئیاتی که باعث می‌شوند کار کردن با آن حس خوبی داشته باشد.</p>
            <p>در کاتالیا تلاش می‌کنیم با ترکیب طراحی، تکنولوژی و شناخت دقیق نیازهای کاربران، محصولاتی بسازیم که هم کاربردی باشند و هم تجربه‌ای متفاوت ارائه دهند. برای ما سادگی به معنی کم کردن قابلیت‌ها نیست؛ یعنی پیدا کردن ساده‌ترین و بهترین راه برای حل یک مسئله.</p>
            <p>ما همیشه در حال یادگیری، آزمایش و بهتر شدن هستیم. به بازخورد کاربران گوش می‌دهیم، ایده‌های جدید را امتحان می‌کنیم و هر روز تلاش می‌کنیم قدم بعدی را بهتر از قدم قبلی برداریم.</p>
            <p>کاتالیا برای ما فقط یک برند نیست؛ مسیری است برای ساختن تجربه‌هایی که ارزش استفاده کردن، برگشتن و به اشتراک گذاشتن داشته باشند.</p>
        </div>
    </div>
</section>

<section class="px-4 pb-4 pt-6 md:px-20 md:pb-0 md:pt-8">
    <div class="grid grid-cols-2 gap-x-2 gap-y-5 border-[#eadfbe] md:grid-cols-4 md:border-b md:pb-5">
        @foreach([
            ['icon' => 'shield', 'title' => 'ضمانت اصالت کالا', 'sub' => 'ارائه فاکتور رسمی و قابل استناد'],
            ['icon' => 'truck', 'title' => 'ارسال رایگان', 'sub' => 'با خرید بالای ۱۲ میلیون تومان'],
            ['icon' => 'routing', 'title' => 'ارسال سراسری', 'sub' => 'پوشش کامل تمام ایران'],
            ['icon' => 'gift', 'title' => 'بسته‌بندی هدیه‌ای', 'sub' => 'ارسال با بسته‌بندی مناسب'],
        ] as $feature)
            <div class="flex min-h-[92px] items-center gap-3 rounded-2xl px-3 md:justify-center md:border-l md:border-[#eadfbe] md:last:border-0">
                <img src="/icons/{{ $feature['icon'] }}.svg" alt="" class="h-9 w-9 shrink-0">
                <div>
                    <p class="text-sm font-bold">{{ $feature['title'] }}</p>
                    <p class="mt-1 text-[11px] text-[#7c7165]">{{ $feature['sub'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endsection
