<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    public const TREE_CACHE_KEY = 'category.tree';

    public const HOME_CACHE_KEY = 'category.home_with_product_counts';

    /**
     * Root categories used for shop sidebar/nav. Rarely changes, so it is
     * cached for a full day.
     *
     * Cached as raw attribute arrays, not Eloquent models: the database
     * cache store's `serializable_classes` config (see config/cache.php)
     * rejects unserializing objects, silently turning them into
     * __PHP_Incomplete_Class on read.
     */
    public function tree(): Collection
    {
        $rows = Cache::remember(self::TREE_CACHE_KEY, 86400, function () {
            return Category::active()->root()->orderBy('sort_order')
                ->get()
                ->map(fn (Category $category) => $category->getAttributes())
                ->all();
        });

        return Category::hydrate($rows);
    }

    /**
     * Root categories with live product counts for the homepage tiles.
     * Cached for an hour since product counts change more often than the
     * category list itself. See tree() for why this caches raw arrays.
     */
    public function homeCategories(int $limit = 6): Collection
    {
        $rows = Cache::remember(self::HOME_CACHE_KEY, 3600, function () use ($limit) {
            return Category::active()->root()->withCount('products')->orderBy('sort_order')->limit($limit)
                ->get()
                ->map(fn (Category $category) => $category->getAttributes())
                ->all();
        });

        return Category::hydrate($rows);
    }

    public static function flushCache(): void
    {
        Cache::forget(self::TREE_CACHE_KEY);
        Cache::forget(self::HOME_CACHE_KEY);
    }
}
