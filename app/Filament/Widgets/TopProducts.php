<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\Widget;

class TopProducts extends Widget
{
    protected static string $view = 'filament.widgets.top-products';

    protected static ?string $heading = 'Top Selling Products';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected static bool $isLazy = false;

    /**
     * @return array{products: array<int, array{name: string, image: ?string, sold: int, revenue: string, percent: int}>}
     */
    protected function getViewData(): array
    {
        $products = Product::query()
            ->with('images')
            ->orderByDesc('total_sold')
            ->limit(8)
            ->get();

        $maxSold = max($products->max('total_sold'), 1);

        return [
            'products' => $products->map(fn (Product $product) => [
                'name' => $product->name,
                'image' => $product->images->first()?->image_path,
                'sold' => $product->total_sold,
                'revenue' => '₹'.number_format($product->total_sold * (float) ($product->sale_price ?: $product->price), 2),
                'percent' => max((int) round(($product->total_sold / $maxSold) * 100), $product->total_sold > 0 ? 4 : 0),
            ])->toArray(),
        ];
    }
}
