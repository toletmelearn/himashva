<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    /**
     * Products frequently bought alongside the given product, ranked by
     * co-occurrence count across all order_items. Falls back to the same
     * category when no order history exists yet.
     */
    public function frequentlyBoughtWith(int $productId, int $limit = 4): Collection
    {
        return Cache::remember("fbt_{$productId}_{$limit}", 3600, function () use ($productId, $limit) {
            $coProductIds = DB::table('order_items as oi1')
                ->join('order_items as oi2', 'oi1.order_id', '=', 'oi2.order_id')
                ->join('products', 'products.id', '=', 'oi2.product_id')
                ->where('oi1.product_id', $productId)
                ->where('oi2.product_id', '!=', $productId)
                ->where('products.status', 'active')
                ->groupBy('oi2.product_id')
                ->orderByDesc(DB::raw('COUNT(*)'))
                ->limit($limit)
                ->pluck('oi2.product_id');

            if ($coProductIds->isEmpty()) {
                return $this->categoryFallback($productId, $limit);
            }

            return Product::whereIn('id', $coProductIds)
                ->with('images')
                ->get()
                ->sortBy(fn (Product $product) => $coProductIds->search($product->id))
                ->values();
        });
    }

    protected function categoryFallback(int $productId, int $limit): Collection
    {
        $product = Product::find($productId);

        if (! $product) {
            return collect();
        }

        return Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $productId)
            ->with('images')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
