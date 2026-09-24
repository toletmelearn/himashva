<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #2C2018; }
        h1 { color: #8B5E3C; margin-bottom: 0; }
        .muted { color: #6D4829; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #EAE2D6; padding: 8px; text-align: left; }
        th { background: #F8F3EC; }
        .totals { width: 300px; margin-left: auto; margin-top: 20px; }
        .totals td { border: none; padding: 4px 8px; }
        .totals .grand { font-weight: bold; font-size: 14px; border-top: 2px solid #8B5E3C; }
    </style>
</head>
<body>
    <h1>Himashva</h1>
    <p class="muted">Handcrafted Candles &amp; Home Fragrances</p>
    @if (settings('gst_number'))
        <p class="muted">GSTIN: {{ settings('gst_number') }}</p>
    @endif
    <hr>
    <table style="border: none;">
        <tr style="border: none;">
            <td style="border: none;">
                <strong>Invoice #:</strong> {{ $order->order_number }}<br>
                <strong>Date:</strong> {{ $order->created_at->format('d M Y') }}<br>
                <strong>Payment:</strong> {{ strtoupper($order->payment_method) }} ({{ ucfirst($order->payment_status) }})
            </td>
            <td style="border: none;">
                <strong>Bill To:</strong><br>
                {{ $order->name }}<br>
                {{ $order->address_line_1 }} {{ $order->address_line_2 }}<br>
                {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}<br>
                {{ $order->phone }}
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product_name }} @if($item->variant_name) ({{ $item->variant_name }}) @endif</td>
                <td>{{ $item->sku }}</td>
                <td>{{ $item->quantity }}</td>
                <td>₹{{ number_format($item->unit_price, 2) }}</td>
                <td>₹{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td>₹{{ number_format($order->subtotal, 2) }}</td></tr>
        <tr><td>Discount</td><td>-₹{{ number_format($order->discount_amount, 2) }}</td></tr>
        <tr><td>Shipping</td><td>₹{{ number_format($order->shipping_amount, 2) }}</td></tr>
        <tr><td>GST @{{ rtrim(rtrim(number_format((float) settings('default_tax_rate', 18), 2), '0'), '.') }}%</td><td>₹{{ number_format($order->tax_amount, 2) }}</td></tr>
        {{-- Note: this label reflects the store's default GST rate; the amount above is the actual tax charged, which may differ if any line item has a custom tax_rate. --}}
        <tr class="grand"><td>Total</td><td>₹{{ number_format($order->total, 2) }}</td></tr>
    </table>
</body>
</html>
