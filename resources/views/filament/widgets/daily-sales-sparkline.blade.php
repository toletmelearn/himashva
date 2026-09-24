<x-filament-widgets::widget>
    <x-filament::section heading="Daily Sales (14 Days)">
        <div class="sparkline-bars">
            @foreach ($days as $day)
                <div
                    class="sparkline-bar"
                    style="height: {{ $day['height'] }}%;"
                    title="{{ $day['label'] }}: {{ $day['count'] }} orders"
                ></div>
            @endforeach
        </div>
        <div class="sparkline-caption">{{ $weekTotal }} orders this week</div>
    </x-filament::section>
</x-filament-widgets::widget>
