<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatbotResponseResource\Pages;
use App\Models\ChatbotResponse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChatbotResponseResource extends Resource
{
    protected static ?string $model = ChatbotResponse::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationLabel = 'Chatbot Responses';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Select::make('category')
                    ->options(ChatbotResponse::CATEGORIES)
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Higher priority responses match first.')
                    ->required(),
                Forms\Components\TagsInput::make('keywords')
                    ->required()
                    ->helperText('Words or phrases that trigger this response.')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('response')
                    ->required()
                    ->rows(4)
                    ->helperText('You can use {whatsapp}, {email}, {free_shipping_threshold}, {flat_shipping_rate} placeholders.')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ChatbotResponse::CATEGORIES[$state] ?? $state),
                Tables\Columns\TextColumn::make('keywords')
                    ->formatStateUsing(function (array|string $state): string {
                        if (is_array($state)) {
                            return implode(', ', $state);
                        }

                        $decoded = json_decode($state, true);

                        return implode(', ', is_array($decoded) ? $decoded : [$state]);
                    })
                    ->limit(50),
                Tables\Columns\TextColumn::make('response')->limit(60),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('sort_order', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')->options(ChatbotResponse::CATEGORIES),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChatbotResponses::route('/'),
            'create' => Pages\CreateChatbotResponse::route('/create'),
            'edit' => Pages\EditChatbotResponse::route('/{record}/edit'),
        ];
    }
}
