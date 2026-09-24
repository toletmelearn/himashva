<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VideosRelationManager extends RelationManager
{
    protected static string $relationship = 'videos';

    protected static ?string $title = 'Videos';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('platform')
                ->options(['youtube' => 'YouTube', 'instagram' => 'Instagram'])
                ->required()
                ->reactive(),
            Forms\Components\TextInput::make('video_url')
                ->label('Video URL')
                ->required()
                ->url()
                ->helperText('Paste the full YouTube or Instagram video/reel URL')
                ->placeholder(fn (callable $get) => match ($get('platform')) {
                    'youtube' => 'https://www.youtube.com/watch?v=...',
                    'instagram' => 'https://www.instagram.com/reel/...',
                    default => 'Select a platform first',
                }),
            Forms\Components\TextInput::make('title')
                ->placeholder('e.g. Unboxing Video, Burn Test, How to Use')
                ->maxLength(100),
            Forms\Components\TextInput::make('thumbnail_url')
                ->label('Custom Thumbnail URL (optional)')
                ->url()
                ->helperText('Leave blank for auto-generated thumbnail (YouTube only). For Instagram, upload a screenshot.'),
            Forms\Components\TextInput::make('sort_order')
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Preview')
                    ->width(80)
                    ->height(45),
                Tables\Columns\TextColumn::make('platform')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'youtube' => 'danger',
                        'instagram' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('title')->default('—'),
                Tables\Columns\TextColumn::make('video_url')->limit(40),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
