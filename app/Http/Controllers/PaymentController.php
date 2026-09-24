<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\CartService;
use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PaymentController extends Controller
{
    public function __construct(protected PaymentGatewayManager $gateways) {}

    public function createRazorpayOrder(Request $request)
    {
        $data = $request->validate(['order_id' => 'required|exists:orders,id']);
        $order = Order::findOrFail($data['order_id']);

        $gateway = $this->gateways->getGateway('razorpay');

        if (! $gateway || ! $gateway->is_active || ! $gateway->getCredential('key_id') || ! $gateway->getCredential('key_secret')) {
            return response()->json(['error' => 'Razorpay is not configured.'], 422);
        }

        $payload = $this->gateways->driver('razorpay')->createOrder($order, $gateway);

        return response()->json($payload);
    }

    public function verifyPayment(Request $request, CartService $cart)
    {
        $orderId = $request->input('order_id');
        $order = Order::findOrFail($orderId);

        $gateway = $this->gateways->getGateway('razorpay');

        if (! $gateway) {
            return response()->json(['success' => false, 'message' => 'Razorpay is not configured.'], 422);
        }

        $result = $this->gateways->driver('razorpay')->verifyPayment($request, $gateway);

        if (! $result->success) {
            $order->update(['payment_status' => 'failed']);

            return response()->json(['success' => false, 'message' => $result->message ?? 'Payment verification failed.'], 422);
        }

        $order->update([
            'payment_status' => 'paid',
            'payment_id' => $result->transactionId,
        ]);

        Payment::where('order_id', $order->id)
            ->where('gateway_order_id', $request->input('razorpay_order_id'))
            ->update([
                'gateway_payment_id' => $result->transactionId,
                'gateway_signature' => $request->input('razorpay_signature'),
                'status' => 'paid',
            ]);

        $cart->clear();
        Session::forget(['applied_coupon', 'pending_order']);

        return response()->json(['success' => true, 'redirect' => route('order.success', $order->order_number)]);
    }
}
