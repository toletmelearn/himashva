@extends('emails.layout')

@section('subject', 'Order Confirmed — #' . $order->order_number)

@section('content')
    <h2>Thank you for your order, {{ $order->name }}!</h2>
    <p>We've received your order and we're getting it ready. Here's a summary.</p>

    <div class="card">
        <p style="margin: 0;"><strong>Order Number:</strong> {{ $order->order_number }}</p>
        <p style="margin: 4px 0 0;"><strong>Order Date:</strong> {{ $order->created_at->format('d M Y') }}</p>
        <p style="margin: 4px 0 0;"><strong>Payment Method:</strong> {{ strtoupper($order->payment_method) }}</p>
        <p style="margin: 4px 0 0;"><strong>Estimated Delivery:</strong> 4-7 business days</p>
    </div>

    <h3>Order Items</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}@if ($item->variant_name) ({{ $item->variant_name }})@endif</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ format_price($item->unit_price) }}</td>
                    <td>{{ format_price($item->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="data-table">
        <tr>
            <td>Subtotal</td>
            <td style="text-align: right;">{{ format_price($order->subtotal) }}</td>
        </tr>
        @if ((float) $order->discount_amount > 0)
            <tr>
                <td>Discount</td>
                <td style="text-align: right;">-{{ format_price($order->discount_amount) }}</td>
            </tr>
        @endif
        <tr>
            <td>Shipping</td>
            <td style="text-align: right;">{{ (float) $order->shipping_amount > 0 ? format_price($order->shipping_amount) : 'Free' }}</td>
        </tr>
        <tr>
            <td>Tax</td>
            <td style="text-align: right;">{{ format_price($order->tax_amount) }}</td>
        </tr>
        <tr>
            <td><strong>Grand Total</strong></td>
            <td style="text-align: right;"><strong>{{ format_price($order->total) }}</strong></td>
        </tr>
    </table>

    <h3>Shipping Address</h3>
    <p style="margin: 0;">
        {{ $order->name }}<br>
        {{ $order->address_line_1 }}@if ($order->address_line_2), {{ $order->address_line_2 }}@endif<br>
        {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}<br>
        {{ $order->country }}
    </p>

    <p style="text-align: center;">
        <a href="{{ route('track.form') }}" class="button">Track Your Order</a>
    </p>
@endsection
