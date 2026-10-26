@extends('layouts.app')
@section('title', 'تایید کد')

@section('content')
<x-auth-card title="تایید کد">
    <form id="otp-form" method="POST" action="{{ route('otp.verify') }}" class="flex flex-col gap-4">
        @csrf
        <input type="hidden" name="otp" id="otp-value">

        <div class="flex items-center justify-between text-sm">
            <span class="text-[#8b8277]">کد تایید ارسال شده را وارد کنید</span>
            <a href="{{ route('register') }}" class="flex items-center gap-1 text-xs text-[#1f7a8c]" aria-label="ویرایش شماره">
                <span aria-hidden="true">✎</span><span dir="ltr">{{ $phone }}</span>
            </a>
        </div>

        <div class="flex justify-between gap-2" dir="ltr" id="otp-boxes">
            @for($i = 0; $i < 6; $i++)
            <input type="text" inputmode="numeric" maxlength="1" autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}" aria-label="رقم {{ $i + 1 }}"
                   class="size-12 rounded-xl border border-[#ead69e] text-center text-lg font-bold outline-none focus:border-[#c8960c] md:size-[52px]">
            @endfor
        </div>
        @error('otp')<p class="text-right text-xs text-red-500">{{ $message }}</p>@enderror
        @error('phone')<p class="text-right text-xs text-red-500">{{ $message }}</p>@enderror

        <div class="flex items-center justify-between text-xs">
            <span class="text-[#a8a096]"><span id="resend-seconds">{{ $resendIn }}</span> ثانیه تا ارسال مجدد</span>
            <button type="submit" form="resend-otp" id="resend-button" class="flex items-center gap-1 text-[#1f7a8c] disabled:cursor-not-allowed disabled:opacity-40" @disabled($resendIn > 0)>
                ارسال مجدد کد ورود
            </button>
        </div>

        <div class="grid grid-cols-2 gap-3 border-t border-[#f0e6c8] pt-4">
            <button type="submit" class="press h-12 rounded-2xl bg-[#111] text-sm text-white">ثبت و ادامه</button>
            <a href="{{ route('home') }}" class="press flex h-12 items-center justify-center rounded-2xl border border-[#111] text-sm">انصراف</a>
        </div>
    </form>

    <form id="resend-otp" method="POST" action="{{ route('otp.send') }}" data-no-loading>
        @csrf
        <input type="hidden" name="phone" value="{{ $phone }}">
    </form>
</x-auth-card>

<script>
(function () {
    const boxes = [...document.querySelectorAll('#otp-boxes input')];
    const hidden = document.getElementById('otp-value');
    const toLatin = (value) => value.replace(/[۰-۹]/g, (d) => d.charCodeAt(0) - 1776).replace(/[٠-٩]/g, (d) => d.charCodeAt(0) - 1632);
    const sync = () => { hidden.value = boxes.map((box) => box.value).join(''); };

    boxes.forEach((box, index) => {
        box.addEventListener('input', () => {
            box.value = toLatin(box.value).replace(/\D/g, '').slice(-1);
            sync();
            if (box.value && boxes[index + 1]) boxes[index + 1].focus();
        });
        box.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && !box.value && boxes[index - 1]) boxes[index - 1].focus();
        });
        box.addEventListener('paste', (event) => {
            const digits = toLatin(event.clipboardData.getData('text')).replace(/\D/g, '').slice(0, 6);
            if (!digits) return;
            event.preventDefault();
            digits.split('').forEach((digit, i) => { if (boxes[i]) boxes[i].value = digit; });
            sync();
            (boxes[Math.min(digits.length, 5)]).focus();
        });
    });
    boxes[0].focus();

    let remaining = {{ (int) $resendIn }};
    const seconds = document.getElementById('resend-seconds');
    const resend = document.getElementById('resend-button');
    const timer = setInterval(() => {
        remaining = Math.max(0, remaining - 1);
        seconds.textContent = remaining;
        if (remaining === 0) { resend.disabled = false; clearInterval(timer); }
    }, 1000);
})();
</script>
@endsection
