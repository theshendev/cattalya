@extends('layouts.app')
@php use App\Support\Jalali; @endphp

@section('title', 'اطلاعات شخصی')

@section('content')
@php
    $cells = [
        ['identity-name', 'نام و نام‌خانوادگی', $user->fullName() ?: '—', false],
        ['identity-birth', 'تاریخ تولد', $user->birth_date ? Jalali::format($user->birth_date) : '—', true],
        ['identity-national', 'کدملی', $user->national_id ?: '—', true],
        ['password', 'رمز عبور', $user->hasPassword() ? '••••••••••••' : 'تنظیم نشده', true],
        ['phone', 'شماره تماس', $user->phone ?: '—', true],
        ['email', 'ایمیل', $user->email ?: '—', true],
    ];
    $opened = session('open_modal');

    foreach ([
        'identity' => ['full_name', 'national_id', 'birth_date'],
        'password' => ['current_password', 'password'],
        'phone' => ['phone'],
        'phone-verify' => ['otp'],
        'email' => ['email'],
    ] as $modal => $fields) {
        if ($errors->hasAny($fields)) {
            $opened = $modal;
        }
    }
@endphp
<section class="px-4 py-6 md:px-20 md:py-8">
    <div class="flex flex-col-reverse gap-5 lg:flex-row-reverse lg:items-start">
        <div class="w-full rounded-3xl bg-white p-5 md:p-7">
            <h1 class="mb-6 flex items-center justify-start gap-2 text-lg font-bold text-[#2b2620]">
                <img src="/icons/user.svg" alt="" class="size-6"> اطلاعات شخصی
            </h1>

            <div class="grid grid-cols-1 sm:grid-cols-3 sm:[&>*:nth-child(-n+3)]:border-b sm:[&>*:nth-child(-n+3)]:pb-5 sm:[&>*:nth-child(n+4)]:pt-5">
                @foreach($cells as [$modal, $label, $value, $ltr])
                <div class="flex items-center justify-between gap-3 border-b border-[#f0e6c8] py-4 sm:border-b-0 sm:px-4 sm:[&:nth-child(-n+3)]:border-b sm:[&:not(:nth-child(3n+1))]:border-r-0 sm:[&:not(:nth-child(3n))]:border-l sm:border-[#f0e6c8]">
                    <div class="min-w-0 text-right">
                        <p class="text-xs text-[#a8a096]">{{ $label }}</p>
                        <p class="mt-1 truncate text-sm font-medium text-[#2b2620]" @if($ltr) dir="ltr" style="text-align:right" @endif>{{ $value }}</p>
                    </div>
                    <button type="button" data-open-modal="{{ str_starts_with($modal, 'identity') ? 'identity' : $modal }}"
                            class="shrink-0 text-[#6b6154] hover:text-[#8a6300]" aria-label="ویرایش {{ $label }}">✎</button>
                </div>
                @endforeach
            </div>
        </div>

        <x-account-sidebar active="profile" />
    </div>
</section>

@php
    $modals = [
        'identity' => ['ثبت اطلاعات شناسایی', route('profile.identity'), 'PATCH', 'ثبت'],
        'password' => ['تغییر رمز عبور', route('profile.password'), 'PATCH', 'ثبت'],
        'phone' => ['تغییر شماره تماس', route('profile.phone.otp'), 'POST', 'ارسال کد تایید'],
        'phone-verify' => ['تایید کد', route('profile.phone.verify'), 'POST', 'ثبت'],
        'email' => ['تغییر ایمیل', route('profile.email'), 'PATCH', 'ثبت'],
    ];
@endphp

