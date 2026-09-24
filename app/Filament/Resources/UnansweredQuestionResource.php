<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnansweredQuestionResource\Pages;
use App\Models\ChatbotConversation;
use App\Models\ChatbotResponse;
use App\Models\UnansweredQuestion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UnansweredQuestionResource extends Resource
{
    protected static ?string $model = UnansweredQuestion::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationLabel = 'Unanswered Questions';

    protected static ?int $navigationSort = 7;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = UnansweredQuestion::pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question')->searchable()->limit(60),
                Tables\Columns\TextColumn::make('frequency')
                    ->sortable()
                    ->color(fn (int $state) => match (true) {
                        $state > 5 => 'danger',
                        $state > 2 => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'resolved' => 'success',
                        'ignored' => 'gray',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('user.name')->label('Customer')->default('Guest'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('resolvedByResponse.category')
                    ->label('Resolved via')
                    ->formatStateUsing(fn (?string $state): string => $state ? (ChatbotResponse::CATEGORIES[$state] ?? $state) : '—'),
            ])
            ->defaultSort('frequency', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'resolved' => 'Resolved',
                        'ignored' => 'Ignored',
                    ])
                    ->default('pending'),
                Tables\Filters\Filter::make('frequency')
                    ->form([
                        Forms\Components\TextInput::make('min')->numeric()->label('Min frequency'),
                        Forms\Components\TextInput::make('max')->numeric()->label('Max frequency'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['min'], fn ($q, $v) => $q->where('frequency', '>=', $v))
                            ->when($data['max'], fn ($q, $v) => $q->where('frequency', '<=', $v));
                    }),
                Tables\Filters\Filter::make('created_at')
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
                Tables\Actions\Action::make('create_response')
                    ->label('Create Response')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->visible(fn (UnansweredQuestion $record) => $record->status === 'pending')
                    ->form(fn (UnansweredQuestion $record) => [
                        Forms\Components\TagsInput::make('keywords')
                            ->default($record->suggestedKeywords())
                            ->required(),
                        Forms\Components\Select::make('category')
                            ->options(ChatbotResponse::CATEGORIES)
                            ->default('general')
                            ->required(),
                        Forms\Components\Textarea::make('response')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (UnansweredQuestion $record, array $data): void {
                        $response = ChatbotResponse::create([
                            'category' => $data['category'],
                            'keywords' => $data['keywords'],
                            'response' => $data['response'],
                            'is_active' => true,
                            'sort_order' => 0,
                        ]);

                        $record->update([
                            'status' => 'resolved',
                            'resolved_by_response_id' => $response->id,
                        ]);

                        UnansweredQuestion::where('normalized_question', $record->normalized_question)
                            ->where('id', '!=', $record->id)
                            ->update([
                                'status' => 'resolved',
                                'resolved_by_response_id' => $response->id,
                            ]);

                        Notification::make()
                            ->title('Chatbot response created')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('ignore')
                    ->label('Ignore')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (UnansweredQuestion $record) => $record->status === 'pending')
                    ->action(fn (UnansweredQuestion $record) => $record->update(['status' => 'ignored'])),
                Tables\Actions\Action::make('view_conversation')
                    ->label('View Conversation')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('gray')
                    ->visible(fn (UnansweredQuestion $record) => filled($record->session_id) && ChatbotConversation::where('session_id', $record->session_id)->exists())
                    ->url(function (UnansweredQuestion $record) {
                        $conversation = ChatbotConversation::where('session_id', $record->session_id)->first();

                        return $conversation ? ChatbotConversationResource::getUrl('view', ['record' => $conversation]) : null;
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnansweredQuestions::route('/'),
        ];
    }
}
