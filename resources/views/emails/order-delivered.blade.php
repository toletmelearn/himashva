@extends('emails.layout')

@section('subject', 'Your Order #' . $order->order_number . ' Has Been Delivered')

@section('content')
    <h2>Your order has arrived, {{ $order->name }}!</h2>
    <p>
        Order <strong>#{{ $order->order_number }}</strong> was delivered on
        {{ optional($order->delivered_at)->format('d M Y') ?? now()->format('d M Y') }}. We hope you love it.
    </p>

    <div class="card">
        <span class="badge badge-success">Delivered</span>
    </div>

    <h3>Loved What You Got?</h3>
    <p>Leave a review to help other customers:</p>
    <ul>
        @foreach ($order->items as $item)
            @if ($item->product)
                <li>
                    <a href="{{ route('product.show', $item->product->slug) }}#reviews">
                        Review {{ $item->product_name }}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>

    <div class="card">
        <p style="margin: 0;">
            Not quite right? You can request a return within 7 days of delivery from your
            <a href="{{ route('account.orders') }}">account orders page</a>.
        </p>
    </div>
@endsection
