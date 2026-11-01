<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('همه')->badge(Product::count()),
            'active' => Tab::make('فعال')
                ->icon('heroicon-o-eye')
                ->badge(Product::where('is_active', true)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),
            'inactive' => Tab::make('غیرفعال')
                ->icon('heroicon-o-eye-slash')
                ->badge(Product::where('is_active', false)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)),
            'low_stock' => Tab::make('موجودی کم')
                ->icon('heroicon-o-exclamation-triangle')
                ->badge(Product::where('stock', '<', 3)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('stock', '<', 3)),
        ];
    }
}
