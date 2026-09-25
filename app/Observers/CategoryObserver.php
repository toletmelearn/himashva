<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\CategoryService;

class CategoryObserver
{
    public function saved(Category $category): void
    {
        CategoryService::flushCache();
    }

    public function deleted(Category $category): void
    {
        CategoryService::flushCache();
    }
}
