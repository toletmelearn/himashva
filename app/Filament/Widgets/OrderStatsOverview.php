<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Superseded by {@see BusinessStatsWidget}, which covers the same
 * order/revenue/customer metrics plus cart abandonment and average
 * order value in the new dashboard layout. Left in place (rather than
 * deleted) in case it is wanted elsewhere; hidden from the dashboard
 * via canView() to avoid duplicate stats blocks.
 */
class OrderStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return false;
    }

    protected function getStats(): array
    {
        $today = Order::whereDate('created_at', today());
        $week = Order::where('created_at', '>=', now()->startOfWeek());
        $month = Order::where('created_at', '>=', now()->startOfMonth());

        return [
            Stat::make('Orders Today', $today->count())
                ->description('Revenue: ₹'.number_format($today->sum('total'), 2))
                ->color('success'),
            Stat::make('Orders This Week', $week->count())
                ->description('Revenue: ₹'.number_format($week->sum('total'), 2))
                ->color('info'),
            Stat::make('Orders This Month', $month->count())
                ->description('Revenue: ₹'.number_format($month->sum('total'), 2))
                ->color('warning'),
            Stat::make('New Customers This Week', User::where('is_admin', false)->where('created_at', '>=', now()->startOfWeek())->count())
                ->color('primary'),
        ];
    }
}
