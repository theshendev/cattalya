@extends('layouts.app')
@php
    use App\Support\Jalali;

    $isMenuView = ! request()->boolean('details');
@endphp

@section('title', 'اطلاعات شخصی')

@unless($isMenuView)
@section('hide-mobile-nav', '1')
@section('mobile-header')
@include('profile.partials.mobile-header', ['title' => 'اطلاعات شخصی'])
@endsection
@endunless

@section('content')
@php
    $cells = [
        ['identity', 'نام و نام‌خانوادگی', $user->fullName() ?: '—', false],
        ['identity', 'تاریخ تولد', $user->birth_date ? Jalali::format($user->birth_date) : '—', true],
        ['identity', 'کدملی', $user->national_id ?: '—', true],
        ['password', 'رمز عبور', $user->hasPassword() ? '*************' : 'تنظیم نشده', false],
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
<section class="px-4 md:px-20 lg:py-8 {{ $isMenuView ? '' : 'py-2' }}">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-start">
        <x-account-sidebar active="profile" :show-on-mobile="$isMenuView" />

        <div class="{{ $isMenuView ? 'max-lg:hidden' : '' }} w-full bg-white max-lg:-mx-4 max-lg:w-[calc(100%+2rem)] max-lg:px-5 lg:rounded-3xl lg:px-6 lg:py-6">
            <h1 class="mb-6 hidden items-center gap-2 text-xl font-bold text-[#2b2620] lg:flex">
                <svg class="size-7 text-[#6b6154]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm-4 5.5c.7-1.8 2.2-2.7 4-2.7s3.3.9 4 2.7"/></svg>
                اطلاعات شخصی
            </h1>

            <dl class="grid grid-cols-1 divide-y divide-[#ede6d3] lg:grid-cols-3 lg:divide-y-0">
                @foreach($cells as $index => [$modal, $label, $value, $isLtr])
                <div class="flex items-center justify-between gap-4 py-5 lg:px-6 lg:py-3 {{ $index < 3 ? 'lg:border-b lg:border-[#ede6d3] lg:pb-7 lg:pt-3' : 'lg:pt-7' }} {{ $index % 3 !== 2 ? 'lg:border-l lg:border-l-[#ede6d3]' : '' }} {{ $index % 3 === 0 ? 'lg:pr-0' : '' }} {{ $index % 3 === 2 ? 'lg:pl-0' : '' }}">
                    <div class="min-w-0 text-right">
                        <dt class="text-xs text-[#a8a096]">{{ $label }}</dt>
                        <dd class="mt-1.5 truncate text-base text-[#2b2620]" @if($isLtr) dir="ltr" style="text-align:right" @endif>{{ $value }}</dd>
                    </div>
                    <button type="button" data-open-modal="{{ $modal }}" class="flex size-9 shrink-0 items-center justify-center rounded-xl text-[#4d4942] transition-colors hover:bg-[#f9f6f3] hover:text-[#8a6300]" aria-label="ویرایش {{ $label }}">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="m14.5 5.5 4 4M4 20h16M6 17l1-4L16.5 3.5a1.4 1.4 0 0 1 2 0l2 2a1.4 1.4 0 0 1 0 2L11 17l-4 1Z"/></svg>
                    </button>
                </div>
                @endforeach
            </dl>
        </div>
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
<dialog id="modal-{{ $key }}" aria-labelledby="modal-{{ $key }}-title"
        class="m-auto w-[calc(100%-2rem)] max-w-[576px] rounded-3xl p-0 backdrop:bg-black/40 max-md:mb-0 max-md:w-full max-md:max-w-full max-md:rounded-b-none">
    <form method="POST" action="{{ $action }}" class="flex flex-col gap-6 px-4 pb-6 pt-3 md:p-5">
        @csrf
        @if($method !== 'POST') @method($method) @endif

        <span class="mx-auto block h-1 w-9 rounded-full bg-[#aaa197] md:hidden" aria-hidden="true"></span>
        <div class="flex items-center justify-between">
            <h2 id="modal-{{ $key }}-title" class="text-lg font-bold text-[#2b2620] md:text-xl">{{ $title }}</h2>
            <button type="button" data-close-modal class="flex size-6 items-center justify-center rounded-full border-[1.5px] border-[#2b2620] text-[#2b2620]" aria-label="بستن">
                <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-width="3" d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </div>

        @switch($key)
            @case('identity')
                <x-auth-field name="full_name" label="نام و نام خانوادگی" :value="$user->fullName()" required />
                <x-auth-field name="national_id" label="کدملی" :value="$user->national_id" :ltr="true" inputmode="numeric" maxlength="10" />
                <x-auth-field name="birth_date" label="تاریخ تولد" :value="$user->birth_date ? Jalali::format($user->birth_date) : ''" :ltr="true" placeholder="1379/01/28" />
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
                <p class="flex items-center gap-2 text-sm text-[#6b6154]">
                    <span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-[#a8a096] text-xs font-bold text-white" aria-hidden="true">!</span>
                    برای ثبت این شماره باید آن را تایید کنید.
                </p>
                @break
            @case('phone-verify')
                <input type="hidden" name="otp" id="profile-otp">
                <div class="flex items-center justify-between gap-3 text-sm">
                    <p class="text-[#8b8277]">کد تایید ارسال شده را وارد کنید</p>
                    <button type="button" data-switch-modal="phone" class="flex items-center gap-1.5 text-[#1f9bb5]" aria-label="ویرایش شماره {{ $pendingPhone }}">
                        <span dir="ltr">{{ $pendingPhone }}</span>
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="m14.5 5.5 4 4M4 20h16M6 17l1-4L16.5 3.5a1.4 1.4 0 0 1 2 0l2 2a1.4 1.4 0 0 1 0 2L11 17l-4 1Z"/></svg>
                    </button>
                </div>
                <div class="flex justify-between gap-2" dir="ltr" data-otp-boxes>
                    @for($i = 0; $i < 6; $i++)
                    <input type="text" inputmode="numeric" maxlength="1" aria-label="رقم {{ $i + 1 }}" class="h-12 w-full max-w-[52px] rounded-xl border border-[#f0e6c8] text-center text-lg text-[#2b2620] outline-none focus:border-[#e3b23c] md:h-14">
                    @endfor
                </div>
                @error('otp')<p class="text-right text-xs text-red-500">{{ $message }}</p>@enderror
                <div class="flex items-center justify-between gap-3 text-sm">
                    <span class="flex items-center gap-1 text-[#a8a096]">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke-width="1.5"/><path stroke-linecap="round" stroke-width="1.5" d="M12 7.5V12l3 2"/></svg>
                        <span><span id="phone-resend-seconds">{{ $phoneResendIn }}</span> ثانیه تا ارسال مجدد</span>
                    </span>
                    <button type="submit" form="phone-resend-form" id="phone-resend-button" class="flex items-center gap-1 text-[#1f9bb5] disabled:cursor-not-allowed disabled:opacity-40" @disabled($phoneResendIn > 0)>
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke-width="1.4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M8.5 10.5a4 4 0 0 1 7-1.5m0 0V6.5m0 2.5H13m2.5 4.5a4 4 0 0 1-7 1.5m0 0v2.5m0-2.5H11"/></svg>
                        ارسال مجدد کد ورود
                    </button>
                </div>
                @break
            @case('email')
                <x-auth-field name="email" label="ایمیل" type="email" :value="$user->email" :ltr="true" />
                @break
        @endswitch

        <div class="grid grid-cols-2 gap-3 border-t border-[#ede6d3] pt-6">
            <button type="button" data-close-modal class="press h-12 rounded-2xl border border-[#111] text-base text-[#2b2620]">انصراف</button>
            <button type="submit" class="press h-12 rounded-2xl bg-[#111] text-base text-white">{{ $submit }}</button>
        </div>
    </form>
</dialog>
@endforeach

@if($pendingPhone)
<form id="phone-resend-form" method="POST" action="{{ route('profile.phone.otp') }}" class="hidden" data-no-loading>
    @csrf
    <input type="hidden" name="phone" value="{{ $pendingPhone }}">
</form>
@endif

@include('auth.partials.scripts')
<script>
(function () {
    const open = (name) => document.getElementById('modal-' + name)?.showModal();
    document.querySelectorAll('[data-open-modal]').forEach((button) => {
        button.addEventListener('click', () => open(button.dataset.openModal));
    });
    document.querySelectorAll('dialog').forEach((dialog) => {
        dialog.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
    });
    document.querySelectorAll('[data-switch-modal]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('dialog').close();
            open(button.dataset.switchModal);
        });
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

    let remaining = {{ (int) $phoneResendIn }};
    const seconds = document.getElementById('phone-resend-seconds');
    const resend = document.getElementById('phone-resend-button');
    if (seconds && resend && remaining > 0) {
        const timer = setInterval(() => {
            remaining -= 1;
            seconds.textContent = remaining;
            if (remaining <= 0) { resend.disabled = false; clearInterval(timer); }
        }, 1000);
    }

    @if($opened)
    open(@json($opened));
    @endif
})();
</script>
@endsection
