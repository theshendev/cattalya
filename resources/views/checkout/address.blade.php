@extends('layouts.app')
@section('title', 'اطلاعات گیرنده')

@section('content')
@php
    $fields = ['title', 'province', 'city', 'address', 'plaque', 'unit', 'postal_code'];
    $reopenEditId = old('_edit_id');
    $shouldReopen = $errors->hasAny($fields) && old('_address_form');
@endphp
<div class="px-4 py-6 md:px-20 md:py-8">
    <x-checkout-stepper :step="2" />

    <div class="mt-5 grid grid-cols-1 items-start gap-5 lg:grid-cols-[1fr_325px]">
        <div class="flex min-w-0 flex-col gap-5">

            <section class="rounded-3xl bg-white p-5 md:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h1 class="text-lg font-bold text-[#2b2620] md:text-xl">اطلاعات گیرنده</h1>
                    <button type="button" id="open-address-dialog" class="press flex h-10 items-center gap-2 rounded-2xl border border-[#1f7a8c] px-4 text-xs text-[#1f7a8c]">
                        <span aria-hidden="true">+</span> افزودن آدرس
                    </button>
                </div>

                @error('address_id')
                <p class="mt-3 rounded-xl bg-[#fbeaea] px-4 py-3 text-right text-sm text-red-600">{{ $message }}</p>
                @enderror

                @if($addresses->isEmpty())
                <div class="mt-6 rounded-2xl border border-dashed border-[#e3d2a0] px-6 py-10 text-center text-sm text-[#a8a096]">
                    هنوز آدرسی ثبت نکرده‌اید. برای ادامه یک آدرس اضافه کنید.
                </div>
                @else
                <form id="select-address-form" method="POST" action="{{ route('checkout.address') }}" class="mt-5">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($addresses as $address)
                        @php $addressData = $address->only(['id', 'title', 'province', 'city', 'address', 'plaque', 'unit', 'postal_code', 'is_default']); @endphp
                        <div class="relative">
                            <label class="block cursor-pointer">
                                <input type="radio" name="address_id" value="{{ $address->id }}" class="peer sr-only" @checked($selectedAddress?->id === $address->id)>
                                <div class="h-full rounded-2xl border border-[#f0e6c8] p-4 text-right transition-colors peer-checked:border-[#2f8f4e] peer-checked:bg-[#f6fbf7] peer-focus-visible:ring-2 peer-focus-visible:ring-[#8a6300]/40">
                                    <div class="flex items-center justify-between gap-2 border-b border-[#f0e6c8] pb-3">
                                        <span class="size-5"></span>
                                        <p class="flex items-center gap-2 font-bold text-[#2b2620]">
                                            {{ $address->title ?: 'آدرس' }}
                                            <img src="/icons/routing.svg" alt="" class="size-5">
                                        </p>
                                    </div>
                                    <p class="mt-3 text-sm leading-7 text-[#6b6154]">{{ $address->fullAddress() }}</p>
                                    <p class="mt-1 text-xs text-[#a8a096]" dir="ltr">{{ $address->postal_code }}</p>
                                    @if($address->is_default)
                                    <span class="mt-3 inline-block rounded-lg bg-[#e6f6ec] px-3 py-1 text-xs font-medium text-[#1a7f3c]">پیش فرض</span>
                                    @endif
                                </div>
                            </label>
                            <button type="button"
                                    class="edit-address absolute left-3 top-3 flex size-7 items-center justify-center rounded-lg text-[#6b6154] hover:bg-[#f9f6f3]"
                                    aria-label="ویرایش آدرس"
                                    data-address='@json($addressData)'>✎</button>
                        </div>
                        @endforeach
                    </div>
                </form>
                @endif
            </section>

            @if($addresses->isNotEmpty())
            <section class="rounded-3xl bg-white p-5 md:p-6">
                <label for="notes" class="text-lg font-bold text-[#2b2620]">یاداشت</label>
                <input form="select-address-form" id="notes" type="text" name="notes" value="{{ old('notes', $notes) }}" maxlength="500"
                       placeholder="در این قسمت بنویسید ..."
                       class="mt-4 w-full rounded-2xl border border-[#f0e6c8] px-4 py-3 text-right text-sm outline-none focus:border-[#8a6300] focus:ring-4 focus:ring-[#8a6300]/10">
            </section>
            @endif
        </div>

        <x-invoice-summary :quote="$quote">
            @if($addresses->isNotEmpty())
            <button form="select-address-form" type="submit" class="press flex h-12 w-full items-center justify-center rounded-2xl bg-[#111] text-sm font-medium text-white hover:bg-[#2b2620]">تایید و ادامه</button>
            @else
            <button type="button" disabled class="flex h-12 w-full cursor-not-allowed items-center justify-center rounded-2xl bg-[#111]/40 text-sm font-medium text-white">تایید و ادامه</button>
            @endif
            <a href="{{ route('cart.index') }}" class="mt-3 block text-center text-xs text-[#6b6154] hover:text-[#8a6300]">بازگشت به سبد خرید</a>
        </x-invoice-summary>
    </div>
</div>

{{-- Add / edit address dialog --}}
<dialog id="address-dialog" class="m-auto w-[calc(100%-2rem)] max-w-[436px] rounded-3xl p-0 backdrop:bg-black/45">
    <div class="p-5 md:p-6">
        <div class="flex items-center justify-between">
            <h2 id="address-dialog-title" class="text-lg font-bold text-[#2b2620]">آدرس جدید</h2>
            <button type="button" data-close-dialog class="flex size-8 items-center justify-center rounded-full border border-[#2b2620]" aria-label="بستن">×</button>
        </div>

        <form id="address-form" method="POST" action="{{ route('addresses.store') }}" class="mt-5 grid grid-cols-2 gap-3">
            @csrf
            <input type="hidden" name="_method" value="POST" id="address-method">
            <input type="hidden" name="_address_form" value="1">
            <input type="hidden" name="_edit_id" id="address-edit-id" value="{{ $reopenEditId }}">

            @php
                $inputs = [
                    ['title', 'عنوان آدرس', 'col-span-2', 'خونه، شرکت، ...'],
                    ['province', 'استان', '', 'تهران'],
                    ['city', 'شهر', '', 'تهران'],
                    ['address', 'آدرس', 'col-span-2', ''],
                    ['plaque', 'پلاک', '', ''],
                    ['unit', 'واحد', '', ''],
                    ['postal_code', 'کد پستی', 'col-span-2', ''],
                ];
            @endphp
            @foreach($inputs as [$name, $label, $span, $placeholder])
            <label class="block rounded-2xl border px-4 pb-2 pt-2 text-right {{ $span }} {{ $errors->has($name) ? 'border-red-400' : 'border-[#f0e6c8]' }}">
                <span class="block text-[10px] text-[#a8a096]">{{ $label }}</span>
                @if($name === 'address')
                <textarea name="address" rows="2" placeholder="{{ $placeholder }}" class="w-full resize-none bg-transparent text-right text-sm outline-none">{{ old('address') }}</textarea>
                @else
                <input type="text" name="{{ $name }}" value="{{ old($name) }}" placeholder="{{ $placeholder }}"
                       @if($name === 'postal_code') inputmode="numeric" maxlength="10" dir="ltr" @endif
                       class="w-full bg-transparent text-right text-sm outline-none">
                @endif
                @error($name)<span class="block text-[11px] text-red-500">{{ $message }}</span>@enderror
            </label>
            @endforeach

            <label class="col-span-2 flex items-center justify-end gap-2 text-sm text-[#6b6154]">
                پیش فرض
                <input type="checkbox" name="is_default" value="1" @checked(old('is_default'))>
            </label>

            <div class="col-span-2 mt-2 grid grid-cols-2 gap-3 border-t border-[#f0e6c8] pt-4">
                <button type="submit" class="press h-12 rounded-2xl bg-[#111] text-sm text-white">تایید ادامه</button>
                <button type="button" data-close-dialog class="press h-12 rounded-2xl border border-[#111] text-sm">انصراف</button>
            </div>
        </form>

        <form id="address-delete-form" method="POST" action="" class="mt-3 hidden text-center">
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
    const updateUrl = @json(url('/addresses'));
    const fields = ['title', 'province', 'city', 'address', 'plaque', 'unit', 'postal_code'];

    function openDialog(address, keepValues = false) {
        const isEdit = !!address;
        title.textContent = isEdit ? 'ویرایش آدرس' : 'آدرس جدید';
        form.action = isEdit ? updateUrl + '/' + address.id : updateUrl;
        document.getElementById('address-method').value = isEdit ? 'PATCH' : 'POST';
        document.getElementById('address-edit-id').value = isEdit ? address.id : '';
        deleteForm.classList.toggle('hidden', !isEdit);
        deleteForm.action = isEdit ? updateUrl + '/' + address.id : '';

        if (isEdit && !keepValues) {
            fields.forEach((name) => { form.elements[name].value = address[name] ?? ''; });
            form.elements.is_default.checked = !!address.is_default;
        }
        dialog.showModal();
    }

    document.getElementById('open-address-dialog').addEventListener('click', () => {
        form.reset();
        openDialog(null);
    });
    document.querySelectorAll('.edit-address').forEach((button) => {
        button.addEventListener('click', () => openDialog(JSON.parse(button.dataset.address)));
    });
    dialog.querySelectorAll('[data-close-dialog]').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });
    dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });

    @if($shouldReopen)
    openDialog(@json($reopenEditId ? ['id' => $reopenEditId] : null), true);
    @endif
})();
</script>
@endsection
