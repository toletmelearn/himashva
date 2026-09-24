<x-layouts.app>
<x-slot:title>Track Your Order | Himashva</x-slot:title>

<div class="max-w-2xl mx-auto px-4 py-12">
    <h1 class="font-display text-3xl text-brand-900 mb-6 text-center">Track Your Order</h1>

    <form action="{{ route('track.submit') }}" method="POST" class="bg-white border border-brand-200 rounded-xl p-6 grid sm:grid-cols-2 gap-4 mb-8">
        @csrf
        <input name="order_number" placeholder="Order Number (e.g. HMV-20260920-0001)" required value="{{ old('order_number') }}" class="border border-brand-300 rounded px-3 py-2 text-sm sm:col-span-2">
        <input name="email" type="email" placeholder="Email used for order" required value="{{ old('email') }}" class="border border-brand-300 rounded px-3 py-2 text-sm sm:col-span-2">
        <button class="bg-brand-700 hover:bg-brand-800 text-white font-medium py-3 rounded-full sm:col-span-2">Track Order</button>
    </form>

    @isset($order)
        <div class="bg-white border border-brand-200 rounded-xl p-6">
            <h2 class="font-semibold text-brand-900 mb-1">Order {{ $order->order_number }}</h2>
            <p class="text-sm text-brand-500 mb-4">Placed on {{ $order->created_at->format('d M Y') }}</p>

            <x-order-timeline :order="$order" />

            <div class="space-y-3 mb-6">
                @foreach ($order->statusHistory as $history)
                    <div class="flex gap-3 text-sm">
                        <span class="text-brand-400 w-32 shrink-0">{{ $history->created_at->format('d M, h:i A') }}</span>
                        <span class="capitalize font-medium text-brand-800">{{ str_replace('_', ' ', $history->to_status) }}</span>
                    </div>
                @endforeach
            </div>

            @if ($order->tracking_number)
                <p class="text-sm"><strong>Tracking:</strong> {{ $order->tracking_number }} via {{ $order->shipping_partner }}</p>
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
    @endisset
</div>
</x-layouts.app>
