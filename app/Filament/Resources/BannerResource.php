<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'محتوای سایت';

    protected static ?string $navigationLabel = 'بنرها';

    protected static ?string $modelLabel = 'بنر';

    protected static ?string $pluralModelLabel = 'بنرها';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->columnSpan(['lg' => 2])->schema([
                Forms\Components\Section::make('تصویر بنر')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                            ->hiddenLabel()
                            ->collection('image')
                            ->image()
                            ->imageEditor()
                            ->required(),
                    ]),
                Forms\Components\Section::make('متن و لینک')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        Forms\Components\TextInput::make('title')->label('عنوان')->nullable(),
                        Forms\Components\TextInput::make('subtitle')->label('زیرعنوان')->nullable(),
                        Forms\Components\TextInput::make('link')
                            ->label('لینک')
                            ->prefixIcon('heroicon-o-link')
                            ->url()
                            ->nullable()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]),
            Forms\Components\Section::make('جایگاه و نمایش')
                ->icon('heroicon-o-squares-2x2')
                ->columnSpan(['lg' => 1])
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('جایگاه بنر')
                        ->options([
                            'hero' => 'بنر اصلی (Hero)',
                            'promo_left' => 'پروموشن چپ',
                            'promo_right' => 'پروموشن راست',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('sort_order')->label('ترتیب')->numeric()->default(0),
                    Forms\Components\Toggle::make('is_active')->label('نمایش در سایت')->default(true),
                ]),
        ])->columns(['default' => 1, 'lg' => 3]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('image')
                    ->label('')
                    ->collection('image')
                    ->width(120)
                    ->height(60),
                Tables\Columns\TextColumn::make('title')->label('عنوان')->weight('bold')->description(fn ($record) => $record->subtitle)->placeholder('بدون عنوان')->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('نوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'hero' => 'بنر اصلی',
                        'promo_left' => 'پروموشن چپ',
                        'promo_right' => 'پروموشن راست',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'hero' => 'primary',
                        'promo_left' => 'success',
                        'promo_right' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\ToggleColumn::make('is_active')->label('فعال'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->striped()
            ->emptyStateHeading('هنوز بنری ثبت نشده')
            ->emptyStateIcon('heroicon-o-photo')
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
