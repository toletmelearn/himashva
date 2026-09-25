<x-layouts.app>
<x-slot:title>Order Confirmed | Himashva</x-slot:title>

<div class="max-w-2xl mx-auto px-4 py-16 text-center">
    <p class="text-6xl mb-4">🎉</p>
    <h1 class="font-display text-3xl text-brand-900 mb-2">Thank you for your order!</h1>
    <p class="text-brand-600 mb-8">Your order <strong>{{ $order->order_number }}</strong> has been placed successfully.</p>

    @guest
        <div style="margin-bottom: 1.5rem; padding: 0.75rem 1rem; background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; font-size: 0.85rem; text-align: center;">
            Already have an account?
            <a href="{{ route('login') }}" style="color: #2563EB; font-weight: 600; text-decoration: underline;">Log in to track your order</a>
        </div>
    @endguest

    <div class="bg-white border border-brand-200 rounded-xl p-6 text-left mb-8">
        <div class="flex justify-between text-sm mb-2"><span>Order Number</span><strong>{{ $order->order_number }}</strong></div>
        <div class="flex justify-between text-sm mb-2"><span>Payment Method</span><strong>{{ strtoupper($order->payment_method) }}</strong></div>
        <div class="flex justify-between text-sm mb-2"><span>Subtotal</span><strong>₹{{ number_format($order->subtotal, 2) }}</strong></div>
        @if ($order->discount_amount > 0)
            <div class="flex justify-between text-sm mb-2"><span>Discount</span><strong>-₹{{ number_format($order->discount_amount, 2) }}</strong></div>
        @endif
        <div class="flex justify-between text-sm mb-2"><span>Shipping</span><strong>{{ $order->shipping_amount > 0 ? '₹' . number_format($order->shipping_amount, 2) : 'Free' }}</strong></div>
        <div class="flex justify-between text-sm mb-2"><span>GST</span><strong>₹{{ number_format($order->tax_amount, 2) }}</strong></div>
        <div class="flex justify-between text-sm mb-2"><span>Total</span><strong>₹{{ number_format($order->total, 2) }}</strong></div>
        @if ($order->estimated_delivery)
            <div class="flex justify-between text-sm"><span>Estimated Delivery</span><strong>{{ $order->estimated_delivery->format('d M Y') }}</strong></div>
        @endif

        @if ($order->customer_notes)
            <div class="mt-4 p-3 bg-brand-50 rounded-lg text-sm">
                <strong>Your note:</strong> {{ $order->customer_notes }}
            </div>
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

    @guest
        @if (! $order->user_id)
        <div x-data="{ creating: false, done: false, error: '' }"
             style="margin-top: 2rem; padding: 1.5rem; background: linear-gradient(135deg, #F8F3EC, #EAE2D6); border-radius: 16px; border: 1px solid #D4C4B0; text-align: left;">

            <h3 style="font-family: Georgia, serif; font-size: 1.1rem; font-weight: 600; color: #2C2018; margin-bottom: 0.5rem;">
                🎉 Save your order details for next time
            </h3>
            <p style="font-size: 0.85rem; color: #6B5D50; margin-bottom: 1.25rem;">
                Create a free account to track this order, save your address, and check out faster next time.
            </p>

            <style>
                @media (max-width: 640px) {
                    .guest-account-grid { grid-template-columns: 1fr !important; }
                }
            </style>

            <div x-show="!done">
                <form @submit.prevent="
                    creating = true; error = '';
                    fetch('{{ route('guest.create-account') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            password: $el.querySelector('[name=password]').value,
                            password_confirmation: $el.querySelector('[name=password_confirmation]').value,
                            order_number: '{{ $order->order_number }}'
                        })
                    }).then(r => r.json()).then(d => {
                        if (d.success) { done = true; }
                        else { error = d.message || 'Could not create account. Please try again.'; creating = false; }
                    }).catch(() => { error = 'Something went wrong.'; creating = false; });
                ">
                    <div class="guest-account-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #2C2018; display: block; margin-bottom: 4px;">Name</label>
                            <input type="text" value="{{ $order->name }}" readonly
                                   style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #D4C4B0; border-radius: 8px; font-size: 16px; background: #F5EFE8; color: #6B5D50; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #2C2018; display: block; margin-bottom: 4px;">Email</label>
                            <input type="email" value="{{ $order->email }}" readonly
                                   style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #D4C4B0; border-radius: 8px; font-size: 16px; background: #F5EFE8; color: #6B5D50; box-sizing: border-box;">
                        </div>
                    </div>

                    <div class="guest-account-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #2C2018; display: block; margin-bottom: 4px;">Choose Password *</label>
                            <input type="password" name="password" required minlength="8"
                                   placeholder="Min 8 characters"
                                   style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #D4C4B0; border-radius: 8px; font-size: 16px; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #2C2018; display: block; margin-bottom: 4px;">Confirm Password *</label>
                            <input type="password" name="password_confirmation" required minlength="8"
                                   placeholder="Repeat password"
                                   style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #D4C4B0; border-radius: 8px; font-size: 16px; box-sizing: border-box;">
                        </div>
                    </div>

                    <div x-show="error" x-text="error" style="color: #EF4444; font-size: 0.8rem; margin-bottom: 0.75rem;"></div>

                    <button type="submit" :disabled="creating"
                            style="background: #8B5E3C; color: white; padding: 0.7rem 2rem; border-radius: 9999px; font-weight: 600; font-size: 0.9rem; border: none; cursor: pointer; width: 100%;"
                            x-text="creating ? 'Creating account...' : 'Create My Account'">
                    </button>
                </form>
            </div>

            <div x-show="done" style="text-align: center; padding: 1rem;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">✅</div>
                <p style="font-weight: 600; color: #2C2018;">Account created! You're now logged in.</p>
                <a href="{{ route('account.orders') }}" style="color: #8B5E3C; font-size: 0.85rem; text-decoration: underline;">View your orders →</a>
            </div>
        </div>
        @endif
    @endguest

    @auth
        <div style="margin-top: 1rem; text-align: center;">
            <a href="{{ route('account.orders') }}" style="color: #8B5E3C; font-size: 0.85rem; font-weight: 600; text-decoration: underline;">
                View all your orders in My Account →
            </a>
        </div>
    @endauth

    <div class="mt-8">
        <a href="{{ route('shop') }}" class="bg-brand-700 hover:bg-brand-800 text-white px-8 py-3 rounded-full font-medium inline-block">Continue Shopping</a>
    </div>
</div>
</x-layouts.app>
