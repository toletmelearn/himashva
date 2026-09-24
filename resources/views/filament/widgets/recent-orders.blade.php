<x-filament-widgets::widget>
    <x-filament::section heading="Recent Orders">
        @if (count($orders))
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach ($orders as $order)
                    <a href="{{ $order['url'] }}" class="block rounded-xl border border-gray-200 dark:border-white/10 p-3 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-semibold text-sm">{{ $order['order_number'] }}</span>
                            <x-filament::badge :color="$order['status_color']">
                                {{ $order['status_label'] }}
                            </x-filament::badge>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $order['customer'] }}</div>
                        <div class="flex items-center justify-between mt-2">
                            <span class="font-bold text-sm">{{ $order['total'] }}</span>
                            <span class="flex items-center gap-1 text-xs text-gray-400">
                                <x-filament::icon :icon="$order['payment_icon']" class="w-4 h-4" />
                                {{ $order['time_ago'] }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">No orders yet.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
