<x-layouts.app>
<x-slot:title>My Orders | Himashva</x-slot:title>

<div class="max-w-5xl mx-auto px-4 py-8">
    @include('account.partials.nav')

    <h1 class="font-display text-3xl text-brand-900 mb-6">My Orders</h1>

    @forelse ($orders as $order)
        <a href="{{ route('account.orders.show', $order->order_number) }}" class="flex justify-between items-center bg-white border border-brand-200 rounded-xl p-4 mb-3 hover:shadow-sm">
            <div>
                <p class="font-medium text-brand-900">{{ $order->order_number }}</p>
                <p class="text-xs text-brand-500">{{ $order->created_at->format('d M Y') }}</p>
            </div>
            <div class="text-right">
                <p class="font-medium">₹{{ number_format($order->total, 2) }}</p>
                <span class="text-xs px-2 py-1 rounded-full bg-brand-100 text-brand-700 capitalize">{{ str_replace('_', ' ', $order->order_status) }}</span>
            </div>
        </a>
    @empty
        <div class="text-center py-16">
            <p class="text-4xl mb-3">📦</p>
            <p class="text-brand-600">You haven't placed any orders yet.</p>
        </div>
    @endforelse

    {{ $orders->links() }}
</div>
</x-layouts.app>
