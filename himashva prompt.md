# HIMASHVA — Complete E-Commerce Project Build Prompt for Claude Code

## INSTRUCTIONS FOR CLAUDE CODE

You are building a world-class e-commerce website called **Himashva** — a handcrafted candle and home fragrance store. Build this as a complete, production-ready Laravel application. Work autonomously in a loop: install, configure, build, and verify each step before moving to the next. Do NOT stop and ask questions — make smart decisions and keep building.

**CRITICAL RULES:**
1. Install Laravel in `xampp/htdocs/himashva` — this is where we are working
2. Database: MySQL, database name = `himashva`, host = `127.0.0.1`, user = `root`, password = `` (empty — standard XAMPP)
3. Use PHP 8.2+ (XAMPP's PHP), Composer for packages
4. Use **Filament v3** for the admin panel — do NOT build admin from scratch
5. Use **Tailwind CSS** via CDN (https://cdn.tailwindcss.com) for the storefront — do NOT use Vite/Node build system. Keep it simple.
6. Use **Blade templates** for the storefront (not Livewire for the frontend)
7. Use **Livewire** only where Filament requires it (admin panel)
8. After every major step, verify it works by checking for errors
9. Create ALL migrations, models, controllers, views, routes, seeders — everything
10. The site must be fully functional when you finish — not a skeleton

---

## PHASE 1: PROJECT SETUP

### Step 1.1 — Create Laravel Project
```
cd C:\xampp\htdocs
composer create-project laravel/laravel himashva
cd himashva
```

### Step 1.2 — Configure .env
Update `.env` with:
```
APP_NAME=Himashva
APP_URL=http://localhost/himashva/public
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=himashva
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public
```

### Step 1.3 — Install Required Packages
```
composer require filament/filament:"^3.0"
composer require laravel/breeze --dev
php artisan breeze:install blade
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf
composer require intervention/image-laravel
php artisan filament:install --panels
php artisan storage:link
php artisan session:table
php artisan key:generate
```

Do NOT run `npm install` or `npm run build` — we use Tailwind CDN for storefront.

---

## PHASE 2: DATABASE ARCHITECTURE

Create ALL these migrations in order. Use `php artisan make:migration` for each.

### Core Tables:

**categories**
- id, name, slug (unique), description (nullable), image (nullable), parent_id (nullable, self-referencing FK), sort_order (default 0), is_active (default true), timestamps

**brands** (for future expansion)
- id, name, slug (unique), logo (nullable), is_active (default true), timestamps

**products**
- id, category_id (FK), brand_id (nullable FK), name, slug (unique), short_description (text, nullable), description (longText), sku (unique), price (decimal 10,2), sale_price (decimal 10,2, nullable), cost_price (decimal 10,2, nullable), stock (unsignedInteger, default 0), low_stock_threshold (unsignedInteger, default 5), weight_grams (unsignedInteger, nullable), is_active (default true), is_featured (default false), is_bestseller (default false), is_new (default false), meta_title (nullable), meta_description (nullable), total_sold (unsignedInteger, default 0), avg_rating (decimal 3,2, default 0), review_count (unsignedInteger, default 0), timestamps, softDeletes

**product_images**
- id, product_id (FK cascade), image_path, alt_text (nullable), sort_order (default 0), is_primary (default false), timestamps

**product_variants**
- id, product_id (FK cascade), name (e.g., "Large / Rose"), sku (unique), price (decimal 10,2), sale_price (nullable), stock (unsignedInteger, default 0), sort_order (default 0), is_active (default true), timestamps

**product_attributes**
- id, product_id (FK cascade), attribute_name (e.g., "Size", "Fragrance", "Color"), attribute_value, timestamps

### User & Customer Tables:

Modify the default **users** table migration to add:
- phone (string 20, nullable), is_admin (boolean, default false), avatar (nullable), date_of_birth (date, nullable), gender (enum: male/female/other, nullable), last_login_at (timestamp, nullable), login_count (unsignedInteger, default 0), source (string, nullable — how they found us), notes (text, nullable — admin notes about customer)

**addresses**
- id, user_id (FK cascade), label (string — "Home", "Office", etc.), name, phone, address_line_1, address_line_2 (nullable), city, state, postal_code (string 10), country (default "India"), is_default (boolean, default false), timestamps

### Order Tables:

**coupons**
- id, code (unique), type (enum: percentage/fixed), value (decimal 10,2), min_order_amount (decimal 10,2, nullable), max_discount_amount (decimal 10,2, nullable), max_uses (nullable), used_count (default 0), per_user_limit (default 1), starts_at (timestamp, nullable), expires_at (timestamp, nullable), is_active (default true), timestamps

**orders**
- id, user_id (FK nullable), order_number (unique — auto-generated like HMV-20260920-0001), name, email, phone, address_line_1, address_line_2 (nullable), city, state, postal_code, country (default "India"), subtotal (decimal 10,2), discount_amount (decimal 10,2, default 0), coupon_id (FK nullable), shipping_amount (decimal 10,2, default 0), tax_amount (decimal 10,2, default 0), total (decimal 10,2), payment_method (string — cod/razorpay/upi), payment_status (enum: pending/paid/failed/refunded, default pending), payment_id (nullable — gateway reference), order_status (enum: pending/confirmed/processing/shipped/out_for_delivery/delivered/cancelled/returned/refunded, default pending), tracking_number (nullable), tracking_url (nullable), shipping_partner (nullable), estimated_delivery (date, nullable), delivered_at (timestamp, nullable), cancelled_at (timestamp, nullable), cancellation_reason (nullable), admin_notes (text, nullable), ip_address (nullable), user_agent (nullable), timestamps, softDeletes

**order_items**
- id, order_id (FK cascade), product_id (FK nullOnDelete), variant_id (FK nullable nullOnDelete), product_name, variant_name (nullable), sku, unit_price (decimal 10,2), quantity (unsignedInteger), line_total (decimal 10,2), timestamps

**order_status_history**
- id, order_id (FK cascade), from_status (nullable), to_status, comment (nullable), changed_by (FK nullable — user_id of admin), timestamps

### Engagement Tables:

**reviews**
- id, user_id (FK cascade), product_id (FK cascade), order_id (FK nullable), rating (tinyInteger 1-5), title (nullable), comment (text, nullable), is_approved (default false), admin_reply (text, nullable), timestamps

**wishlists**
- id, user_id (FK cascade), product_id (FK cascade), timestamps
- unique constraint on [user_id, product_id]

**cart_items** (for logged-in users — guests use session)
- id, user_id (FK cascade), product_id (FK cascade), variant_id (FK nullable), quantity (unsignedInteger), timestamps

**contact_messages**
- id, name, email, phone (nullable), subject, message (text), is_read (default false), admin_reply (text, nullable), replied_at (nullable), ip_address (nullable), timestamps

**newsletter_subscribers**
- id, email (unique), name (nullable), is_active (default true), subscribed_at, unsubscribed_at (nullable), timestamps

### CMS Tables:

**banners**
- id, title, subtitle (nullable), image_path, link (nullable), button_text (nullable), position (enum: hero/promo/sidebar, default hero), sort_order (default 0), is_active (default true), starts_at (nullable), ends_at (nullable), timestamps

**pages** (for About, Terms, Privacy, etc.)
- id, title, slug (unique), content (longText), meta_title (nullable), meta_description (nullable), is_active (default true), timestamps

**site_settings**
- id, key (unique), value (text, nullable), group (string — general/social/payment/shipping/seo)

### Analytics:

**customer_activities**
- id, user_id (FK nullable), session_id (nullable), activity_type (string — page_view/product_view/add_to_cart/wishlist/search/purchase), subject_type (nullable), subject_id (nullable), data (json, nullable), ip_address, user_agent (nullable), timestamps

---

## PHASE 3: ELOQUENT MODELS

Create all Eloquent models with proper relationships, accessors, scopes, and casts.

Key model features:
- **Product**: `discount_percentage` accessor (calculates from price & sale_price), `scopeActive()`, `scopeFeatured()`, `scopeBestseller()`, `scopeInStock()`, `primaryImage` relationship, price formatting helpers
- **Order**: Auto-generate `order_number` in `boot()` method using date + sequence, `formattedTotal` accessor
- **User**: `isAdmin()` method, relationships to orders/reviews/wishlists/addresses/cartItems, `totalSpent` accessor, `orderCount` accessor
- **Category**: Self-referencing parent/children, `scopeActive()`, `scopeRoot()` (where parent_id is null), product count
- **Coupon**: `isValid()` method checking dates/usage/active status, `calculateDiscount($amount)` method

---

## PHASE 4: FILAMENT ADMIN PANEL

Set up Filament admin at `/admin` URL.

### Admin Resources to create (using `php artisan make:filament-resource`):

1. **ProductResource** — Full CRUD with:
   - TextInput for name, sku, prices
   - RichEditor for description
   - Select for category and brand
   - FileUpload for images (multiple, reorderable)
   - Toggle for is_active, is_featured, is_bestseller, is_new
   - Stock management
   - Variant management as a repeater/relation manager
   - Table with filters: category, active status, featured, stock level, price range
   - Bulk actions: activate/deactivate, mark featured

2. **CategoryResource** — CRUD with image upload, parent selection, sort order, tree view

3. **OrderResource** — View/edit with:
   - Order items table
   - Status change with history tracking
   - Customer details
   - Payment info
   - Tracking info input
   - Print invoice action (PDF using DomPDF)
   - Status filters, date range filter, payment status filter
   - Export to Excel action

4. **CustomerResource** (User model where is_admin = false) — with:
   - Customer details
   - Order history tab
   - Address list
   - Activity log
   - Total spent, order count stats
   - **Advanced filters**: by city, state, order count range, total spent range, registration date range, last login date, gender, source
   - Export to Excel with all filter options
   - Bulk email action placeholder

5. **CouponResource** — Full CRUD with validation rules display

6. **ReviewResource** — Approve/reject, admin reply

7. **BannerResource** — CRUD with image, position, scheduling

8. **PageResource** — CMS pages CRUD with rich editor

9. **ContactMessageResource** — View, mark read, reply

10. **NewsletterResource** — View subscribers, export

11. **SiteSettingResource** — or use a custom Filament page for settings management grouped by:
    - General: site name, tagline, logo, favicon, contact email, phone, address, WhatsApp number
    - Social: Instagram, Facebook, Pinterest, YouTube, Twitter URLs
    - Payment: Razorpay key/secret fields, COD toggle, UPI toggle
    - Shipping: free shipping threshold, flat rate, Shiprocket API credentials
    - SEO: default meta title, description, Google Analytics ID

### Admin Dashboard Widgets:
- Total orders today / this week / this month
- Revenue today / this week / this month
- New customers this week
- Low stock products alert
- Recent orders table (last 10)
- Top selling products chart
- Order status breakdown chart
- Revenue trend line chart (last 30 days)

---

## PHASE 5: STOREFRONT FRONTEND

### Layout (resources/views/layouts/app.blade.php):

Use Tailwind CDN. The design must match the Himashva prototype — warm candle-store aesthetic.

**Include in <head>:**
```html
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        brand: { 50: '#FDFBF7', 100: '#F8F3EC', 200: '#EAE2D6', 300: '#D4BFA6', 400: '#C9A77D', 500: '#8B5E3C', 600: '#6D4829', 700: '#5C3D26', 800: '#3D2B1F', 900: '#2C2018' },
      },
      fontFamily: {
        display: ['Georgia', 'Cambria', 'serif'],
        body: ['system-ui', '-apple-system', 'sans-serif'],
      }
    }
  }
}
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
```

**Header structure (included in layout):**
1. Announcement bar — accent-colored strip with offer text (fetched from site_settings)
2. Main header — Logo | Search bar | Account/Wishlist/Cart icons with badge
3. Category navigation — horizontal scrollable bar with all active categories
4. Mobile: hamburger menu with slide-out drawer

**Footer structure:**
1. 4-column grid: Brand info + social links | Quick Links | Categories | Policies
2. Bottom bar: Copyright | Payment icons | "Made with ♥ in India"
3. **WhatsApp floating button** — fixed bottom-right, links to `https://wa.me/{whatsapp_number}` from site_settings
4. Social media links from site_settings (Instagram, Facebook, Pinterest, YouTube, Twitter)

### Pages to build:

1. **Homepage** (`/`)
   - Hero banner slider (from banners table, position=hero)
   - USP bar (4 features: Natural Soy Wax, Handcrafted, Free Shipping, Gift Ready)
   - Shop by Category grid (circular thumbnails, 6 columns)
   - Featured Products grid (4 columns, product cards with discount badge, wishlist, prices)
   - Promotional banner (from banners table, position=promo)
   - Best Sellers grid
   - Customer testimonials (from approved reviews)
   - Newsletter signup form (AJAX submit)

2. **Shop page** (`/shop`)
   - Sidebar filters: categories (checkboxes), price range (min/max inputs), sort by (newest/price-low-high/price-high-low/popularity/rating)
   - Product grid (responsive: 4 cols desktop, 2 mobile)
   - Pagination
   - "X products found" count
   - Category page (`/category/{slug}`) — same layout filtered by category

3. **Product detail page** (`/product/{slug}`)
   - Image gallery (primary + thumbnails, click to enlarge)
   - Product name, price (sale + original with discount %), SKU
   - Short description
   - Variant selector (if variants exist) — dropdown or buttons
   - Quantity input with +/- buttons
   - Add to Cart button (large, prominent)
   - Add to Wishlist button
   - Tabs: Description | Additional Info | Reviews (with review form for logged-in users)
   - Related products carousel (same category)
   - Share buttons (WhatsApp, Facebook, copy link)

4. **Cart page** (`/cart`)
   - Table: product image, name, variant, unit price, quantity (editable), line total, remove button
   - Coupon code input with Apply button
   - Cart summary: subtotal, discount, shipping, total
   - Continue Shopping + Proceed to Checkout buttons
   - Update cart with AJAX (no full page reload)

5. **Checkout page** (`/checkout`)
   - Login prompt for guests (with option to continue as guest)
   - Shipping address form (or select from saved addresses if logged in)
   - Order summary sidebar
   - Payment method selection: COD, Razorpay (show placeholder if keys not configured), UPI
   - Place Order button
   - For Razorpay: include Razorpay checkout.js, create order via their API, handle success/failure callbacks

6. **Order confirmation** (`/order/success/{order_number}`)
   - Thank you message, order number, order details, estimated delivery
   - Continue shopping button

7. **Customer account pages** (auth required):
   - `/account` — Dashboard with recent orders, saved addresses count
   - `/account/orders` — Order history with status badges
   - `/account/orders/{order_number}` — Order detail with status timeline, items, tracking
   - `/account/addresses` — CRUD for saved addresses
   - `/account/wishlist` — Wishlist grid with move-to-cart option
   - `/account/profile` — Edit name, email, phone, password, DOB, gender, avatar
   - `/account/reviews` — My reviews

8. **Auth pages** (Laravel Breeze):
   - Login, Register, Forgot Password, Reset Password
   - Style them to match the Himashva theme

9. **Static pages**:
   - `/about` — About Us (from pages table)
   - `/contact` — Contact form + map placeholder + WhatsApp + phone + email
   - `/privacy-policy`, `/terms`, `/shipping-policy`, `/cancellation-refund` — from pages table
   - `/track-order` — Input order number + email, show status timeline

10. **Search** (`/search?q=...`)
    - Full-text search across product name, description, SKU, category name
    - Results grid with highlight

### Product Card Component (used everywhere):
```
- Discount badge (top-left, red, "-X%")
- Wishlist heart button (top-right, toggles with AJAX)
- Product image (hover zoom effect)
- Product name (2-line clamp)
- Price: ₹sale_price + ₹original_price (strikethrough) — or just ₹price if no sale
- "Add to Cart" button (AJAX, shows success toast)
- "Select Options" button (if has variants — links to product page)
```

---

## PHASE 6: CHATBOT

Build a simple AI-powered chatbot widget:

1. Create a floating chat button (bottom-right, above WhatsApp button)
2. On click, opens a chat panel (fixed position, 380px wide, 500px tall)
3. Pre-loaded with common Q&A responses (stored in a `chatbot_responses` table or config):
   - "What are your shipping charges?" → "Free shipping on orders above ₹999. Flat ₹49 for orders below ₹999."
   - "What payment methods do you accept?" → "We accept COD, UPI, Razorpay (Credit/Debit cards, Net Banking, Wallets)."
   - "How can I track my order?" → "Visit /track-order and enter your order number and email."
   - "What is your return policy?" → "We offer 7-day returns on undamaged products. Visit our Cancellation & Refund policy page."
   - "How do I contact you?" → "WhatsApp: {number}, Email: {email}, or use our Contact page."
   - And 15-20 more common e-commerce questions
4. Use keyword matching to find the best response
5. If no match, show: "I couldn't find an answer. Please WhatsApp us at {number} or email {email} for help."
6. Save all chat conversations to a `chatbot_conversations` table (user_id nullable, session_id, messages as JSON, timestamps) so admin can review them in Filament
7. Style: clean, modern chat UI with brand colors

---

## PHASE 7: PAYMENT GATEWAY INTEGRATION (Razorpay)

1. Install: `composer require razorpay/razorpay`
2. Create a `PaymentController` with methods:
   - `createRazorpayOrder($amount)` — calls Razorpay API to create an order
   - `verifyPayment(Request $request)` — verifies the payment signature
3. In checkout, when user selects Razorpay:
   - Create a Razorpay order via API
   - Open Razorpay checkout modal (include checkout.js from https://checkout.razorpay.com/v1/checkout.js)
   - On success: verify payment, update order payment_status to 'paid', save payment_id
   - On failure: show error, keep order as payment_status 'failed'
4. Store Razorpay key_id and key_secret in site_settings (admin configurable)
5. If Razorpay credentials are not set, hide the Razorpay payment option gracefully
6. Add a `payments` table: id, order_id, gateway, gateway_order_id, gateway_payment_id, gateway_signature, amount, currency, status, raw_response (json), timestamps

---

## PHASE 8: SHIPPING INTEGRATION (Shiprocket Placeholder)

1. Create a `ShippingService` class with interface:
   - `calculateShipping($pincode, $weight, $cod = false)` — returns shipping cost
   - `createShipment($order)` — creates shipment and returns tracking number
   - `trackShipment($trackingNumber)` — returns tracking status
   - `cancelShipment($trackingNumber)` — cancels shipment
2. Create a `ShiprocketService` implementing this interface (with placeholder API calls)
3. Store Shiprocket API email/password/token in site_settings
4. In admin order detail, add buttons: "Create Shipment" and "Track Shipment"
5. Add pincode serviceability check on product page (AJAX — "Check delivery to your pincode")

---

## PHASE 9: SEEDERS

Create DatabaseSeeder that seeds:

1. **Admin user**: name = "Admin", email = "admin@himashva.com", password = "password", is_admin = true
2. **Categories** (with proper slugs): All Festive Candles, Urli Candles, Glass Jar Candles, Concrete Base Candles, Wooden Base Candles, Handmade Soap, Wax Sachet, Wax Melts, Tealights Candles, Tin Jars Candles, Coconut Shell Candles, Bouquet Candles, Hampers & Combo, Cake & Dessert Candle, Baby Shower, Pillar Candles, Figure Candle
3. **Demo products** (8-10 products with realistic names, descriptions, prices in ₹, sale prices, assigned to categories)
4. **Banners** (2-3 hero banners with placeholder images)
5. **Site Settings** with all default values:
   - site_name = Himashva
   - tagline = Handcrafted Candles & Home Fragrances
   - contact_email = info@himashva.com
   - contact_phone = +91 XXXXXXXXXX
   - whatsapp_number = 91XXXXXXXXXX
   - address = India
   - currency = INR
   - currency_symbol = ₹
   - free_shipping_threshold = 999
   - flat_shipping_rate = 49
   - min_order_amount = 499
   - social_instagram, social_facebook, social_pinterest, social_youtube = # (placeholder)
   - razorpay_key_id, razorpay_key_secret = (empty — admin fills later)
   - shiprocket_email, shiprocket_password = (empty — admin fills later)
   - meta_title = Himashva — Handcrafted Candles & Home Fragrances
   - meta_description = Shop handcrafted soy wax candles, wax melts, handmade soaps and gift hampers. 100% natural, eco-friendly.
6. **Pages**: About Us, Privacy Policy, Terms & Conditions, Shipping Policy, Cancellation & Refund — with placeholder content
7. **Coupons**: WELCOME10 (10% off, max ₹100), FIRST50 (₹50 off, min order ₹499)

---

## PHASE 10: ROUTES STRUCTURE

```php
// Public storefront
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/category/{slug}', [ShopController::class, 'category'])->name('category.show');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/search', [ShopController::class, 'search'])->name('search');

// Cart (works for guests and logged-in)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.applyCoupon');
Route::post('/cart/remove-coupon', [CartController::class, 'removeCoupon'])->name('cart.removeCoupon');

// Wishlist (AJAX)
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

// Checkout
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])->name('checkout.placeOrder');
Route::get('/order/success/{orderNumber}', [CheckoutController::class, 'success'])->name('order.success');

// Payment
Route::post('/payment/razorpay/create', [PaymentController::class, 'createRazorpayOrder']);
Route::post('/payment/razorpay/verify', [PaymentController::class, 'verifyPayment']);

// Track order (public)
Route::get('/track-order', [OrderTrackController::class, 'form'])->name('track.form');
Route::post('/track-order', [OrderTrackController::class, 'track'])->name('track.submit');

// Newsletter
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

// Contact
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// Chatbot
Route::post('/chatbot/message', [ChatbotController::class, 'respond'])->name('chatbot.respond');

// Static pages
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');

// Pincode check
Route::post('/check-pincode', [ShippingController::class, 'checkPincode'])->name('check.pincode');

// Customer account (auth required)
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::put('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{orderNumber}', [AccountController::class, 'orderDetail'])->name('orders.show');
    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{id}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::get('/reviews', [AccountController::class, 'reviews'])->name('reviews');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

// Auth routes (Laravel Breeze)
require __DIR__.'/auth.php';
```

---

## PHASE 11: SERVICES & HELPERS

### CartService
- `getItems()` — returns cart items (from session for guests, from cart_items table for logged-in)
- `add($productId, $quantity, $variantId = null)`
- `update($itemId, $quantity)`
- `remove($itemId)`
- `getSubtotal()`
- `getCount()`
- `clear()`
- `mergeCarts()` — when a guest logs in, merge session cart into DB cart

### CouponService
- `validate($code, $subtotal, $userId = null)` — returns coupon or validation error
- `calculateDiscount($coupon, $subtotal)` — returns discount amount
- `incrementUsage($couponId)`

### OrderService
- `createOrder($cartItems, $addressData, $paymentMethod, $coupon = null)`
- `updateStatus($orderId, $newStatus, $comment = null, $adminId = null)`
- `generateInvoice($order)` — returns PDF

### SettingsHelper
- Create a global `settings($key, $default = null)` helper function
- Cache settings for performance
- Use this everywhere: `settings('whatsapp_number')`, `settings('free_shipping_threshold')`

---

## PHASE 12: FINAL TOUCHES

1. **SEO**: Add meta tags to all pages (title, description, og:image). Create a sitemap route.
2. **Breadcrumbs**: Home > Category > Product on product pages
3. **Flash messages**: Success/error toasts on all actions (add to cart, wishlist, coupon apply, etc.)
4. **Loading states**: Show spinner on AJAX actions
5. **Empty states**: Nice empty state messages for empty cart, no search results, no orders, no wishlist items
6. **404 page**: Custom branded 404 page
7. **Favicon**: Set a candle emoji or simple icon
8. **Mobile responsive**: Test all pages on mobile viewport. Hamburger menu, stacked grids, full-width buttons.
9. **WhatsApp floating button**: Fixed position, bottom-right corner, green WhatsApp icon, always visible, links to `https://wa.me/{settings('whatsapp_number')}`
10. **Back to top button**: Shows on scroll, smooth scroll to top
11. **Image placeholders**: Use colored gradient placeholders (like in the prototype) for products without images
12. **Currency formatting**: Always show ₹ symbol, Indian number formatting (1,999.00)
13. **Performance**: Eager load relationships to avoid N+1, cache categories and settings, lazy load images

---

## VERIFICATION CHECKLIST

After building everything, verify:
- [ ] `php artisan migrate` runs without errors
- [ ] `php artisan db:seed` runs without errors
- [ ] Homepage loads at http://localhost/himashva/public
- [ ] Admin panel loads at http://localhost/himashva/public/admin
- [ ] Admin can login with admin@himashva.com / password
- [ ] Admin can create/edit products with images
- [ ] Admin can manage orders, categories, coupons, banners
- [ ] Customer data page has filters and Excel export
- [ ] Shop page shows products with filters
- [ ] Product detail page shows gallery, variants, reviews
- [ ] Cart add/update/remove works
- [ ] Coupon apply works
- [ ] Checkout flow works (at least COD)
- [ ] Customer can register, login, view orders
- [ ] Wishlist toggle works
- [ ] Chatbot responds to common questions
- [ ] WhatsApp button visible and links correctly
- [ ] Contact form submits
- [ ] Newsletter subscribe works
- [ ] Mobile responsive on all pages
- [ ] Search works

---

## START BUILDING NOW

Begin with Phase 1 and work through each phase sequentially. Do not skip any phase. Do not stop to ask questions — make reasonable decisions and keep building. If a package installation fails, try an alternative approach. If something is unclear, implement the most logical solution.

The goal is a fully functional, beautiful, world-class e-commerce website when you finish.

**START NOW.**