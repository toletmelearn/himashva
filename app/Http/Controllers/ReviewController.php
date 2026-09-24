<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use App\Models\ReviewMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'order_id' => 'nullable|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'media' => 'nullable|array|max:5',
            'media.*' => 'file|max:5120|mimes:jpg,jpeg,png,webp,mp4,mov',
        ]);

        $data['user_id'] = Auth::id();
        $data['is_verified_purchase'] = $this->hasDeliveredOrderContaining(Auth::id(), $data['product_id']);

        $review = Review::create([
            'user_id' => $data['user_id'],
            'product_id' => $data['product_id'],
            'order_id' => $data['order_id'] ?? null,
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'comment' => $data['comment'] ?? null,
            'is_verified_purchase' => $data['is_verified_purchase'],
        ]);

        foreach ($request->file('media', []) as $index => $file) {
            $mime = $file->getMimeType();
            $type = str_starts_with($mime, 'video/') ? 'video' : 'image';
            $fileName = Str::random(20).'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs("reviews/{$review->id}", $fileName, 'public');

            ReviewMedia::create([
                'review_id' => $review->id,
                'type' => $type,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $mime,
                'sort_order' => $index,
            ]);
        }

        $product = $review->product;
        $product->update([
            'review_count' => $product->reviews()->count(),
            'avg_rating' => round($product->reviews()->avg('rating'), 2),
        ]);

        return back()->with('success', 'Thank you for your review! It will be visible once approved.');
    }

    protected function hasDeliveredOrderContaining(?int $userId, int $productId): bool
    {
        if (! $userId) {
            return false;
        }

        return Order::where('user_id', $userId)
            ->where('order_status', 'delivered')
            ->whereHas('items', fn ($query) => $query->where('product_id', $productId))
            ->exists();
    }
}
