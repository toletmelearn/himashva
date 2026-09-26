<x-layouts.app>
<x-slot:title>{{ settings('site_name', 'Himashva') }} — {{ settings('tagline') }}</x-slot:title>

<x-json-ld type="organization" />

@php
    $heroSlideCount = $heroBanners->count() ?: 1;
@endphp
<section x-data="{ i: 0, count: {{ $heroSlideCount }} }" x-init="count > 1 && setInterval(() => i = (i + 1) % count, 5000)" class="hero-mock">
    <div class="hero-bg-pattern"></div>
    @forelse ($heroBanners as $index => $banner)
        <div x-show="i === {{ $index }}" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            class="container">
            <a href="{{ $banner->link ?: route('shop') }}" class="hero-slide-link" aria-hidden="true" tabindex="-1"></a>
            <div class="hero-content">
                <h1 data-aos="fade-up">{{ $banner->title }}</h1>
                @if ($banner->subtitle)
                    <p data-aos="fade-up" data-aos-delay="120">{{ $banner->subtitle }}</p>
                @endif
                <div data-aos="fade-up" data-aos-delay="240" style="display:flex; gap:12px; flex-wrap:wrap;">
                    <a href="{{ $banner->link ?: route('shop') }}" class="btn-primary">{{ $banner->button_text ?: 'Shop Collection' }}</a>
                    <a href="{{ route('page.show', 'about-us') }}" class="btn-outline">Our Story</a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-glow" aria-hidden="true"></div>
                @if ($heroProducts->count())
                    <div class="hero-candle-grid">
                        @foreach ($heroProducts as $hpIndex => $hp)
                            <a href="{{ route('product.show', $hp->slug) }}" class="candle-placeholder hero-tile float" style="animation-delay: {{ $hpIndex * 0.4 }}s">
                                <img src="{{ asset('storage/' . $hp->images->first()->image_path) }}" alt="{{ $hp->name }}" loading="lazy">
                                <span class="hero-tile-caption">{{ Str::limit($hp->name, 28) }}</span>
                            </a>
                        @endforeach
                    </div>
                    <div class="hero-trust-badge float" style="animation-delay: 1.2s">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2l2.9 6.26L21.5 9l-5 4.87L17.8 21 12 17.77 6.2 21l1.3-7.13L2.5 9l6.6-.74L12 2z" fill="#A16207"/></svg>
                        <div>
                            <strong>4.8/5</strong>
                            <span>from 5,000+ happy customers</span>
                        </div>
                    </div>
                @else
                    <div class="hero-candle-grid float">
                        <div class="candle-placeholder">🕯️<br><small>Handcrafted</small></div>
                        <div class="candle-placeholder">🕯️<br><small>Natural Soy Wax</small></div>
                        <div class="candle-placeholder">🕯️<br><small>Gift Ready</small></div>
                        <div class="candle-placeholder">🕯️<br><small>{{ settings('site_name', 'Himashva') }}</small></div>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div x-show="i === 0" class="container">
            <a href="{{ route('shop') }}" class="hero-slide-link" aria-hidden="true" tabindex="-1"></a>
            <div class="hero-content">
                <h1 data-aos="fade-up">Handcrafted Candles, Made With Love</h1>
                <p data-aos="fade-up" data-aos-delay="120">100% natural soy wax, subtle scents, and clean burns for beautiful spaces.</p>
                <div data-aos="fade-up" data-aos-delay="240" style="display:flex; gap:12px; flex-wrap:wrap;">
                    <a href="{{ route('shop') }}" class="btn-primary">Shop Collection</a>
                    <a href="{{ route('page.show', 'about-us') }}" class="btn-outline">Our Story</a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-glow" aria-hidden="true"></div>
                <div class="hero-candle-grid float">
                    <div class="candle-placeholder">🕯️<br><small>Handcrafted</small></div>
                    <div class="candle-placeholder">🕯️<br><small>Natural Soy Wax</small></div>
                    <div class="candle-placeholder">🕯️<br><small>Gift Ready</small></div>
                    <div class="candle-placeholder">🕯️<br><small>{{ settings('site_name', 'Himashva') }}</small></div>
                </div>
            </div>
        </div>
    @endforelse

    @if ($heroBanners->count() > 1)
        <button type="button" class="hero-arrow hero-arrow-prev" aria-label="Previous slide" @click="i = (i - 1 + count) % count">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <button type="button" class="hero-arrow hero-arrow-next" aria-label="Next slide" @click="i = (i + 1) % count">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <div class="hero-dots" role="tablist" aria-label="Hero slides">
            @foreach ($heroBanners as $index => $banner)
                <button type="button" class="hero-dot" :class="{ 'active': i === {{ $index }} }" @click="i = {{ $index }}" aria-label="Go to slide {{ $index + 1 }}"></button>
            @endforeach
        </div>
    @endif
