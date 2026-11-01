<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\GoldPrice;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'فروشگاه';

    protected static ?string $navigationLabel = 'محصولات';

    protected static ?string $modelLabel = 'محصول';

    protected static ?string $pluralModelLabel = 'محصولات';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->columnSpan(['lg' => 2])->schema([
                Forms\Components\Section::make('اطلاعات اصلی')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('نام محصول')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state)))
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('slug')
                            ->label('نامک (Slug)')
                            ->prefixIcon('heroicon-o-link')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('category_id')
                            ->label('دسته‌بندی')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('توضیحات')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('تصاویر محصول')
                    ->description('اولین تصویر به‌عنوان تصویر اصلی نمایش داده می‌شود. برای تغییر ترتیب، تصاویر را بکشید.')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                            ->hiddenLabel()
                            ->collection('images')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->imageEditor()
                            ->panelLayout('grid')
                            ->maxFiles(10),
                    ]),

                Forms\Components\Section::make('قیمت‌گذاری')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Forms\Components\Toggle::make('is_fixed_price')
                            ->label('قیمت ثابت')
                            ->live()
                            ->helperText('در صورت فعال بودن، قیمت به‌جای فرمول طلا به صورت دستی تعیین می‌شود.')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('weight_grams')
                            ->label('وزن')
                            ->suffix('گرم')
                            ->numeric()
                            ->step(0.001)
                            ->required()
                            ->hidden(fn (Forms\Get $get) => $get('is_fixed_price')),
                        Forms\Components\TextInput::make('wage_percent')
                            ->label('اجرت ساخت')
                            ->suffix('٪')
                            ->numeric()
                            ->step(0.01)
                            ->default(7)
                            ->hidden(fn (Forms\Get $get) => $get('is_fixed_price')),
                        Forms\Components\TextInput::make('tax_percent')
                            ->label('مالیات')
                            ->suffix('٪')
                            ->numeric()
                            ->step(0.01)
                            ->default(9)
                            ->hidden(fn (Forms\Get $get) => $get('is_fixed_price')),
                        Forms\Components\TextInput::make('fee')
                            ->label('هزینه ثابت')
                            ->suffix('تومن')
                            ->numeric()
                            ->default(0)
                            ->hidden(fn (Forms\Get $get) => $get('is_fixed_price')),
                        Forms\Components\TextInput::make('fixed_price')
                            ->label('قیمت ثابت')
                            ->suffix('تومن')
                            ->numeric()
                            ->visible(fn (Forms\Get $get) => $get('is_fixed_price')),
                    ])->columns(2),

                Forms\Components\Section::make('مشخصات ظاهری')
                    ->icon('heroicon-o-sparkles')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('karat')
                            ->label('عیار')
                            ->numeric()
                            ->default(18)
                            ->required(),
                        Forms\Components\TextInput::make('gold_color')
                            ->label('رنگ طلا')
                            ->placeholder('طلای زرد'),
                        Forms\Components\TextInput::make('shine')
                            ->label('درخشندگی')
                            ->placeholder('براق'),
                        Forms\Components\TextInput::make('dimensions')
                            ->label('ابعاد تقریبی')
                            ->placeholder('12*12 سانتی‌متر'),
                    ])->columns(2),
            ]),

            Forms\Components\Group::make()->columnSpan(['lg' => 1])->schema([
                Forms\Components\Section::make('وضعیت و موجودی')
                    ->icon('heroicon-o-archive-box')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('نمایش در فروشگاه')
                            ->default(true),
                        Forms\Components\TextInput::make('stock')
                            ->label('موجودی')
                            ->suffix('عدد')
                            ->numeric()
                            ->required()
                            ->default(0),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('ترتیب نمایش')
                            ->numeric()
                            ->default(0),
                    ]),

                Forms\Components\Section::make('قیمت طلا')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Forms\Components\Placeholder::make('current_gold_price')
                            ->hiddenLabel()
                            ->content(fn () => number_format(GoldPrice::currentPricePerGram()).' تومن / گرم'),
                    ])
                    ->description('قیمت فعلی هر گرم طلا که در محاسبه قیمت محصول استفاده می‌شود.'),
            ]),
        ])->columns(['default' => 1, 'lg' => 3]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')
                    ->label('')
                    ->collection('images')
                    ->conversion('thumb')
                    ->limit(1)
                    ->square()
                    ->size(56),
                Tables\Columns\TextColumn::make('name')
                    ->label('محصول')
                    ->weight('bold')
                    ->description(fn (Product $record) => $record->category?->name)
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('weight_grams')
                    ->label('وزن')
                    ->suffix(' گرم')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('calculated_price')
                    ->label('قیمت')
                    ->state(fn (Product $record) => number_format($record->calculatePrice()).' تومن')
                    ->weight('bold')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('stock')
                    ->label('موجودی')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state > 0 ? "{$state} عدد" : 'ناموجود')
                    ->color(fn ($state) => match (true) {
                        $state <= 0 => 'danger',
                        $state < 3 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('فعال'),
            ])
            ->defaultSort('sort_order')
            ->striped()
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('دسته‌بندی')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('فعال‌سازی')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('غیرفعال‌سازی')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make()->label('حذف انتخاب‌شده‌ها'),
                ]),
            ])
            ->emptyStateHeading('هنوز محصولی ثبت نشده')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
