<?php

namespace App\Http\Controllers;

use App\Models\CustomerActivity;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function show(Request $request, string $slug, RecommendationService $recommendations)
    {
        $product = Product::visible()->with(['images', 'variants', 'attributes_', 'category', 'sizeGuides', 'videos'])
            ->where('slug', $slug)
            ->firstOrFail();

        $sizeGuide = $product->sizeGuides->where('is_active', true)->first()
            ?? $product->category?->sizeGuides()->active()->first();

        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with('images')
            ->limit(4)
            ->get();

        $frequentlyBought = $recommendations->frequentlyBoughtWith($product->id, 4);

        $reviews = $product->approvedReviews()->with(['user', 'media'])->latest()->paginate(5, ['*'], 'reviews_page');

        CustomerActivity::create([
            'user_id' => Auth::id(),
            'session_id' => $request->session()->getId(),
            'activity_type' => 'product_view',
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $inWishlist = Auth::check() && Wishlist::where('user_id', Auth::id())->where('product_id', $product->id)->exists();

        $recentlyViewed = array_values(array_filter($request->session()->get('recently_viewed', []), fn ($id) => $id !== $product->id));
        array_unshift($recentlyViewed, $product->id);
        $request->session()->put('recently_viewed', array_slice($recentlyViewed, 0, 8));

        return view('product.show', compact('product', 'relatedProducts', 'frequentlyBought', 'reviews', 'inWishlist', 'sizeGuide'));
    }

    public function quickView(Product $product)
    {
        abort_unless($product->status === 'active', 404);

        $product->load(['images', 'variants', 'category']);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'short_description' => $product->short_description,
            'stock' => $product->stock,
            'category' => $product->category?->name,
            'url' => route('product.show', $product->slug),
            'images' => $product->images->map(fn ($image) => [
                'path' => $image->image_path !== 'placeholder.jpg' ? asset('storage/'.$image->image_path) : null,
            ]),
            'variants' => $product->variants->where('is_active', true)->values()->map(fn ($variant) => [
                'id' => $variant->id,
                'name' => $variant->name,
                'price' => $variant->sale_price ?: $variant->price,
                'stock' => $variant->stock,
            ]),
        ]);
    }
}
