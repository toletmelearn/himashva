@extends('emails.layout')

@section('subject', 'You left something behind at Himashva 🕯️')

@section('content')
    <h2>You left something behind!</h2>
    <p>We saved the items in your cart — they're still waiting for you.</p>

    <table class="data-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cart->cart_data as $item)
                <tr>
                    <td>{{ $item['product_name'] }}@if (! empty($item['variant_name'])) ({{ $item['variant_name'] }})@endif</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>{{ format_price($item['price']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="data-table">
        <tr>
            <td><strong>Total</strong></td>
            <td style="text-align: right;"><strong>{{ format_price($cart->total) }}</strong></td>
        </tr>
    </table>

    @if (settings('abandoned_cart_coupon_code') && settings('abandoned_cart_coupon_text'))
        <div class="card">
            <p style="margin: 0;">{{ settings('abandoned_cart_coupon_text') }}</p>
            <p style="margin: 8px 0 0;"><strong>Coupon Code: {{ settings('abandoned_cart_coupon_code') }}</strong></p>
        </div>
    @endif

    <p style="text-align: center;">
        <a href="{{ url('/cart') }}" class="button">Complete Your Order</a>
    </p>

    @if (settings('whatsapp_number'))
        <p style="text-align: center; font-size: 13px;">
            Need help deciding? <a href="https://wa.me/{{ preg_replace('/\D/', '', settings('whatsapp_number')) }}">Chat with us on WhatsApp</a>
        </p>
    @endif
@endsection
