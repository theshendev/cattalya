@props(['category' => null])

<div {{ $attributes->merge(['class' => 'flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_50%_42%,#fff_0,#fbfaf7_58%,#f3f0eb_100%)] text-[#c8960c]']) }} aria-hidden="true">
    <svg class="h-[72%] w-[72%] drop-shadow-[0_8px_18px_rgb(138_99_0_/_0.12)]" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
        @switch($category)
            @case('earring')
                <path d="M50 40v28m60-28v28" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                <path d="M50 65c-17 12-23 29-13 42 8 11 20 5 19-7-1-11-9-22-6-35Zm60 0c17 12 23 29 13 42-8 11-20 5-19-7 1-11 9-22 6-35Z" stroke="currentColor" stroke-width="3"/>
                <circle cx="50" cy="36" r="4" fill="currentColor"/><circle cx="110" cy="36" r="4" fill="currentColor"/>
                @break
            @case('ring')
                <circle cx="80" cy="96" r="35" stroke="currentColor" stroke-width="5"/>
                <path d="m80 34 18 20-18 20-18-20 18-20Z" fill="#fff" stroke="currentColor" stroke-width="4"/>
                <path d="m70 44 10 10 10-10" stroke="#ead184" stroke-width="2"/>
                @break
            @case('bangle')
            @case('bracelet')
                <circle cx="80" cy="80" r="49" stroke="currentColor" stroke-width="5"/>
                <circle cx="80" cy="80" r="40" stroke="#ead184" stroke-width="2" stroke-dasharray="4 5"/>
                <path d="m80 116-10-10a7 7 0 0 1 10-10 7 7 0 0 1 10 10l-10 10Z" fill="#fff" stroke="currentColor" stroke-width="3"/>
                @break
            @case('necklace')
                <path d="M27 48c7 48 29 71 53 71s46-23 53-71" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                <path d="m80 127-13-13a9 9 0 0 1 13-13 9 9 0 0 1 13 13l-13 13Z" fill="#fff" stroke="currentColor" stroke-width="3"/>
                @break
            @case('pendant')
                <path d="M80 21v49" stroke="currentColor" stroke-width="3"/>
                <path d="M80 61c6 15 18 18 31 13-5 13-2 25 13 31-15 6-18 18-13 31-13-5-25-2-31 13-6-15-18-18-31-13 5-13 2-25-13-31 15-6 18-18 13-31 13 5 25 2 31-13Z" fill="#fff" stroke="currentColor" stroke-width="3"/>
                <circle cx="80" cy="105" r="13" fill="#f8e8b5"/>
                @break
            @case('anklet')
                <path d="M24 75c35-27 77-27 112 0-22 38-90 38-112 0Z" stroke="currentColor" stroke-width="4"/>
                <circle cx="44" cy="70" r="4" fill="currentColor"/><circle cx="65" cy="61" r="4" fill="currentColor"/><circle cx="95" cy="61" r="4" fill="currentColor"/><circle cx="116" cy="70" r="4" fill="currentColor"/>
                <path d="M80 58v40m-8 0h16" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                @break
            @default
                <path d="M80 24c8 23 20 35 44 43-24 8-36 20-44 44-8-24-20-36-44-44 24-8 36-20 44-43Z" fill="#fff" stroke="currentColor" stroke-width="4"/>
                <circle cx="80" cy="67" r="15" fill="#f8e8b5"/>
        @endswitch
    </svg>
</div>
