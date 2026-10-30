@if($isInStock)
<form method="POST" action="{{ route('cart.add', $product) }}">
    @csrf
    <button type="submit" class="press flex h-12 w-full items-center justify-center gap-2 rounded-2xl bg-[#111] font-medium text-white hover:bg-[#2b2620]">
        <img src="/icons/cart.svg" alt="" class="size-5 invert">
        افزودن به سبد خرید
    </button>
</form>
@else
<div class="flex h-12 w-full items-center justify-center rounded-2xl bg-[#f0e6c8] font-medium text-[#a8a096]">ناموجود</div>
@endif
