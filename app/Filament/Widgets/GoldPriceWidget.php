<?php

namespace App\Filament\Widgets;

use App\Models\GoldPrice;
use Filament\Widgets\Widget;

class GoldPriceWidget extends Widget
{
    protected static ?int $sort = 2;

    protected static string $view = 'filament.widgets.gold-price-widget';

    protected int|string|array $columnSpan = 'full';

    public function getGoldPrice(): ?GoldPrice
    {
        return GoldPrice::current();
    }
}
