<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make('همه')->badge(Order::count())];

        foreach (Order::STATUSES as $status => $label) {
            $tabs[$status] = Tab::make($label)
                ->icon(OrderResource::statusIcon($status))
                ->badge(Order::where('status', $status)->count())
                ->badgeColor(OrderResource::statusColor($status))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status));
        }

        return $tabs;
    }
}
