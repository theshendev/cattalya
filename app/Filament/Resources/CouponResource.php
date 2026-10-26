<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'کدهای تخفیف';

    protected static ?string $modelLabel = 'کد تخفیف';

    protected static ?string $pluralModelLabel = 'کدهای تخفیف';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->label('کد')
                ->required()
                ->unique(ignoreRecord: true)
                ->dehydrateStateUsing(fn (string $state) => strtoupper(trim($state))),
            Forms\Components\Select::make('type')
                ->label('نوع تخفیف')
                ->options(['percent' => 'درصدی', 'fixed' => 'مبلغ ثابت (تومن)'])
                ->default('percent')
                ->required(),
            Forms\Components\TextInput::make('value')
                ->label('مقدار')
                ->numeric()
                ->required(),
            Forms\Components\TextInput::make('min_order')
                ->label('حداقل مبلغ سفارش (تومن)')
                ->numeric()
                ->default(0),
            Forms\Components\TextInput::make('usage_limit')
                ->label('سقف تعداد استفاده')
                ->numeric(),
            Forms\Components\DateTimePicker::make('expires_at')
                ->label('تاریخ انقضا'),
            Forms\Components\Toggle::make('is_active')
                ->label('فعال')
                ->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('کد')->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('نوع')
                    ->formatStateUsing(fn (string $state) => $state === 'percent' ? 'درصدی' : 'مبلغ ثابت'),
                Tables\Columns\TextColumn::make('value')->label('مقدار')->numeric(),
                Tables\Columns\TextColumn::make('used_count')->label('دفعات استفاده'),
                Tables\Columns\TextColumn::make('expires_at')->label('انقضا')->dateTime(),
                Tables\Columns\IconColumn::make('is_active')->label('فعال')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCoupons::route('/'),
        ];
    }
}
