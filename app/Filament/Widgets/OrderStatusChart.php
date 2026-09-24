<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrderStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Order Status Breakdown';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.widgets.order-status-chart';

    /**
     * Colors keyed by order_status value, per spec Part 3B.
     *
     * @var array<string, string>
     */
    protected array $statusColors = [
        'pending' => '#F59E0B',
        'confirmed' => '#F59E0B',
        'processing' => '#3B82F6',
        'shipped' => '#8B5CF6',
        'out_for_delivery' => '#8B5CF6',
        'delivered' => '#10B981',
        'cancelled' => '#EF4444',
        'returned' => '#6B7280',
        'refunded' => '#6B7280',
    ];

    public function getTotalCount(): int
    {
        return Order::query()->count();
    }

    protected function getData(): array
    {
        $statuses = Order::query()
            ->selectRaw('order_status, COUNT(*) as count')
            ->groupBy('order_status')
            ->pluck('count', 'order_status');

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $statuses->values()->toArray(),
                    'backgroundColor' => $statuses->keys()
                        ->map(fn ($status) => $this->statusColors[$status] ?? '#D4BFA6')
                        ->toArray(),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $statuses->keys()->map(fn ($s) => ucfirst(str_replace('_', ' ', $s)))->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'cutout' => '70%',
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
