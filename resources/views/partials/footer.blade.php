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
                    @if (settings('social_instagram') && settings('social_instagram') !== '#')
                        <a href="{{ settings('social_instagram') }}" aria-label="Instagram" class="footer-social-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/></svg>
                        </a>
                    @endif
                    @if (settings('social_facebook') && settings('social_facebook') !== '#')
                        <a href="{{ settings('social_facebook') }}" aria-label="Facebook" class="footer-social-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </a>
                    @endif
                    @if (settings('social_youtube') && settings('social_youtube') !== '#')
                        <a href="{{ settings('social_youtube') }}" aria-label="YouTube" class="footer-social-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23 7s-.3-2-1.2-2.7c-1.1-1.2-2.4-1.2-3-1.3C16.6 3 12 3 12 3s-4.6 0-6.8.1C4.6 4 3.3 4 2.2 5.3 1.3 6 1 8 1 8S.7 10.2.7 12.4v2.1C.7 16.7 1 18.8 1 18.8s.3 2 1.2 2.7c1.1 1.2 2.6 1.1 3.3 1.2C7.6 22.9 12 23 12 23s4.6 0 6.8-.3c.6-.1 1.9-.1 3-1.3.9-.7 1.2-2.7 1.2-2.7S23 16.6 23 14.4v-2.1C23 10.2 23 7 23 7zM9.7 15.5V8.4l6.6 3.6-6.6 3.5z"/></svg>
                        </a>
                    @endif
                    @if (settings('social_twitter') && settings('social_twitter') !== '#')
                        <a href="{{ settings('social_twitter') }}" aria-label="X (Twitter)" class="footer-social-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                    @endif
                    @if (settings('social_pinterest') && settings('social_pinterest') !== '#')
                        <a href="{{ settings('social_pinterest') }}" aria-label="Pinterest" class="footer-social-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0C5.4 0 0 5.4 0 12c0 5.1 3.2 9.4 7.6 11.2-.1-.9-.2-2.4 0-3.4.2-.9 1.4-6 1.4-6s-.4-.7-.4-1.8c0-1.7 1-2.9 2.2-2.9 1 0 1.5.8 1.5 1.7 0 1-.7 2.6-1 4 .3 1.2 1.2 2.2 1.8 2.2 2.1 0 3.8-2.2 3.8-5.5 0-2.9-2.1-4.9-5-4.9-3.4 0-5.4 2.6-5.4 5.2 0 1 .4 2.1.9 2.7.1.1.1.3.1.3l-.3 1.4c-.1.2-.2.3-.4.2-1.5-.7-2.4-2.9-2.4-4.6 0-3.8 2.7-7.3 7.9-7.3 4.1 0 7.4 2.9 7.4 6.9 0 4.1-2.6 7.5-6.2 7.5-1.2 0-2.4-.6-2.8-1.4l-.7 2.8c-.3 1-.9 2.3-1.5 3.1.9.3 1.9.5 2.9.5 6.6 0 12-5.4 12-12S18.6 0 12 0z"/></svg>
                        </a>
                    @endif
                    @if (settings('social_linkedin') && settings('social_linkedin') !== '#')
                        <a href="{{ settings('social_linkedin') }}" aria-label="LinkedIn" class="footer-social-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
                        </a>
                    @endif
                    @if (settings('whatsapp_number'))
                        <a href="https://wa.me/{{ settings('whatsapp_number') }}" target="_blank" rel="noopener" aria-label="WhatsApp" class="footer-social-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                        </a>
                    @endif
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
