@props(['title', 'subtitle' => null])

<div class="fixed inset-0 z-[70] overflow-hidden bg-[#263a3b]" role="dialog" aria-modal="true" aria-labelledby="auth-title">
    <div class="absolute inset-0 opacity-80">
        <div class="absolute -bottom-44 -left-12 size-[520px] rounded-full border-[38px] border-[#c8960c]"></div>
        <div class="absolute bottom-20 left-40 size-32 rotate-45 border-4 border-[#b9f1ff] bg-[#60c4e7] shadow-[0_0_60px_rgb(96_196_231_/_0.8)]"></div>
        <div class="absolute inset-y-0 right-0 flex w-2/3 items-center justify-center text-center text-[#fcfde3]">
            <div><p class="text-4xl font-bold md:text-6xl">درخشش ابدی،</p><p class="mt-4 text-4xl font-bold md:text-6xl">زیبایی جاودان.</p></div>
        </div>
    </div>
    <div class="absolute inset-0 bg-black/45"></div>

    <div class="absolute inset-x-0 bottom-0 max-h-[92vh] overflow-y-auto rounded-t-[30px] bg-white p-5 shadow-2xl md:bottom-auto md:left-1/2 md:right-auto md:top-1/2 md:w-full md:max-w-[436px] md:-translate-x-1/2 md:-translate-y-1/2 md:rounded-3xl md:p-6">
        <span class="mx-auto mb-4 block h-1 w-10 rounded-full bg-[#aaa197] md:hidden"></span>
        <div class="mb-5 flex items-start justify-between gap-3">
            <div class="text-right">
                <h1 id="auth-title" class="text-lg font-bold text-[#2b2620]">{{ $title }}</h1>
                @if($subtitle)<p class="mt-2 text-sm text-[#6b6154]">{{ $subtitle }}</p>@endif
            </div>
            <a href="{{ route('home') }}" class="flex size-8 shrink-0 items-center justify-center rounded-full border border-[#2b2620] text-lg" aria-label="بستن">×</a>
        </div>

        {{ $slot }}
    </div>
</div>
