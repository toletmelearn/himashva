<?php

namespace App\Services\Payment\Drivers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Services\Payment\DTOs\PaymentResult;
use App\Services\Payment\DTOs\RefundResult;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;

class RazorpayDriver implements PaymentGatewayInterface
{
    protected function api(PaymentGateway $config): Api
    {
        return new Api($config->getCredential('key_id'), $config->getCredential('key_secret'));
    }

    public function createOrder(Order $order, PaymentGateway $config): array
    {
        $api = $this->api($config);

        $razorpayOrder = $api->order->create([
            'receipt' => $order->order_number,
            'amount' => (int) round($order->total * 100),
            'currency' => 'INR',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'gateway' => $config->name,
            'gateway_order_id' => $razorpayOrder['id'],
            'amount' => $order->total,
            'currency' => 'INR',
            'status' => 'created',
        ]);

        return [
            'key' => $config->getCredential('key_id'),
            'amount' => (int) round($order->total * 100),
            'currency' => 'INR',
            'razorpay_order_id' => $razorpayOrder['id'],
            'order_number' => $order->order_number,
            'name' => settings('site_name', 'Himashva'),
            'prefill' => ['name' => $order->name, 'email' => $order->email, 'contact' => $order->phone],
        ];
    }

    public function verifyPayment(Request $request, PaymentGateway $config): PaymentResult
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $order = Order::findOrFail($data['order_id']);
        $api = $this->api($config);

        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $data['razorpay_order_id'],
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_signature' => $data['razorpay_signature'],
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay payment verification failed', ['order_id' => $order->id, 'message' => $e->getMessage()]);

            return new PaymentResult(
                success: false,
                transactionId: null,
                amount: (float) $order->total,
                message: 'Payment verification failed.',
            );
        }

        return new PaymentResult(
            success: true,
            transactionId: $data['razorpay_payment_id'],
            amount: (float) $order->total,
            method: 'razorpay',
            rawResponse: $data,
        );
    }

    public function refund(Payment $payment, float $amount, PaymentGateway $config): RefundResult
    {
        $api = $this->api($config);

        try {
            $refund = $api->payment->fetch($payment->gateway_payment_id)->refund([
                'amount' => (int) round($amount * 100),
            ]);

            return new RefundResult(
                success: true,
                refundId: $refund['id'] ?? null,
                amount: $amount,
                rawResponse: is_array($refund) ? $refund : (array) $refund,
            );
        } catch (\Exception $e) {
            Log::error('Razorpay refund failed', ['payment_id' => $payment->id, 'message' => $e->getMessage()]);

            return new RefundResult(success: false, refundId: null, amount: $amount, rawResponse: ['error' => $e->getMessage()]);
        }
    }

    public function getCheckoutView(): string
    {
        return 'checkout.partials.razorpay';
    }
}
