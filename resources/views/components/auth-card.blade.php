@props(['title', 'subtitle' => null])

@php
    $heroBanner = \App\Models\Banner::where('type', 'hero')->where('is_active', true)->first();
@endphp

<div inert aria-hidden="true">
    <x-home-showcase :hero-banner="$heroBanner" />
</div>

<div class="fixed inset-0 z-[70] overflow-hidden bg-black/40" role="dialog" aria-modal="true" aria-labelledby="auth-title">
    <div class="absolute inset-x-0 bottom-0 max-h-[92vh] overflow-y-auto rounded-t-[30px] bg-white p-4 shadow-2xl md:bottom-auto md:left-1/2 md:right-auto md:top-1/2 md:w-full md:max-w-[436px] md:-translate-x-1/2 md:-translate-y-1/2 md:rounded-3xl">
        <span class="mx-auto mb-4 block h-1 w-10 rounded-full bg-[#aaa197] md:hidden"></span>
        <div class="mb-5 flex items-start justify-between gap-3">
            <div class="text-right">
                <h1 id="auth-title" class="text-lg font-bold text-[#2b2620]">{{ $title }}</h1>
                @if($subtitle)<p class="mt-2 text-sm text-[#6b6154]">{{ $subtitle }}</p>@endif
            </div>
            <a href="{{ route('home') }}" class="-m-1 flex size-8 shrink-0 items-center justify-center text-[#2b2620]" aria-label="بستن">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9.25" stroke-width="1.5"/><path stroke-linecap="round" stroke-width="1.5" d="m9.2 9.2 5.6 5.6m0-5.6-5.6 5.6"/></svg>
            </a>
        </div>

        {{ $slot }}
    </div>
</div>
