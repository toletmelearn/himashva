<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductPerformanceExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Product::query()
            ->with('category')
            ->select('products.*')
            ->selectRaw('products.total_sold * COALESCE(products.sale_price, products.price) as revenue')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn (Product $product) => [
                $product->name,
                $product->category?->name,
                $product->total_sold,
                $product->stock,
                $product->avg_rating,
                $product->review_count,
                $product->price,
                $product->sale_price,
                $product->revenue,
            ]);
    }

    public function headings(): array
    {
        return ['Name', 'Category', 'Units Sold', 'Stock', 'Avg. Rating', 'Reviews', 'Price', 'Sale Price', 'Revenue'];
    }
}
