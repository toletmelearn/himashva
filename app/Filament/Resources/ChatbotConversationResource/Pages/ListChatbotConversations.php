<?php

namespace App\Filament\Resources\ChatbotConversationResource\Pages;

use App\Filament\Resources\ChatbotConversationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChatbotConversations extends ListRecords
{
    protected static string $resource = ChatbotConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
