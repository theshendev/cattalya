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

    protected static ?string $navigationLabel = 'پیام‌های تماس';

    protected static ?string $modelLabel = 'پیام';

    protected static ?string $pluralModelLabel = 'پیام‌های تماس';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('first_name')->label('نام')->disabled(),
            Forms\Components\TextInput::make('last_name')->label('نام خانوادگی')->disabled(),
            Forms\Components\TextInput::make('email')->label('ایمیل')->disabled(),
            Forms\Components\TextInput::make('phone')->label('شماره تماس')->disabled(),
            Forms\Components\Textarea::make('description')->label('توضیحات')->disabled()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->label('نام')->formatStateUsing(fn ($state, ContactMessage $record) => $record->first_name.' '.$record->last_name)->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('phone')->label('شماره تماس')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('ایمیل'),
                Tables\Columns\TextColumn::make('description')->label('توضیحات')->limit(60),
                Tables\Columns\IconColumn::make('read_at')->label('خوانده شده')->boolean()->getStateUsing(fn (ContactMessage $record) => $record->read_at !== null),
                Tables\Columns\TextColumn::make('created_at')->label('تاریخ')->dateTime(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('markRead')
                    ->label('خوانده شد')
                    ->visible(fn (ContactMessage $record) => $record->read_at === null)
                    ->action(fn (ContactMessage $record) => $record->update(['read_at' => now()])),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageContactMessages::route('/'),
        ];
    }
}
