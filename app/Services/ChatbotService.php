<?php

namespace App\Services;

use App\Models\ChatbotResponse;
use App\Models\UnansweredQuestion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ChatbotService
{
    public const CACHE_KEY = 'chatbot.responses';

    public function activeResponses(): Collection
    {
        // Cached as raw attribute arrays, not Eloquent models: the database
        // cache store's `serializable_classes` config (see config/cache.php)
        // rejects unserializing objects, silently turning them into
        // __PHP_Incomplete_Class on read.
        $rows = Cache::remember(self::CACHE_KEY, 3600, function () {
            return ChatbotResponse::active()
                ->orderByDesc('sort_order')
                ->get()
                ->map(fn (ChatbotResponse $response) => $response->getAttributes())
                ->all();
        });

        return ChatbotResponse::hydrate($rows);
    }

    public function match(string $message): ?ChatbotResponse
    {
        $message = Str::lower($message);

        foreach ($this->activeResponses() as $response) {
            foreach ($response->keywords as $keyword) {
                if (Str::contains($message, Str::lower($keyword))) {
                    return $response;
                }
            }
        }

        return null;
    }

    public function reply(string $message): string
    {
        $matched = $this->match($message);

        if ($matched) {
            return $this->interpolate($matched->response);
        }

        $this->logUnansweredQuestion($message);

        $whatsapp = settings('whatsapp_number', '');
        $email = settings('contact_email', 'info@himashva.com');

        return "I couldn't find an answer to that. Please WhatsApp us at {$whatsapp} or email {$email} for help.";
    }

    protected function logUnansweredQuestion(string $message): void
    {
        $normalized = Str::lower(Str::squish($message));

        $existing = UnansweredQuestion::where('normalized_question', $normalized)->first();

        if ($existing) {
            $existing->increment('frequency');

            return;
        }

        UnansweredQuestion::create([
            'question' => $message,
            'normalized_question' => $normalized,
            'frequency' => 1,
            'session_id' => Session::getId(),
            'user_id' => Auth::id(),
            'status' => 'pending',
        ]);
    }

    protected function interpolate(string $text): string
    {
        return strtr($text, [
            '{whatsapp}' => settings('whatsapp_number', ''),
            '{email}' => settings('contact_email', 'info@himashva.com'),
            '{free_shipping_threshold}' => (string) settings('free_shipping_threshold', 999),
            '{flat_shipping_rate}' => (string) settings('flat_shipping_rate', 49),
        ]);
    }
}
