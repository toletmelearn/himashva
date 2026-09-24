<x-filament-panels::page>
    <div class="space-y-3">
        @foreach ($record->messages ?? [] as $msg)
            <div class="{{ $msg['from'] === 'bot' ? 'text-left' : 'text-right' }}">
                <span class="inline-block px-3 py-2 rounded-xl {{ $msg['from'] === 'bot' ? 'bg-gray-100' : 'bg-amber-100' }}">
                    <strong>{{ ucfirst($msg['from']) }}:</strong> {{ $msg['text'] }}
                </span>
                <div class="text-xs text-gray-400">{{ $msg['at'] ?? '' }}</div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
