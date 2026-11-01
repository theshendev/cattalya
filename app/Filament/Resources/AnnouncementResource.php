<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'محتوای سایت';

    protected static ?string $navigationLabel = 'اعلانات';

    protected static ?string $modelLabel = 'اعلان';

    protected static ?string $pluralModelLabel = 'اعلانات';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('متن اعلان')
                ->description('این متن در نوار بالای سایت نمایش داده می‌شود.')
                ->icon('heroicon-o-megaphone')
                ->schema([
                    Forms\Components\Textarea::make('text')
                        ->hiddenLabel()
                        ->required()
                        ->rows(3),
                    Forms\Components\Toggle::make('is_active')
                        ->label('نمایش در سایت')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('text')->label('متن')->weight('bold')->limit(80)->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')->label('فعال'),
                Tables\Columns\TextColumn::make('updated_at')->label('آخرین ویرایش')->since()->sortable(),
            ])
            ->striped()
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->emptyStateHeading('هنوز اعلانی ثبت نشده')
            ->emptyStateIcon('heroicon-o-megaphone');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
