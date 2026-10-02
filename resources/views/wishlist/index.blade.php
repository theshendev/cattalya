@extends('layouts.app')

@section('title', 'علاقه‌مندی‌ها')

@section('content')
<section class="px-4 py-8 md:px-20 md:py-12">
    <div class="mx-auto flex max-w-[1353px] flex-col-reverse gap-5 lg:flex-row-reverse lg:items-start">
        <div class="w-full rounded-3xl bg-white p-5">
            <h1 class="border-b border-[#f0e6c8] pb-5 text-lg font-bold text-[#2b2620] md:text-xl">علاقه‌مندی‌ها</h1>
            @if($products->isEmpty())
                <div class="flex min-h-64 flex-col items-center justify-center gap-4 text-center">
                    <img src="/icons/heart.svg" alt="" class="size-10 opacity-40">
                    <p class="text-sm text-[#6b6154]">هنوز محصولی به علاقه‌مندی‌ها اضافه نکرده‌اید.</p>
                    <a href="{{ route('products.index') }}" class="press rounded-2xl bg-[#8a6300] px-6 py-3 text-sm font-bold text-white">مشاهده محصولات</a>
                </div>
            @else
                <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-5">
                    @foreach($products as $product)<x-product-card :product="$product" />@endforeach
                </div>
                <div class="mt-6">{{ $products->links() }}</div>
            @endif
        </div>
        <x-account-sidebar active="wishlist" />
    </div>
</section>
@endsection