@foreach($modals as $key => [$title, $action, $method, $submit])
<dialog id="modal-{{ $key }}" class="m-auto w-[calc(100%-2rem)] max-w-[436px] rounded-3xl p-0 backdrop:bg-black/45">
    <form method="POST" action="{{ $action }}" class="flex flex-col gap-3 p-5 md:p-6">
        @csrf
        @if($method !== 'POST') @method($method) @endif

        <div class="mb-1 flex items-center justify-between">
            <h2 class="text-lg font-bold text-[#2b2620]">{{ $title }}</h2>
            <button type="button" data-close-modal class="flex size-8 items-center justify-center rounded-full border border-[#2b2620]" aria-label="بستن">×</button>
        </div>

        @switch($key)
            @case('identity')
                <x-auth-field name="full_name" label="نام و نام خانوادگی" :value="$user->fullName()" required />
                <x-auth-field name="national_id" label="کدملی" :value="$user->national_id" :ltr="true" inputmode="numeric" maxlength="10" />
                <x-auth-field name="birth_date" label="تاریخ تولد (مثال: 1379/01/28)" :value="$user->birth_date ? Jalali::format($user->birth_date) : ''" :ltr="true" placeholder="1379/01/28" />
                @break
            @case('password')
                @if($user->hasPassword())
                <x-auth-field name="current_password" label="رمز عبور فعلی" type="password" :password="true" autocomplete="current-password" />
                @endif
                <x-auth-field name="password" label="رمز عبور جدید" type="password" :password="true" autocomplete="new-password" />
                <x-auth-field name="password_confirmation" label="تکرار رمز عبور جدید" type="password" :password="true" autocomplete="new-password" />
                @break
            @case('phone')
                <x-auth-field name="phone" label="شماره تماس" type="tel" :value="$user->phone" :ltr="true" inputmode="numeric" />
                <p class="flex items-center justify-start gap-2 text-xs text-[#6b6154]"><span class="flex size-4 items-center justify-center rounded-full bg-[#a8a096] text-[10px] text-white">!</span> برای ثبت این شماره باید آن را تایید کنید.</p>
                @break
            @case('phone-verify')
                <input type="hidden" name="otp" id="profile-otp">
                <p class="text-right text-sm text-[#8b8277]">کد تایید ارسال شده به <strong dir="ltr">{{ $pendingPhone }}</strong> را وارد کنید</p>
                <div class="flex justify-between gap-2" dir="ltr" data-otp-boxes>
                    @for($i = 0; $i < 6; $i++)
                    <input type="text" inputmode="numeric" maxlength="1" aria-label="رقم {{ $i + 1 }}" class="size-12 rounded-xl border border-[#ead69e] text-center text-lg font-bold outline-none focus:border-[#c8960c]">
                    @endfor
                </div>
                @error('otp')<p class="text-right text-xs text-red-500">{{ $message }}</p>@enderror
                @break
            @case('email')
                <x-auth-field name="email" label="ایمیل" type="email" :value="$user->email" :ltr="true" />
                @break
        @endswitch

        <div class="mt-2 grid grid-cols-2 gap-3 border-t border-[#f0e6c8] pt-4">
            <button type="submit" class="press h-12 rounded-2xl bg-[#111] text-sm text-white">{{ $submit }}</button>
            <button type="button" data-close-modal class="press h-12 rounded-2xl border border-[#111] text-sm">انصراف</button>
        </div>
    </form>
</dialog>
@endforeach

@include('auth.partials.scripts')
<script>
(function () {
    document.querySelectorAll('[data-open-modal]').forEach((button) => {
        button.addEventListener('click', () => document.getElementById('modal-' + button.dataset.openModal).showModal());
    });
    document.querySelectorAll('dialog').forEach((dialog) => {
        dialog.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
    });

    const boxes = [...document.querySelectorAll('[data-otp-boxes] input')];
    const hidden = document.getElementById('profile-otp');
    const toLatin = (value) => value.replace(/[۰-۹]/g, (d) => d.charCodeAt(0) - 1776);
    boxes.forEach((box, index) => {
        box.addEventListener('input', () => {
            box.value = toLatin(box.value).replace(/\D/g, '').slice(-1);
            hidden.value = boxes.map((b) => b.value).join('');
            if (box.value && boxes[index + 1]) boxes[index + 1].focus();
        });
        box.addEventListener('keydown', (event) => { if (event.key === 'Backspace' && !box.value && boxes[index - 1]) boxes[index - 1].focus(); });
    });

    @if($opened)
    document.getElementById('modal-{{ $opened }}').showModal();
    @endif
})();
</script>
@endsection
