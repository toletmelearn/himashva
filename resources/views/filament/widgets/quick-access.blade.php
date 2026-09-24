<x-filament-widgets::widget>
    <x-filament::section heading="Quick Access">
        <div class="hv-quick-grid">
            @foreach ($cards as $card)
                <a
                    href="{{ $card['url'] }}"
                    class="quick-card"
                    style="background: linear-gradient(135deg, {{ $card['from'] }} 0%, {{ $card['to'] }} 100%);"
                >
                    <span class="quick-card-badge">{{ $card['badge'] }}</span>
                    <span class="quick-card-icon">
                        <x-filament::icon :icon="$card['icon']" class="w-7 h-7" />
                    </span>
                    <span class="quick-card-label">{{ $card['label'] }}</span>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
