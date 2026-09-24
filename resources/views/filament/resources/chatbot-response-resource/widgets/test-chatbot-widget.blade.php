<x-filament-widgets::widget>
    <x-filament::section heading="Test Chatbot" description="Type a message to see which response would be matched, so you can verify your keyword coverage.">
        <div class="space-y-4">
            <x-filament::input.wrapper>
                <x-filament::input
                    type="text"
                    wire:model.live.debounce.300ms="testMessage"
                    placeholder="e.g. how long does shipping take?"
                />
            </x-filament::input.wrapper>

            @if (filled($testMessage))
                @php($result = $this->getMatchResult())

                <div class="rounded-lg border border-gray-200 p-4 text-sm dark:border-gray-700">
                    @if ($result['matched'])
                        <p class="font-medium text-success-600 dark:text-success-400">
                            Matched — category: {{ \App\Models\ChatbotResponse::CATEGORIES[$result['category']] ?? $result['category'] }}
                        </p>
                        <p class="mt-2 text-gray-600 dark:text-gray-300">{{ $result['response'] }}</p>
                    @else
                        <p class="font-medium text-warning-600 dark:text-warning-400">
                            No match found — this question would be logged as unanswered for admin review.
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
