@extends('layouts.app')
@section('title', 'ورود / ثبت‌نام')

@section('content')
<div class="fixed inset-0 z-[70] overflow-hidden bg-[#263a3b]" role="dialog" aria-modal="true" aria-labelledby="auth-title">
    <div class="absolute inset-0 opacity-80">
        <div class="absolute -bottom-44 -left-12 size-[520px] rounded-full border-[38px] border-[#c8960c]"></div>
        <div class="absolute bottom-20 left-40 size-32 rotate-45 border-4 border-[#b9f1ff] bg-[#60c4e7] shadow-[0_0_60px_rgb(96_196_231_/_0.8)]"></div>
        <div class="absolute inset-y-0 right-0 flex w-2/3 items-center justify-center text-center text-[#fcfde3]">
            <div><p class="text-4xl font-bold md:text-6xl">درخشش ابدی،</p><p class="mt-4 text-4xl font-bold md:text-6xl">زیبایی جاودان.</p></div>
        </div>
    </div>
    <div class="absolute inset-0 bg-black/45"></div>

    <div class="absolute inset-x-0 bottom-0 rounded-t-[30px] bg-white p-5 shadow-2xl md:left-1/2 md:right-auto md:top-1/2 md:bottom-auto md:w-full md:max-w-lg md:-translate-x-1/2 md:-translate-y-1/2 md:rounded-3xl md:p-7">
        <span class="mx-auto mb-4 block h-1 w-10 rounded-full bg-[#aaa197] md:hidden"></span>
        <div class="mb-6 flex items-start justify-between">
            <div class="text-right">
                <h1 id="auth-title" class="text-xl font-bold">{{ session('otp_sent') ? 'تایید کد' : 'ورود یا ثبت‌نام' }}</h1>
                <p class="mt-2 text-sm text-[#6b6154]">{{ session('otp_sent') ? 'کد تایید ارسال‌شده را وارد کنید.' : 'برای ورود به حساب کاربری شماره تماس خود را وارد کنید.' }}</p>
            </div>
            <a href="{{ route('home') }}" class="flex size-8 items-center justify-center rounded-full border border-[#2b2620] text-lg" aria-label="بستن">×</a>
        </div>

        @if(! session('otp_sent'))
        <form method="POST" action="{{ route('login.otp') }}" class="flex flex-col gap-4">
            @csrf
            <label class="rounded-2xl border border-[#ead69e] px-4 py-2">
                <span class="block text-xs text-[#8b8277]">شماره تماس</span>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="09123456789" dir="ltr" required autocomplete="tel" inputmode="numeric" class="w-full border-0 bg-transparent py-1 text-right text-sm outline-none">
            </label>
            @error('phone')<p class="text-xs text-red-500">{{ $message }}</p>@enderror

            <div class="grid grid-cols-2 gap-2 border-t border-[#f0e6c8] pt-5">
                <a href="{{ route('home') }}" class="press flex h-12 items-center justify-center rounded-2xl border border-[#111]">انصراف</a>
                <button type="submit" class="press h-12 rounded-2xl bg-[#111] text-white">ارسال کد</button>
            </div>
        </form>
        @else
        <form method="POST" action="{{ route('login.verify') }}" class="flex flex-col gap-4">
            @csrf
            <input type="hidden" name="phone" value="{{ session('phone') }}">
            <p class="text-sm text-[#6b6154]">کد ارسال‌شده به <strong dir="ltr" class="text-[#16a6c9]">{{ session('phone') }}</strong></p>
            <label>
                <span class="mb-2 block text-sm text-[#6b6154]">کد تایید</span>
                <input id="otp" type="text" name="otp" maxlength="5" placeholder="_  _  _  _  _" dir="ltr" autofocus required inputmode="numeric" autocomplete="one-time-code" class="w-full rounded-2xl border border-[#ead69e] px-4 py-4 text-center text-2xl font-bold tracking-[.55rem] outline-none focus:border-[#c8960c]">
            </label>
            @error('otp')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
            <div class="flex items-center justify-between text-xs text-[#a8a096]">
                <span>کد تا ۵ دقیقه معتبر است</span>
                <button type="submit" form="resend-otp" class="text-[#16a6c9]">ارسال مجدد کد</button>
            </div>
            <div class="grid grid-cols-2 gap-2 border-t border-[#f0e6c8] pt-5">
                <a href="{{ route('home') }}" class="press flex h-12 items-center justify-center rounded-2xl border border-[#111]">انصراف</a>
                <button type="submit" class="press h-12 rounded-2xl bg-[#111] text-white">تایید و ادامه</button>
            </div>
        </form>
        <form id="resend-otp" method="POST" action="{{ route('login.otp') }}" data-no-loading>
            @csrf
            <input type="hidden" name="phone" value="{{ session('phone') }}">
        </form>
        @endif
    </div>
</div>
@endsection
