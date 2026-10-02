<?php

namespace App\Filament\Widgets;

use App\Models\GoldPrice;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayOrders = Order::whereDate('created_at', today())->count();
        $todayRevenue = Order::whereDate('created_at', today())
            ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])
            ->sum('total_amount');
        $pendingOrders = Order::where('status', 'pending')->count();
        $lowStock = Product::where('stock', '<', 3)->where('is_active', true)->count();
        $currentGoldPrice = GoldPrice::currentPricePerGram();

        return [
            Stat::make('سفارش‌های امروز', $todayOrders)
                ->description('تعداد سفارش‌های ثبت شده امروز')
                ->icon('heroicon-o-shopping-cart')
                ->color('primary'),

            Stat::make('درآمد امروز', number_format($todayRevenue).' تومن')
                ->description('از سفارش‌های پرداخت‌شده')
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('در انتظار پرداخت', $pendingOrders)
                ->description('سفارش‌های منتظر پرداخت')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('موجودی کم', $lowStock.' محصول')
                ->description('محصولاتی که کمتر از ۳ عدد دارند')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($lowStock > 0 ? 'danger' : 'success'),
        ];
    }
}
