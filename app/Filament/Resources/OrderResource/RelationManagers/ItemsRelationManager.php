<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

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
            ->recordTitleAttribute('product_name')
            ->columns([
                Tables\Columns\TextColumn::make('product_name'),
                Tables\Columns\TextColumn::make('variant_name'),
                Tables\Columns\TextColumn::make('sku'),
                Tables\Columns\TextColumn::make('unit_price')->money('INR'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('line_total')->money('INR'),
            ]);
    }
}
