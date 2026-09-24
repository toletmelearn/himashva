<?php

namespace App\Filament\Resources\AbandonedCartResource\Widgets;

use App\Models\AbandonedCart;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AbandonedCartStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $abandoned = AbandonedCart::whereIn('status', ['active', 'reminded'])->count();
        $recovered = AbandonedCart::where('status', 'recovered')->count();
        $expired = AbandonedCart::where('status', 'expired')->count();
        $recoveryRate = ($recovered + $expired) > 0 ? round(($recovered / ($recovered + $expired)) * 100, 1) : 0;
        $revenueRecovered = AbandonedCart::where('status', 'recovered')->sum('total');

        return [
            Stat::make('Total Abandoned', $abandoned)->color('warning'),
            Stat::make('Total Recovered', $recovered)->color('success'),
            Stat::make('Recovery Rate', $recoveryRate.'%')->color('info'),
            Stat::make('Revenue Recovered', '₹'.number_format((float) $revenueRecovered, 2))->color('primary'),
        ];
    }
}
