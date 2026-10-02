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

    protected static ?string $navigationLabel = 'محصولات';

    protected static ?string $modelLabel = 'محصول';

    protected static ?string $pluralModelLabel = 'محصولات';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('اطلاعات اصلی')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('نام محصول')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))
                    )
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('slug')
                    ->label('نامک (Slug)')
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
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('قیمت‌گذاری')->schema([
                Forms\Components\Toggle::make('is_fixed_price')
                    ->label('قیمت ثابت')
                    ->live()
                    ->helperText('در صورت فعال بودن، قیمت به صورت دستی تعیین می‌شود'),
                Forms\Components\Placeholder::make('current_gold_price')
                    ->label('قیمت فعلی طلا')
                    ->content(fn () => number_format(GoldPrice::currentPricePerGram()).' تومن / گرم'),

                Forms\Components\TextInput::make('weight_grams')
                    ->label('وزن (گرم)')
                    ->numeric()
                    ->step(0.001)
                    ->required()
                    ->hidden(fn (Forms\Get $get) => $get('is_fixed_price')),
                Forms\Components\TextInput::make('wage_percent')
                    ->label('اجرت ساخت (%)')
                    ->numeric()
                    ->step(0.01)
                    ->default(7)
                    ->hidden(fn (Forms\Get $get) => $get('is_fixed_price')),
                Forms\Components\TextInput::make('tax_percent')
                    ->label('مالیات (%)')
                    ->numeric()
                    ->step(0.01)
                    ->default(9)
                    ->hidden(fn (Forms\Get $get) => $get('is_fixed_price')),
                Forms\Components\TextInput::make('fee')
                    ->label('هزینه ثابت (تومن)')
                    ->numeric()
                    ->default(0)
                    ->hidden(fn (Forms\Get $get) => $get('is_fixed_price')),
                Forms\Components\TextInput::make('fixed_price')
                    ->label('قیمت ثابت (تومن)')
                    ->numeric()
                    ->visible(fn (Forms\Get $get) => $get('is_fixed_price')),
            ])->columns(2),

            Forms\Components\Section::make('موجودی و وضعیت')->schema([
                Forms\Components\TextInput::make('stock')
                    ->label('موجودی')
                    ->numeric()
                    ->required()
                    ->default(0),
                Forms\Components\TextInput::make('sort_order')
                    ->label('ترتیب نمایش')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
            ])->columns(3),

            Forms\Components\Section::make('تصاویر محصول')->schema([
                Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                    ->label('تصاویر')
                    ->collection('images')
                    ->multiple()
                    ->reorderable()
                    ->image()
                    ->imageEditor()
                    ->maxFiles(10),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')
                    ->label('تصویر')
                    ->collection('images')
                    ->conversion('thumb'),
                Tables\Columns\TextColumn::make('name')
                    ->label('نام محصول')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('دسته‌بندی')
                    ->badge(),
                Tables\Columns\TextColumn::make('weight_grams')
                    ->label('وزن (گ)')
                    ->suffix('g')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('calculated_price')
                    ->label('قیمت (تومن)')
                    ->state(fn (Product $record) => number_format($record->calculatePrice()))
                    ->color('success'),
                Tables\Columns\TextColumn::make('stock')
                    ->label('موجودی')
                    ->color(fn ($state) => $state < 3 ? 'danger' : 'success'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('دسته‌بندی')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')->label('وضعیت'),
                Tables\Filters\Filter::make('low_stock')
                    ->label('موجودی کم')
                    ->query(fn ($query) => $query->where('stock', '<', 3)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف انتخاب‌شده‌ها'),
                ]),
            ]);
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
