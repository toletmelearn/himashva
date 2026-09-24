<?php

namespace App\Http\Controllers;

use App\Models\AbandonedCart;
use App\Models\Address;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\OrderService;
use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cart,
        protected CouponService $coupons,
        protected OrderService $orders,
        protected PaymentGatewayManager $gateways,
    ) {}

    public function index()
    {
        $items = $this->cart->getItems();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $subtotal = $this->cart->getSubtotal();
        $couponCode = Session::get('applied_coupon');
        $discount = 0;
        $coupon = null;

        if ($couponCode) {
            [$valid, , $coupon] = $this->coupons->validate($couponCode, $subtotal);
            $discount = $valid ? $this->coupons->calculateDiscount($coupon, $subtotal) : 0;
        }

        $shippingThreshold = (float) settings('free_shipping_threshold', 999);
        $flatRate = (float) settings('flat_shipping_rate', 49);
        $shipping = ($subtotal - $discount) >= $shippingThreshold ? 0 : $flatRate;
        $tax = $this->orders->calculateTax($items, $subtotal, $discount);
        $total = $subtotal - $discount + $shipping + $tax;

        $addresses = Auth::check() ? Address::where('user_id', Auth::id())->get() : collect();
        $activeGateways = $this->gateways->activeGateways();

        // COD needs no configured gateway row, so it's always offered even if unseeded.
        if (! $activeGateways->contains('name', 'cod')) {
            $activeGateways = $activeGateways->push(new PaymentGateway([
                'name' => 'cod',
                'display_name' => 'Cash on Delivery',
                'description' => 'Pay when you receive your order',
            ]));
        }

        $razorpayGateway = $activeGateways->firstWhere('name', 'razorpay');
        $razorpayEnabled = $razorpayGateway
            && filled($razorpayGateway->getCredential('key_id'))
            && filled($razorpayGateway->getCredential('key_secret'));

        $pendingOrderId = Session::get('pending_order');
        $pendingPaymentMethod = $pendingOrderId
            ? Order::whereKey($pendingOrderId)->value('payment_method')
            : null;

        return view('checkout.index', compact('items', 'subtotal', 'discount', 'shipping', 'tax', 'total', 'addresses', 'razorpayEnabled', 'activeGateways', 'pendingPaymentMethod'));
    }

    public function placeOrder(Request $request)
    {
        $items = $this->cart->getItems();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // 'cod' is always a valid fallback — it needs no configured gateway row to work.
        $activeGatewayNames = $this->gateways->activeGateways()->pluck('name')->push('cod')->unique();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string',
            'address_line_2' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'required|string|max:10',
            'country' => 'nullable|string',
            'payment_method' => ['required', Rule::in($activeGatewayNames)],
        ]);

        $data['country'] = $data['country'] ?? 'India';

        $subtotal = $this->cart->getSubtotal();
        $couponCode = Session::get('applied_coupon');
        $coupon = null;

        if ($couponCode) {
            [$valid, , $coupon] = $this->coupons->validate($couponCode, $subtotal);
            $coupon = $valid ? $coupon : null;
        }

        $order = $this->orders->createOrder($items, $data, $data['payment_method'], $coupon);

        if ($data['payment_method'] === 'cod') {
            $this->cart->clear();
            Session::forget('applied_coupon');

            return redirect()->route('order.success', $order->order_number)->with('success', 'Order placed successfully!');
        }

        // any non-COD gateway: hold cart until payment verified, redirect to checkout page with order context
        return redirect()->route('checkout.index')->with('pending_order', $order->id);
    }

    /**
     * Captures a guest's email as soon as they type it at checkout, so an
     * abandoned-cart reminder can still be sent even if they never place
     * an order. Logged-in users already get an email-attached snapshot
     * via CartService::snapshotCart(), so this only matters for guests.
     */
    public function saveEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        if (! Auth::check()) {
            $existing = AbandonedCart::where('session_id', Session::getId())->where('status', 'active')->first();

            if ($existing) {
                $existing->update(['email' => $request->email, 'expires_at' => now()->addDays(30)]);
            } else {
                $items = $this->cart->getItems();

                if ($items->isNotEmpty()) {
                    [$cartData, $total] = $this->cart->buildCartSnapshotData($items);

                    AbandonedCart::create([
                        'session_id' => Session::getId(),
                        'email' => $request->email,
                        'cart_data' => $cartData,
                        'total' => $total,
                        'status' => 'active',
                        'expires_at' => now()->addDays(30),
                    ]);
                }
            }
        }

        return response()->json(['success' => true]);
    }

    public function success(string $orderNumber)
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->firstOrFail();

        return view('checkout.success', compact('order'));
    }
}