</section>

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

<div class="usp-bar-mock">
    <div class="container">
        <div class="usp-grid-mock">
            @foreach ([['🌿','100% Natural Soy Wax','Eco-friendly and sustainable materials'],['✋','Handcrafted','Each candle poured in small batches'],['🚚','Free Shipping ₹'.number_format(settings('free_shipping_threshold', 999)).'+','Pan-India delivery on qualifying orders'],['🎁','Gift Ready','Beautiful packaging for every occasion']] as $i => [$icon, $title, $desc])
                <div class="usp-item-mock" data-aos="fade-up" data-aos-delay="{{ $i * 100 }}">
                    <div class="usp-icon-mock float">{{ $icon }}</div>
                    <h3>{{ $title }}</h3>
                    <p>{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>

@if ($categories->count())
<section class="section-mock">
    <div class="container">
        <div class="section-header-mock">
            <h2 class="section-title-mock">Shop by Category</h2>
            <a href="{{ route('shop') }}" class="section-link">View All Categories</a>
        </div>
        <div class="category-grid-mock stagger-in">
            @foreach ($categories as $i => $cat)
                <a href="{{ route('category.show', $cat->slug) }}" class="category-card-mock">
                    <div class="category-thumb img-zoom">
                        @if ($cat->image)
                            <img src="{{ asset('storage/' . $cat->image) }}" alt="{{ $cat->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="category-thumb-inner cat-{{ ($i % 6) + 1 }}">🕯️</div>
                        @endif
                    </div>
                    <h3>{{ $cat->name }}</h3>
                    <span>{{ $cat->products_count }} Products</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($featured->count())
<section class="section-mock" style="background: var(--bg-warm);">
    <div class="container">
        <div class="section-header-mock">
            <h2 class="section-title-mock">Featured Products</h2>
            <a href="{{ route('shop') }}" class="section-link">View All Products</a>
        </div>
        <div class="product-grid-mock stagger-in">
            @foreach ($featured as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($promoBanner)
<section class="section-mock">
    <div class="container">
        <div data-aos="zoom-in" data-aos-duration="800" class="promo-banner-mock">
            <h2>{{ $promoBanner->title }}</h2>
            <p>{{ $promoBanner->subtitle }}</p>
            <a href="{{ $promoBanner->link ?: route('shop') }}" class="btn-promo active:scale-95">{{ $promoBanner->button_text ?: 'Shop Now' }}</a>
        </div>
    </div>
</section>
@endif

@if ($bestsellers->count())
<section class="section-mock">
    <div class="container">
        <div class="section-header-mock">
            <h2 class="section-title-mock">Best Sellers</h2>
            <a href="{{ route('shop') }}" class="section-link">View All</a>
        </div>
        <div class="product-grid-mock stagger-in">
            @foreach ($bestsellers as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="py-16 bg-brand-800 text-white overflow-hidden">
    <div class="container">
        <h2 class="font-display text-3xl text-center mb-10 text-brand-200" data-aos="fade-up">Why Thousands Trust {{ settings('site_name', 'Himashva') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div data-aos="fade-up">
                <div class="count-up text-4xl md:text-5xl font-display font-bold" data-target="5000" data-suffix="+">0</div>
                <p class="text-brand-300 text-sm mt-2">Happy Customers</p>
            </div>
            <div data-aos="fade-up" data-aos-delay="100">
                <div class="count-up text-4xl md:text-5xl font-display font-bold" data-target="200" data-suffix="+">0</div>
                <p class="text-brand-300 text-sm mt-2">Products Crafted</p>
            </div>
            <div data-aos="fade-up" data-aos-delay="200">
                <div class="count-up text-4xl md:text-5xl font-display font-bold" data-target="50" data-suffix="+">0</div>
                <p class="text-brand-300 text-sm mt-2">Unique Fragrances</p>
            </div>
            <div data-aos="fade-up" data-aos-delay="300">
                <div class="count-up text-4xl md:text-5xl font-display font-bold" data-target="4" data-suffix=".8★">0</div>
                <p class="text-brand-300 text-sm mt-2">Average Rating</p>
            </div>
        </div>
    </div>
</section>

@if ($testimonials->count())
<section class="section-mock" style="background: var(--bg-warm);">
    <div class="container">
        <div class="section-header-mock">
            <h2 class="section-title-mock">What Our Customers Say</h2>
        </div>
        <div class="testimonial-grid-mock">
            @foreach ($testimonials as $index => $review)
                <div data-aos="fade-up" data-aos-delay="{{ $index * 100 }}" class="testimonial-card-mock glow-hover">
                    <div class="testimonial-stars">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
                    <p class="testimonial-text">"{{ $review->comment }}"</p>
                    <div class="testimonial-author">{{ $review->user->name }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<div class="container">
    <x-recently-viewed />
</div>

</x-layouts.app>
