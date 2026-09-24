<?php

namespace App\Services\Payment\Drivers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Services\Payment\DTOs\PaymentResult;
use App\Services\Payment\DTOs\RefundResult;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CodDriver implements PaymentGatewayInterface
{
    public function createOrder(Order $order, PaymentGateway $config): array
    {
        $orderId = 'COD-'.strtoupper(Str::random(10));

        Payment::create([
            'order_id' => $order->id,
            'gateway' => $config->name,
            'gateway_order_id' => $orderId,
            'amount' => $order->total,
            'currency' => 'INR',
            'status' => 'pending',
        ]);

        return [
            'order_id' => $orderId,
            'amount' => (float) $order->total,
            'order_number' => $order->order_number,
        ];
    }

    public function verifyPayment(Request $request, PaymentGateway $config): PaymentResult
    {
        $orderId = $request->input('order_id');
        $order = Order::findOrFail($orderId);

        return new PaymentResult(
            success: true,
            transactionId: null,
            amount: (float) $order->total,
            method: 'cod',
        );
    }

    public function refund(Payment $payment, float $amount, PaymentGateway $config): RefundResult
    {
        return new RefundResult(success: true, refundId: null, amount: $amount, rawResponse: ['note' => 'Manual refund for COD order']);
    }

    public function getCheckoutView(): string
    {
        return 'checkout.partials.cod';
    }
}
