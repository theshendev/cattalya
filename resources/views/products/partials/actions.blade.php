@auth
<form method="POST" action="{{ $isWished ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}" data-no-loading>
    @csrf
    @if($isWished) @method('DELETE') @endif
    <button type="submit" aria-label="{{ $isWished ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها' }}" class="flex size-10 items-center justify-center rounded-full border border-[#f0e6c8] bg-white/90 transition-transform hover:scale-110 md:size-9 md:border-0 md:bg-transparent">
        <img src="{{ $isWished ? '/icons/heart-filled.svg' : '/icons/heart.svg' }}" alt="" class="size-5 md:size-6">
    </button>
</form>
@else
<a href="{{ route('login') }}" aria-label="برای افزودن به علاقه‌مندی‌ها وارد شوید" class="flex size-10 items-center justify-center rounded-full border border-[#f0e6c8] bg-white/90 transition-transform hover:scale-110 md:size-9 md:border-0 md:bg-transparent">
    <img src="/icons/heart.svg" alt="" class="size-5 md:size-6">
</a>
@endauth
<button type="button" data-share-product aria-label="اشتراک‌گذاری" class="flex size-10 items-center justify-center text-[#4d4942] transition-transform hover:scale-110 md:size-9">
    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="6" r="2.5" stroke-width="1.4"/><circle cx="6" cy="17" r="2.5" stroke-width="1.4"/><circle cx="18" cy="17" r="2.5" stroke-width="1.4"/><path stroke-linecap="round" stroke-width="1.4" d="M9.6 6.9A7 7 0 0 0 5.1 12.4m13.8 0a7 7 0 0 0-4.5-5.5M9 19.6a7 7 0 0 0 6 0"/></svg>
</button>
