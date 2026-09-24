<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class DailySalesSparkline extends Widget
{
    protected static string $view = 'filament.widgets.daily-sales-sparkline';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected static bool $isLazy = false;

    /**
     * @return array{days: array<int, array{date: string, label: string, count: int, height: int}>, weekTotal: int}
     */
    protected function getViewData(): array
    {
        $counts = Order::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        $days = collect(range(13, 0))->map(function (int $i) use ($counts) {
            $date = now()->subDays($i)->format('Y-m-d');

            return [
                'date' => $date,
                'label' => Carbon::parse($date)->format('d M'),
                'count' => (int) ($counts[$date] ?? 0),
            ];
        });

        $max = max($days->max('count'), 1);

        $days = $days->map(fn (array $day) => [
            ...$day,
            'height' => max((int) round(($day['count'] / $max) * 100), $day['count'] > 0 ? 8 : 3),
        ]);

        return [
            'days' => $days->toArray(),
            'weekTotal' => Order::whereDate('created_at', '>=', now()->startOfWeek())->count(),
        ];
    }
}
