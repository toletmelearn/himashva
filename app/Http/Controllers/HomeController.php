<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function index(CategoryService $categories)
    {
        $heroBanners = Banner::active()->position('hero')->orderBy('sort_order')->get();
        $promoBanner = Banner::active()->position('promo')->orderBy('sort_order')->first();
        $categories = $categories->homeCategories();
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

    public function recentPurchases(): JsonResponse
    {
        $purchases = Order::query()
            ->where('created_at', '>=', now()->subHours(48))
            ->whereIn('order_status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->with(['items.product.images'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function (Order $order) {
                $item = $order->items->first();

                if (! $item || ! $item->product) {
                    return null;
                }

                $product = $item->product;
                $image = $product->images->first();
                $imagePath = ($image && $image->image_path !== 'placeholder.jpg')
                    ? asset('storage/'.$image->image_path)
                    : null;

                return [
                    'name' => explode(' ', $order->name)[0],
                    'city' => $order->city,
                    'product' => $product->name,
                    'product_url' => route('product.show', $product->slug),
                    'image' => $imagePath,
                    'time_ago' => $order->created_at->diffForHumans(),
                ];
            })
            ->filter()
            ->values();

        return response()->json($purchases);
    }
}
