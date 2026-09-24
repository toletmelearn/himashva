<?php

namespace Tests\Feature;

use App\Filament\Resources\ChatbotResponseResource\Pages\ListChatbotResponses;
use App\Models\ChatbotResponse;
use App\Models\User;
use App\Services\ChatbotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_matches_response_by_keyword(): void
    {
        ChatbotResponse::create([
            'category' => 'shipping',
            'keywords' => ['shipping', 'delivery'],
            'response' => 'We ship across India.',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $reply = app(ChatbotService::class)->reply('What is your delivery time?');

        $this->assertSame('We ship across India.', $reply);
    }

    public function test_service_prefers_higher_sort_order_on_multiple_matches(): void
    {
        ChatbotResponse::create([
            'category' => 'general',
            'keywords' => ['hi'],
            'response' => 'Low priority greeting.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        ChatbotResponse::create([
            'category' => 'shipping',
            'keywords' => ['shipping'],
            'response' => 'High priority shipping answer.',
            'is_active' => true,
            'sort_order' => 100,
        ]);

        // "shipping" contains the substring "hi", so both rows are candidates —
        // the higher sort_order must win.
        $reply = app(ChatbotService::class)->reply('how much is shipping');

        $this->assertSame('High priority shipping answer.', $reply);
    }

    public function test_service_falls_back_when_nothing_matches(): void
    {
        ChatbotResponse::create([
            'category' => 'shipping',
            'keywords' => ['shipping'],
            'response' => 'We ship across India.',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $reply = app(ChatbotService::class)->reply('completely unrelated gibberish');

        $this->assertStringContainsString("couldn't find an answer", $reply);
    }

    public function test_inactive_responses_are_not_matched(): void
    {
        ChatbotResponse::create([
            'category' => 'shipping',
            'keywords' => ['shipping'],
            'response' => 'We ship across India.',
            'is_active' => false,
            'sort_order' => 10,
        ]);

        $reply = app(ChatbotService::class)->reply('tell me about shipping');

        $this->assertStringContainsString("couldn't find an answer", $reply);
    }

    public function test_response_supports_setting_placeholders(): void
    {
        ChatbotResponse::create([
            'category' => 'general',
            'keywords' => ['contact'],
            'response' => 'Reach us at {email}.',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $reply = app(ChatbotService::class)->reply('how do I contact you');

        $this->assertSame('Reach us at '.settings('contact_email', 'info@himashva.com').'.', $reply);
    }

    public function test_saving_a_response_clears_the_cache(): void
    {
        $response = ChatbotResponse::create([
            'category' => 'shipping',
            'keywords' => ['shipping'],
            'response' => 'Original answer.',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        app(ChatbotService::class)->activeResponses();
        $this->assertTrue(Cache::has(ChatbotService::CACHE_KEY));

        $response->update(['response' => 'Updated answer.']);
        $this->assertFalse(Cache::has(ChatbotService::CACHE_KEY));

        $reply = app(ChatbotService::class)->reply('what about shipping');
        $this->assertSame('Updated answer.', $reply);
    }

    public function test_deleting_a_response_clears_the_cache(): void
    {
        $response = ChatbotResponse::create([
            'category' => 'shipping',
            'keywords' => ['shipping'],
            'response' => 'Original answer.',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        app(ChatbotService::class)->activeResponses();
        $this->assertTrue(Cache::has(ChatbotService::CACHE_KEY));

        $response->delete();
        $this->assertFalse(Cache::has(ChatbotService::CACHE_KEY));
    }

    public function test_chatbot_endpoint_returns_matched_reply_and_stores_conversation(): void
    {
        ChatbotResponse::create([
            'category' => 'returns',
            'keywords' => ['refund'],
            'response' => 'We offer 7-day returns.',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $response = $this->postJson(route('chatbot.respond'), ['message' => 'Can I get a refund?']);

        $response->assertOk()->assertJson(['reply' => 'We offer 7-day returns.']);

        $this->assertDatabaseCount('chatbot_conversations', 1);
    }

    public function test_admin_can_view_chatbot_responses_list_with_records(): void
    {
        $record = ChatbotResponse::create([
            'category' => 'shipping',
            'keywords' => ['shipping', 'delivery'],
            'response' => 'We ship across India.',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(ListChatbotResponses::class)
            ->assertCanSeeTableRecords([$record])
            ->assertTableColumnFormattedStateSet('keywords', 'shipping, delivery', record: $record);
    }
}
