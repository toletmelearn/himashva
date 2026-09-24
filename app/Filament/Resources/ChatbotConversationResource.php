<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatbotConversationResource\Pages;
use App\Models\ChatbotConversation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ChatbotConversationResource extends Resource
{
    protected static ?string $model = ChatbotConversation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationLabel = 'Chatbot Logs';

    protected static ?int $navigationSort = 5;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Customer')->default('Guest'),
                Tables\Columns\TextColumn::make('session_id')->limit(15),
                Tables\Columns\TextColumn::make('messages')->formatStateUsing(function ($state) {
                    $msgs = is_array($state) ? $state : (json_decode($state, true) ?? []);

                    return count($msgs).' messages';
                }),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->label('Customer'),
                Filter::make('updated_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $v) => $q->whereDate('updated_at', '>=', $v))
                            ->when($data['until'], fn ($q, $v) => $q->whereDate('updated_at', '<=', $v));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChatbotConversations::route('/'),
            'view' => Pages\ViewChatbotConversation::route('/{record}'),
        ];
    }
}
