<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\CategoryService;

class ProductObserver
{
    public function saved(Product $product): void
    {
        CategoryService::flushCache();
    }

    public function deleted(Product $product): void
    {
        CategoryService::flushCache();
    }
}
