<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Services\Payment\DTOs\PaymentResult;
use App\Services\Payment\DTOs\RefundResult;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function createOrder(Order $order, PaymentGateway $config): array;

    public function verifyPayment(Request $request, PaymentGateway $config): PaymentResult;

    public function refund(Payment $payment, float $amount, PaymentGateway $config): RefundResult;

    public function getCheckoutView(): string;
}
