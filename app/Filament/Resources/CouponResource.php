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

    protected static ?string $navigationGroup = 'بازاریابی';

    protected static ?string $navigationLabel = 'کدهای تخفیف';

    protected static ?string $modelLabel = 'کد تخفیف';

    protected static ?string $pluralModelLabel = 'کدهای تخفیف';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('کد تخفیف')
                ->icon('heroicon-o-ticket')
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('کد')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->extraInputAttributes(['dir' => 'ltr', 'class' => 'uppercase'])
                        ->dehydrateStateUsing(fn (string $state) => strtoupper(trim($state))),
                    Forms\Components\ToggleButtons::make('type')
                        ->label('نوع تخفیف')
                        ->options(['percent' => 'درصدی', 'fixed' => 'مبلغ ثابت'])
                        ->icons(['percent' => 'heroicon-o-receipt-percent', 'fixed' => 'heroicon-o-banknotes'])
                        ->default('percent')
                        ->inline()
                        ->live()
                        ->required(),
                    Forms\Components\TextInput::make('value')
                        ->label('مقدار')
                        ->suffix(fn (Forms\Get $get) => $get('type') === 'fixed' ? 'تومن' : '٪')
                        ->numeric()
                        ->required(),
                ])->columns(3),

            Forms\Components\Section::make('محدودیت‌ها')
                ->icon('heroicon-o-adjustments-horizontal')
                ->schema([
                    Forms\Components\TextInput::make('min_order')->label('حداقل مبلغ سفارش')->suffix('تومن')->numeric()->default(0),
                    Forms\Components\TextInput::make('usage_limit')->label('سقف تعداد استفاده')->numeric()->helperText('خالی = نامحدود'),
                    Forms\Components\DateTimePicker::make('expires_at')->label('تاریخ انقضا'),
                    Forms\Components\Toggle::make('is_active')->label('فعال')->default(true),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('کد')->weight('bold')->fontFamily('mono')->copyable()->searchable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('تخفیف')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'percent' ? number_format($state).'٪' : number_format($state).' تومن'),
                Tables\Columns\TextColumn::make('used_count')
                    ->label('استفاده')
                    ->formatStateUsing(fn ($state, $record) => $state.($record->usage_limit ? ' از '.$record->usage_limit : ' بار')),
                Tables\Columns\TextColumn::make('expires_at')->label('انقضا')->dateTime()->placeholder('بدون انقضا')->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')->label('فعال'),
            ])
            ->striped()
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف انتخاب‌شده‌ها'),
                ]),
            ])
            ->emptyStateHeading('هنوز کد تخفیفی ثبت نشده')
            ->emptyStateIcon('heroicon-o-ticket');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCoupons::route('/'),
        ];
    }
}
