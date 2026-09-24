<x-filament-widgets::widget>
    <div class="himashva-greeting">
        <div class="himashva-greeting__title">
            {{ $greeting }}{{ $name ? ', ' . $name : '' }}
        </div>
        <div class="himashva-greeting__subtitle">
            Here's what's happening at Himashva today.
        </div>

        <div class="himashva-greeting__stats">
            <div class="himashva-greeting__stat">
                <div class="himashva-greeting__stat-value">{{ $ordersToday }}</div>
                <div class="himashva-greeting__stat-label">Orders Today</div>
            </div>
            <div class="himashva-greeting__stat">
                <div class="himashva-greeting__stat-value">₹{{ number_format($revenueToday, 2) }}</div>
                <div class="himashva-greeting__stat-label">Revenue Today</div>
            </div>
            <div class="himashva-greeting__stat">
                <div class="himashva-greeting__stat-value">{{ $pendingOrders }}</div>
                <div class="himashva-greeting__stat-label">Pending Orders</div>
            </div>
            <div class="himashva-greeting__stat">
                <div class="himashva-greeting__stat-value">{{ $pendingReturns }}</div>
                <div class="himashva-greeting__stat-label">Pending Returns</div>
            </div>
            <div class="himashva-greeting__stat">
                <div class="himashva-greeting__stat-value">{{ $lowStockProducts }}</div>
                <div class="himashva-greeting__stat-label">Low Stock</div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
