@php
    $navCategories = \App\Models\Category::active()->root()->orderBy('sort_order')->get();
    $cartCount = app(\App\Services\CartService::class)->getCount();
@endphp

<div class="overflow-hidden py-3.5 bg-brand-800 text-brand-200">
    <div class="marquee-animate flex gap-16 whitespace-nowrap" style="width: max-content;">
        @for ($i = 0; $i < 2; $i++)
            <span class="text-xs tracking-[0.25em] uppercase">🕯️ Handcrafted With Love</span>
            <span class="text-xs tracking-[0.25em] uppercase opacity-50">✦</span>
            <span class="text-xs tracking-[0.25em] uppercase">100% Natural Soy Wax</span>
            <span class="text-xs tracking-[0.25em] uppercase opacity-50">✦</span>
            <span class="text-xs tracking-[0.25em] uppercase">🌿 Eco-Friendly Packaging</span>
            <span class="text-xs tracking-[0.25em] uppercase opacity-50">✦</span>
            <span class="text-xs tracking-[0.25em] uppercase">Free Shipping ₹{{ number_format(settings('free_shipping_threshold', 999)) }}+</span>
            <span class="text-xs tracking-[0.25em] uppercase opacity-50">✦</span>
            <span class="text-xs tracking-[0.25em] uppercase">🎁 Gift Ready Packaging</span>
            <span class="text-xs tracking-[0.25em] uppercase opacity-50">✦</span>
            <span class="text-xs tracking-[0.25em] uppercase">Pan-India Delivery</span>
            <span class="text-xs tracking-[0.25em] uppercase opacity-50">✦</span>
        @endfor
    </div>
</div>

@if (settings('announcement_text'))
<div class="announcement-bar">
    <div class="announcement-bar-social">
        @if (settings('social_instagram') && settings('social_instagram') !== '#')
            <a href="{{ settings('social_instagram') }}" aria-label="Instagram" class="footer-social-icon announcement-social-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/></svg>
            </a>
        @endif
        @if (settings('social_facebook') && settings('social_facebook') !== '#')
            <a href="{{ settings('social_facebook') }}" aria-label="Facebook" class="footer-social-icon announcement-social-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
            </a>
        @endif
        @if (settings('social_youtube') && settings('social_youtube') !== '#')
            <a href="{{ settings('social_youtube') }}" aria-label="YouTube" class="footer-social-icon announcement-social-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23 7s-.3-2-1.2-2.7c-1.1-1.2-2.4-1.2-3-1.3C16.6 3 12 3 12 3s-4.6 0-6.8.1C4.6 4 3.3 4 2.2 5.3 1.3 6 1 8 1 8S.7 10.2.7 12.4v2.1C.7 16.7 1 18.8 1 18.8s.3 2 1.2 2.7c1.1 1.2 2.6 1.1 3.3 1.2C7.6 22.9 12 23 12 23s4.6 0 6.8-.3c.6-.1 1.9-.1 3-1.3.9-.7 1.2-2.7 1.2-2.7S23 16.6 23 14.4v-2.1C23 10.2 23 7 23 7zM9.7 15.5V8.4l6.6 3.6-6.6 3.5z"/></svg>
            </a>
        @endif
        @if (settings('social_twitter') && settings('social_twitter') !== '#')
            <a href="{{ settings('social_twitter') }}" aria-label="X (Twitter)" class="footer-social-icon announcement-social-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
        @endif
        @if (settings('social_pinterest') && settings('social_pinterest') !== '#')
            <a href="{{ settings('social_pinterest') }}" aria-label="Pinterest" class="footer-social-icon announcement-social-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0C5.4 0 0 5.4 0 12c0 5.1 3.2 9.4 7.6 11.2-.1-.9-.2-2.4 0-3.4.2-.9 1.4-6 1.4-6s-.4-.7-.4-1.8c0-1.7 1-2.9 2.2-2.9 1 0 1.5.8 1.5 1.7 0 1-.7 2.6-1 4 .3 1.2 1.2 2.2 1.8 2.2 2.1 0 3.8-2.2 3.8-5.5 0-2.9-2.1-4.9-5-4.9-3.4 0-5.4 2.6-5.4 5.2 0 1 .4 2.1.9 2.7.1.1.1.3.1.3l-.3 1.4c-.1.2-.2.3-.4.2-1.5-.7-2.4-2.9-2.4-4.6 0-3.8 2.7-7.3 7.9-7.3 4.1 0 7.4 2.9 7.4 6.9 0 4.1-2.6 7.5-6.2 7.5-1.2 0-2.4-.6-2.8-1.4l-.7 2.8c-.3 1-.9 2.3-1.5 3.1.9.3 1.9.5 2.9.5 6.6 0 12-5.4 12-12S18.6 0 12 0z"/></svg>
            </a>
        @endif
        @if (settings('social_linkedin') && settings('social_linkedin') !== '#')
            <a href="{{ settings('social_linkedin') }}" aria-label="LinkedIn" class="footer-social-icon announcement-social-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
            </a>
        @endif
    </div>
    <span class="announcement-bar-text">{{ settings('announcement_text') }}</span>
</div>
@endif

<div class="top-bar">
    <div class="container">
        <div class="top-bar-links">
            <a href="{{ route('page.show', 'about-us') }}" class="hover-line">About Us</a>
            <a href="{{ route('track.form') }}" class="hover-line">Track Order</a>
            <a href="{{ route('contact.show') }}" class="hover-line">Contact</a>
        </div>
        <div class="top-bar-links top-bar-social hidden md:flex items-center gap-2">
            <span>Minimum order value ₹{{ number_format(settings('min_order_amount', 499)) }}</span>
        </div>
    </div>
