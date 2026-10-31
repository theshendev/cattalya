@extends('layouts.app')
@section('title', 'اطلاعات گیرنده')
@section('hide-mobile-nav', '1')

@section('mobile-header')
<a href="{{ route('cart.index') }}" class="flex items-center gap-3 text-base text-[#2b2620]" aria-label="بازگشت به سبد خرید">
    <svg class="size-6 text-[#6b6154]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 12h16m-6-6 6 6-6 6"/></svg>
    اطلاعات گیرنده
</a>
@endsection

@section('content')
@php
    $user = auth()->user();
    $userFullName = trim($user->name.' '.$user->last_name);
    $locationFields = ['province', 'city', 'address', 'plaque', 'unit', 'postal_code'];
    $receiverFields = ['title', 'receiver_name', 'phone'];
    $reopenEditId = old('_edit_id');
    $shouldReopen = old('_address_form') && $errors->hasAny([...$locationFields, ...$receiverFields]);
    $reopenStep = $errors->hasAny($locationFields) ? 1 : 2;
    $fieldClass = 'block rounded-2xl border px-4 pb-2.5 pt-2 text-right transition-colors focus-within:border-[#dfc47d]';
@endphp
<div class="px-4 py-4 md:px-20 md:py-8">
    <x-checkout-stepper :step="2" />

    <div class="mt-5 grid grid-cols-1 items-start gap-5 lg:grid-cols-[1fr_325px]">
        <div class="flex min-w-0 flex-col gap-5">

            <section class="rounded-3xl bg-white py-5 md:py-6">
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 md:px-6">
                    <h1 class="text-lg font-bold text-[#2b2620] md:text-xl">اطلاعات گیرنده</h1>
                    <button type="button" id="open-address-dialog" class="press flex h-10 items-center gap-3 rounded-2xl border border-[#1f9bb5] px-5 text-sm text-[#1f7a8c]">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="M12 5v14M5 12h14"/></svg>
                        افزودن آدرس
                    </button>
                </div>

                @error('address_id')
                <p class="mx-5 mt-3 rounded-xl bg-[#fbeaea] px-4 py-3 text-right text-sm text-red-600 md:mx-6">{{ $message }}</p>
                @enderror

                @if($addresses->isEmpty())
                <div class="mx-5 mt-6 rounded-2xl border border-dashed border-[#e3d2a0] px-6 py-10 text-center text-sm text-[#a8a096] md:mx-6">
                    هنوز آدرسی ثبت نکرده‌اید. برای ادامه یک آدرس اضافه کنید.
                </div>
                @else
                <form id="select-address-form" method="POST" action="{{ route('checkout.address') }}" class="relative mt-5" data-address-carousel>
                    @csrf
                    <div data-address-track class="scrollbar-hide flex snap-x snap-mandatory scroll-px-5 gap-4 overflow-x-auto scroll-smooth px-5 pb-1 md:scroll-px-6 md:gap-5 md:px-6">
                        @foreach($addresses as $address)
                        @php
                            $addressData = $address->only(['id', 'title', 'receiver_name', 'phone', 'province', 'city', 'address', 'plaque', 'unit', 'postal_code', 'is_default'])
                                + ['receiver_is_self' => $address->receiver_name === $userFullName && $address->phone === $user->phone];
                        @endphp
                        <div class="relative w-[78%] shrink-0 snap-start md:w-[calc((100%-2.5rem)/3)]">
                            <label class="group block h-full cursor-pointer">
                                <input type="radio" name="address_id" value="{{ $address->id }}" class="peer sr-only" @checked($selectedAddress?->id === $address->id)>
                                <div class="flex h-full flex-col rounded-2xl border border-[#f0e6c8] px-5 pb-5 pt-4 text-right transition-colors group-has-checked:border-[#2f8f4e] peer-focus-visible:ring-2 peer-focus-visible:ring-[#8a6300]/40">
                                    <p class="flex items-center gap-2 border-b border-[#ede6d3] pb-4 font-bold text-[#2b2620]">
                                        <svg class="size-6 text-[#a8a096]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M12 21s-7-6.1-7-11.5a7 7 0 0 1 14 0C19 14.9 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5" stroke-width="1.4"/></svg>
                                        {{ $address->title ?: 'آدرس' }}
                                    </p>
                                    <p class="mt-4 flex-1 text-sm leading-7 text-[#6b6154] md:text-base md:leading-8">{{ $address->fullAddress() }}</p>
                                    <div class="mt-3 flex min-h-8 items-center">
                                        @if($address->is_default)
                                        <span class="rounded-full bg-[#e6f6ec] px-3 py-1 text-sm font-medium text-[#1a7f3c]">پیش فرض</span>
                                        @else
                                        <span class="pr-3 text-sm font-medium text-[#1f7a8c] group-has-checked:hidden">انتخاب آدرس</span>
                                        @endif
                                    </div>
                                </div>
                            </label>
                            <button type="button" class="edit-address absolute left-4 top-3 flex size-9 items-center justify-center rounded-xl text-[#4d4942] hover:bg-[#f9f6f3]" aria-label="ویرایش آدرس {{ $address->title }}" data-address='@json($addressData)'>
                                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="m14.5 5.5 4 4M4 20h16M6 17l1-4L16.5 3.5a1.4 1.4 0 0 1 2 0l2 2a1.4 1.4 0 0 1 0 2L11 17l-4 1Z"/></svg>
                            </button>
                        </div>
                        @endforeach
                    </div>

                    @if($addresses->count() > 3)
                    <button type="button" data-address-prev aria-label="آدرس‌های قبلی" class="absolute right-0 top-1/2 z-10 hidden h-24 w-12 -translate-y-1/2 translate-x-1/4 items-center justify-center rounded-2xl border border-[#e3d2a0] bg-white text-[#8a6300] disabled:opacity-0 md:flex">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 5 7 7-7 7"/></svg>
                    </button>
                    <button type="button" data-address-next aria-label="آدرس‌های بعدی" class="absolute left-0 top-1/2 z-10 hidden h-24 w-12 -translate-x-1/4 -translate-y-1/2 items-center justify-center rounded-2xl border border-[#e3d2a0] bg-white text-[#8a6300] disabled:opacity-0 md:flex">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m15 5-7 7 7 7"/></svg>
                    </button>
                    @endif
                </form>
                @endif
            </section>

            @if($addresses->isNotEmpty())
            <section class="rounded-3xl bg-white p-5 md:p-6">
                <label for="notes" class="text-lg font-bold text-[#2b2620]">یاداشت</label>
                <input form="select-address-form" id="notes" type="text" name="notes" value="{{ old('notes', $notes) }}" maxlength="500"
                       placeholder="در این قسمت بنویسید ..."
                       class="mt-5 h-14 w-full rounded-2xl border border-[#f0e6c8] px-5 text-right text-sm outline-none placeholder:text-[#a8a096] focus:border-[#dfc47d] md:text-base">
            </section>
            @endif
        </div>

        <x-invoice-summary :quote="$quote">
            @if($addresses->isNotEmpty())
            <button form="select-address-form" type="submit" class="press flex h-12 w-full items-center justify-center rounded-2xl bg-[#111] text-base text-white hover:bg-[#2b2620]">تایید و ادامه</button>
            @else
            <button type="button" disabled class="flex h-12 w-full cursor-not-allowed items-center justify-center rounded-2xl bg-[#111]/40 text-base text-white">تایید و ادامه</button>
            @endif
        </x-invoice-summary>
    </div>
