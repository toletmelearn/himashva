<x-layouts.app>
<x-slot:title>Order {{ $order->order_number }} | Himashva</x-slot:title>

<div class="max-w-4xl mx-auto px-4 py-8">
    @include('account.partials.nav')

    <h1 class="font-display text-3xl text-brand-900 mb-1">Order {{ $order->order_number }}</h1>
    <p class="text-sm text-brand-500 mb-6">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>

    <div class="bg-white border border-brand-200 rounded-xl p-6 mb-6">
        <x-order-timeline :order="$order" />
    </div>

    <div class="bg-white border border-brand-200 rounded-xl p-6 mb-6">
        <h2 class="font-semibold text-brand-900 mb-4">Status Timeline</h2>
        <div class="space-y-3">
            @foreach ($order->statusHistory as $history)
                <div class="flex gap-3 text-sm">
                    <span class="text-brand-400 w-32 shrink-0">{{ $history->created_at->format('d M, h:i A') }}</span>
                    <span class="capitalize font-medium text-brand-800">{{ str_replace('_', ' ', $history->to_status) }}</span>
                    @if ($history->comment)<span class="text-brand-500">— {{ $history->comment }}</span>@endif
                </div>
            @endforeach
        </div>
        @if ($order->tracking_number)
            <div class="mt-4 pt-4 border-t border-brand-100 text-sm">
                <p><strong>Tracking:</strong> {{ $order->tracking_number }} ({{ $order->shipping_partner }})</p>
                @if ($order->tracking_url)<a href="{{ $order->tracking_url }}" target="_blank" class="text-brand-700 underline">Track Shipment</a>@endif
            </div>
        @endif
    </div>

    <div class="bg-white border border-brand-200 rounded-xl p-6">
        <h2 class="font-semibold text-brand-900 mb-4">Items</h2>
        @foreach ($order->items as $item)
            <div class="flex justify-between text-sm py-2 border-b border-brand-50 last:border-0">
                <span>{{ $item->product_name }} @if($item->variant_name)({{ $item->variant_name }})@endif × {{ $item->quantity }}</span>
                <span>₹{{ number_format($item->line_total, 2) }}</span>
            </div>
        @endforeach
        <div class="flex justify-between font-semibold text-brand-900 pt-4 mt-2 border-t border-brand-100">
            <span>Total</span><span>₹{{ number_format($order->total, 2) }}</span>
        </div>
    </div>

    <div class="bg-white border border-brand-200 rounded-xl p-6 mt-6 text-sm">
        <h2 class="font-semibold text-brand-900 mb-2">Shipping Address</h2>
        <p>{{ $order->name }}</p>
        <p>{{ $order->address_line_1 }} {{ $order->address_line_2 }}</p>
        <p>{{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}</p>
        <p>{{ $order->phone }}</p>
    </div>

    @if ($order->returns->isNotEmpty())
        <div class="bg-white border border-brand-200 rounded-xl p-6 mt-6 text-sm">
            <h2 class="font-semibold text-brand-900 mb-2">Return Request</h2>
            @foreach ($order->returns as $return)
                <p>Status: <span class="capitalize font-medium">{{ str_replace('_', ' ', $return->status) }}</span></p>
                <p class="text-brand-500">Reason: {{ str_replace('_', ' ', $return->reason_category) }}</p>
            @endforeach
        </div>
    @elseif ($canRequestReturn)
        <div class="bg-white border border-brand-200 rounded-xl p-6 mt-6">
            <h2 class="font-semibold text-brand-900 mb-4">Request a Return</h2>
            <form method="POST" action="{{ route('account.orders.return', $order->order_number) }}" class="space-y-4 text-sm">
                @csrf
                <div class="space-y-2">
                    @foreach ($order->items as $item)
                        <div class="flex items-center gap-3 border-b border-brand-50 pb-2">
                            <input type="checkbox" name="items[{{ $loop->index }}][order_item_id]" value="{{ $item->id }}">
                            <span class="flex-1">{{ $item->product_name }} @if($item->variant_name)({{ $item->variant_name }})@endif</span>
                            <input type="number" name="items[{{ $loop->index }}][quantity]" min="1" max="{{ $item->quantity }}" value="{{ $item->quantity }}" class="w-16 border border-brand-200 rounded px-2 py-1">
                            <select name="items[{{ $loop->index }}][condition]" class="border border-brand-200 rounded px-2 py-1">
                                <option value="unopened">Unopened</option>
                                <option value="opened">Opened</option>
                                <option value="damaged">Damaged</option>
                            </select>
                        </div>
                    @endforeach
                </div>
                <div>
                    <label class="block text-brand-700 mb-1">Reason</label>
                    <select name="reason_category" class="w-full border border-brand-200 rounded px-3 py-2" required>
                        <option value="defective">Defective</option>
                        <option value="wrong_item">Wrong Item</option>
                        <option value="not_as_described">Not as Described</option>
                        <option value="changed_mind">Changed Mind</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-brand-700 mb-1">Additional details (optional)</label>
                    <textarea name="reason_text" class="w-full border border-brand-200 rounded px-3 py-2"></textarea>
                </div>
                <button type="submit" class="bg-brand-900 text-white px-4 py-2 rounded-lg">Submit Return Request</button>
            </form>
        </div>
    @endif
</div>
</x-layouts.app>
