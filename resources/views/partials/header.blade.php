@php
    $navCategories = \App\Models\Category::active()->root()->orderBy('sort_order')->get();
    $cartCount = app(\App\Services\CartService::class)->getCount();
@endphp

@if (settings('announcement_text'))
<div class="announcement-bar">
    <span>{{ settings('announcement_text') }}</span>
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
