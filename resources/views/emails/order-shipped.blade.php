@extends('emails.layout')

@section('subject', 'Your Order #' . $order->order_number . ' Has Been Shipped!')

@section('content')
    <h2>Great news, {{ $order->name }}!</h2>
    <p>Your order <strong>#{{ $order->order_number }}</strong> is on its way to you.</p>

    <div class="card">
        <span class="badge badge-info">Shipped</span>
        <p style="margin: 12px 0 0;"><strong>Estimated Delivery:</strong> 4-7 business days</p>
        @if ($order->shipping_partner)
            <p style="margin: 4px 0 0;"><strong>Courier Partner:</strong> {{ $order->shipping_partner }}</p>
        @endif
        @if ($order->tracking_number)
            <p style="margin: 4px 0 0;"><strong>Tracking Number:</strong> {{ $order->tracking_number }}</p>
        @endif
    </div>

    <h3>Items in This Shipment</h3>
    <ul>
        @foreach ($order->items as $item)
            <li>{{ $item->product_name }}@if ($item->variant_name) ({{ $item->variant_name }})@endif — Qty {{ $item->quantity }}</li>
        @endforeach
    </ul>

    <p style="text-align: center;">
        @if ($order->tracking_url)
            <a href="{{ $order->tracking_url }}" class="button">Track Your Order</a>
        @else
            <a href="{{ route('track.form') }}" class="button">Track Your Order</a>
        @endif
    </p>
@endsection
