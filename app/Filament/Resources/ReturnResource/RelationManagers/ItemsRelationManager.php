<?php

namespace App\Filament\Resources\ReturnResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('orderItem.product_name')->label('Product'),
                Tables\Columns\TextColumn::make('orderItem.variant_name')->label('Variant')->default('—'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('condition')->badge(),
            ])
            ->headerActions([])
            ->actions([]);
    }
}
