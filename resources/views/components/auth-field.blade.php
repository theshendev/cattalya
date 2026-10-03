@props(['name', 'label', 'type' => 'text', 'value' => null, 'ltr' => false, 'password' => false])

<div>
    <label class="relative block rounded-2xl border px-4 pb-2 pt-2 text-right {{ $errors->has($name) ? 'border-red-400' : 'border-[#ead69e]' }} focus-within:border-[#c8960c]">
        <span class="block text-[10px] text-[#8b8277]">{{ $label }}</span>
        <input name="{{ $name }}" type="{{ $type }}" value="{{ $password ? '' : old($name, $value) }}"
               @if($ltr) dir="ltr" @endif
               {{ $attributes->merge(['class' => 'w-full border-0 bg-transparent py-1 text-right text-sm outline-none '.($password ? 'pl-8' : '')]) }}>
        @if($password)
        <button type="button" data-toggle-password class="absolute left-4 top-1/2 -translate-y-1/2 text-[#6b6154]" aria-label="نمایش رمز عبور">
            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="1.6" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3" stroke-width="1.6"/></svg>
        </button>
        @endif
    </label>
    @error($name)<p class="mt-1 text-right text-xs text-red-500">{{ $message }}</p>@enderror
</div>
