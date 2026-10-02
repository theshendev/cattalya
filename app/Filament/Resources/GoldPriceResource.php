<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GoldPriceResource\Pages;
use App\Models\GoldPrice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GoldPriceResource extends Resource
{
    protected static ?string $model = GoldPrice::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'قیمت طلا';

    protected static ?string $modelLabel = 'قیمت طلا';

    protected static ?string $pluralModelLabel = 'تاریخچه قیمت طلا';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('price_per_gram')
                ->label('قیمت هر گرم طلا ۱۸ عیار (تومن)')
                ->numeric()
                ->required(),
            Forms\Components\Select::make('source')
                ->label('منبع')
                ->options(['api' => 'API خودکار', 'manual' => 'ورود دستی'])
                ->default('manual')
                ->required(),
            Forms\Components\DateTimePicker::make('fetched_at')
                ->label('زمان دریافت')
                ->default(now()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('price_per_gram')
                    ->label('قیمت هر گرم (تومن)')
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('source')
                    ->label('منبع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'api' ? 'API خودکار' : 'دستی')
                    ->color(fn ($state) => $state === 'api' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('fetched_at')
                    ->label('زمان دریافت')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('ثبت شده')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('ورود دستی قیمت'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGoldPrices::route('/'),
            'create' => Pages\CreateGoldPrice::route('/create'),
        ];
    }
}
