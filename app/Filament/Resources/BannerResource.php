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

    protected static ?string $navigationGroup = 'CMS';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('title')->required(),
                Forms\Components\TextInput::make('subtitle'),
                Forms\Components\FileUpload::make('image_path')
                    ->image()
                    ->required()
                    ->directory('banners')
                    ->disk('public')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('link'),
                Forms\Components\TextInput::make('button_text'),
                Forms\Components\Select::make('position')
                    ->options(['hero' => 'Hero', 'promo' => 'Promo', 'sidebar' => 'Sidebar'])
                    ->required(),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\DateTimePicker::make('starts_at'),
                Forms\Components\DateTimePicker::make('ends_at'),
                Forms\Components\Toggle::make('is_active')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')->disk('public')->defaultImageUrl(asset('images/no-image.png')),
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('position'),
                Tables\Columns\TextColumn::make('sort_order'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('position')
                    ->options(['hero' => 'Hero', 'promo' => 'Promo', 'sidebar' => 'Sidebar']),
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\Filter::make('currently_live')
                    ->label('Currently Live')
                    ->query(fn ($query) => $query
                        ->where('is_active', true)
                        ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                        ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
