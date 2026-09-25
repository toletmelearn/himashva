<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $ogTitle = $title ?? settings('meta_title', 'Himashva — Handcrafted Candles & Home Fragrances');
        $ogDescription = $description ?? settings('meta_description', '');
        $ogImage = isset($image) ? $image : settings('og_image');
    @endphp
    <title>{{ $ogTitle }}</title>
    <meta name="description" content="{{ $ogDescription }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
        <meta name="twitter:card" content="summary_large_image">
    @else
        <meta name="twitter:card" content="summary">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🕯️</text></svg>">

    {{-- Tailwind CSS via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#FDFBF7', 100: '#F8F3EC', 200: '#EAE2D6', 300: '#D4BFA6',
                            400: '#C9A77D', 500: '#8B5E3C', 600: '#6D4829', 700: '#5C3D26',
                            800: '#3D2B1F', 900: '#2C2018',
                        },
                    },
                    fontFamily: {
                        display: ['"Cormorant Garamond"', 'Georgia', 'serif'],
                        body: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- AOS - Animate On Scroll --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">

    <style>
        /* ===== HIMASHVA DESIGN SYSTEM ===== */
        :root {
            --bg: #FDFBF7;
            --bg-warm: #F8F3EC;
            --bg-card: #FFFFFF;
            --text-primary: #2C2018;
            --text-secondary: #6B5D50;
            --text-muted: #9C8E80;
            --accent: #8B5E3C;
            --accent-hover: #6D4829;
            --accent-light: #C9A77D;
            --border: #EAE2D6;
            --border-light: #F0EAE0;
            --red-badge: #C0392B;
            --green: #27AE60;
            --overlay: rgba(44,32,24,0.5);
            --shadow-sm: 0 1px 3px rgba(44,32,24,0.06);
            --shadow-md: 0 4px 12px rgba(44,32,24,0.08);
            --shadow-lg: 0 8px 30px rgba(44,32,24,0.12);
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --font-display: 'Cormorant Garamond', Georgia, serif;
            --font-body: 'Inter', system-ui, -apple-system, sans-serif;
            --max-w: 1280px;
        }
        body { font-family: var(--font-body); line-height: 1.6; -webkit-font-smoothing: antialiased; }
        .font-display { font-family: 'Cormorant Garamond', Georgia, serif; }
        [x-cloak] { display: none !important; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

        /* Announcement + top bar */
        .announcement-bar { background: var(--accent); color: #fff; text-align: center; padding: 8px 16px; font-size: 12px; font-weight: 500; letter-spacing: 0.5px; }
        .announcement-bar span { opacity: 0.85; }
        .announcement-bar strong { font-weight: 600; }
        .top-bar { background: var(--bg); border-bottom: 1px solid var(--border-light); padding: 6px 0; font-size: 12px; color: var(--text-muted); }
        .top-bar .container { display: flex; justify-content: space-between; align-items: center; }
        .top-bar-links { display: flex; gap: 16px; }
        .top-bar-links a:hover { color: var(--accent); }

        /* Header */
        .site-header { background: var(--bg); }
        .header-main { display: flex; align-items: center; justify-content: space-between; padding: 16px 0; gap: 24px; }
        .logo-himashva { font-family: var(--font-display); font-size: 28px; font-weight: 600; letter-spacing: 1px; color: var(--text-primary); line-height: 1; flex-shrink: 0; }
        .logo-himashva small { display: block; font-family: var(--font-body); font-size: 9px; font-weight: 500; letter-spacing: 3px; color: var(--text-muted); margin-top: 2px; }
        .header-search { flex: 1; max-width: 420px; position: relative; }
        .header-search input { width: 100%; padding: 10px 44px 10px 16px; border: 1px solid var(--border); border-radius: 40px; background: var(--bg-warm); font-size: 16px; color: var(--text-primary); outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
        @media (min-width: 769px) { .header-search input { font-size: 13px; } }
        .header-search input::placeholder { color: var(--text-muted); }
        .header-search input:focus { border-color: var(--accent-light); box-shadow: 0 0 0 3px rgba(139,94,60,0.1); }
        .header-search button { position: absolute; right: 4px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; display: grid; place-items: center; color: var(--text-secondary); }
        .header-icons { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
        .icon-btn { width: 40px; height: 40px; display: grid; place-items: center; border-radius: 50%; color: var(--text-primary); position: relative; transition: background 0.2s; }
        .icon-btn:hover { background: var(--bg-warm); }
        .icon-btn svg { width: 20px; height: 20px; }
        .cart-badge-dot { position: absolute; top: 2px; right: 2px; background: var(--red-badge); color: #fff; font-size: 10px; font-weight: 700; width: 16px; height: 16px; border-radius: 50%; display: grid; place-items: center; line-height: 1; }

        /* Category nav */
        .category-nav { border-bottom: 1px solid var(--border-light); background: var(--bg); }
        .category-nav-inner { display: flex; gap: 0; overflow-x: auto; scrollbar-width: none; -ms-overflow-style: none; }
        .category-nav-inner::-webkit-scrollbar { display: none; }
        .category-nav-inner a { padding: 12px 18px; font-size: 12.5px; font-weight: 500; white-space: nowrap; color: var(--text-secondary); border-bottom: 2px solid transparent; transition: color 0.2s, border-color 0.2s; text-transform: uppercase; letter-spacing: 0.3px; }
        .category-nav-inner a:hover, .category-nav-inner a.active { color: var(--accent); border-bottom-color: var(--accent); }

        .container { width: min(var(--max-w), 100% - 32px); margin-inline: auto; }

        /* Hero */
        .hero-mock { position: relative; background: linear-gradient(135deg, #F5EDE2 0%, #E8D9C5 50%, #D4BFA6 100%); overflow: hidden; min-height: 480px; display: flex; align-items: center; }
        .hero-bg-pattern { position: absolute; inset: 0; opacity: 0.06; background-image: radial-gradient(circle at 20% 50%, #8B5E3C 1px, transparent 1px), radial-gradient(circle at 80% 20%, #8B5E3C 1px, transparent 1px); background-size: 60px 60px; }
        .hero-mock .container { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; padding-block: 60px; position: relative; z-index: 1; }
        .hero-content h1 { font-family: var(--font-display); font-size: clamp(36px, 5vw, 52px); font-weight: 500; line-height: 1.15; color: var(--text-primary); margin-bottom: 16px; }
        .hero-content p { font-size: 15px; color: var(--text-secondary); max-width: 420px; margin-bottom: 28px; line-height: 1.7; }
        .btn-primary { display: inline-flex; align-items: center; gap: 8px; padding: 14px 32px; background: var(--accent); color: #fff; font-size: 13px; font-weight: 600; letter-spacing: 0.5px; border-radius: var(--radius-sm); transition: background 0.2s, transform 0.1s; }
        .btn-primary:hover { background: var(--accent-hover); }
        .btn-primary:active { transform: scale(0.98); }
        .btn-outline { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border: 1.5px solid var(--accent); color: var(--accent); font-size: 13px; font-weight: 600; letter-spacing: 0.5px; border-radius: var(--radius-sm); transition: all 0.2s; }
        .btn-outline:hover { background: var(--accent); color: #fff; }
        .hero-visual { display: flex; justify-content: center; align-items: center; position: relative; }
        .hero-glow { position: absolute; width: 320px; height: 320px; border-radius: 50%; background: radial-gradient(circle, rgba(139,94,60,0.35), transparent 70%); filter: blur(40px); top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 0; }
        .hero-candle-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; max-width: 380px; position: relative; z-index: 1; }
        .candle-placeholder { aspect-ratio: 3/4; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); display: grid; place-items: center; font-family: var(--font-display); font-size: 14px; color: #fff; text-align: center; padding: 20px; overflow: hidden; }
        .candle-placeholder img { width: 100%; height: 100%; object-fit: cover; }
        .candle-placeholder:nth-child(1) { background: linear-gradient(160deg, #D4A574, #A67852); }
        .candle-placeholder:nth-child(2) { background: linear-gradient(160deg, #C9B8A0, #8B7D6B); margin-top: 30px; }
        .candle-placeholder:nth-child(3) { background: linear-gradient(160deg, #E8C5A0, #C4956A); margin-top: -30px; }
        .candle-placeholder:nth-child(4) { background: linear-gradient(160deg, #B8957A, #7D5F45); }
        .hero-tile { padding: 0; position: relative; display: block; transition: transform 0.35s ease, box-shadow 0.35s ease; }
        .hero-tile:hover { transform: translateY(-6px) scale(1.02); box-shadow: 0 14px 40px rgba(44,32,24,0.2); }
        .hero-tile-caption { position: absolute; inset-inline: 0; bottom: 0; padding: 10px 12px 8px; font-family: var(--font-body); font-size: 11px; font-weight: 600; letter-spacing: 0.2px; color: #fff; text-align: left; background: linear-gradient(to top, rgba(28,20,14,0.85), rgba(28,20,14,0)); }
        .hero-trust-badge { position: absolute; left: -16px; bottom: -14px; display: flex; align-items: center; gap: 10px; background: #fff; border-radius: 999px; padding: 10px 18px 10px 14px; box-shadow: var(--shadow-lg); z-index: 2; }
        .hero-trust-badge strong { display: block; font-family: var(--font-display); font-size: 15px; color: var(--text-primary); line-height: 1.1; }
        .hero-trust-badge span { display: block; font-size: 10px; color: var(--text-secondary); white-space: nowrap; }

        /* Section */
        .section-mock { padding: 60px 0; }
        .section-header-mock { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; gap: 20px; }
        .section-title-mock { font-family: var(--font-display); font-size: clamp(24px, 3vw, 32px); font-weight: 500; line-height: 1.2; }
        .section-link { font-size: 13px; font-weight: 600; color: var(--accent); white-space: nowrap; transition: opacity 0.2s; }
        .section-link:hover { opacity: 0.7; }

        /* Category grid */
        .category-grid-mock { display: grid; grid-template-columns: repeat(6, 1fr); gap: 20px; }
        .category-card-mock { text-align: center; transition: transform 0.2s; }
        .category-card-mock:hover { transform: translateY(-4px); }
        .category-thumb { aspect-ratio: 1; border-radius: 50%; overflow: hidden; margin-bottom: 12px; border: 3px solid var(--border-light); transition: border-color 0.2s; }
        .category-card-mock:hover .category-thumb { border-color: var(--accent-light); }
        .category-thumb-inner { width: 100%; height: 100%; display: grid; place-items: center; font-size: 28px; }
        .cat-1 { background: linear-gradient(135deg, #F5E6D3, #E8D0B5); }
        .cat-2 { background: linear-gradient(135deg, #E8E0D0, #D4C8B5); }
        .cat-3 { background: linear-gradient(135deg, #F0E2D0, #DBC8AD); }
        .cat-4 { background: linear-gradient(135deg, #E5DDD0, #CFC2AE); }
        .cat-5 { background: linear-gradient(135deg, #F2EADB, #E0D2BE); }
        .cat-6 { background: linear-gradient(135deg, #EAE0D2, #D6C9B5); }
        .category-card-mock h3 { font-size: 13px; font-weight: 600; margin-bottom: 2px; }
        .category-card-mock span { font-size: 12px; color: var(--text-muted); }

        /* Product grid / cards */
        .product-grid-mock { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .product-card-mock { background: var(--bg-card); border: 1px solid var(--border-light); border-radius: var(--radius-md); overflow: hidden; transition: box-shadow 0.25s, transform 0.25s; position: relative; }
        .product-card-mock:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
        .product-image-wrap { position: relative; overflow: hidden; background: var(--bg-warm); }
        .product-image { aspect-ratio: 1; width: 100%; object-fit: cover; transition: transform 0.4s ease; }
        .product-card-mock:hover .product-image { transform: scale(1.05); }
        .product-img-placeholder { aspect-ratio: 1; width: 100%; display: grid; place-items: center; font-size: 40px; transition: transform 0.4s ease; }
        .product-card-mock:hover .product-img-placeholder { transform: scale(1.05); }
        .pi-1 { background: linear-gradient(160deg, #F5E6D3, #E2C9A8); }
        .pi-2 { background: linear-gradient(160deg, #E8DDD0, #D0BFA8); }
        .pi-3 { background: linear-gradient(160deg, #F0E8DB, #DBC8AB); }
        .pi-4 { background: linear-gradient(160deg, #EAE0D2, #CCB898); }
        .pi-5 { background: linear-gradient(160deg, #E5D8C8, #C8AE8F); }
        .pi-6 { background: linear-gradient(160deg, #F2EADB, #DEC9A8); }
        .pi-7 { background: linear-gradient(160deg, #E8E0D0, #C5AD8E); }
        .pi-8 { background: linear-gradient(160deg, #F0E2D0, #D4B890); }
        .discount-badge-mock { position: absolute; top: 10px; left: 10px; background: var(--red-badge); color: #fff; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: var(--radius-sm); z-index: 2; }
        .wishlist-btn-mock { position: absolute; top: 10px; right: 10px; width: 34px; height: 34px; background: rgba(255,255,255,0.9); border-radius: 50%; display: grid; place-items: center; z-index: 2; transition: background 0.2s, transform 0.15s; backdrop-filter: blur(4px); }
        .wishlist-btn-mock:hover { background: #fff; transform: scale(1.1); }
        .wishlist-btn-mock svg { width: 16px; height: 16px; color: var(--text-secondary); }
        .wishlist-btn-mock.is-active svg { color: var(--red-badge); fill: var(--red-badge); }
        .quick-view-btn-mock { position: absolute; bottom: 10px; right: 10px; width: 34px; height: 34px; background: rgba(255,255,255,0.9); border-radius: 50%; display: grid; place-items: center; z-index: 2; opacity: 0; transition: opacity 0.2s, background 0.2s, transform 0.15s; backdrop-filter: blur(4px); }
        .product-card-mock:hover .quick-view-btn-mock { opacity: 1; }
        .quick-view-btn-mock:hover { background: var(--accent); transform: scale(1.1); }
        .quick-view-btn-mock:hover svg { color: #fff; }
        .quick-view-btn-mock svg { width: 16px; height: 16px; color: var(--text-secondary); }
        .product-info-mock { padding: 14px 16px 16px; }
        .product-name-mock { font-size: 13.5px; font-weight: 500; line-height: 1.4; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 38px; }
        .product-price-mock { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
        .price-current { font-size: 16px; font-weight: 700; color: var(--text-primary); }
        .price-original { font-size: 13px; color: var(--text-muted); text-decoration: line-through; }
        .product-actions-mock { display: flex; gap: 8px; }
        .btn-add-cart { flex: 1; padding: 9px 14px; background: var(--accent); color: #fff; font-size: 12px; font-weight: 600; border-radius: var(--radius-sm); transition: background 0.2s; text-align: center; }
        .btn-add-cart:hover { background: var(--accent-hover); }
        .btn-select { flex: 1; padding: 9px 14px; border: 1.5px solid var(--border); color: var(--text-secondary); font-size: 12px; font-weight: 600; border-radius: var(--radius-sm); transition: all 0.2s; text-align: center; }
        .btn-select:hover { border-color: var(--accent); color: var(--accent); }

        /* USP bar */
        .usp-bar-mock { background: var(--bg-warm); border-top: 1px solid var(--border-light); border-bottom: 1px solid var(--border-light); }
        .usp-grid-mock { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; padding: 40px 0; }
        .usp-item-mock { text-align: center; padding: 0 12px; }
        .usp-icon-mock { width: 48px; height: 48px; margin: 0 auto 12px; display: grid; place-items: center; font-size: 24px; background: rgba(139,94,60,0.08); border-radius: 50%; }
        .usp-item-mock h3 { font-size: 14px; font-weight: 600; margin-bottom: 4px; }
        .usp-item-mock p { font-size: 12.5px; color: var(--text-muted); line-height: 1.5; }

        /* Promo banner */
        .promo-banner-mock { background: linear-gradient(135deg, #3D2B1F, #5C3D26); color: #fff; text-align: center; padding: 64px 24px; border-radius: var(--radius-lg); margin: 20px 0; }
        .promo-banner-mock h2 { font-family: var(--font-display); font-size: clamp(28px, 4vw, 40px); font-weight: 400; margin-bottom: 12px; }
        .promo-banner-mock p { font-size: 14px; opacity: 0.8; max-width: 480px; margin: 0 auto 24px; }
        .btn-promo { display: inline-flex; padding: 12px 32px; background: #fff; color: var(--text-primary); font-size: 13px; font-weight: 600; border-radius: var(--radius-sm); transition: opacity 0.2s; }
        .btn-promo:hover { opacity: 0.9; }

        /* Testimonials */
        .testimonial-grid-mock { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .testimonial-card-mock { padding: 28px; background: var(--bg-card); border: 1px solid var(--border-light); border-radius: var(--radius-md); }
        .testimonial-stars { color: #F5A623; font-size: 14px; margin-bottom: 12px; }
        .testimonial-text { font-size: 14px; color: var(--text-secondary); line-height: 1.65; margin-bottom: 16px; font-style: italic; }
        .testimonial-author { font-size: 13px; font-weight: 600; }
        .testimonial-location { font-size: 12px; color: var(--text-muted); }

        /* Newsletter */
        .newsletter-section-mock { background: var(--bg-warm); text-align: center; padding: 56px 24px; }
        .newsletter-section-mock h2 { font-family: var(--font-display); font-size: 28px; font-weight: 500; margin-bottom: 8px; }
        .newsletter-section-mock p { font-size: 14px; color: var(--text-secondary); margin-bottom: 24px; }
        .newsletter-form-mock { display: flex; gap: 8px; max-width: 440px; margin: 0 auto; }
        .newsletter-form-mock input { flex: 1; padding: 12px 18px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: #fff; font-size: 16px; outline: none; }
        .newsletter-form-mock input:focus { border-color: var(--accent-light); }
        .newsletter-form-mock button { padding: 12px 24px; background: var(--accent); color: #fff; font-size: 13px; font-weight: 600; border-radius: var(--radius-sm); transition: background 0.2s; }
        .newsletter-form-mock button:hover { background: var(--accent-hover); }

        /* Footer */
        .site-footer-mock { background: #2C2018; color: #E8DDD0; padding-top: 56px; }
        .footer-grid-mock { display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr; gap: 40px; padding-bottom: 40px; }
        .footer-brand-mock { font-family: var(--font-display); font-size: 24px; font-weight: 600; color: #fff; margin-bottom: 12px; }
        .footer-desc-mock { font-size: 13px; color: #B5A898; line-height: 1.65; max-width: 280px; margin-bottom: 16px; }
        .footer-social-mock { display: flex; gap: 10px; }
        .footer-social-mock a { width: 36px; height: 36px; display: grid; place-items: center; border: 1px solid rgba(255,255,255,0.15); border-radius: 50%; color: #B5A898; font-size: 14px; transition: all 0.2s; }
        .footer-social-mock a:hover { background: var(--accent); border-color: var(--accent); color: #fff; }
        .footer-col-mock h4 { font-size: 14px; font-weight: 600; color: #fff; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.5px; }
        .footer-col-mock a { display: block; font-size: 13px; color: #B5A898; padding: 4px 0; transition: color 0.2s; }
        .footer-col-mock a:hover { color: #fff; }
        .footer-bottom-mock { border-top: 1px solid rgba(255,255,255,0.08); padding: 20px 0; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #8B7D6B; }
        .footer-payments-mock { display: flex; gap: 8px; align-items: center; }
        .payment-icon-mock { padding: 4px 10px; background: rgba(255,255,255,0.08); border-radius: var(--radius-sm); font-size: 11px; color: #B5A898; }

        .mobile-menu-btn { display: none; width: 40px; height: 40px; place-items: center; color: var(--text-primary); }
        .mobile-menu-btn svg { width: 22px; height: 22px; }

        @media (max-width: 1024px) {
            .category-grid-mock { grid-template-columns: repeat(3, 1fr); }
            .product-grid-mock { grid-template-columns: repeat(3, 1fr); }
            .footer-grid-mock { grid-template-columns: 1fr 1fr; gap: 32px; }
        }
        @media (max-width: 768px) {
            .mobile-menu-btn { display: grid; }
            .top-bar { display: none; }
            .header-search { max-width: none; order: 3; flex-basis: 100%; }
            .header-main { flex-wrap: wrap; padding: 12px 0; gap: 10px; }
            .hero-mock .container { grid-template-columns: 1fr; gap: 32px; }
            .hero-visual { display: none; }
            .hero-mock { min-height: auto; padding: 40px 0; }
            .category-grid-mock { grid-template-columns: repeat(3, 1fr); gap: 12px; }
            .product-grid-mock { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .usp-grid-mock { grid-template-columns: repeat(2, 1fr); gap: 16px; }
            .testimonial-grid-mock { grid-template-columns: 1fr; }
            .footer-grid-mock { grid-template-columns: 1fr; }
            .footer-bottom-mock { flex-direction: column; gap: 12px; text-align: center; }
            .section-mock { padding: 40px 0; }
        }
        @media (max-width: 480px) {
            .category-grid-mock { grid-template-columns: repeat(3, 1fr); gap: 10px; }
            .category-thumb { border-width: 2px; }
            .category-card-mock h3 { font-size: 11px; }
            .product-grid-mock { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .product-info-mock { padding: 10px 12px 14px; }
            .product-name-mock { font-size: 12px; min-height: 34px; }
            .price-current { font-size: 14px; }
            .newsletter-form-mock { flex-direction: column; }
        }

        /* ===== CINEMATIC EFFECTS ===== */

        /* Gradient shimmer text */
        .gradient-text {
            background: linear-gradient(135deg, #8B5E3C 0%, #C9A77D 50%, #8B5E3C 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: shimmer 4s ease-in-out infinite;
        }
        @keyframes shimmer { 0%,100% { background-position: 0 center; } 50% { background-position: 100% center; } }

        /* Glow on hover */
        .glow-hover { transition: box-shadow 0.4s ease, transform 0.3s ease; }
        .glow-hover:hover {
            box-shadow: 0 8px 30px rgba(139, 94, 60, 0.12), 0 4px 15px rgba(139, 94, 60, 0.08);
            transform: translateY(-3px);
        }

        /* Float animation */
        .float { animation: floatAnim 6s ease-in-out infinite; }
        @keyframes floatAnim { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

        /* Subtle pulse for badges */
        .pulse-subtle { animation: pulseSub 2.5s ease-in-out infinite; }
        @keyframes pulseSub { 0%,100% { opacity: 1; } 50% { opacity: 0.75; } }

        /* Image zoom on hover */
        .img-zoom { overflow: hidden; }
        .img-zoom img, .img-zoom > div {
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .img-zoom:hover img, .img-zoom:hover > div { transform: scale(1.06); }

        /* Animated underline */
        .hover-line { position: relative; }
        .hover-line::after {
            content: ''; position: absolute; width: 0; height: 1.5px;
            bottom: -2px; left: 0; background: currentColor;
            transition: width 0.3s ease;
        }
        .hover-line:hover::after { width: 100%; }

        /* Glass effect */
        .glass-effect {
            background: rgba(253, 251, 247, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* Tilt card */
        .tilt-hover { transition: transform 0.35s ease; }
        .tilt-hover:hover { transform: perspective(800px) rotateX(1deg) rotateY(-1.5deg) translateY(-4px); }

        /* Marquee */
        @keyframes marqueeScroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
        .marquee-animate { animation: marqueeScroll 25s linear infinite; }

        /* Scroll progress */
        #scrollBar {
            position: fixed; top: 0; left: 0; height: 3px; z-index: 9999;
            background: linear-gradient(90deg, #8B5E3C, #C9A77D, #8B5E3C);
            background-size: 200% 100%;
            width: 0%; transition: width 0.05s linear;
            animation: shimmer 3s ease infinite;
        }

        /* Smooth fade in for page */
        .page-ready { animation: pageFade 0.5s ease-out forwards; }
        @keyframes pageFade { from { opacity: 0.3; } to { opacity: 1; } }

        /* Counter pulse on finish */
        .counter-done { animation: counterPop 0.3s ease; }
        @keyframes counterPop { 0% { transform: scale(1); } 50% { transform: scale(1.15); } 100% { transform: scale(1); } }

        /* WhatsApp button glow */
        .wa-glow {
            box-shadow: 0 0 10px rgba(37,211,102,0.3), 0 0 30px rgba(37,211,102,0.1);
            animation: waGlow 2s ease-in-out infinite;
        }
        @keyframes waGlow {
            0%,100% { box-shadow: 0 0 10px rgba(37,211,102,0.3); }
            50% { box-shadow: 0 0 20px rgba(37,211,102,0.5), 0 0 40px rgba(37,211,102,0.15); }
        }

        /* ===== GLOBAL MOBILE FIXES ===== */
        html { -webkit-text-size-adjust: 100%; }

        /* Prevent iOS zoom on focus: all inputs need >=16px */
        input, textarea, select { font-size: 16px; }

        .product-scroll-container, .scrollbar-hide {
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x mandatory;
        }
        .product-scroll-container > *, .scrollbar-hide > * { scroll-snap-align: start; }

        @media (max-width: 640px) {
            .container { padding-left: 1rem; padding-right: 1rem; }
        }
        @media (max-width: 767px) {
            .wa-float { bottom: 84px !important; }
            .back-to-top { bottom: 148px !important; }
        }

        /* Respect user motion preferences */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.15s !important;
            }
        }

        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-brand-50 text-brand-900 font-body antialiased page-ready">

    {{-- Scroll Progress Bar --}}
    <div id="scrollBar"></div>

    @include('partials.header')

    <main>
        @if (session('success'))
            <div class="max-w-7xl mx-auto px-4 pt-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition>
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
            </div>
        @endif
        @if (session('error'))
            <div class="max-w-7xl mx-auto px-4 pt-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition>
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.chatbot')

    {{-- WhatsApp Floating Button --}}
    @if(settings('whatsapp_number'))
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', settings('whatsapp_number')) }}?text={{ urlencode('Hi! I was browsing '.settings('site_name', 'Himashva').' and have a question.') }}" target="_blank" rel="noopener"
       class="wa-float fixed bottom-6 right-6 z-50 bg-green-500 hover:bg-green-600 text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center wa-glow transition-transform hover:scale-110"
       aria-label="Chat on WhatsApp">
        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.111.547 4.099 1.504 5.828L0 24l6.335-1.652A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75c-1.894 0-3.689-.482-5.254-1.385l-.377-.224-3.909 1.02 1.04-3.796-.246-.391A9.691 9.691 0 012.25 12 9.75 9.75 0 0112 2.25 9.75 9.75 0 0121.75 12 9.75 9.75 0 0112 21.75z"/></svg>
    </a>
    @endif

    {{-- Back to Top --}}
    <button x-data="{ show: false }"
        x-show="show" x-cloak
        @scroll.window="show = window.scrollY > 500"
        @click="window.scrollTo({top: 0, behavior: 'smooth'})"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="back-to-top fixed bottom-24 right-6 z-40 bg-brand-700 hover:bg-brand-800 text-white w-11 h-11 rounded-full shadow-lg flex items-center justify-center transition-transform hover:scale-110"
        aria-label="Back to top">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 15l7-7 7 7"/></svg>
    </button>

    {{-- Toast notification --}}
    <div x-data="{ show: false, message: '' }"
        @toast.window="message = $event.detail; show = true; setTimeout(() => show = false, 2500)"
        x-show="show" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-6"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0 translate-y-6"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-brand-800 text-white text-sm px-6 py-3 rounded-full shadow-xl">
        <span x-text="message"></span>
    </div>

    <x-quick-view-modal />

    {{-- AOS Library --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

    {{-- GSAP for advanced animations --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.7/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.7/ScrollTrigger.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize AOS with safe defaults
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 650,
                easing: 'ease-out-cubic',
                once: true,
                offset: 50,
            });
        }

        // Scroll progress bar
        window.addEventListener('scroll', function() {
            var bar = document.getElementById('scrollBar');
            if (bar) {
                var h = document.documentElement;
                var pct = (h.scrollTop / (h.scrollHeight - h.clientHeight)) * 100;
                bar.style.width = Math.min(pct, 100) + '%';
            }
        }, { passive: true });

        // GSAP animations (only if loaded)
        if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger);

            // Counter animation
            document.querySelectorAll('.count-up').forEach(function(el) {
                var target = parseInt(el.getAttribute('data-target')) || 0;
                var suffix = el.getAttribute('data-suffix') || '';
                var prefix = el.getAttribute('data-prefix') || '';
                gsap.fromTo(el, { innerText: 0 }, {
                    innerText: target,
                    duration: 2.2,
                    ease: 'power2.out',
                    snap: { innerText: 1 },
                    scrollTrigger: { trigger: el, start: 'top 88%' },
                    onUpdate: function() {
                        el.textContent = prefix + Math.round(parseFloat(el.textContent)).toLocaleString('en-IN') + suffix;
                    },
                    onComplete: function() {
                        el.classList.add('counter-done');
                    }
                });
            });

            // Stagger children
            document.querySelectorAll('.stagger-in').forEach(function(parent) {
                gsap.from(parent.children, {
                    y: 30, opacity: 0, duration: 0.5, stagger: 0.08,
                    ease: 'power2.out',
                    scrollTrigger: { trigger: parent, start: 'top 88%' }
                });
            });

            // Parallax
            document.querySelectorAll('.parallax').forEach(function(el) {
                gsap.to(el, {
                    yPercent: -15, ease: 'none',
                    scrollTrigger: { trigger: el, start: 'top bottom', end: 'bottom top', scrub: 1 }
                });
            });

            // Image reveal
            document.querySelectorAll('.clip-reveal').forEach(function(el) {
                gsap.from(el, {
                    clipPath: 'inset(0 0 100% 0)', duration: 1, ease: 'power3.out',
                    scrollTrigger: { trigger: el, start: 'top 85%' }
                });
            });

            // Recompute trigger positions once the page has fully settled (fonts,
            // Tailwind's async-injected stylesheet, images) — otherwise triggers
            // near the bottom of the page can be positioned against a shorter,
            // not-yet-final layout and never fire, leaving elements stuck at
            // opacity:0 permanently.
            window.addEventListener('load', function() {
                ScrollTrigger.refresh();
            });
        }
    });
    </script>

    @include('compare.partials.floating-bar')

    {{-- Social Proof Toast --}}
    <div x-data="socialProofToast()" x-cloak>
        <div x-show="visible" x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-full opacity-0"
             style="position: fixed; bottom: 20px; left: 20px; z-index: 45; max-width: 320px; background: white; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.12); border: 1px solid #EAE2D6; overflow: hidden;">
            <button @click="visible = false" style="position: absolute; top: 6px; right: 8px; background: none; border: none; font-size: 16px; color: #9C8E80; cursor: pointer; line-height: 1;">&times;</button>
            <a :href="current?.product_url || '#'" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; text-decoration: none;">
                <template x-if="current?.image">
                    <img :src="current.image" :alt="current?.product" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover; flex-shrink: 0;">
                </template>
                <template x-if="!current?.image">
                    <div style="width: 50px; height: 50px; border-radius: 8px; background: #F8F3EC; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">🕯️</div>
                </template>
                <div style="min-width: 0;">
                    <div style="font-size: 0.8rem; color: #2C2018;">
                        <span style="font-weight: 600;" x-text="current?.name"></span>
                        <span>from</span>
                        <span style="font-weight: 600;" x-text="current?.city"></span>
                    </div>
                    <div style="font-size: 0.75rem; color: #6B5D50; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        purchased <span style="font-weight: 500;" x-text="current?.product"></span>
                    </div>
                    <div style="font-size: 0.65rem; color: #9C8E80; margin-top: 2px;" x-text="current?.time_ago"></div>
                </div>
            </a>
            <div style="height: 3px; background: #F0EBE3;">
                <div style="height: 100%; background: linear-gradient(90deg, #8B5E3C, #C9A77D); transition: width 0.3s linear;" :style="'width: ' + progress + '%'"></div>
            </div>
        </div>
    </div>

    <script>
    function socialProofToast() {
        return {
            purchases: [],
            currentIndex: 0,
            current: null,
            visible: false,
            progress: 100,
            timer: null,
            async init() {
                if (window.location.pathname.includes('/checkout') || window.location.pathname.includes('/cart')) return;

                try {
                    const res = await fetch('{{ route('api.recent-purchases') }}');
                    this.purchases = await res.json();
                } catch (e) { return; }

                if (this.purchases.length === 0) return;

                setTimeout(() => this.showNext(), 8000);
            },
            showNext() {
                if (this.purchases.length === 0) return;
                this.current = this.purchases[this.currentIndex % this.purchases.length];
                this.currentIndex++;
                this.visible = true;
                this.progress = 100;

                const duration = 5000;
                const interval = 50;
                let elapsed = 0;
                clearInterval(this.timer);
                this.timer = setInterval(() => {
                    elapsed += interval;
                    this.progress = Math.max(0, 100 - (elapsed / duration * 100));
                    if (elapsed >= duration) {
                        clearInterval(this.timer);
                        this.visible = false;
                        setTimeout(() => this.showNext(), 20000 + Math.random() * 15000);
                    }
                }, interval);
            }
        };
    }
    </script>

    @stack('scripts')
</body>
</html>
