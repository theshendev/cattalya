<?php

namespace App\Filament\Resources\GoldPriceResource\Pages;

use App\Filament\Resources\GoldPriceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGoldPrices extends ListRecords
{
    protected static string $resource = GoldPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
