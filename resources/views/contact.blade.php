@extends('layouts.app')

@section('title', 'تماس با ما')

@section('content')
<section class="px-4 pb-3 pt-8 md:px-20 md:pb-0 md:pt-5">
    <div class="mx-auto grid max-w-[1352px] overflow-hidden rounded-[24px] md:min-h-[714px] md:grid-cols-2 md:gap-10 md:bg-white md:p-5">
        <div class="flex flex-col justify-center md:px-10">
            <h1 class="text-[24px] font-bold md:text-2xl">تماس با ما</h1>
            <p class="mb-6 mt-2 text-[14px] leading-7 text-[#6b6154] md:mb-8 md:text-sm">کاتالیا با یک ایده ساده شکل گرفت: ساختن تجربه‌هایی که زندگی روزمره را کمی ساده‌تر، جذاب‌تر و لذت‌بخش‌تر کنند.</p>

            <form action="{{ route('contact.store') }}" method="POST" class="grid gap-5 md:grid-cols-2 md:gap-4">
                @csrf
                <label class="contact-field order-2 md:order-none">
                    <span>نام</span>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="پروانه سادات" autocomplete="given-name" required>
                    @error('first_name')<small>{{ $message }}</small>@enderror
                </label>
                <label class="contact-field order-1 md:order-none">
                    <span>نام خانوادگی</span>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="موسوی" autocomplete="family-name" required>
                    @error('last_name')<small>{{ $message }}</small>@enderror
                </label>
                <label class="contact-field order-4 md:order-none">
                    <span>شماره تماس</span>
                    <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="۰۹۳۶۳۶۷۶۴۰۵" autocomplete="tel" inputmode="numeric" required>
                    @error('phone')<small>{{ $message }}</small>@enderror
                </label>
                <label class="contact-field order-3 md:order-none">
                    <span>ایمیل</span>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Mohamad.shendabadi@gmail.com" autocomplete="email" dir="ltr" required>
                    @error('email')<small>{{ $message }}</small>@enderror
                </label>
                <label class="contact-field order-5 md:order-none md:col-span-2">
                    <span>توضیحات</span>
                    <textarea name="description" rows="5" placeholder="اینجا بنویسید ...">{{ old('description') }}</textarea>
                    @error('description')<small>{{ $message }}</small>@enderror
                </label>
                <div class="order-6 mt-0 border-t border-[#eadfbe] pt-4 md:order-none md:col-span-2 md:mt-12">
                    <button type="submit" class="press h-12 w-full rounded-2xl bg-[#101010] text-base text-white md:w-60">ثبت پیام</button>
                </div>
            </form>
        </div>

        <aside class="hidden items-center justify-center rounded-[20px] bg-[#213738] text-center text-[#fffde9] md:flex">
            <div class="flex flex-col items-center">
                <img src="/images/cattalya-emblem.png" alt="نشان کاتالیا" class="w-[360px] max-w-[80%]">
                <h2 class="mt-1 text-[26px] font-bold">کاتالیا یک خانواده ست</h2>
                <p class="mt-3 text-sm">سوالی دارید؟ با کاتالیا در ارتباط باشید.</p>
                <div class="mt-12 flex gap-3">
                    <a href="mailto:info@cattalya.com" class="social-button"><img src="/icons/sms.svg" alt="ایمیل"></a>
                    <a href="https://wa.me/989363676405" class="social-button"><img src="/icons/whatsapp.svg" alt="واتساپ"></a>
                    <a href="https://instagram.com/cattalya" class="social-button"><img src="/icons/instagram.svg" alt="اینستاگرام"></a>
                </div>
            </div>
        </aside>
    </div>
</section>
@endsection