</div>

<header x-data="{ scrolled: false }"
    @scroll.window="scrolled = window.scrollY > 30"
    :class="scrolled ? 'glass-effect shadow-md' : ''"
    class="site-header sticky top-0 z-50 transition-all duration-500 border-b border-brand-200">
    <div class="container">
        <div class="header-main">
            <button class="mobile-menu-btn" aria-label="Menu" x-data @click="$dispatch('toggle-mobile-menu')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
            </button>

            <a href="{{ route('home') }}" class="logo-himashva transition-transform duration-300 hover:scale-105">
                {{ settings('site_name', 'Himashva') }}
                <small>HANDCRAFTED CANDLES</small>
            </a>

            <form action="{{ route('search') }}" method="GET" class="header-search" x-data="searchAutocomplete()" @click.away="showResults = false">
                <input type="search" name="q" x-model="query" value="{{ request('q') }}"
                    @input.debounce.300ms="search()"
                    @focus="if (results.length) showResults = true"
                    @keydown.escape="showResults = false"
                    placeholder="Search for candles, fragrances, gifts...">
                <button type="submit" aria-label="Search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                </button>

                <div x-show="showResults && results.length > 0" x-cloak x-transition
                    class="absolute top-[calc(100%+8px)] left-0 right-0 bg-white rounded-xl shadow-xl border border-brand-100 overflow-hidden z-50 max-h-96 overflow-y-auto">
                    <template x-for="item in results" :key="item.slug">
                        <a :href="item.url" class="flex items-center gap-3 px-4 py-3 border-b border-brand-50 hover:bg-brand-50 transition">
                            <img :src="item.image || '{{ asset('images/no-image.png') }}'" :alt="item.name" class="w-11 h-11 rounded-lg object-cover shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-brand-900 truncate" x-text="item.name"></div>
                                <div class="text-xs text-brand-400" x-text="item.category"></div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-sm font-semibold text-brand-700" x-text="'₹' + parseFloat(item.price).toFixed(2)"></div>
                                <template x-if="item.original_price">
                                    <div class="text-xs line-through text-brand-400" x-text="'₹' + parseFloat(item.original_price).toFixed(2)"></div>
                                </template>
                            </div>
                        </a>
                    </template>
                    <a :href="'{{ route('search') }}?q=' + encodeURIComponent(query)" class="block text-center py-3 text-sm font-semibold text-brand-700 hover:bg-brand-50">
                        View all results →
                    </a>
                </div>

                <div x-show="showResults && results.length === 0 && query.length >= 2 && !searching" x-cloak x-transition
                    class="absolute top-[calc(100%+8px)] left-0 right-0 bg-white rounded-xl shadow-xl p-6 text-center z-50">
                    <p class="text-sm text-brand-400">No products found for "<span x-text="query" class="text-brand-900"></span>"</p>
                </div>
            </form>

            <div class="header-icons">
                <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" class="icon-btn" aria-label="Account">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </a>
                @auth
                    <a href="{{ route('account.wishlist') }}" class="icon-btn" aria-label="Wishlist">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </a>
                @endauth
                <a href="{{ route('cart.index') }}" class="icon-btn" aria-label="Cart">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    <span id="cart-count-badge" class="cart-badge-dot {{ $cartCount > 0 ? 'pulse-subtle' : '' }}">{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </div>

    <nav class="category-nav">
        <div class="container">
            <div class="category-nav-inner">
                <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') ? 'active' : '' }}">All Candles</a>
                @foreach ($navCategories as $cat)
                    <a href="{{ route('category.show', $cat->slug) }}" class="{{ request()->routeIs('category.show') && request()->route('slug') === $cat->slug ? 'active' : '' }}">{{ $cat->name }}</a>
                @endforeach
            </div>
        </div>
    </nav>
</header>

<div x-data="{ open: false }" @toggle-mobile-menu.window="open = !open" x-show="open" x-cloak
    class="md:hidden fixed inset-0 z-50 bg-black/40" @click.self="open = false">
    <div class="bg-white w-72 h-full p-6 overflow-y-auto" @click.stop>
        <button @click="open = false" class="mb-4 text-brand-700">✕ Close</button>
        <form action="{{ route('search') }}" method="GET" class="mb-4 flex">
            <input type="text" name="q" placeholder="Search..." class="w-full border border-brand-300 rounded-l px-3 py-2 text-sm">
            <button class="bg-brand-700 text-white px-3 rounded-r text-sm">Go</button>
        </form>
        <div class="flex flex-col gap-3 text-sm">
            <a href="{{ route('shop') }}" class="text-brand-800 font-medium">All Candles</a>
            @foreach ($navCategories as $cat)
                <a href="{{ route('category.show', $cat->slug) }}" class="text-brand-800">{{ $cat->name }}</a>
            @endforeach
        </div>
    </div>
</div>

<script>
function searchAutocomplete() {
    return {
        query: '{{ request('q') }}',
        results: [],
        showResults: false,
        searching: false,
        async search() {
            if (this.query.length < 2) {
                this.results = [];
                this.showResults = false;
                return;
            }
            this.searching = true;
            try {
                const res = await fetch('{{ route('api.search') }}?q=' + encodeURIComponent(this.query));
                this.results = await res.json();
                this.showResults = true;
            } catch (e) {
                console.error(e);
            }
            this.searching = false;
        },
    };
}
</script>
