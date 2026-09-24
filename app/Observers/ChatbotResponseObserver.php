<?php

namespace App\Observers;

use App\Models\ChatbotResponse;
use App\Services\ChatbotService;
use Illuminate\Support\Facades\Cache;

class ChatbotResponseObserver
{
    public function saved(ChatbotResponse $chatbotResponse): void
    {
        Cache::forget(ChatbotService::CACHE_KEY);
    }

    public function deleted(ChatbotResponse $chatbotResponse): void
    {
        Cache::forget(ChatbotService::CACHE_KEY);
    }
}
