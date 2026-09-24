<x-filament-widgets::widget>
    <x-filament::section heading="Top Selling Products">
        @forelse ($products as $product)
            <div class="top-product-row">
                <img
                    class="top-product-img"
                    src="{{ $product['image'] && $product['image'] !== 'placeholder.jpg' ? asset('storage/'.$product['image']) : asset('images/no-image.png') }}"
                    alt="{{ $product['name'] }}"
                    loading="lazy"
                >
                <div class="top-product-info">
                    <div class="top-product-name">{{ $product['name'] }}</div>
                    <div class="top-product-bar">
                        <div class="top-product-bar-fill" style="width: {{ $product['percent'] }}%;"></div>
                    </div>
                </div>
                <div class="top-product-stat">
                    <div class="top-product-sold">{{ $product['sold'] }} sold</div>
                    <div class="top-product-revenue">{{ $product['revenue'] }}</div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">No product sales yet.</p>
        @endforelse
    </x-filament::section>
</x-filament-widgets::widget>
