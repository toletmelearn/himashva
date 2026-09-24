<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UnansweredQuestion extends Model
{
    protected $fillable = [
        'question', 'normalized_question', 'frequency', 'session_id',
        'user_id', 'status', 'resolved_by_response_id', 'admin_notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolvedByResponse(): BelongsTo
    {
        return $this->belongsTo(ChatbotResponse::class, 'resolved_by_response_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePopular($query)
    {
        return $query->orderByDesc('frequency');
    }

    /**
     * @return array<int, string>
     */
    public function suggestedKeywords(): array
    {
        $stopWords = [
            'is', 'the', 'how', 'what', 'do', 'does', 'can', 'i', 'my', 'a', 'an',
            'and', 'or', 'to', 'for', 'in', 'on', 'at', 'of', 'it', 'this', 'that',
            'are', 'was', 'be', 'have', 'has', 'you', 'your', 'we', 'they', 'me',
            'about', 'with', 'from', 'there', 'will', 'would', 'could', 'should',
            'not', 'but', 'if', 'so', 'just', 'get', 'got', 'please', 'thanks',
            'thank', 'want', 'need', 'know', 'also', 'any', 'some',
        ];

        $words = str_word_count(Str::lower($this->question), 1);

        return array_values(array_unique(array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) > 2 && ! in_array($word, $stopWords, true);
        })));
    }
}
