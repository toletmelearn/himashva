<x-layouts.app>
<x-slot:title>Shopping Cart | Himashva</x-slot:title>

<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="font-display text-3xl text-brand-900 mb-6">Shopping Cart</h1>

    @if (session('auto_coupon') && ! session('applied_coupon'))
        <div x-data="{ show: true, applying: false }" x-show="show"
            class="rounded-xl px-5 py-4 mb-6 flex items-center justify-between gap-4"
            style="background: linear-gradient(135deg, #FEF3C7, #FDE68A); border: 1px solid #F59E0B;">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🎉</span>
                <div>
                    <div class="font-semibold text-sm" style="color:#92400E;">
                        Coupon <span class="bg-white px-2 py-0.5 rounded font-mono tracking-wide">{{ session('auto_coupon') }}</span> is ready!
                    </div>
                    <div class="text-xs" style="color:#A16207;">Click "Apply" to get your discount</div>
                </div>
            </div>
            <div class="flex gap-2 shrink-0">
                <button @click="
                        applying = true;
                        fetch('{{ route('cart.applyCoupon') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ code: '{{ session('auto_coupon') }}' })
                        }).then(r => r.json()).then(d => {
                            if (d.success) { window.location.reload(); }
                            else { window.dispatchEvent(new CustomEvent('toast', { detail: d.message || 'Coupon is invalid or expired' })); applying = false; }
                        }).catch(() => { applying = false; })"
                    :disabled="applying"
                    class="text-white text-xs font-semibold px-4 py-2 rounded-lg"
                    style="background:#F59E0B;">
                    <span x-text="applying ? 'Applying...' : 'Apply'"></span>
                </button>
                <button @click="show = false; fetch('{{ route('cart.dismissCoupon') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })"
                    class="text-xs px-3 py-2 rounded-lg" style="background:transparent;border:1px solid #D97706;color:#D97706;">
                    ✕
                </button>
            </div>
        </div>
    @endif

    @if ($items->isEmpty())
        <div class="text-center py-20">
            <p class="text-5xl mb-4 float">🕯️</p>
            <p class="text-brand-600 mb-6">Your cart is empty.</p>
            <a href="{{ route('shop') }}" class="bg-brand-700 hover:bg-brand-800 text-white px-6 py-3 rounded-full font-medium active:scale-95 transition-transform inline-block">Continue Shopping</a>
        </div>
    @else
        <div class="grid md:grid-cols-3 gap-8">
            <div class="md:col-span-2 space-y-4">
                @foreach ($items as $item)
                    @php
                        $price = $item->variant?->sale_price ?? $item->variant?->price ?? $item->product->sale_price ?? $item->product->price;
                    @endphp
                    <div data-aos="fade-up" data-aos-delay="{{ $loop->index * 60 }}"
                        x-data="{ removing: false }"
                        x-show="!removing"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 translate-x-0"
                        x-transition:leave-end="opacity-0 -translate-x-8"
                        class="flex gap-4 bg-white border border-brand-200 rounded-xl p-4">
                        <div class="w-20 h-20 bg-brand-100 rounded-lg flex items-center justify-center text-2xl shrink-0 overflow-hidden">
                            @if ($item->product->images->first() && $item->product->images->first()->image_path !== 'placeholder.jpg')
                                <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                            @else
                                🕯️
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-brand-900">{{ $item->product->name }}</p>
                            @if ($item->variant)<p class="text-xs text-brand-500">{{ $item->variant->name }}</p>@endif
                            <p class="text-sm text-brand-600">₹{{ number_format($price, 2) }}</p>

                            <form action="{{ route('cart.update', $item->id) }}" method="POST" class="flex items-center gap-2 mt-2" x-data="{ qty: {{ $item->quantity }} }">
                                @csrf @method('PATCH')
                                <div class="flex items-center border border-brand-300 rounded-full overflow-hidden">
                                    <button type="button" @click="qty = Math.max(1, qty - 1)"
                                        class="w-8 h-8 text-brand-700 hover:bg-brand-100 active:scale-90 transition-transform">−</button>
                                    <input type="number" name="quantity" x-model="qty" min="1" class="w-10 text-center border-0 text-sm focus:outline-none focus:ring-0">
                                    <button type="button" @click="qty++" class="w-8 h-8 text-brand-700 hover:bg-brand-100 active:scale-90 transition-transform">+</button>
                                </div>
                                <button class="text-xs text-brand-600 underline">Update</button>
                            </form>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-brand-900">₹{{ number_format($price * $item->quantity, 2) }}</p>
                            <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="mt-2"
                                @submit.prevent="removing = true; setTimeout(() => $el.submit(), 280)">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-500 hover:underline">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach

                <a href="{{ route('shop') }}" class="inline-block text-sm text-brand-700 hover:underline mt-2">← Continue Shopping</a>
            </div>

            <div class="bg-white border border-brand-200 rounded-xl p-6 h-fit">
                <h2 class="font-semibold text-brand-900 mb-4">Order Summary</h2>

                <form action="{{ route('cart.applyCoupon') }}" method="POST" class="flex gap-2 mb-4">
                    @csrf
                    <input type="text" name="code" placeholder="Coupon code" class="flex-1 border border-brand-300 rounded px-3 py-2 text-sm">
                    <button class="bg-brand-700 text-white px-4 rounded text-sm">Apply</button>
                </form>

                @if ($coupon)
                    <div class="flex justify-between items-center text-sm text-green-700 mb-2">
                        <span>Coupon: {{ $coupon->code }}</span>
                        <form action="{{ route('cart.removeCoupon') }}" method="POST">@csrf<button class="text-red-500 text-xs">Remove</button></form>
                    </div>
                @endif

                <div class="space-y-2 text-sm border-t border-brand-100 pt-4">
                    <div class="flex justify-between"><span>Subtotal</span><span>₹{{ number_format($subtotal, 2) }}</span></div>
                    @if ($discount > 0)<div class="flex justify-between text-green-600"><span>Discount</span><span>-₹{{ number_format($discount, 2) }}</span></div>@endif
                    <div class="flex justify-between"><span>Shipping</span><span>{{ $shipping > 0 ? '₹' . number_format($shipping, 2) : 'Free' }}</span></div>
                    <div class="flex justify-between font-semibold text-brand-900 text-base border-t border-brand-100 pt-2">
                        <span>Total</span>
                        <span>₹<span class="count-up" data-target="{{ (int) round($total) }}">0</span></span>
                    </div>
                </div>

                <a href="{{ route('checkout.index') }}" class="glow-hover block text-center bg-brand-700 hover:bg-brand-800 text-white font-medium py-3 rounded-full mt-6 transition active:scale-95">
                    Proceed to Checkout
                </a>
            </div>
        </div>
    @endif

    <x-recently-viewed />
</div>
</x-layouts.app>
