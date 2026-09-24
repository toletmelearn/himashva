<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * @var array<string, array{0: int, 1: int}>
     */
    protected const DISCOUNT_BUCKETS = [
        '0-20' => [0, 20],
        '21-40' => [21, 40],
        '41-60' => [41, 60],
        '61-80' => [61, 80],
        '81-100' => [81, 100],
    ];

    public function index(Request $request)
    {
        $query = Product::visible()->with(['images', 'category']);

        return $this->render($query, $request, null, 'Shop All Products');
    }

    public function category(Request $request, string $slug)
    {
        $category = Category::active()->where('slug', $slug)->firstOrFail();

        $query = Product::visible()->with(['images', 'category'])->where('category_id', $category->id);

        return $this->render($query, $request, $category, $category->name);
    }

    public function search(Request $request)
    {
        $term = $request->input('q', '');

        $products = Product::visible()->with(['images', 'category'])
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', "%{$term}%"));
            })
            ->paginate(12)->withQueryString();

        $categories = Category::active()->root()->orderBy('sort_order')->get();

        return view('shop.search', compact('products', 'categories', 'term'));
    }

    public function searchAutocomplete(Request $request)
    {
        $term = trim((string) $request->input('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $products = Product::active()->with(['images', 'category'])
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            })
            ->limit(6)
            ->get()
            ->map(fn (Product $product) => [
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->sale_price ?: $product->price,
                'original_price' => $product->sale_price ? $product->price : null,
                'category' => $product->category?->name,
                'image' => $product->images->first() && $product->images->first()->image_path !== 'placeholder.jpg'
                    ? asset('storage/'.$product->images->first()->image_path)
                    : null,
                'url' => route('product.show', $product->slug),
            ]);

        return response()->json($products);
    }

    protected function render(Builder $query, Request $request, ?Category $activeCategory, string $title)
    {
        $this->applyBaseFilters($query, $request);

        $discountCounts = $this->discountBucketCounts(clone $query);

        $this->applyDiscountFilter($query, $request);
        $this->applySort($query, $request);

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::active()->root()->orderBy('sort_order')->get();

        return view('shop.index', [
            'products' => $products,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'title' => $title,
            'discountCounts' => $discountCounts,
        ]);
    }

    protected function applyBaseFilters(Builder $query, Request $request): void
    {
        if ($request->filled('categories')) {
            $query->whereIn('category_id', (array) $request->input('categories'));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }
    }

    protected function applyDiscountFilter(Builder $query, Request $request): void
    {
        $selected = array_intersect((array) $request->input('discount', []), array_keys(self::DISCOUNT_BUCKETS));

        if (empty($selected)) {
            return;
        }

        $query->where(function (Builder $outer) use ($selected) {
            foreach ($selected as $bucket) {
                [$min, $max] = self::DISCOUNT_BUCKETS[$bucket];
                $outer->orWhere(fn (Builder $inner) => $inner->discountBetween($min, $max));
            }
        });
    }

    protected function applySort(Builder $query, Request $request): void
    {
        match ($request->input('sort')) {
            'price-low-high' => $query->orderBy('price'),
            'price-high-low' => $query->orderByDesc('price'),
            'popularity' => $query->orderByDesc('total_sold'),
            'rating' => $query->orderByDesc('avg_rating'),
            'discount' => $query->orderByRaw('((price - COALESCE(sale_price, price)) / price) * 100 DESC'),
            default => $query->latest(),
        };
    }

    /**
     * Counts products per discount bucket for the current base filters
     * (category/price), so the sidebar can show live counts the same way
     * the discount filter itself is applied.
     *
     * @return array<string, int>
     */
    protected function discountBucketCounts(Builder $query): array
    {
        $counts = [];

        foreach (self::DISCOUNT_BUCKETS as $bucket => [$min, $max]) {
            $counts[$bucket] = (clone $query)->discountBetween($min, $max)->count();
        }

        return $counts;
    }
}
