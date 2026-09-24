<x-filament-widgets::widget>
    <x-filament::section>
        <a href="{{ $url }}" class="flex items-center gap-x-3 text-sm font-medium text-warning-600 hover:underline dark:text-warning-400">
            <x-filament::icon icon="heroicon-o-light-bulb" class="h-5 w-5" />
            {{ $count }} unanswered {{ Str::plural('question', $count) }} need{{ $count === 1 ? 's' : '' }} attention
        </a>
    </x-filament::section>
</x-filament-widgets::widget>
