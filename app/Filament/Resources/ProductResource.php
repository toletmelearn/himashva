<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('brand_id')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $state, Forms\Set $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('short_description')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Pricing & Stock')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('₹'),
                        Forms\Components\TextInput::make('sale_price')
                            ->numeric()
                            ->prefix('₹'),
                        Forms\Components\TextInput::make('cost_price')
                            ->numeric()
                            ->prefix('₹'),
                        Forms\Components\TextInput::make('tax_rate')
                            ->label('GST Rate')
                            ->required()
                            ->numeric()
                            ->suffix('%')
                            ->default(18.00),
                        Forms\Components\TextInput::make('stock')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('low_stock_threshold')
                            ->required()
                            ->numeric()
                            ->default(5),
                        Forms\Components\TextInput::make('weight_grams')
                            ->numeric(),
                    ]),
                Forms\Components\Section::make('Flags')
                    ->columns(4)
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft', 'active' => 'Active',
                                'out_of_stock' => 'Out of Stock', 'archived' => 'Archived',
                            ])
                            ->required()
                            ->default('active'),
                        Forms\Components\Toggle::make('is_featured'),
                        Forms\Components\Toggle::make('is_bestseller'),
                        Forms\Components\Toggle::make('is_new'),
                    ]),
                Forms\Components\Section::make('SEO')
                    ->columns(1)
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')->maxLength(255),
                        Forms\Components\Textarea::make('meta_description'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images.image_path')
                    ->label('Image')
                    ->limit(1)
                    ->disk('public')
                    ->defaultImageUrl(asset('images/no-image.png')),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($record) => $record->stock <= $record->low_stock_threshold ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'active' => 'success', 'out_of_stock' => 'danger', 'archived' => 'gray', default => 'warning',
                }),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft', 'active' => 'Active',
                        'out_of_stock' => 'Out of Stock', 'archived' => 'Archived',
                    ]),
                TernaryFilter::make('is_featured'),
                SelectFilter::make('brand_id')
                    ->relationship('brand', 'name')
                    ->label('Brand'),
                TernaryFilter::make('is_bestseller'),
                TernaryFilter::make('is_new'),
                Filter::make('low_stock')
                    ->query(fn ($query) => $query->whereColumn('stock', '<=', 'low_stock_threshold')),
                Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('price_from')->numeric(),
                        Forms\Components\TextInput::make('price_to')->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['price_from'], fn ($q, $v) => $q->where('price', '>=', $v))
                            ->when($data['price_to'], fn ($q, $v) => $q->where('price', '<=', $v));
                    }),
                Filter::make('stock_range')
                    ->form([
                        Forms\Components\TextInput::make('stock_from')->numeric(),
                        Forms\Components\TextInput::make('stock_to')->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['stock_from'], fn ($q, $v) => $q->where('stock', '>=', $v))
                            ->when($data['stock_to'], fn ($q, $v) => $q->where('stock', '<=', $v));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->action(fn ($records) => $records->each->update(['status' => 'active']))
                        ->icon('heroicon-o-check'),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->action(fn ($records) => $records->each->update(['status' => 'draft']))
                        ->icon('heroicon-o-x-mark'),
                    Tables\Actions\BulkAction::make('mark_featured')
                        ->label('Mark Featured')
                        ->action(fn ($records) => $records->each->update(['is_featured' => true]))
                        ->icon('heroicon-o-star'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
            VariantsRelationManager::class,
        ];
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
