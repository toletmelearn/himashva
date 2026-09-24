<?php

namespace App\Filament\Resources\ChatbotResponseResource\Pages;

use App\Filament\Resources\ChatbotResponseResource;
use App\Filament\Resources\ChatbotResponseResource\Widgets\TestChatbotWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChatbotResponses extends ListRecords
{
    protected static string $resource = ChatbotResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            TestChatbotWidget::class,
        ];
    }
}
