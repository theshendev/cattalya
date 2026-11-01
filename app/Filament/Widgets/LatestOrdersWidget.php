<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestOrdersWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'آخرین سفارش‌ها';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->with('user')->latest()->limit(8))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#'),
                Tables\Columns\TextColumn::make('user.name')->label('مشتری'),
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
                    ->formatStateUsing(fn ($state) => number_format($state).' تومن'),
                Tables\Columns\TextColumn::make('created_at')->label('تاریخ ثبت')->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('مشاهده')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Order $record) => OrderResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
