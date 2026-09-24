<?php

namespace App\Filament\Resources\ChatbotResponseResource\Widgets;

use App\Models\ChatbotResponse;
use App\Services\ChatbotService;
use Filament\Widgets\Widget;

class TestChatbotWidget extends Widget
{
    protected static string $view = 'filament.resources.chatbot-response-resource.widgets.test-chatbot-widget';

    protected int|string|array $columnSpan = 'full';

    public ?string $testMessage = null;

    /**
     * @return array{matched: bool, category: ?string, response: ?string}
     */
    public function getMatchResult(): array
    {
        if (blank($this->testMessage)) {
            return ['matched' => false, 'category' => null, 'response' => null];
        }

        /** @var ChatbotResponse|null $matched */
        $matched = app(ChatbotService::class)->match($this->testMessage);

        return [
            'matched' => (bool) $matched,
            'category' => $matched?->category,
            'response' => $matched?->response,
        ];
    }
}
