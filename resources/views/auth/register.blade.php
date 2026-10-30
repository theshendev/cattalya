@extends('layouts.app')
@section('title', 'ثبت نام')

@section('content')
<x-auth-card title="ثبت نام" subtitle="شماره تماس خود را وارد کنید">
    <form method="POST" action="{{ route('otp.send') }}" class="flex flex-col gap-3">
        @csrf
        <x-auth-field name="phone" label="شماره تماس" type="tel" placeholder="09123456789" :ltr="true" required autocomplete="tel" inputmode="numeric" />

        <p class="text-right text-xs text-[#2b2620]">
            حساب کاربری را قبلا ایجاد کردید؟ <a href="{{ route('login') }}" class="text-[#1f7a8c] hover:underline">ورود به حساب کاربری</a>
        </p>

        <div class="mt-2 grid grid-cols-2 gap-3 border-t border-[#ede6d3] pt-4">
            <a href="{{ route('home') }}" class="press flex h-12 items-center justify-center rounded-2xl border border-[#111] text-sm">انصراف</a>
            <button type="submit" class="press h-12 rounded-2xl bg-[#111] text-sm text-white">ارسال کد</button>
        </div>
    </form>
</x-auth-card>
@endsection
