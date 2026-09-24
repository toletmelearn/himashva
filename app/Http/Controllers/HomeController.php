<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;

class HomeController extends Controller
{
    public function index()
    {
        $heroBanners = Banner::active()->position('hero')->orderBy('sort_order')->get();
        $promoBanner = Banner::active()->position('promo')->orderBy('sort_order')->first();
        $categories = Category::active()->root()->withCount('products')->orderBy('sort_order')->limit(6)->get();
        $featured = Product::active()->featured()->with('images')->inRandomOrder()->limit(8)->get();
        $bestsellers = Product::active()->bestseller()->with('images')->limit(8)->get();
        $testimonials = Review::approved()->with(['user', 'product'])->latest()->limit(6)->get();

        // A small real-photo lineup for the hero visual, so it shows actual
        // catalog items instead of generic placeholder tiles.
        $heroProducts = Product::active()
            ->whereHas('images', fn ($q) => $q->where('image_path', '!=', 'placeholder.jpg'))
            ->with(['images' => fn ($q) => $q->where('image_path', '!=', 'placeholder.jpg')->orderBy('sort_order')])
            ->orderByDesc('is_bestseller')
            ->orderByDesc('is_featured')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('home', compact('heroBanners', 'promoBanner', 'categories', 'featured', 'bestsellers', 'testimonials', 'heroProducts'));
    }
}
