<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'ارتباطات';

    protected static ?string $navigationLabel = 'پیام‌های تماس';

    protected static ?string $modelLabel = 'پیام';

    protected static ?string $pluralModelLabel = 'پیام‌های تماس';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $unreadCount = ContactMessage::whereNull('read_at')->count();

        return $unreadCount > 0 ? (string) $unreadCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('فرستنده')
                ->icon('heroicon-o-user')
                ->schema([
                    Forms\Components\TextInput::make('first_name')->label('نام')->disabled(),
                    Forms\Components\TextInput::make('last_name')->label('نام خانوادگی')->disabled(),
                    Forms\Components\TextInput::make('email')->label('ایمیل')->prefixIcon('heroicon-o-envelope')->disabled(),
                    Forms\Components\TextInput::make('phone')->label('شماره تماس')->prefixIcon('heroicon-o-phone')->disabled(),
                ])->columns(2),
            Forms\Components\Section::make('پیام')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->schema([
                    Forms\Components\Textarea::make('description')->hiddenLabel()->rows(5)->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                Tables\Columns\IconColumn::make('read_at')
                    ->label('')
                    ->getStateUsing(fn (ContactMessage $record) => $record->read_at !== null)
                    ->icon(fn (bool $state) => $state ? 'heroicon-o-envelope-open' : 'heroicon-s-envelope')
                    ->color(fn (bool $state) => $state ? 'gray' : 'primary'),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('فرستنده')
                    ->weight(fn (ContactMessage $record) => $record->read_at === null ? 'bold' : 'normal')
                    ->formatStateUsing(fn ($state, ContactMessage $record) => $record->first_name.' '.$record->last_name)
                    ->description(fn (ContactMessage $record) => $record->phone)
                    ->searchable(['first_name', 'last_name', 'phone']),
                Tables\Columns\TextColumn::make('description')->label('پیام')->limit(70)->wrap(),
                Tables\Columns\TextColumn::make('email')->label('ایمیل')->copyable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label('زمان')->since()->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('مشاهده')->slideOver()
                    ->after(fn (ContactMessage $record) => $record->read_at ?: $record->update(['read_at' => now()])),
                Tables\Actions\Action::make('markRead')
                    ->label('خوانده شد')
                    ->icon('heroicon-o-check')
                    ->visible(fn (ContactMessage $record) => $record->read_at === null)
                    ->action(fn (ContactMessage $record) => $record->update(['read_at' => now()])),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('markRead')
                    ->label('علامت‌گذاری به‌عنوان خوانده‌شده')
                    ->icon('heroicon-o-check')
                    ->action(fn ($records) => $records->each->update(['read_at' => now()]))
                    ->deselectRecordsAfterCompletion(),
            ])
            ->emptyStateHeading('هنوز پیامی دریافت نشده')
            ->emptyStateIcon('heroicon-o-envelope');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageContactMessages::route('/'),
        ];
    }
}
