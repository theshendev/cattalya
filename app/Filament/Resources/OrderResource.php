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

    protected static ?string $navigationGroup = 'فروشگاه';

    protected static ?string $navigationLabel = 'سفارش‌ها';

    protected static ?string $modelLabel = 'سفارش';

    protected static ?string $pluralModelLabel = 'سفارش‌ها';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $pendingCount = Order::where('status', 'pending')->count();

        return $pendingCount > 0 ? (string) $pendingCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function statusColor(?string $status): string
    {
        return match ($status) {
            'paid', 'delivered' => 'success',
            'cancelled' => 'danger',
            'shipped' => 'info',
            'processing' => 'primary',
            default => 'warning',
        };
    }

    public static function statusIcon(?string $status): string
    {
        return match ($status) {
            'paid' => 'heroicon-o-check-circle',
            'processing' => 'heroicon-o-cog-6-tooth',
            'shipped' => 'heroicon-o-truck',
            'delivered' => 'heroicon-o-gift',
            'cancelled' => 'heroicon-o-x-circle',
            default => 'heroicon-o-clock',
        };
    }

    public static function formatToman(mixed $amount): string
    {
        return number_format((float) $amount).' تومن';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('وضعیت سفارش')
                ->description('وضعیت و کد رهگیری سفارش را به‌روز کنید.')
                ->icon('heroicon-o-truck')
                ->schema([
                    Forms\Components\ToggleButtons::make('status')
                        ->label('وضعیت')
                        ->options(Order::STATUSES)
                        ->colors(collect(Order::STATUSES)->mapWithKeys(fn ($label, $key) => [$key => static::statusColor($key)])->all())
                        ->icons(collect(Order::STATUSES)->mapWithKeys(fn ($label, $key) => [$key => static::statusIcon($key)])->all())
                        ->inline()
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('tracking_code')
                        ->label('کد رهگیری')
                        ->prefixIcon('heroicon-o-qr-code')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('notes')
                        ->label('یادداشت')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(2),
        ])->columns(1);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Grid::make(['default' => 1, 'xl' => 3])->schema([
                Infolists\Components\Group::make()->columnSpan(['xl' => 2])->schema([
                    Infolists\Components\Section::make('اقلام سفارش')
                        ->icon('heroicon-o-shopping-bag')
                        ->schema([
                            Infolists\Components\RepeatableEntry::make('items')
                                ->hiddenLabel()
                                ->schema([
                                    Infolists\Components\TextEntry::make('product_name')->label('محصول')->weight('bold')->columnSpan(2),
                                    Infolists\Components\TextEntry::make('weight_grams')->label('وزن')->suffix(' گرم'),
                                    Infolists\Components\TextEntry::make('unit_price')->label('قیمت واحد')->formatStateUsing(fn ($state) => static::formatToman($state)),
                                    Infolists\Components\TextEntry::make('quantity')->label('تعداد')->badge()->color('gray'),
                                ])->columns(5),
                        ]),

                    Infolists\Components\Section::make('آدرس تحویل')
                        ->icon('heroicon-o-map-pin')
                        ->schema([
                            Infolists\Components\TextEntry::make('shipping_address.receiver_name')->label('نام گیرنده')->icon('heroicon-o-user'),
                            Infolists\Components\TextEntry::make('shipping_address.phone')->label('تلفن')->icon('heroicon-o-phone')->copyable(),
                            Infolists\Components\TextEntry::make('shipping_address.province')->label('استان'),
                            Infolists\Components\TextEntry::make('shipping_address.city')->label('شهر'),
                            Infolists\Components\TextEntry::make('shipping_address.address')->label('آدرس')->columnSpanFull(),
                            Infolists\Components\TextEntry::make('shipping_address.plaque')->label('پلاک')->placeholder('—'),
                            Infolists\Components\TextEntry::make('shipping_address.unit')->label('واحد')->placeholder('—'),
                            Infolists\Components\TextEntry::make('shipping_address.postal_code')->label('کد پستی')->copyable(),
                        ])->columns(2),
                ]),

                Infolists\Components\Group::make()->columnSpan(['xl' => 1])->schema([
                    Infolists\Components\Section::make('وضعیت')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Infolists\Components\TextEntry::make('id')->label('شماره سفارش')->prefix('#')->weight('bold'),
                            Infolists\Components\TextEntry::make('status')
                                ->label('وضعیت')
                                ->formatStateUsing(fn ($state) => Order::STATUSES[$state] ?? $state)
                                ->badge()
                                ->size('lg')
                                ->icon(fn ($state) => static::statusIcon($state))
                                ->color(fn ($state) => static::statusColor($state)),
                            Infolists\Components\TextEntry::make('tracking_code')->label('کد رهگیری')->placeholder('—')->copyable(),
                            Infolists\Components\TextEntry::make('created_at')->label('تاریخ ثبت')->dateTime(),
                        ]),

                    Infolists\Components\Section::make('مشتری')
                        ->icon('heroicon-o-user-circle')
                        ->schema([
                            Infolists\Components\TextEntry::make('user.name')->label('نام'),
                            Infolists\Components\TextEntry::make('user.phone')->label('موبایل')->copyable(),
                            Infolists\Components\TextEntry::make('notes')->label('یادداشت مشتری')->placeholder('—'),
                            Infolists\Components\TextEntry::make('gift_message')
                                ->label('پیام هدیه')
                                ->icon('heroicon-o-gift')
                                ->visible(fn (Order $record) => $record->is_gift)
                                ->formatStateUsing(fn ($state) => $state ?: 'بدون متن'),
                        ]),

                    Infolists\Components\Section::make('خلاصه مالی')
                        ->icon('heroicon-o-banknotes')
                        ->schema([
                            Infolists\Components\TextEntry::make('subtotal_amount')->label('جمع اقلام')->formatStateUsing(fn ($state) => static::formatToman($state)),
                            Infolists\Components\TextEntry::make('shipping_cost')->label('هزینه ارسال')->formatStateUsing(fn ($state) => static::formatToman($state)),
                            Infolists\Components\TextEntry::make('packaging_cost')->label('بسته‌بندی')->formatStateUsing(fn ($state) => static::formatToman($state)),
                            Infolists\Components\TextEntry::make('discount_amount')
                                ->label(fn (Order $record) => $record->coupon_code ? "تخفیف ({$record->coupon_code})" : 'تخفیف')
                                ->color('danger')
                                ->formatStateUsing(fn ($state) => static::formatToman($state)),
                            Infolists\Components\TextEntry::make('total_amount')
                                ->label('مبلغ کل')
                                ->weight('bold')
                                ->size('lg')
                                ->color('primary')
                                ->formatStateUsing(fn ($state) => static::formatToman($state)),
                            Infolists\Components\TextEntry::make('gold_price_snapshot')
                                ->label('قیمت طلا در زمان ثبت')
                                ->formatStateUsing(fn ($state) => static::formatToman($state).' / گرم'),
                        ]),
                ]),
            ]),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('سفارش')->prefix('#')->weight('bold')->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('مشتری')
                    ->description(fn (Order $record) => $record->user?->phone)
                    ->searchable(query: fn ($query, string $search) => $query->whereHas(
                        'user',
                        fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")
                    )),
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn ($state) => Order::STATUSES[$state] ?? $state)
                    ->badge()
                    ->icon(fn ($state) => static::statusIcon($state))
                    ->color(fn ($state) => static::statusColor($state)),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('مبلغ کل')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => static::formatToman($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('tracking_code')->label('کد رهگیری')->placeholder('—')->copyable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->dateTime()
                    ->description(fn (Order $record) => $record->created_at->diffForHumans())
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->recordUrl(fn (Order $record) => static::getUrl('view', ['record' => $record]))
            ->filters([
                Tables\Filters\Filter::make('today')
                    ->label('فقط امروز')
                    ->toggle()
                    ->query(fn ($query) => $query->whereDate('created_at', today())),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('مشاهده'),
                    Tables\Actions\EditAction::make()->label('ویرایش وضعیت'),
                    Tables\Actions\Action::make('markShipped')
                        ->label('علامت‌گذاری به‌عنوان ارسال‌شده')
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(fn (Order $record) => in_array($record->status, ['paid', 'processing'], true))
                        ->action(fn (Order $record) => $record->update(['status' => 'shipped'])),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('changeStatus')
                    ->label('تغییر وضعیت')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('status')->label('وضعیت جدید')->options(Order::STATUSES)->required(),
                    ])
                    ->action(fn ($records, array $data) => $records->each->update(['status' => $data['status']]))
                    ->deselectRecordsAfterCompletion(),
            ])
            ->emptyStateHeading('هنوز سفارشی ثبت نشده')
            ->emptyStateIcon('heroicon-o-shopping-cart');
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
