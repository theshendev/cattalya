<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class SalesChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'فروش ۳۰ روز گذشته';

    protected static ?string $maxHeight = '260px';

    protected int|string|array $columnSpan = ['default' => 'full', 'xl' => 2];

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn (int $daysAgo) => today()->subDays($daysAgo));

        $revenueByDay = Order::query()
            ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])
            ->where('created_at', '>=', $days->first())
            ->get(['total_amount', 'created_at'])
            ->groupBy(fn (Order $order) => $order->created_at->toDateString())
            ->map(fn ($orders) => (int) $orders->sum('total_amount'));

        return [
            'datasets' => [[
                'label' => 'فروش (تومن)',
                'data' => $days->map(fn ($day) => $revenueByDay->get($day->toDateString(), 0))->all(),
                'borderColor' => '#8a6300',
                'backgroundColor' => 'rgba(138, 99, 0, 0.12)',
                'fill' => true,
                'tension' => 0.35,
            ]],
            'labels' => $days->map(fn ($day) => $day->format('m/d'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
