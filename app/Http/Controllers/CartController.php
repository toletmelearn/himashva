<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function __construct(protected CartService $cart, protected CouponService $coupons) {}

    public function index()
    {
        $items = $this->cart->getItems();
        $subtotal = $this->cart->getSubtotal();
        $couponCode = Session::get('applied_coupon');
        $discount = 0;
        $coupon = null;

        if ($couponCode) {
            [$valid, , $coupon] = $this->coupons->validate($couponCode, $subtotal);

            if ($valid) {
                $discount = $this->coupons->calculateDiscount($coupon, $subtotal);
            } else {
                Session::forget('applied_coupon');
            }
        }

        $shippingThreshold = (float) settings('free_shipping_threshold', 999);
        $flatRate = (float) settings('flat_shipping_rate', 49);
        $shipping = ($subtotal - $discount) >= $shippingThreshold ? 0 : $flatRate;
        $total = $subtotal - $discount + $shipping;

        return view('cart.index', compact('items', 'subtotal', 'discount', 'coupon', 'shipping', 'total'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        [$success, $error] = $this->cart->add($data['product_id'], $data['quantity'] ?? 1, $data['variant_id'] ?? null);

        if (! $success) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $error]);
            }

            return back()->with('error', $error);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'count' => $this->cart->getCount()]);
        }

        return back()->with('success', 'Added to cart.');
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate(['quantity' => 'required|integer|min:1']);
        $this->cart->update($id, $data['quantity']);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'subtotal' => $this->cart->getSubtotal()]);
        }

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Request $request, string $id)
    {
        $this->cart->remove($id);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Item removed.');
    }

    public function applyCoupon(Request $request)
    {
        $data = $request->validate(['code' => 'required|string']);
        $subtotal = $this->cart->getSubtotal();

        [$valid, $error, $coupon] = $this->coupons->validate($data['code'], $subtotal);

        if (! $valid) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $error]);
            }

            return back()->with('error', $error);
        }

        Session::put('applied_coupon', $coupon->code);
        Session::forget('auto_coupon');

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Coupon applied!');
    }

    public function removeCoupon()
    {
        Session::forget('applied_coupon');

        return back()->with('success', 'Coupon removed.');
    }

    public function dismissCoupon()
    {
        Session::forget('auto_coupon');

        return response()->json(['ok' => true]);
    }
}
