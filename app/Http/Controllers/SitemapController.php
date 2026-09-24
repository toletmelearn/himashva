<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('shop'), 'priority' => '0.9'],
        ]);

        Category::active()->get()->each(function ($category) use ($urls) {
            $urls->push(['loc' => route('category.show', $category->slug), 'priority' => '0.7']);
        });

        Product::visible()->get()->each(function ($product) use ($urls) {
            $urls->push(['loc' => route('product.show', $product->slug), 'priority' => '0.8']);
        });

        Page::active()->get()->each(function ($page) use ($urls) {
            $urls->push(['loc' => route('page.show', $page->slug), 'priority' => '0.5']);
        });

        return response()->view('sitemap', compact('urls'))
            ->header('Content-Type', 'text/xml');
    }
}
