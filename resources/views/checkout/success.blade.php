<x-layouts.app>
<x-slot:title>Order Confirmed | Himashva</x-slot:title>

<div class="max-w-2xl mx-auto px-4 py-16 text-center">
    <p class="text-6xl mb-4">🎉</p>
    <h1 class="font-display text-3xl text-brand-900 mb-2">Thank you for your order!</h1>
    <p class="text-brand-600 mb-8">Your order <strong>{{ $order->order_number }}</strong> has been placed successfully.</p>

    <div class="bg-white border border-brand-200 rounded-xl p-6 text-left mb-8">
        <div class="flex justify-between text-sm mb-2"><span>Order Number</span><strong>{{ $order->order_number }}</strong></div>
        <div class="flex justify-between text-sm mb-2"><span>Payment Method</span><strong>{{ strtoupper($order->payment_method) }}</strong></div>
        <div class="flex justify-between text-sm mb-2"><span>Subtotal</span><strong>₹{{ number_format($order->subtotal, 2) }}</strong></div>
        @if ($order->discount_amount > 0)
            <div class="flex justify-between text-sm mb-2"><span>Discount</span><strong>-₹{{ number_format($order->discount_amount, 2) }}</strong></div>
        @endif
        <div class="flex justify-between text-sm mb-2"><span>Shipping</span><strong>{{ $order->shipping_amount > 0 ? '₹' . number_format($order->shipping_amount, 2) : 'Free' }}</strong></div>
        <div class="flex justify-between text-sm mb-2"><span>GST</span><strong>₹{{ number_format($order->tax_amount, 2) }}</strong></div>
        <div class="flex justify-between text-sm mb-2"><span>Total</span><strong>₹{{ number_format($order->total, 2) }}</strong></div>
        @if ($order->estimated_delivery)
            <div class="flex justify-between text-sm"><span>Estimated Delivery</span><strong>{{ $order->estimated_delivery->format('d M Y') }}</strong></div>
        @endif

        <div class="border-t border-brand-100 mt-4 pt-4 space-y-1">
            @foreach ($order->items as $item)
                <div class="flex justify-between text-sm text-brand-600">
                    <span>{{ $item->product_name }} × {{ $item->quantity }}</span>
                    <span>₹{{ number_format($item->line_total, 2) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <a href="{{ route('shop') }}" class="bg-brand-700 hover:bg-brand-800 text-white px-8 py-3 rounded-full font-medium inline-block">Continue Shopping</a>
</div>
</x-layouts.app>
