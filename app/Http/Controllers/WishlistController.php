<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $items = Wishlist::with(['product.images'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('account.wishlist', compact('items'));
    }

    public function toggle(Request $request)
    {
        if (! auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Please log in to use wishlist.'], 401);
        }

        $data = $request->validate(['product_id' => 'required|exists:products,id']);

        $existing = Wishlist::where('user_id', auth()->id())->where('product_id', $data['product_id'])->first();

        if ($existing) {
            $existing->delete();
            $inWishlist = false;
        } else {
            Wishlist::create(['user_id' => auth()->id(), 'product_id' => $data['product_id']]);
            $inWishlist = true;
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'in_wishlist' => $inWishlist]);
        }

        return back()->with('success', $inWishlist ? 'Added to wishlist.' : 'Removed from wishlist.');
    }
}
