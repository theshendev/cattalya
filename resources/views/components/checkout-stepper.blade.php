@props(['step' => 1, 'done' => false])

@php
    $steps = [
        1 => ['label' => 'سبد خرید', 'icon' => 'M6 9h12l-1 11H7L6 9Zm3 0V7a3 3 0 0 1 6 0v2m-6 4h.01M15 13h.01'],
        2 => ['label' => 'اطلاعات گیرنده', 'icon' => 'M9 4h6v3H9zM7 5.5H6a1 1 0 0 0-1 1V20a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V6.5a1 1 0 0 0-1-1h-1M9 12h6m-6 4h4'],
        3 => ['label' => 'پرداخت', 'icon' => 'M5 9.5 15 5l2 4M4 9.5h15a1 1 0 0 1 1 1V18a1 1 0 0 1-1 1H9m11-6h-4a1.5 1.5 0 0 0 0 3h4M4 9.5V13m3.5 7a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm-1.3-3.5 1 1 1.8-1.8'],
    ];
@endphp

<ol class="flex items-center justify-between gap-2 rounded-3xl bg-white px-4 py-5 md:justify-center md:gap-5 md:py-6" aria-label="مراحل خرید">
    @foreach($steps as $number => $item)
        @php
            $isComplete = $done || $number < $step;
            $isCurrent = ! $done && $number === $step;
            $tone = $isComplete ? 'text-[#2f8f4e]' : ($isCurrent ? 'text-[#a34d06]' : 'text-[#a8a096]');
        @endphp
        <li class="flex items-center gap-2 md:gap-5" @if($isCurrent) aria-current="step" @endif>
            <span class="flex items-center gap-1.5 whitespace-nowrap text-xs md:gap-2 md:text-base {{ $tone }}">
                <svg class="size-5 shrink-0 md:size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"/></svg>
                <span>{{ $item['label'] }}</span>
            </span>
            @unless($loop->last)
                <span class="h-px w-5 sm:w-10 md:w-28 {{ $isComplete ? 'bg-[#2f8f4e]' : 'bg-[#a8a096]' }}"></span>
            @endunless
        </li>
    @endforeach
</ol>
