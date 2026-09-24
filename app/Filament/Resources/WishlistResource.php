<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WishlistResource\Pages;
use App\Models\Wishlist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Read-only. Wishlists are created by customers on the storefront — this
 * resource exists purely so admins can see what customers want but haven't
 * bought, for restocking/remarketing decisions.
 */
class WishlistResource extends Resource
{
    protected static ?string $model = Wishlist::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Wishlists';

    protected static ?int $navigationSort = 6;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user.name')->label('Customer')->disabled(),
                Forms\Components\TextInput::make('user.email')->label('Email')->disabled(),
                Forms\Components\TextInput::make('product.name')->label('Product')->disabled(),
                Forms\Components\TextInput::make('product.status')->label('Product Status')->disabled(),
                Forms\Components\TextInput::make('product.stock')->label('Product Stock')->disabled(),
                Forms\Components\DateTimePicker::make('created_at')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Customer')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('product.name')->label('Product')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('product.status')->label('Product Status')->badge()->color(fn (?string $state) => match ($state) {
                    'active' => 'success', 'out_of_stock' => 'danger', 'archived' => 'gray', default => 'warning',
                }),
                Tables\Columns\TextColumn::make('product.stock')->label('Product Stock'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->label('Product'),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                            ->when($data['until'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWishlists::route('/'),
        ];
    }
}
