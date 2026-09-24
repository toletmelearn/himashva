<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnRequest;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class DashboardGreeting extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-greeting';

    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $user = Filament::auth()->user();
        $hour = now()->hour;

        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        return [
            'greeting' => $greeting,
            'name' => $user?->name,
            'ordersToday' => Order::whereDate('created_at', today())->count(),
            'revenueToday' => Order::whereDate('created_at', today())->sum('total'),
            'pendingOrders' => Order::where('order_status', 'pending')->count(),
            'pendingReturns' => ReturnRequest::where('status', 'requested')->count(),
            'lowStockProducts' => Product::whereColumn('stock', '<=', 'low_stock_threshold')->count(),
        ];
    }
}
