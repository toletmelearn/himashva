<?php

namespace App\Filament\Resources\ChatbotResponseResource\Pages;

use App\Filament\Resources\ChatbotResponseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChatbotResponse extends EditRecord
{
    protected static string $resource = ChatbotResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
