@extends('layouts.app')
@section('title', 'ورود به حساب')

@section('content')
<x-auth-card title="ورود به حساب" subtitle="برای ورود به حساب کاربری اطلاعات زیر را وارد کنید">
    <form method="POST" action="{{ route('login.attempt') }}" class="flex flex-col gap-3">
        @csrf
        <x-auth-field name="phone" label="شماره تماس" type="tel" placeholder="09123456789" :ltr="true" required autocomplete="tel" inputmode="numeric" />
        <x-auth-field name="password" label="رمز عبور" type="password" :password="true" required autocomplete="current-password" />

        <p class="text-right text-xs text-[#2b2620]">
            حساب کاربری ندارید؟ <a href="{{ route('register') }}" class="text-[#1f7a8c] hover:underline">ثبت نام کنید</a>
        </p>
        <p class="text-right text-xs text-[#2b2620]">
            رمز عبور ندارید یا فراموش کرده‌اید؟ <a href="{{ route('register') }}" class="text-[#1f7a8c] hover:underline">ورود با کد یکبار مصرف</a>
        </p>

        <div class="mt-2 grid grid-cols-2 gap-3 border-t border-[#ede6d3] pt-4">
            <a href="{{ route('home') }}" class="press flex h-12 items-center justify-center rounded-2xl border border-[#111] text-sm">انصراف</a>
            <button type="submit" class="press h-12 rounded-2xl bg-[#111] text-sm text-white">ورود به حساب</button>
        </div>
    </form>
</x-auth-card>
@include('auth.partials.scripts')
@endsection