</div>

{{-- Add / edit address: two steps, a centred dialog on desktop and a bottom sheet on mobile --}}
<dialog id="address-dialog" aria-labelledby="address-dialog-title"
        class="m-auto w-[calc(100%-2rem)] max-w-[576px] rounded-3xl p-0 backdrop:bg-black/40 max-md:mb-0 max-md:w-full max-md:max-w-full max-md:rounded-b-none">
    <div class="px-4 pb-6 pt-3 md:p-5">
        <span class="mx-auto mb-6 block h-1 w-9 rounded-full bg-[#aaa197] md:hidden" aria-hidden="true"></span>
        <div class="flex items-center justify-between">
            <h2 id="address-dialog-title" class="text-lg font-bold text-[#2b2620] md:text-xl">آدرس جدید</h2>
            <button type="button" data-close-dialog class="flex size-6 items-center justify-center rounded-full border-[1.5px] border-[#2b2620] text-[#2b2620]" aria-label="بستن">
                <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-width="3" d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </div>

        <form id="address-form" method="POST" action="{{ route('addresses.store') }}" class="mt-6" novalidate>
            @csrf
            <input type="hidden" name="_method" value="POST" id="address-method">
            <input type="hidden" name="_address_form" value="1">
            <input type="hidden" name="_edit_id" id="address-edit-id" value="{{ $reopenEditId }}">

            <div data-address-step="1" class="grid grid-cols-2 gap-x-3 gap-y-6">
                @foreach([
                    ['province', 'استان', '', 'تهران'],
                    ['city', 'شهر', '', 'تهران'],
                    ['address', 'آدرس', 'col-span-2', 'خیابان، کوچه، ...'],
                    ['plaque', 'پلاک', '', ''],
                    ['unit', 'واحد', '', ''],
                    ['postal_code', 'کد پستی', 'col-span-2', ''],
                ] as [$name, $label, $span, $placeholder])
                <label class="{{ $fieldClass }} {{ $span }} {{ $errors->has($name) ? 'border-red-400' : 'border-[#f0e6c8]' }}">
                    <span class="block text-xs text-[#a8a096]">{{ $label }}</span>
                    @if($name === 'address')
                    <textarea name="address" rows="2" required placeholder="{{ $placeholder }}" class="mt-1 w-full resize-none bg-transparent text-right text-base leading-7 text-[#2b2620] outline-none placeholder:text-[#d9d4cc]">{{ old('address') }}</textarea>
                    @else
                    <input type="text" name="{{ $name }}" value="{{ old($name) }}" placeholder="{{ $placeholder }}"
                           @if(in_array($name, ['province', 'city'], true)) required @endif
                           @if($name === 'postal_code') required inputmode="numeric" maxlength="10" pattern="[0-9۰-۹]{10}" @endif
                           class="mt-1 w-full bg-transparent text-right text-base text-[#2b2620] outline-none placeholder:text-[#d9d4cc]">
                    @endif
                    @error($name)<span class="mt-1 block text-[11px] text-red-500">{{ $message }}</span>@enderror
                </label>
                @endforeach

                <div class="col-span-2 grid grid-cols-2 gap-3 border-t border-[#ede6d3] pt-6">
                    <button type="button" data-close-dialog class="press h-12 rounded-2xl border border-[#111] text-base text-[#2b2620]">انصراف</button>
                    <button type="button" data-address-next-step class="press h-12 rounded-2xl bg-[#111] text-base text-white">تایید ادامه</button>
                </div>
            </div>

            <div data-address-step="2" class="hidden grid-cols-2 gap-x-3 gap-y-6">
                <label class="{{ $fieldClass }} col-span-2 {{ $errors->has('title') ? 'border-red-400' : 'border-[#f0e6c8]' }}">
                    <span class="block text-xs text-[#a8a096]">نام آدرس</span>
                    <input type="text" name="title" value="{{ old('title') }}" maxlength="50" placeholder="خونه، شرکت، ..." class="mt-1 w-full bg-transparent text-right text-base text-[#2b2620] outline-none placeholder:text-[#d9d4cc]">
                    @error('title')<span class="mt-1 block text-[11px] text-red-500">{{ $message }}</span>@enderror
                </label>

                <div class="col-span-2 flex flex-col gap-4">
                    <input type="hidden" name="receiver_is_self" value="0">
                    @foreach([['receiver_is_self', 'گیرنده خودم هستم', old('receiver_is_self', '1') === '1'], ['is_default', 'آدرس پیش فرض', (bool) old('is_default')]] as [$name, $label, $isChecked])
                    <label class="flex w-fit cursor-pointer items-center gap-3 text-base text-[#2b2620]">
                        <input type="checkbox" name="{{ $name }}" value="1" @checked($isChecked) @if($name === 'receiver_is_self') id="receiver-is-self" @endif
                               class="size-6 shrink-0 cursor-pointer appearance-none rounded-md border border-[#d9d4cc] bg-white bg-center bg-no-repeat bg-[length:16px] checked:border-[#b08000] checked:bg-[#b08000] checked:bg-[url('data:image/svg+xml;utf8,<svg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%2024%2024%22%20fill=%22none%22%20stroke=%22white%22%20stroke-width=%222.5%22%20stroke-linecap=%22round%22%20stroke-linejoin=%22round%22><path%20d=%22m5%2012%205%205L20%207%22/></svg>')] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#c9a227]">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>

                <div data-receiver-fields class="col-span-2 grid grid-cols-2 gap-3">
                    @foreach([['receiver_name', 'نام و نام خانوادگی', 'text', ''], ['phone', 'شماره همراه', 'tel', '09xxxxxxxxx']] as [$name, $label, $type, $placeholder])
                    <label class="{{ $fieldClass }} {{ $errors->has($name) ? 'border-red-400' : 'border-[#f0e6c8]' }}">
                        <span class="block text-xs text-[#a8a096]">{{ $label }}</span>
                        <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name) }}" placeholder="{{ $placeholder }}" @if($name === 'phone') inputmode="numeric" maxlength="11" @endif
                               class="mt-1 w-full bg-transparent text-right text-base text-[#2b2620] outline-none placeholder:text-[#d9d4cc]">
                        @error($name)<span class="mt-1 block text-[11px] text-red-500">{{ $message }}</span>@enderror
                    </label>
                    @endforeach
                </div>

                <div class="col-span-2 grid grid-cols-2 gap-3 border-t border-[#ede6d3] pt-6">
                    <button type="button" data-close-dialog class="press h-12 rounded-2xl border border-[#111] text-base text-[#2b2620]">انصراف</button>
                    <button type="submit" class="press h-12 rounded-2xl bg-[#111] text-base text-white">ذخیره آدرس</button>
                </div>
            </div>
        </form>

        <form id="address-delete-form" method="POST" action="" class="mt-4 hidden text-center">
            @csrf @method('DELETE')
            <button type="submit" class="text-xs text-red-500 hover:underline">حذف این آدرس</button>
        </form>
    </div>
