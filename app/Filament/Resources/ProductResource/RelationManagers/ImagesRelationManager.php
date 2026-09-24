<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image_path')
                    ->image()
                    ->directory('products')
                    ->disk('public')
                    ->required(),
                Forms\Components\Select::make('image_type')
                    ->label('Image Type')
                    ->options([
                        'ai_generated' => 'AI Generated',
                        'real' => 'Real Photo',
                    ])
                    ->default('real')
                    ->required()
                    ->helperText('Catalog imports are AI generated; upload the real product photo here once available.'),
                Forms\Components\TextInput::make('alt_text')
                    ->maxLength(255),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_primary'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('alt_text')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')->disk('public'),
                Tables\Columns\TextColumn::make('image_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'real' ? 'Real Photo' : 'AI Generated')
                    ->color(fn (string $state) => $state === 'real' ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('alt_text'),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\IconColumn::make('is_primary')->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->reorderable('sort_order');
    }
}
