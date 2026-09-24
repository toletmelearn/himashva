<?php

namespace App\Filament\Pages;

use App\Exports\ProductPerformanceExport;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class ProductPerformanceReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?string $navigationLabel = 'Performance Report';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.product-performance-report';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->isAdmin() || $user->hasRole('super_admin') || $user->can('products.view') || $user->can('products.manage'));
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->select('products.*')
                    ->selectRaw('products.total_sold * COALESCE(products.sale_price, products.price) as revenue')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label('Category')->sortable(),
                Tables\Columns\TextColumn::make('total_sold')->label('Units Sold')->sortable(),
                Tables\Columns\TextColumn::make('stock')->sortable(),
                Tables\Columns\TextColumn::make('avg_rating')->label('Avg. Rating')->sortable(),
                Tables\Columns\TextColumn::make('review_count')->label('Reviews')->sortable(),
                Tables\Columns\TextColumn::make('price')->money('INR')->sortable(),
                Tables\Columns\TextColumn::make('sale_price')->money('INR')->sortable(),
                Tables\Columns\TextColumn::make('revenue')
                    ->getStateUsing(fn (Product $record) => $record->total_sold * (float) ($record->sale_price ?: $record->price))
                    ->money('INR')
                    ->sortable(),
            ])
            ->defaultSort('revenue', 'desc')
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(Category::query()->pluck('name', 'id')),
                Filter::make('ordered_between')
                    ->label('Ordered between')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['from'] || $data['until'],
                            fn ($q) => $q->whereHas('orderItems.order', function ($orderQuery) use ($data) {
                                $orderQuery
                                    ->when($data['from'], fn ($oq, $v) => $oq->whereDate('created_at', '>=', $v))
                                    ->when($data['until'], fn ($oq, $v) => $oq->whereDate('created_at', '<=', $v));
                            })
                        );
                    }),
                Filter::make('min_revenue')
                    ->form([
                        Forms\Components\TextInput::make('min_revenue')->numeric()->prefix('₹'),
                    ])
                    ->query(fn ($query, array $data) => $query->when(
                        $data['min_revenue'],
                        fn ($q, $v) => $q->whereRaw('products.total_sold * COALESCE(products.sale_price, products.price) >= ?', [$v])
                    )),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export to Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn () => Excel::download(new ProductPerformanceExport, 'product-performance.xlsx')),
            ]);
    }
}
