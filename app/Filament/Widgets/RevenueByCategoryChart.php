<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;

class RevenueByCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue by Category';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected static bool $isLazy = false;

    /**
     * Warm palette, per spec Part 3C.
     *
     * @var array<int, string>
     */
    protected array $warmColors = [
        '#8B5E3C', '#C9A77D', '#D97706', '#F2994A',
        '#EB5757', '#B45309', '#F2C94C', '#A0522D',
    ];

    protected function getData(): array
    {
        $rows = OrderItem::query()
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw('categories.name as category, SUM(order_items.line_total) as revenue')
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (₹)',
                    'data' => $rows->pluck('revenue')->map(fn ($v) => (float) $v)->toArray(),
                    'backgroundColor' => $this->warmColors,
                ],
            ],
            'labels' => $rows->pluck('category')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
