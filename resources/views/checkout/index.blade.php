<x-layouts.app>
<x-slot:title>Checkout | Himashva</x-slot:title>

<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="font-display text-3xl text-brand-900 mb-2">Checkout</h1>
    <div class="flex items-center gap-2 text-xs text-brand-500 mb-6">
        <span class="text-brand-700 font-semibold">Address</span>
        <span>→</span>
        <span class="text-brand-700 font-semibold">Payment</span>
        <span>→</span>
        <span>Review</span>
    </div>

    @guest
        <div class="bg-brand-100 border border-brand-200 rounded-xl p-4 mb-6 text-sm text-brand-700">
            Have an account? <a href="{{ route('login') }}" class="underline font-medium">Log in</a> for faster checkout, or continue as guest below.
        </div>
    @endguest

    <div class="grid md:grid-cols-3 gap-8" x-data="{
        paymentMethod: '{{ old('payment_method', optional($activeGateways->first())->name ?? 'cod') }}',
        pendingOrderId: {{ session('pending_order', 'null') }},
    }">
        <form action="{{ route('checkout.placeOrder') }}" method="POST" id="checkout-form" class="md:col-span-2 space-y-6"
            x-data="{ loading: false }" @submit="loading = true">
            @csrf

            @if ($addresses->count())
                <div data-aos="fade-up" class="bg-white border border-brand-200 rounded-xl p-6">
                    <h2 class="font-semibold text-brand-900 mb-3">Saved Addresses</h2>
                    <div class="space-y-2">
                        @foreach ($addresses as $addr)
                            <label class="flex items-start gap-2 text-sm border border-brand-200 rounded-lg p-3 cursor-pointer">
                                <input type="radio" name="saved_address" value="{{ $addr->id }}"
                                    onchange="document.getElementById('name').value='{{ $addr->name }}';document.getElementById('phone').value='{{ $addr->phone }}';document.getElementById('address_line_1').value='{{ $addr->address_line_1 }}';document.getElementById('address_line_2').value='{{ $addr->address_line_2 }}';document.getElementById('city').value='{{ $addr->city }}';document.getElementById('state').value='{{ $addr->state }}';document.getElementById('postal_code').value='{{ $addr->postal_code }}';">
                                <span>{{ $addr->label }} — {{ $addr->address_line_1 }}, {{ $addr->city }}, {{ $addr->state }} {{ $addr->postal_code }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div data-aos="fade-up" data-aos-delay="100" class="bg-white border border-brand-200 rounded-xl p-6">
                <h2 class="font-semibold text-brand-900 mb-4">Shipping Details</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <input id="name" name="name" placeholder="Full Name" required value="{{ old('name', auth()->user()->name ?? '') }}" class="border border-brand-300 rounded px-3 py-2 text-sm sm:col-span-2">
                    <input name="email" type="email" placeholder="Email" required value="{{ old('email', auth()->user()->email ?? '') }}" class="border border-brand-300 rounded px-3 py-2 text-sm"
                        @blur="fetch('{{ route('checkout.saveEmail') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({ email: $event.target.value }),
                        })">
                    <input id="phone" name="phone" placeholder="Phone" required value="{{ old('phone', auth()->user()->phone ?? '') }}" class="border border-brand-300 rounded px-3 py-2 text-sm">
                    <input id="address_line_1" name="address_line_1" placeholder="Address Line 1" required value="{{ old('address_line_1') }}" class="border border-brand-300 rounded px-3 py-2 text-sm sm:col-span-2">
                    <input id="address_line_2" name="address_line_2" placeholder="Address Line 2 (optional)" value="{{ old('address_line_2') }}" class="border border-brand-300 rounded px-3 py-2 text-sm sm:col-span-2">
                    <input id="city" name="city" placeholder="City" required value="{{ old('city') }}" class="border border-brand-300 rounded px-3 py-2 text-sm">
                    <input id="state" name="state" placeholder="State" required value="{{ old('state') }}" class="border border-brand-300 rounded px-3 py-2 text-sm">
                    <input id="postal_code" name="postal_code" placeholder="Postal Code" required value="{{ old('postal_code') }}" class="border border-brand-300 rounded px-3 py-2 text-sm">
                    <input name="country" placeholder="Country" value="India" class="border border-brand-300 rounded px-3 py-2 text-sm">
                </div>
            </div>

            <div data-aos="fade-up" data-aos-delay="200" class="bg-white border border-brand-200 rounded-xl p-6">
                <h2 class="font-semibold text-brand-900 mb-4">Payment Method</h2>
                <div class="space-y-2 text-sm">
                    @forelse ($activeGateways as $gateway)
                        <label class="flex items-center gap-2 transition-transform duration-200 has-[:checked]:scale-[1.02]">
                            <input type="radio" name="payment_method" value="{{ $gateway->name }}" x-model="paymentMethod">
                            <span>
                                {{ $gateway->display_name }}
                                @if ($gateway->description)
                                    <span class="text-brand-400 text-xs">— {{ $gateway->description }}</span>
                                @endif
                            </span>
                        </label>
                    @empty
                        <p class="text-red-600">No payment methods are currently available. Please contact support.</p>
                    @endforelse
                </div>
                @error('payment_method')
                    <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" :disabled="loading" @if ($activeGateways->isEmpty()) disabled @endif
                class="w-full bg-brand-700 hover:bg-brand-800 text-white font-medium py-3 rounded-full transition disabled:opacity-70 flex items-center justify-center gap-2 active:scale-95">
                <svg x-show="loading" x-cloak class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span x-text="loading ? 'Placing Order...' : 'Place Order'"></span>
            </button>
        </form>

        <div data-aos="fade-left" class="glass-effect border border-brand-200 rounded-xl p-6 h-fit">
            <h2 class="font-semibold text-brand-900 mb-4">Order Summary</h2>
            @foreach ($items as $item)
                <div class="flex justify-between text-sm py-1">
                    <span class="text-brand-600">{{ $item->product->name }} × {{ $item->quantity }}</span>
                </div>
            @endforeach
            <div class="space-y-2 text-sm border-t border-brand-100 pt-4 mt-2">
                <div class="flex justify-between"><span>Subtotal</span><span>₹{{ number_format($subtotal, 2) }}</span></div>
                @if ($discount > 0)<div class="flex justify-between text-green-600"><span>Discount</span><span>-₹{{ number_format($discount, 2) }}</span></div>@endif
                <div class="flex justify-between"><span>Shipping</span><span>{{ $shipping > 0 ? '₹' . number_format($shipping, 2) : 'Free' }}</span></div>
                <div class="flex justify-between"><span>GST</span><span>₹{{ number_format($tax, 2) }}</span></div>
                <div class="flex justify-between font-semibold text-brand-900 text-base border-t border-brand-100 pt-2"><span>Total</span><span>₹{{ number_format($total, 2) }}</span></div>
            </div>
        </div>
    </div>
</div>

@if (session('pending_order') && $razorpayEnabled && $pendingPaymentMethod === 'razorpay')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        (async function () {
            const res = await fetch('{{ route('payment.razorpay.create') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ order_id: {{ session('pending_order') }} }),
            });
            const data = await res.json();
            if (data.error) return;

            const rzp = new Razorpay({
                key: data.key,
                amount: data.amount,
                currency: data.currency,
                name: data.name,
                order_id: data.razorpay_order_id,
                prefill: data.prefill,
                handler: async function (response) {
                    const verify = await fetch('{{ route('payment.razorpay.verify') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({
                            order_id: {{ session('pending_order') }},
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_signature: response.razorpay_signature,
                        }),
                    });
                    const result = await verify.json();
                    if (result.success) window.location.href = result.redirect;
                },
            });
            rzp.open();
        })();
    </script>
@endif
</x-layouts.app>
