@props(['step' => 1, 'done' => false])

@php
    $steps = [
        1 => ['label' => 'سبد خرید', 'icon' => '/icons/cart.svg'],
        2 => ['label' => 'اطلاعات گیرنده', 'icon' => '/icons/routing.svg'],
        3 => ['label' => 'پرداخت', 'icon' => '/icons/shield.svg'],
    ];
@endphp

<ol class="flex items-center justify-center gap-3 rounded-3xl bg-white px-4 py-5 md:gap-5" aria-label="مراحل خرید">
    @foreach($steps as $number => $item)
        @php
            $isComplete = $done || $number < $step;
            $isCurrent = ! $done && $number === $step;
            $tone = $isComplete ? 'text-[#2f8f4e]' : ($isCurrent ? 'text-[#a34d06]' : 'text-[#a8a096]');
        @endphp
        <li class="flex items-center gap-3 md:gap-5" @if($isCurrent) aria-current="step" @endif>
            <span class="flex items-center gap-2 text-xs md:text-sm {{ $tone }}">
                <img src="{{ $item['icon'] }}" alt="" class="size-5 {{ $isCurrent || $isComplete ? '' : 'opacity-40' }}">
                <span class="{{ $isCurrent ? 'font-bold' : '' }}">{{ $item['label'] }}</span>
            </span>
            @unless($loop->last)
                <span class="h-px w-8 md:w-32 {{ $isComplete ? 'bg-[#2f8f4e]' : 'bg-[#d9d4cb]' }}"></span>
            @endunless
        </li>
    @endforeach
</ol>
