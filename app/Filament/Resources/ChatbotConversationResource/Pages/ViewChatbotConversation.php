<?php

namespace App\Filament\Resources\ChatbotConversationResource\Pages;

use App\Filament\Resources\ChatbotConversationResource;
use Filament\Resources\Pages\ViewRecord;

class ViewChatbotConversation extends ViewRecord
{
    protected static string $resource = ChatbotConversationResource::class;

    protected static string $view = 'filament.pages.view-chatbot-conversation';
}
