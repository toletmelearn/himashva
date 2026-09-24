<x-layouts.app>
<x-slot:title>My Account | Himashva</x-slot:title>

<div class="max-w-5xl mx-auto px-4 py-8">
    @include('account.partials.nav')

    <h1 class="font-display text-3xl text-brand-900 mb-6">Welcome back, {{ $user->name }}</h1>

    <div class="grid sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white border border-brand-200 rounded-xl p-5">
            <p class="text-2xl font-semibold text-brand-800">{{ $user->order_count }}</p>
            <p class="text-sm text-brand-500">Total Orders</p>
        </div>
        <div class="bg-white border border-brand-200 rounded-xl p-5">
            <p class="text-2xl font-semibold text-brand-800">₹{{ number_format($user->total_spent, 2) }}</p>
            <p class="text-sm text-brand-500">Total Spent</p>
        </div>
        <div class="bg-white border border-brand-200 rounded-xl p-5">
            <p class="text-2xl font-semibold text-brand-800">{{ $addressCount }}</p>
            <p class="text-sm text-brand-500">Saved Addresses</p>
        </div>
    </div>

    <h2 class="font-semibold text-brand-900 mb-3">Recent Orders</h2>
    @forelse ($recentOrders as $order)
        <a href="{{ route('account.orders.show', $order->order_number) }}" class="flex justify-between items-center bg-white border border-brand-200 rounded-xl p-4 mb-2 hover:shadow-sm">
            <div>
                <p class="font-medium text-brand-900">{{ $order->order_number }}</p>
                <p class="text-xs text-brand-500">{{ $order->created_at->format('d M Y') }}</p>
            </div>
            <div class="text-right">
                <p class="font-medium">₹{{ number_format($order->total, 2) }}</p>
                <p class="text-xs text-brand-500 capitalize">{{ str_replace('_', ' ', $order->order_status) }}</p>
            </div>
        </a>
    @empty
        <p class="text-sm text-brand-500">No orders yet. <a href="{{ route('shop') }}" class="underline">Start shopping</a>.</p>
    @endforelse
</div>
</x-layouts.app>
