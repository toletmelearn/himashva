<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CompareController extends Controller
{
    public const MAX_COMPARE = 4;

    public const SESSION_KEY = 'compare_products';

    public function add(Product $product)
    {
        $ids = Session::get(self::SESSION_KEY, []);

        if (in_array($product->id, $ids, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Product already in comparison.',
                'count' => count($ids),
            ]);
        }

        if (count($ids) >= self::MAX_COMPARE) {
            return response()->json([
                'success' => false,
                'message' => 'You can compare up to '.self::MAX_COMPARE.' products at a time.',
                'count' => count($ids),
            ], 422);
        }

        $ids[] = $product->id;
        Session::put(self::SESSION_KEY, $ids);

        return response()->json(['success' => true, 'count' => count($ids), 'ids' => $ids]);
    }

    public function remove(Product $product)
    {
        $ids = Session::get(self::SESSION_KEY, []);
        $ids = array_values(array_diff($ids, [$product->id]));
        Session::put(self::SESSION_KEY, $ids);

        return response()->json(['success' => true, 'count' => count($ids), 'ids' => $ids]);
    }

    public function clear()
    {
        Session::forget(self::SESSION_KEY);

        return response()->json(['success' => true, 'count' => 0]);
    }

    public function show(Request $request)
    {
        $ids = Session::get(self::SESSION_KEY, []);

        $products = Product::whereIn('id', $ids)
            ->with(['category', 'images', 'variants', 'attributes_', 'brand'])
            ->get()
            ->sortBy(fn ($p) => array_search($p->id, $ids))
            ->values();

        $attributeNames = $products
            ->flatMap(fn ($product) => $product->attributes_->pluck('attribute_name'))
            ->unique()
            ->values();

        return view('compare.show', compact('products', 'attributeNames'));
    }
}
