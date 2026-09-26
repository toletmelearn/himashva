<section class="newsletter-section-mock" data-aos="fade-up">
    <h2>Stay Connected</h2>
    <p>Subscribe for new launches, festive offers, and candle care tips.</p>
    <form action="{{ route('newsletter.subscribe') }}" method="POST" class="newsletter-form-mock">
        @csrf
        <input type="email" name="email" aria-label="Your email address" required placeholder="Your email address">
        <button type="submit">Subscribe</button>
    </form>
</section>

<footer class="site-footer-mock mt-0">
    <div class="container">
        <div class="footer-grid-mock stagger-in">
            <div>
                <div class="footer-brand-mock">{{ settings('site_name', 'Himashva') }}</div>
                <p class="footer-desc-mock">{{ settings('tagline', 'Handcrafted candles and home fragrances made with 100% natural soy wax. Subtle scents, clean burns, beautiful spaces.') }}</p>
                <div class="footer-social-mock">
                    @if (settings('social_instagram') && settings('social_instagram') !== '#')<a href="{{ settings('social_instagram') }}" aria-label="Instagram">📷</a>@endif
                    @if (settings('social_facebook') && settings('social_facebook') !== '#')<a href="{{ settings('social_facebook') }}" aria-label="Facebook">📘</a>@endif
                    @if (settings('social_youtube') && settings('social_youtube') !== '#')<a href="{{ settings('social_youtube') }}" aria-label="YouTube">▶️</a>@endif
                    @if (settings('social_twitter') && settings('social_twitter') !== '#')<a href="{{ settings('social_twitter') }}" aria-label="X (Twitter)">✖️</a>@endif
                    @if (settings('social_pinterest') && settings('social_pinterest') !== '#')<a href="{{ settings('social_pinterest') }}" aria-label="Pinterest">📌</a>@endif
                    @if (settings('social_linkedin') && settings('social_linkedin') !== '#')<a href="{{ settings('social_linkedin') }}" aria-label="LinkedIn">💼</a>@endif
                    @if (settings('whatsapp_number'))<a href="https://wa.me/{{ settings('whatsapp_number') }}" target="_blank" rel="noopener" aria-label="WhatsApp">💬</a>@endif
                </div>
            </div>
            <div class="footer-col-mock">
                <h4>Quick Links</h4>
                <a href="{{ route('shop') }}">Shop All</a>
                <a href="{{ route('page.show', 'about-us') }}">About Us</a>
                <a href="{{ route('contact.show') }}">Contact</a>
                <a href="{{ route('track.form') }}">Track Order</a>
            </div>
            <div class="footer-col-mock">
                <h4>Categories</h4>
                @foreach (\App\Models\Category::active()->root()->orderBy('sort_order')->limit(5)->get() as $cat)
                    <a href="{{ route('category.show', $cat->slug) }}">{{ $cat->name }}</a>
                @endforeach
            </div>
            <div class="footer-col-mock">
                <h4>Policies</h4>
                <a href="{{ route('page.show', 'privacy-policy') }}">Privacy Policy</a>
                <a href="{{ route('page.show', 'terms-conditions') }}">Terms &amp; Conditions</a>
                <a href="{{ route('page.show', 'shipping-policy') }}">Shipping Policy</a>
                <a href="{{ route('page.show', 'cancellation-refund') }}">Cancellation &amp; Refund</a>
            </div>
        </div>
        <div class="footer-bottom-mock">
            <span>&copy; {{ date('Y') }} {{ settings('site_name', 'Himashva') }}. Made with ♥ in India.</span>
            <div class="footer-payments-mock">
                <span class="payment-icon-mock">UPI</span>
                <span class="payment-icon-mock">Visa</span>
                <span class="payment-icon-mock">MC</span>
                <span class="payment-icon-mock">COD</span>
            </div>
        </div>
    </div>
</footer>
