@props(['size' => 'md'])

@php
$scale = match($size) {
    'sm' => 0.8,
    'lg' => 1.3,
    default => 1.0,
};
// Base dimensions from Figma: icon mark ~40×40px, gap ~8.89px, text 20px
$iconW  = round(39.844 * $scale, 2);  // Frame width
$iconH  = round(40 * $scale, 2);       // Frame height
$gap    = round(8.89 * $scale, 2);

// Layer positions (from Figma, scaled)
// Vector (stem): w=20.284, h=33.579, ml=0.18, mt=3.29 — inset bleed -2.36% -11.63% -2.36% -3.83%
$sv_w   = round(20.284 * $scale, 2);
$sv_h   = round(33.579 * $scale, 2);
$sv_ml  = round(0.18 * $scale, 2);
$sv_mt  = round(3.29 * $scale, 2);

// Vector2 (leaf-bottom): w=23.865, h=15.316, ml=16.14, mt=24.49
$lb_w   = round(23.865 * $scale, 2);
$lb_h   = round(15.316 * $scale, 2);
$lb_ml  = round(16.14 * $scale, 2);
$lb_mt  = round(24.49 * $scale, 2);

// Vector3 (leaf-top): w=23.865, h=15.311, ml=16.14, mt=0.63
$lt_w   = round(23.865 * $scale, 2);
$lt_h   = round(15.311 * $scale, 2);
$lt_ml  = round(16.14 * $scale, 2);
$lt_mt  = round(0.63 * $scale, 2);

// Group30 (dot/gem): w=18.14, h=14.784, ml=11.39, mt=14.75
$dot_w  = round(18.14 * $scale, 2);
$dot_h  = round(14.784 * $scale, 2);
$dot_ml = round(11.39 * $scale, 2);
$dot_mt = round(14.75 * $scale, 2);

$textSize = match($size) {
    'sm' => '16px',
    'lg' => '26px',
    default => '20px',
};
@endphp

<div class="flex items-center" dir="ltr" style="gap: {{ $gap }}px">
    {{-- Icon mark: all layers share one CSS grid cell (col-1 row-1) so they stack --}}
    <div class="inline-grid place-items-start leading-[0] relative shrink-0"
         style="grid-template-columns: max-content; grid-template-rows: max-content; width: {{ $iconW }}px; height: {{ $iconH }}px">

        {{-- stem (Vector) --}}
        <div class="col-start-1 row-start-1 relative"
             style="width:{{ $sv_w }}px; height:{{ $sv_h }}px; margin-left:{{ $sv_ml }}px; margin-top:{{ $sv_mt }}px">
            <div class="absolute" style="inset: -2.36% -11.63% -2.36% -3.83%">
                <img src="/logo/stem.svg" alt="" class="block max-w-none size-full">
            </div>
        </div>

        {{-- leaf-bottom (Vector2) --}}
        <div class="col-start-1 row-start-1 relative"
             style="width:{{ $lb_w }}px; height:{{ $lb_h }}px; margin-left:{{ $lb_ml }}px; margin-top:{{ $lb_mt }}px">
            <div class="absolute" style="inset: -5.08% -3.26% -5.08% -16.68%">
                <img src="/logo/leaf-bottom.svg" alt="" class="block max-w-none size-full">
            </div>
        </div>

        {{-- leaf-top (Vector3) --}}
        <div class="col-start-1 row-start-1 relative"
             style="width:{{ $lt_w }}px; height:{{ $lt_h }}px; margin-left:{{ $lt_ml }}px; margin-top:{{ $lt_mt }}px">
            <div class="absolute" style="inset: -5.08% -4.09% -5.08% -16.74%">
                <img src="/logo/leaf-top.svg" alt="" class="block max-w-none size-full">
            </div>
        </div>

        {{-- dot/gem (Group30) --}}
        <div class="col-start-1 row-start-1 inline-grid place-items-start relative"
             style="grid-template-columns: max-content; grid-template-rows: max-content; margin-left:{{ $dot_ml }}px; margin-top:{{ $dot_mt }}px">
            <div class="col-start-1 row-start-1 relative"
                 style="width:{{ $dot_w }}px; height:{{ $dot_h }}px">
                <img src="/logo/dot.svg" alt="" class="absolute block inset-0 max-w-none size-full">
            </div>
        </div>

        {{-- C-shape Frame: w=39.844, h=40, ml=0, mt=0, overflow hidden --}}
        <div class="col-start-1 row-start-1 overflow-clip relative"
             style="width:{{ $iconW }}px; height:{{ $iconH }}px; margin-left:0; margin-top:0">
            {{-- c-top (Group): inset 8.52% 45.41% 7.64% -0.42% --}}
            <div class="absolute" style="inset: 8.52% 45.41% 7.64% -0.42%">
                <img src="/logo/c-top.svg" alt="" class="absolute block inset-0 max-w-none size-full">
            </div>
            {{-- c-mid (Group1): inset 60.15% 0 0 37.4% --}}
            <div class="absolute" style="inset: 60.15% 0 0 37.4%">
                <img src="/logo/c-mid.svg" alt="" class="absolute block inset-0 max-w-none size-full">
            </div>
            {{-- c-bot (Group2): inset 1.26% -0.42% 58.95% 34.83% --}}
            <div class="absolute" style="inset: 1.26% -0.42% 58.95% 34.83%">
                <img src="/logo/c-bot.svg" alt="" class="absolute block inset-0 max-w-none size-full">
            </div>
        </div>
    </div>

    {{-- Brand name --}}
    <span class="font-bold shrink-0"
          style="font-family: 'Vazirmatn', sans-serif; font-size: {{ $textSize }}; color: #8a6300; letter-spacing: 0.05em; text-transform: uppercase">
        Cattalya
    </span>
</div>
