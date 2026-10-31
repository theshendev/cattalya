<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Banner;
use App\Models\Category;
use App\Models\GoldPrice;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $goldPrice = GoldPrice::current();
        $announcement = Announcement::where('is_active', true)->latest()->first();
        $heroBanner = Banner::where('type', 'hero')->where('is_active', true)->first();
        $promoLeft = Banner::where('type', 'promo_left')->where('is_active', true)->first();
        $promoRight = Banner::where('type', 'promo_right')->where('is_active', true)->first();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        $newestProducts = Product::with(['category', 'media'])
            ->available()
            ->latest()
            ->take(8)
            ->get();

        $featuredProducts = Product::with(['category', 'media'])
            ->available()
            ->inRandomOrder()
            ->take(8)
            ->get();

        return view('home', compact(
            'goldPrice', 'announcement', 'heroBanner',
            'promoLeft', 'promoRight', 'categories',
            'newestProducts', 'featuredProducts',
        ));
    }
}
