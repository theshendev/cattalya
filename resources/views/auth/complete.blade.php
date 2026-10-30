@extends('layouts.app')
@section('title', 'تکمیل اطلاعات')

@section('content')
<x-auth-card title="تکمیل اطلاعات" subtitle="اطلاعات زیر را وارد کنید">
    <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-3">
        @csrf
        <x-auth-field name="full_name" label="نام و نام خانوادگی" required autocomplete="name" />
        <x-auth-field name="national_id" label="کد ملی (اختیاری)" :ltr="true" inputmode="numeric" maxlength="10" />
        <x-auth-field name="password" label="رمز عبور" type="password" :password="true" required autocomplete="new-password" />
        <x-auth-field name="password_confirmation" label="تکرار رمز عبور" type="password" :password="true" required autocomplete="new-password" />

        <div class="mt-2 grid grid-cols-2 gap-3 border-t border-[#ede6d3] pt-4">
            <a href="{{ route('home') }}" class="press flex h-12 items-center justify-center rounded-2xl border border-[#111] text-sm">انصراف</a>
            <button type="submit" class="press h-12 rounded-2xl bg-[#111] text-sm text-white">ایجاد حساب</button>
        </div>
    </form>
</x-auth-card>
@include('auth.partials.scripts')
@endsection
