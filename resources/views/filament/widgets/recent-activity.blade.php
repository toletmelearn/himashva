<x-filament-widgets::widget>
    <x-filament::section heading="Recent Activity">
        @if (count($activities))
            <ul class="activity-timeline">
                @foreach ($activities as $activity)
                    <li class="activity-item">
                        <span class="activity-dot" style="background: {{ $activity['color'] }};">
                            <x-filament::icon :icon="$activity['icon']" />
                        </span>
                        <div>
                            <div class="activity-text">{{ $activity['text'] }}</div>
                            <div class="activity-time">{{ $activity['time'] }}</div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">No recent activity.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
