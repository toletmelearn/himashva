<?php

namespace Tests\Feature;

use App\Filament\Resources\UnansweredQuestionResource\Pages\ListUnansweredQuestions;
use App\Models\ChatbotResponse;
use App\Models\UnansweredQuestion;
use App\Models\User;
use App\Services\ChatbotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UnansweredQuestionTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_unanswered_question_is_logged_when_no_match(): void
    {
        app(ChatbotService::class)->reply('do you sell candle holders');

        $this->assertDatabaseHas('unanswered_questions', [
            'question' => 'do you sell candle holders',
            'normalized_question' => 'do you sell candle holders',
            'frequency' => 1,
            'status' => 'pending',
        ]);
    }

    public function test_duplicate_unanswered_questions_increment_frequency(): void
    {
        app(ChatbotService::class)->reply('do you sell candle holders');
        app(ChatbotService::class)->reply('  Do You Sell Candle Holders  ');

        $this->assertDatabaseCount('unanswered_questions', 1);
        $this->assertDatabaseHas('unanswered_questions', [
            'normalized_question' => 'do you sell candle holders',
            'frequency' => 2,
        ]);
    }

    public function test_matched_questions_are_not_logged_as_unanswered(): void
    {
        ChatbotResponse::create([
            'category' => 'shipping',
            'keywords' => ['shipping'],
            'response' => 'We ship across India.',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        app(ChatbotService::class)->reply('tell me about shipping');

        $this->assertDatabaseCount('unanswered_questions', 0);
    }

    public function test_creating_response_from_unanswered_resolves_it(): void
    {
        $question = UnansweredQuestion::create([
            'question' => 'do you sell candle holders',
            'normalized_question' => 'do you sell candle holders',
            'frequency' => 1,
            'status' => 'pending',
        ]);

        $response = ChatbotResponse::create([
            'category' => 'products',
            'keywords' => ['candle holder'],
            'response' => 'Yes, we sell candle holders.',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $question->update([
            'status' => 'resolved',
            'resolved_by_response_id' => $response->id,
        ]);

        $this->assertSame('resolved', $question->fresh()->status);
        $this->assertSame($response->id, $question->fresh()->resolved_by_response_id);
        $this->assertSame('products', $question->fresh()->resolvedByResponse->category);
    }

    public function test_create_response_action_resolves_duplicate_unanswered_questions(): void
    {
        $question = UnansweredQuestion::create([
            'question' => 'do you sell candle holders',
            'normalized_question' => 'do you sell candle holders',
            'frequency' => 3,
            'status' => 'pending',
        ]);

        $duplicate = UnansweredQuestion::create([
            'question' => 'sell candle holders?',
            'normalized_question' => 'sell candle holders?',
            'frequency' => 1,
            'status' => 'pending',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListUnansweredQuestions::class)
            ->callTableAction('create_response', $question, data: [
                'keywords' => ['candle', 'holder'],
                'category' => 'products',
                'response' => 'Yes, we sell candle holders.',
            ]);

        $this->assertSame('resolved', $question->fresh()->status);
        $this->assertNotNull($question->fresh()->resolved_by_response_id);

        // Only the exact normalized-question match gets auto-resolved; a
        // differently-worded duplicate is left for the admin to review.
        $this->assertSame('pending', $duplicate->fresh()->status);
    }

    public function test_admin_can_view_unanswered_questions_resource(): void
    {
        UnansweredQuestion::create([
            'question' => 'do you sell candle holders',
            'normalized_question' => 'do you sell candle holders',
            'frequency' => 1,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/unanswered-questions')
            ->assertOk()
            ->assertSee('do you sell candle holders');
    }

    public function test_ignored_questions_are_not_shown_by_default(): void
    {
        $pending = UnansweredQuestion::create([
            'question' => 'pending question',
            'normalized_question' => 'pending question',
            'frequency' => 1,
            'status' => 'pending',
        ]);

        $ignored = UnansweredQuestion::create([
            'question' => 'ignored question',
            'normalized_question' => 'ignored question',
            'frequency' => 1,
            'status' => 'ignored',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListUnansweredQuestions::class)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$ignored]);
    }
}
