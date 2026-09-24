<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Artisan;

class LowStockProducts extends BaseWidget
{
    protected static ?string $heading = 'Low Stock Alert';

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->whereColumn('stock', '<=', 'low_stock_threshold')->orderBy('stock'))
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('sku')->label('SKU'),
                Tables\Columns\TextColumn::make('stock')->color('danger'),
                Tables\Columns\TextColumn::make('low_stock_threshold'),
            ])
            ->paginated(false)
            ->headerActions([
                Tables\Actions\Action::make('run_low_stock_check')
                    ->label('Run Low Stock Check')
                    ->icon('heroicon-o-envelope')
                    ->action(function () {
                        Artisan::call('himashva:low-stock-alert');

                        Notification::make()
                            ->title('Low stock check run')
                            ->body(trim(Artisan::output()))
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
