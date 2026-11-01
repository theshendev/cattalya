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

    protected static ?string $navigationGroup = 'فروشگاه';

    protected static ?string $navigationLabel = 'قیمت طلا';

    protected static ?string $modelLabel = 'قیمت طلا';

    protected static ?string $pluralModelLabel = 'تاریخچه قیمت طلا';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('ثبت قیمت جدید')
                ->description('قیمت جدید بلافاصله در محاسبه قیمت همه محصولات استفاده می‌شود.')
                ->icon('heroicon-o-currency-dollar')
                ->schema([
                    Forms\Components\TextInput::make('price_per_gram')
                        ->label('قیمت هر گرم طلای ۱۸ عیار')
                        ->suffix('تومن')
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
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('price_per_gram')
                    ->label('قیمت هر گرم')
                    ->weight('bold')
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => number_format($state).' تومن')
                    ->sortable(),
                Tables\Columns\TextColumn::make('source')
                    ->label('منبع')
                    ->badge()
                    ->icon(fn ($state) => $state === 'api' ? 'heroicon-o-bolt' : 'heroicon-o-pencil')
                    ->formatStateUsing(fn ($state) => $state === 'api' ? 'API خودکار' : 'دستی')
                    ->color(fn ($state) => $state === 'api' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('fetched_at')
                    ->label('زمان دریافت')
                    ->dateTime()
                    ->description(fn ($record) => $record->fetched_at?->diffForHumans())
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('ثبت شده')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->label('منبع')
                    ->options(['api' => 'API خودکار', 'manual' => 'دستی']),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('ورود دستی قیمت'),
            ])
            ->emptyStateHeading('هنوز قیمتی ثبت نشده')
            ->emptyStateIcon('heroicon-o-currency-dollar');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGoldPrices::route('/'),
            'create' => Pages\CreateGoldPrice::route('/create'),
        ];
    }
}
