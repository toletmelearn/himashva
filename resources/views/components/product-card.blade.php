@props(['product'])

@php
    $piClass = 'pi-' . (($product->id % 8) + 1);
@endphp

<div class="product-card-mock glow-hover tilt-hover" x-data="{}">
    <div class="product-image-wrap">
        <div style="position: absolute; top: 8px; left: 8px; display: flex; flex-direction: column; gap: 4px; z-index: 2;">
            @if ($product->sale_price && $product->sale_price < $product->price)
                @php $discount = round((($product->price - $product->sale_price) / $product->price) * 100); @endphp
                <span style="background: #EF4444; color: white; font-size: 0.6rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">-{{ $discount }}% OFF</span>
            @endif
            @if ($product->is_new || $product->created_at->gt(now()->subDays(14)))
                <span style="background: #10B981; color: white; font-size: 0.6rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">NEW</span>
            @endif
            @if ($product->is_bestseller)
                <span style="background: #F59E0B; color: white; font-size: 0.6rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">BESTSELLER</span>
            @endif
            @if ($product->stock > 0 && $product->stock <= ($product->low_stock_threshold ?? 5))
                <span style="background: #8B5CF6; color: white; font-size: 0.6rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">FEW LEFT</span>
            @endif
        </div>
        @if ($product->discount_percentage > 0)
            <span class="discount-badge-mock pulse-subtle">-{{ $product->discount_percentage }}%</span>
        @endif

        <button type="button"
            onclick="fetch('{{ route('compare.add', $product->id) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
            }).then(r => r.json()).then(d => {
                if (d.success) { window.dispatchEvent(new CustomEvent('compare-updated', { detail: d.count })); }
                window.dispatchEvent(new CustomEvent('toast', { detail: d.message || 'Added to compare' }));
            })"
            class="compare-btn-mock text-xs text-brand-600 underline"
            aria-label="Add to compare">
            Compare
        </button>

        <button x-data="{ liked: {{ ($inWishlist ?? false) ? 'true' : 'false' }} }"
            @click.prevent="fetch('{{ route('wishlist.toggle') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ product_id: {{ $product->id }} })
            }).then(r => {
                if (r.status === 401) { window.location.href = '{{ route('login') }}'; return null; }
                return r.json();
            }).then(d => { if (d) { liked = d.in_wishlist } })"
            :class="liked ? 'is-active' : ''"
            class="wishlist-btn-mock transition-all duration-300 hover:scale-110"
            aria-label="Add to wishlist">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>

        <a href="{{ route('product.show', $product->slug) }}" class="block">
            <div class="img-zoom">
                @if ($product->images->first() && $product->images->first()->image_path !== 'placeholder.jpg')
                    <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" alt="{{ $product->name }}" class="product-image" loading="lazy">
                @else
                    <div class="product-img-placeholder {{ $piClass }}">🕯️</div>
                @endif
            </div>
        </a>

        <button type="button"
            @click.prevent="$dispatch('quick-view', { slug: '{{ $product->slug }}' })"
            class="quick-view-btn-mock"
            aria-label="Quick view {{ $product->name }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
        </button>
    </div>

    <div class="product-info-mock">
        <a href="{{ route('product.show', $product->slug) }}" class="block">
            <h3 class="product-name-mock">{{ $product->name }}</h3>
        </a>
        <div class="product-price-mock">
            @if ($product->sale_price)
                <span class="price-current">₹{{ number_format($product->sale_price, 2) }}</span>
                <span class="price-original">₹{{ number_format($product->price, 2) }}</span>
            @else
                <span class="price-current">₹{{ number_format($product->price, 2) }}</span>
            @endif
        </div>

        <div class="product-actions-mock">
            @if ($product->variants->count() > 0)
                <a href="{{ route('product.show', $product->slug) }}" class="btn-select">Select Options</a>
            @else
                <button
                    @click="fetch('{{ route('cart.add') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({ product_id: {{ $product->id }}, quantity: 1 })
                    }).then(r => r.json()).then(d => {
                        document.getElementById('cart-count-badge').textContent = d.count;
                        window.dispatchEvent(new CustomEvent('toast', { detail: 'Added to cart!' }));
                    }).catch(err => console.error('Cart error:', err))"
                    class="btn-add-cart active:scale-95 transition-transform">
                    Add to Cart
                </button>
            @endif
        </div>
    </div>
</div>
