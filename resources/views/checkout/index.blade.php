@extends('layouts.app')
@section('title', 'تکمیل سفارش')

@section('content')
<div class="px-4 md:px-20 py-6 md:py-10">
    <h1 class="text-xl md:text-2xl font-bold text-[#2b2620] text-right mb-6 md:mb-8 animate-fade-up">تکمیل سفارش</h1>

    <form method="POST" action="{{ route('checkout.store') }}">
        @csrf
        <div class="flex flex-col lg:grid lg:grid-cols-3 gap-5 lg:gap-8">

            {{-- Address + Gateway --}}
            <div class="lg:col-span-2 flex flex-col gap-5 lg:gap-6">

                {{-- Delivery Address --}}
                <div data-reveal class="bg-white rounded-2xl md:rounded-3xl p-5 md:p-6">
                    <h2 class="font-bold text-[#2b2620] text-right mb-4 md:mb-5">آدرس تحویل</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-[#6b6154] text-right mb-2 font-medium">نام و نام خانوادگی گیرنده *</label>
                            <input type="text" name="receiver_name" value="{{ old('receiver_name', $user?->name) }}"
                                   class="w-full bg-[#f9f6f3] border border-[#f0e6c8] rounded-2xl px-4 py-3 text-right text-sm outline-none focus:border-[#8a6300] focus:bg-white focus:ring-4 focus:ring-[#8a6300]/10 transition-all duration-200 @error('receiver_name') border-red-400 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm text-[#6b6154] text-right mb-2 font-medium">شماره تماس *</label>
                            <input type="tel" name="phone" value="{{ old('phone', $user?->phone) }}"
                                   dir="ltr"
                                   class="w-full bg-[#f9f6f3] border border-[#f0e6c8] rounded-2xl px-4 py-3 text-center text-sm outline-none focus:border-[#8a6300] focus:bg-white focus:ring-4 focus:ring-[#8a6300]/10 transition-all duration-200 @error('phone') border-red-400 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm text-[#6b6154] text-right mb-2 font-medium">استان *</label>
                            <input type="text" name="province" value="{{ old('province') }}"
                                   class="w-full bg-[#f9f6f3] border border-[#f0e6c8] rounded-2xl px-4 py-3 text-right text-sm outline-none focus:border-[#8a6300] focus:bg-white focus:ring-4 focus:ring-[#8a6300]/10 transition-all duration-200 @error('province') border-red-400 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm text-[#6b6154] text-right mb-2 font-medium">شهر *</label>
                            <input type="text" name="city" value="{{ old('city') }}"
                                   class="w-full bg-[#f9f6f3] border border-[#f0e6c8] rounded-2xl px-4 py-3 text-right text-sm outline-none focus:border-[#8a6300] focus:bg-white focus:ring-4 focus:ring-[#8a6300]/10 transition-all duration-200 @error('city') border-red-400 @enderror">
                        </div>
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm text-[#6b6154] text-right mb-2 font-medium">آدرس دقیق *</label>
                            <textarea name="address" rows="3"
                                      class="w-full bg-[#f9f6f3] border border-[#f0e6c8] rounded-2xl px-4 py-3 text-right text-sm outline-none focus:border-[#8a6300] focus:bg-white focus:ring-4 focus:ring-[#8a6300]/10 transition-all duration-200 resize-none @error('address') border-red-400 @enderror">{{ old('address') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm text-[#6b6154] text-right mb-2 font-medium">کد پستی *</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code') }}"
                                   maxlength="10" dir="ltr"
                                   class="w-full bg-[#f9f6f3] border border-[#f0e6c8] rounded-2xl px-4 py-3 text-center text-sm outline-none focus:border-[#8a6300] focus:bg-white focus:ring-4 focus:ring-[#8a6300]/10 transition-all duration-200 @error('postal_code') border-red-400 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm text-[#6b6154] text-right mb-2 font-medium">توضیحات (اختیاری)</label>
                            <input type="text" name="notes" value="{{ old('notes') }}"
                                   class="w-full bg-[#f9f6f3] border border-[#f0e6c8] rounded-2xl px-4 py-3 text-right text-sm outline-none focus:border-[#8a6300] focus:bg-white focus:ring-4 focus:ring-[#8a6300]/10 transition-all duration-200">
                        </div>
                    </div>
                </div>

                {{-- Payment Gateway --}}
                <div data-reveal class="bg-white rounded-2xl md:rounded-3xl p-5 md:p-6">
                    <h2 class="font-bold text-[#2b2620] text-right mb-4 md:mb-5">روش پرداخت</h2>
                    @if(empty($gateways))
                        <p class="text-sm text-red-600 text-right">در حال حاضر هیچ درگاه پرداختی فعال نیست.</p>
                    @else
                    <div class="grid grid-cols-3 gap-2 md:gap-3">
                        @foreach($gateways as $value => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="gateway" value="{{ $value }}"
                                   {{ old('gateway', array_key_first($gateways)) === $value ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="border-2 border-[#f0e6c8] rounded-2xl p-3 md:p-4 text-center peer-checked:border-[#8a6300] peer-checked:bg-[#fdeee0] peer-checked:scale-[1.03] peer-checked:shadow-md peer-focus-visible:ring-2 peer-focus-visible:ring-[#8a6300]/40 transition-all duration-300 hover:border-[#8a6300] hover:-translate-y-0.5">
                                <p class="font-bold text-[#2b2620] text-xs md:text-sm">{{ $label }}</p>
                                <p class="text-[10px] md:text-xs text-[#a8a096] mt-1 hidden sm:block">پرداخت آنلاین</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Order Summary --}}
            <div data-reveal class="bg-white rounded-2xl md:rounded-3xl p-5 md:p-6 lg:sticky lg:top-24 h-fit">
                <h2 class="font-bold text-[#2b2620] text-right mb-4 md:mb-5">خلاصه سفارش</h2>

                <div class="flex flex-col gap-2 md:gap-3 mb-5">
                    @foreach($items as $item)
                    <div class="flex items-center gap-3 rounded-xl -mx-2 px-2 py-1 transition-colors duration-200 hover:bg-[#f9f6f3]">
                        <div class="text-right flex-1 min-w-0">
                            <p class="text-xs text-[#2b2620] font-medium line-clamp-1">{{ $item['name'] }}</p>
                            <p class="text-xs text-[#a8a096]">× {{ $item['quantity'] }}</p>
                        </div>
                        <p class="text-sm font-bold text-[#2b2620] shrink-0">{{ number_format($item['total_price']) }}</p>
                    </div>
                    @endforeach
                </div>

                <div class="h-px bg-[#f0e6c8] mb-4"></div>

                <div class="flex items-center justify-between font-bold text-[#2b2620] mb-5">
                    <span class="text-base md:text-lg">{{ number_format($total) }} تومان</span>
                    <span>جمع کل</span>
                </div>

                <button type="submit"
                        class="press w-full bg-[#8a6300] text-white font-bold py-3 md:py-4 rounded-2xl hover:bg-[#6b4d00] hover:shadow-xl hover:shadow-[#8a6300]/25">
                    پرداخت و تکمیل سفارش
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
