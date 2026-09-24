<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue Trend';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 2;

    protected static bool $isLazy = false;

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '90' => 'Last 90 days',
            'year' => 'This Year',
        ];
    }

    protected function getData(): array
    {
        [$days, $start] = $this->resolveRange();

        $revenue = Order::query()
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as orders')
            ->where('created_at', '>=', $start)
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (₹)',
                    'data' => $days->map(fn ($d) => (float) ($revenue[$d]->total ?? 0))->toArray(),
                    'borderColor' => '#8B5E3C',
                    'backgroundColor' => 'rgba(139, 94, 60, 0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Orders',
                    'data' => $days->map(fn ($d) => (int) ($revenue[$d]->orders ?? 0))->toArray(),
                    'borderColor' => '#C9A77D',
                    'backgroundColor' => 'rgba(201, 167, 125, 0.15)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $days->map(fn ($d) => Carbon::parse($d)->format('d M'))->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'position' => 'left',
                    'title' => ['display' => true, 'text' => 'Revenue (₹)', 'color' => '#8B5E3C'],
                ],
                'y1' => [
                    'type' => 'linear',
                    'position' => 'right',
                    'title' => ['display' => true, 'text' => 'Orders', 'color' => '#C9A77D'],
                    'grid' => ['drawOnChartArea' => false],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array{0: Collection<int, string>, 1: Carbon}
     */
    protected function resolveRange(): array
    {
        if ($this->filter === 'year') {
            $start = now()->startOfYear();
            $daysCount = (int) now()->diffInDays($start);

            return [
                collect(range($daysCount, 0))->map(fn ($i) => now()->subDays($i)->format('Y-m-d')),
                $start,
            ];
        }

        $daysCount = (int) ($this->filter ?? 30);
        $start = now()->subDays($daysCount);

        return [
            collect(range($daysCount - 1, 0))->map(fn ($i) => now()->subDays($i)->format('Y-m-d')),
            $start,
        ];
    }
}
