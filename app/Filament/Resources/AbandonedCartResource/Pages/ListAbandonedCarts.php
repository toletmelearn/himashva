<?php

namespace App\Filament\Resources\AbandonedCartResource\Pages;

use App\Filament\Resources\AbandonedCartResource;
use App\Filament\Resources\AbandonedCartResource\Widgets\AbandonedCartStatsOverview;
use Filament\Resources\Pages\ListRecords;

class ListAbandonedCarts extends ListRecords
{
    protected static string $resource = AbandonedCartResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AbandonedCartStatsOverview::class,
        ];
    }
}