</dialog>

<script>
(function () {
    const dialog = document.getElementById('address-dialog');
    const form = document.getElementById('address-form');
    const deleteForm = document.getElementById('address-delete-form');
    const title = document.getElementById('address-dialog-title');
    const receiverIsSelf = document.getElementById('receiver-is-self');
    const receiverFields = form.querySelector('[data-receiver-fields]');
    const updateUrl = @json(url('/addresses'));
    const fields = ['title', 'receiver_name', 'phone', 'province', 'city', 'address', 'plaque', 'unit', 'postal_code'];

    function showStep(step) {
        form.querySelectorAll('[data-address-step]').forEach((panel) => {
            const isActive = panel.dataset.addressStep === String(step);
            panel.classList.toggle('hidden', !isActive);
            panel.classList.toggle('grid', isActive);
        });
        deleteForm.classList.toggle('hidden', step !== 1 || form.elements._edit_id.value === '');
    }

    function syncReceiverFields() {
        receiverFields.classList.toggle('hidden', receiverIsSelf.checked);
    }

    function openDialog(address, step = 1, keepValues = false) {
        const isEdit = !!address;
        title.textContent = isEdit ? 'ویرایش آدرس' : 'آدرس جدید';
        form.action = isEdit ? updateUrl + '/' + address.id : updateUrl;
        document.getElementById('address-method').value = isEdit ? 'PATCH' : 'POST';
        document.getElementById('address-edit-id').value = isEdit ? address.id : '';
        deleteForm.action = isEdit ? updateUrl + '/' + address.id : '';

        if (isEdit && !keepValues) {
            fields.forEach((name) => { form.elements[name].value = address[name] ?? ''; });
            form.elements.is_default.checked = !!address.is_default;
            receiverIsSelf.checked = !!address.receiver_is_self;
        }
        syncReceiverFields();
        showStep(step);
        dialog.showModal();
    }

    document.getElementById('open-address-dialog').addEventListener('click', () => {
        form.reset();
        receiverIsSelf.checked = true;
        openDialog(null);
    });
    document.querySelectorAll('.edit-address').forEach((button) => {
        button.addEventListener('click', () => openDialog(JSON.parse(button.dataset.address)));
    });
    form.querySelector('[data-address-next-step]').addEventListener('click', () => {
        const stepOne = form.querySelector('[data-address-step="1"]');
        const invalid = [...stepOne.querySelectorAll('input, textarea')].find((input) => !input.checkValidity());
        if (invalid) {
            invalid.reportValidity();
            return;
        }
        showStep(2);
    });
    receiverIsSelf.addEventListener('change', syncReceiverFields);
    dialog.querySelectorAll('[data-close-dialog]').forEach((button) => button.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });

    const carousel = document.querySelector('[data-address-carousel]');
    const prev = carousel?.querySelector('[data-address-prev]');
    const next = carousel?.querySelector('[data-address-next]');
    if (prev && next) {
        const track = carousel.querySelector('[data-address-track]');
        const step = () => track.clientWidth / 3;
        // In RTL, scrollLeft runs from 0 (start, on the right) to a negative maximum.
        const update = () => {
            const position = Math.abs(track.scrollLeft);
            prev.disabled = position < 4;
            next.disabled = position > track.scrollWidth - track.clientWidth - 4;
        };
        prev.addEventListener('click', () => track.scrollBy({ left: step() }));
        next.addEventListener('click', () => track.scrollBy({ left: -step() }));
        track.addEventListener('scroll', update, { passive: true });
        update();
    }

    @if($shouldReopen)
    openDialog(@json($reopenEditId ? ['id' => $reopenEditId] : null), {{ $reopenStep }}, true);
    @endif
})();
</script>
@endsection
