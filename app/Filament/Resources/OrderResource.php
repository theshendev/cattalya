<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'سفارش‌ها';

    protected static ?string $modelLabel = 'سفارش';

    protected static ?string $pluralModelLabel = 'سفارش‌ها';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('وضعیت سفارش')->schema([
                Forms\Components\Select::make('status')
                    ->label('وضعیت')
                    ->options(Order::STATUSES)
                    ->required(),
                Forms\Components\TextInput::make('tracking_code')
                    ->label('کد رهگیری'),
                Forms\Components\Textarea::make('notes')
                    ->label('یادداشت')
                    ->rows(2)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('اطلاعات سفارش')->schema([
                Infolists\Components\TextEntry::make('id')->label('شماره سفارش'),
                Infolists\Components\TextEntry::make('user.name')->label('مشتری'),
                Infolists\Components\TextEntry::make('user.phone')->label('موبایل'),
                Infolists\Components\TextEntry::make('status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn ($state) => Order::STATUSES[$state] ?? $state)
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'paid', 'delivered' => 'success',
                        'cancelled' => 'danger',
                        'shipped' => 'info',
                        default => 'warning',
                    }),
                Infolists\Components\TextEntry::make('total_amount')
                    ->label('مبلغ کل')
                    ->formatStateUsing(fn ($state) => number_format($state).' تومن'),
                Infolists\Components\TextEntry::make('discount_amount')
                    ->label('تخفیف')
                    ->formatStateUsing(fn ($state, Order $record) => number_format($state).' تومن'.($record->coupon_code ? " ({$record->coupon_code})" : '')),
                Infolists\Components\TextEntry::make('shipping_cost')
                    ->label('هزینه ارسال / بسته‌بندی')
                    ->formatStateUsing(fn ($state, Order $record) => number_format($state).' / '.number_format($record->packaging_cost).' تومن'),
                Infolists\Components\TextEntry::make('gift_message')
                    ->label('هدیه')
                    ->visible(fn (Order $record) => $record->is_gift)
                    ->formatStateUsing(fn ($state) => $state ?: 'بدون متن'),
                Infolists\Components\TextEntry::make('notes')->label('یادداشت مشتری')->placeholder('—'),
                Infolists\Components\TextEntry::make('gold_price_snapshot')
                    ->label('قیمت طلا در زمان ثبت')
                    ->formatStateUsing(fn ($state) => number_format($state).' تومن/گرم'),
                Infolists\Components\TextEntry::make('tracking_code')->label('کد رهگیری'),
                Infolists\Components\TextEntry::make('created_at')->label('تاریخ ثبت')->dateTime(),
            ])->columns(2),

            Infolists\Components\Section::make('آدرس تحویل')->schema([
                Infolists\Components\TextEntry::make('shipping_address.receiver_name')->label('نام گیرنده'),
                Infolists\Components\TextEntry::make('shipping_address.phone')->label('تلفن'),
                Infolists\Components\TextEntry::make('shipping_address.province')->label('استان'),
                Infolists\Components\TextEntry::make('shipping_address.city')->label('شهر'),
                Infolists\Components\TextEntry::make('shipping_address.address')->label('آدرس')->columnSpanFull(),
                Infolists\Components\TextEntry::make('shipping_address.plaque')->label('پلاک')->placeholder('—'),
                Infolists\Components\TextEntry::make('shipping_address.unit')->label('واحد')->placeholder('—'),
                Infolists\Components\TextEntry::make('shipping_address.postal_code')->label('کد پستی'),
            ])->columns(2),

            Infolists\Components\Section::make('اقلام سفارش')->schema([
                Infolists\Components\RepeatableEntry::make('items')->schema([
                    Infolists\Components\TextEntry::make('product_name')->label('محصول'),
                    Infolists\Components\TextEntry::make('weight_grams')->label('وزن')->suffix('g'),
                    Infolists\Components\TextEntry::make('unit_price')
                        ->label('قیمت واحد')
                        ->formatStateUsing(fn ($state) => number_format($state).' تومن'),
                    Infolists\Components\TextEntry::make('quantity')->label('تعداد'),
                ])->columns(4),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('مشتری')->searchable(),
                Tables\Columns\TextColumn::make('user.phone')->label('موبایل'),
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn ($state) => Order::STATUSES[$state] ?? $state)
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'paid', 'delivered' => 'success',
                        'cancelled' => 'danger',
                        'shipped' => 'info',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('مبلغ کل')
                    ->formatStateUsing(fn ($state) => number_format($state).' تومن')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(Order::STATUSES),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('مشاهده'),
                Tables\Actions\EditAction::make()->label('ویرایش'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
