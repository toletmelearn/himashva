<?php

namespace App\Filament\Widgets;

use App\Models\AbandonedCart;
use App\Models\Order;
use App\Models\User;
use Closure;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class BusinessStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.business-stats';

    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<int, array{label: string, icon: string, color: string, value: string, trend: array{direction: string, percent: float}}>
     */
    protected function getViewData(): array
    {
        return [
            'stats' => [
                [
                    'label' => 'Total Revenue',
                    'icon' => 'heroicon-o-currency-rupee',
                    'color' => '#10B981',
                    'value' => '₹'.number_format((float) Order::where('payment_status', 'paid')->sum('total'), 2),
                    'trend' => $this->weeklyTrend(fn ($query) => $query->where('payment_status', 'paid')->sum('total')),
                ],
                [
                    'label' => "Today's Orders",
                    'icon' => 'heroicon-o-shopping-cart',
                    'color' => '#3B82F6',
                    'value' => (string) Order::whereDate('created_at', today())->count(),
                    'trend' => $this->weeklyTrend(fn ($query) => $query->count()),
                ],
                [
                    'label' => 'Pending Orders',
                    'icon' => 'heroicon-o-clock',
                    'color' => '#F59E0B',
                    'value' => (string) Order::where('order_status', 'pending')->count(),
                    'trend' => $this->weeklyTrend(fn ($query) => $query->where('order_status', 'pending')->count()),
                ],
                [
                    'label' => 'Total Customers',
                    'icon' => 'heroicon-o-users',
                    'color' => '#06B6D4',
                    'value' => (string) User::where('is_admin', false)->count(),
                    'trend' => $this->weeklyTrend(fn ($query) => $query->count(), User::query()),
                ],
                [
                    'label' => 'Cart Abandonment',
                    'icon' => 'heroicon-o-x-circle',
                    'color' => '#EF4444',
                    'value' => (string) AbandonedCart::where('status', 'active')->count(),
                    'trend' => $this->weeklyTrend(fn ($query) => $query->where('status', 'active')->count(), AbandonedCart::query()),
                ],
                [
                    'label' => 'Avg Order Value',
                    'icon' => 'heroicon-o-calculator',
                    'color' => '#8B5CF6',
                    'value' => '₹'.number_format((float) Order::where('payment_status', 'paid')->avg('total'), 2),
                    'trend' => $this->weeklyTrend(fn ($query) => $query->where('payment_status', 'paid')->avg('total')),
                ],
            ],
        ];
    }

    /**
     * Compare this week (startOfWeek -> now) against the equivalent period last week.
     *
     * @return array{direction: string, percent: float}
     */
    protected function weeklyTrend(Closure $aggregate, ?Builder $base = null): array
    {
        $base ??= Order::query();

        $thisWeekQuery = (clone $base)->whereBetween('created_at', [now()->startOfWeek(), now()]);
        $lastWeekQuery = (clone $base)->whereBetween('created_at', [
            now()->startOfWeek()->subWeek(),
            now()->subWeek(),
        ]);

        $thisWeek = (float) $aggregate($thisWeekQuery);
        $lastWeek = (float) $aggregate($lastWeekQuery);

        if ($lastWeek <= 0.0) {
            return [
                'direction' => $thisWeek > 0 ? 'up' : 'down',
                'percent' => $thisWeek > 0 ? 100.0 : 0.0,
            ];
        }

        $percent = (($thisWeek - $lastWeek) / $lastWeek) * 100;

        return [
            'direction' => $percent >= 0 ? 'up' : 'down',
            'percent' => round(abs($percent), 1),
        ];
    }
}
