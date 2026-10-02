@extends('layouts.app')

@section('title', 'اطلاعات شخصی')

@section('content')
<section class="px-4 py-8 md:px-20 md:py-12">
    <div class="mx-auto flex max-w-[1353px] flex-col-reverse gap-5 lg:flex-row-reverse lg:items-start">
        <div class="w-full rounded-3xl bg-white p-5 lg:min-h-[367px]">
            <div class="mb-6 flex items-center justify-between border-b border-[#f0e6c8] pb-5">
                <h1 class="text-lg font-bold text-[#2b2620] md:text-xl">اطلاعات شخصی</h1>
                <button type="button" data-profile-edit class="press rounded-xl border border-[#e3d2a0] px-4 py-2 text-xs font-medium text-[#8a6300] hover:bg-[#fdeee0]">ویرایش اطلاعات</button>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-3">
                @csrf
                @method('PATCH')

                @foreach([
                    ['name', 'نام', $user->name, 'text'],
                    ['last_name', 'نام خانوادگی', $user->last_name, 'text'],
                    ['phone', 'شماره موبایل', $user->phone, 'tel'],
                    ['national_id', 'کد ملی', $user->national_id, 'text'],
                    ['birth_date', 'تاریخ تولد', optional($user->birth_date)->format('Y-m-d'), 'date'],
                    ['email', 'ایمیل', $user->email, 'email'],
                ] as [$name, $label, $value, $type])
                <label class="flex min-h-14 flex-col justify-center border-b border-[#f0e6c8] pb-3 text-right">
                    <span class="mb-1 text-xs text-[#a8a096]">{{ $label }}</span>
                    <input name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" @if(in_array($name, ['phone', 'national_id', 'birth_date'])) dir="ltr" @endif
                           @if(in_array($name, ['name', 'phone'])) required @endif
                           disabled data-profile-field
                           class="w-full bg-transparent text-sm font-medium text-[#2b2620] outline-none disabled:cursor-default disabled:text-[#2b2620]">
                    @error($name)<span class="mt-1 text-xs text-red-500">{{ $message }}</span>@enderror
                </label>
                @endforeach

                <div data-profile-actions class="hidden gap-3 md:col-span-3 md:justify-end">
                    <button type="button" data-profile-cancel class="press rounded-2xl border border-[#e3d2a0] px-6 py-3 text-sm text-[#6b6154]">انصراف</button>
                    <button type="submit" class="press rounded-2xl bg-[#8a6300] px-7 py-3 text-sm font-bold text-white hover:bg-[#6b4d00]">ذخیره تغییرات</button>
                </div>
            </form>
        </div>

        <x-account-sidebar active="profile" />
    </div>
</section>

<script>
(() => {
    const edit = document.querySelector('[data-profile-edit]');
    const cancel = document.querySelector('[data-profile-cancel]');
    const fields = [...document.querySelectorAll('[data-profile-field]')];
    const actions = document.querySelector('[data-profile-actions]');
    const setEditing = (editing) => {
        fields.forEach((field) => field.disabled = !editing);
        actions.classList.toggle('hidden', !editing);
        actions.classList.toggle('flex', editing);
        edit.classList.toggle('hidden', editing);
        if (editing) fields[0]?.focus();
    };
    edit?.addEventListener('click', () => setEditing(true));
    cancel?.addEventListener('click', () => { fields.forEach((field) => field.value = field.defaultValue); setEditing(false); });
    @if($errors->any()) setEditing(true); @endif
})();
</script>
@endsection
