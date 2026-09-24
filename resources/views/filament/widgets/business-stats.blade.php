<x-filament-widgets::widget>
    <x-filament::section heading="Business Overview">
        <div class="bstat-grid">
            @foreach ($stats as $stat)
                <div class="bstat-card">
                    <div class="bstat-icon" style="background: {{ $stat['color'] }};">
                        <x-filament::icon :icon="$stat['icon']" />
                    </div>
                    <div>
                        <div class="bstat-value">{{ $stat['value'] }}</div>
                        <div class="bstat-label">{{ $stat['label'] }}</div>
                        <span class="bstat-trend bstat-trend-{{ $stat['trend']['direction'] }}">
                            @if ($stat['trend']['direction'] === 'up')
                                &#9650;
                            @else
                                &#9660;
                            @endif
                            {{ $stat['trend']['percent'] }}%
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
