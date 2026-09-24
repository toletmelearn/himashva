<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Page;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(ChatbotResponseSeeder::class);
        $this->call(PaymentGatewaySeeder::class);
        $this->call(ShippingProviderSeeder::class);

        $admin = User::updateOrCreate(
            ['email' => 'admin@himashva.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('super_admin');

        $categoryNames = [
            'All Festive Candles', 'Urli Candles', 'Glass Jar Candles', 'Concrete Base Candles',
            'Wooden Base Candles', 'Handmade Soap', 'Wax Sachet', 'Wax Melts', 'Tealights Candles',
            'Tin Jars Candles', 'Coconut Shell Candles', 'Bouquet Candles', 'Hampers & Combo',
            'Cake & Dessert Candle', 'Baby Shower', 'Pillar Candles', 'Figure Candle',
        ];

        $categories = collect($categoryNames)->map(fn ($name, $i) => Category::updateOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'sort_order' => $i, 'is_active' => true]
        ));

        $products = [
            ['name' => 'Rose Petal Jar Candle', 'category' => 'Glass Jar Candles', 'price' => 599, 'sale' => 449, 'desc' => 'Hand-poured soy wax candle infused with rose fragrance, perfect for gifting.'],
            ['name' => 'Sandalwood Concrete Candle', 'category' => 'Concrete Base Candles', 'price' => 799, 'sale' => null, 'desc' => 'Earthy sandalwood scent poured into a minimalist concrete vessel.'],
            ['name' => 'Diwali Urli Candle Set', 'category' => 'Urli Candles', 'price' => 999, 'sale' => 749, 'desc' => 'Festive floating urli candles for Diwali decor, set of 4.'],
            ['name' => 'Lavender Wooden Base Candle', 'category' => 'Wooden Base Candles', 'price' => 649, 'sale' => null, 'desc' => 'Calming lavender candle on a reclaimed wood base.'],
            ['name' => 'Handmade Oatmeal Soap Bar', 'category' => 'Handmade Soap', 'price' => 249, 'sale' => 199, 'desc' => 'Gentle exfoliating oatmeal soap bar, cold-processed.'],
            ['name' => 'Vanilla Wax Melt Pack', 'category' => 'Wax Melts', 'price' => 299, 'sale' => null, 'desc' => 'Set of 6 vanilla-scented wax melts for your warmer.'],
            ['name' => 'Coconut Shell Tropical Candle', 'category' => 'Coconut Shell Candles', 'price' => 549, 'sale' => 429, 'desc' => 'Tropical coconut-lime scent in an eco-friendly coconut shell.'],
            ['name' => 'Rose Gold Tealight Set', 'category' => 'Tealights Candles', 'price' => 349, 'sale' => null, 'desc' => 'Box of 12 unscented rose-gold tealights.'],
            ['name' => 'Cinnamon Tin Jar Candle', 'category' => 'Tin Jars Candles', 'price' => 399, 'sale' => 329, 'desc' => 'Warm cinnamon-spice candle in a reusable tin jar.'],
            ['name' => 'Wedding Gift Hamper', 'category' => 'Hampers & Combo', 'price' => 1499, 'sale' => 1199, 'desc' => 'Curated hamper with 3 candles, a soap bar, and wax sachets.'],
        ];

        foreach ($products as $i => $p) {
            $category = $categories->firstWhere('name', $p['category']);
            $product = Product::updateOrCreate(
                ['sku' => 'HMV-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'category_id' => $category->id,
                    'name' => $p['name'],
                    'slug' => Str::slug($p['name']),
                    'short_description' => Str::limit($p['desc'], 80),
                    'description' => $p['desc'].' Made with 100% natural soy wax, hand-poured in small batches. Burn time approximately 40-45 hours.',
                    'price' => $p['price'],
                    'sale_price' => $p['sale'],
                    'stock' => rand(10, 100),
                    'is_active' => true,
                    'is_featured' => $i < 4,
                    'is_bestseller' => $i % 3 === 0,
                    'is_new' => $i >= 8,
                    'meta_title' => $p['name'].' | Himashva',
                    'meta_description' => Str::limit($p['desc'], 150),
                ]
            );

            $product->images()->firstOrCreate([
                'image_path' => 'placeholder.jpg',
                'is_primary' => true,
            ]);
        }

        $banners = [
            ['title' => 'Festive Candle Collection', 'subtitle' => 'Handcrafted with love', 'position' => 'hero', 'button_text' => 'Shop Now'],
            ['title' => 'New Arrivals', 'subtitle' => 'Fresh scents for every season', 'position' => 'hero', 'button_text' => 'Explore'],
            ['title' => 'Flat 20% Off Hampers', 'subtitle' => 'Limited time offer', 'position' => 'promo', 'button_text' => 'Grab Deal'],
        ];

        foreach ($banners as $i => $b) {
            Banner::updateOrCreate(
                ['title' => $b['title']],
                array_merge($b, ['image_path' => 'placeholder.jpg', 'sort_order' => $i, 'is_active' => true])
            );
        }

        $settings = [
            'site_name' => ['Himashva', 'general'],
            'tagline' => ['Handcrafted Candles & Home Fragrances', 'general'],
            'contact_email' => ['info@himashva.com', 'general'],
            'contact_phone' => ['+91 9876543210', 'general'],
            'whatsapp_number' => ['919876543210', 'general'],
            'address' => ['India', 'general'],
            'announcement_text' => ['Free shipping on orders above ₹999', 'general'],
            'currency' => ['INR', 'general'],
            'currency_symbol' => ['₹', 'general'],
            'free_shipping_threshold' => ['999', 'shipping'],
            'flat_shipping_rate' => ['49', 'shipping'],
            'min_order_amount' => ['499', 'shipping'],
            'social_instagram' => ['#', 'social'],
            'social_facebook' => ['#', 'social'],
            'social_pinterest' => ['#', 'social'],
            'social_youtube' => ['#', 'social'],
            'razorpay_key_id' => ['', 'payment'],
            'razorpay_key_secret' => ['', 'payment'],
            'shiprocket_email' => ['', 'shipping'],
            'shiprocket_password' => ['', 'shipping'],
            'meta_title' => ['Himashva — Handcrafted Candles & Home Fragrances', 'seo'],
            'meta_description' => ['Shop handcrafted soy wax candles, wax melts, handmade soaps and gift hampers. 100% natural, eco-friendly.', 'seo'],
            'send_order_emails' => ['1', 'email'],
            'abandoned_cart_recovery_enabled' => ['0', 'marketing'],
        ];

        foreach ($settings as $key => [$value, $group]) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        }

        $pages = [
            'About Us' => 'Himashva is a handcrafted candle and home fragrance brand rooted in Indian craftsmanship. Every candle is hand-poured using 100% natural soy wax, free from harmful chemicals, and designed to bring warmth to your home.',
            'Privacy Policy' => $this->privacyPolicyContent(),
            'Terms & Conditions' => $this->termsConditionsContent(),
            'Shipping Policy' => $this->shippingPolicyContent(),
            'Cancellation & Refund' => $this->cancellationRefundContent(),
        ];

        foreach ($pages as $title => $content) {
            Page::updateOrCreate(
                ['slug' => Str::slug($title)],
                ['title' => $title, 'content' => $content, 'is_active' => true]
            );
        }

        Coupon::updateOrCreate(['code' => 'WELCOME10'], [
            'type' => 'percentage', 'value' => 10, 'max_discount_amount' => 100,
            'per_user_limit' => 1, 'is_active' => true,
        ]);

        Coupon::updateOrCreate(['code' => 'FIRST50'], [
            'type' => 'fixed', 'value' => 50, 'min_order_amount' => 499,
            'per_user_limit' => 1, 'is_active' => true,
        ]);
    }

    private function privacyPolicyContent(): string
    {
        $updated = now()->format('F j, Y');

        return <<<HTML
        <p><strong>Last updated: {$updated}</strong></p>

        <h2>Who We Are</h2>
        <p>Himashva ("we", "us", "our") is a handcrafted candle and home fragrance brand based in India. This Privacy Policy explains how we collect, use, and protect your personal information when you visit our website or place an order with us.</p>

        <h2>Information We Collect</h2>
        <p>When you browse our website, create an account, or place an order, we may collect:</p>
        <ul>
            <li>Contact details such as your name, email address, and phone number</li>
            <li>Shipping and billing address</li>
            <li>Payment information, processed securely through our payment partners (we never store your full card details)</li>
            <li>Order history and preferences</li>
            <li>Browsing behaviour on our site, via cookies and analytics tools</li>
        </ul>

        <h2>Why We Collect It</h2>
        <p>We use your information to process and deliver your orders, communicate order updates, respond to customer support requests, and improve our products and website experience. With your consent, we may also send you marketing emails about new products and offers.</p>

        <h2>Cookies and Tracking</h2>
        <p>Our website uses cookies to keep your cart and session active and to understand how visitors use our site through analytics. You can disable cookies in your browser settings, though some features of the site may not work correctly without them.</p>

        <h2>Sharing Your Information</h2>
        <p>We do not sell your personal data. We share information only with trusted third parties who help us run our business, including:</p>
        <ul>
            <li>Payment gateways (such as Razorpay) to process your payment securely</li>
            <li>Shipping and courier partners to deliver your order</li>
            <li>Email/SMS service providers to send order and marketing communications</li>
        </ul>
        <p>These partners are only given the information necessary to perform their service and are not permitted to use it for any other purpose.</p>

        <h2>Data Security</h2>
        <p>We use industry-standard security measures, including encrypted checkout and secure servers, to protect your personal information from unauthorised access, alteration, or disclosure.</p>

        <h2>Your Rights</h2>
        <p>You may request access to, correction of, or deletion of your personal data at any time by emailing us at <strong>{$this->settingValue('contact_email', 'info@himashva.com')}</strong>. You can also unsubscribe from marketing emails using the link in any email we send you.</p>

        <h2>Data Retention</h2>
        <p>We retain order-related information for as long as required for accounting, tax, and legal compliance purposes. Marketing data is retained until you unsubscribe or request deletion.</p>

        <h2>Children's Privacy</h2>
        <p>Our website is not intended for individuals under the age of 18, and we do not knowingly collect personal information from children.</p>

        <h2>Changes to This Policy</h2>
        <p>We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated "Last updated" date.</p>

        <h2>Contact Us</h2>
        <p>If you have any questions about this Privacy Policy, please contact us at <strong>{$this->settingValue('contact_email', 'info@himashva.com')}</strong> or call <strong>{$this->settingValue('contact_phone', '+91 9876543210')}</strong>.</p>
        HTML;
    }

    private function termsConditionsContent(): string
    {
        return <<<'HTML'
        <h2>Acceptance of Terms</h2>
        <p>By accessing or using the Himashva website, you agree to be bound by these Terms &amp; Conditions. If you do not agree with any part of these terms, please do not use our website.</p>

        <h2>Use of Our Website</h2>
        <p>You agree to use this website only for lawful purposes and in a way that does not infringe the rights of, or restrict or inhibit the use and enjoyment of, this site by any third party.</p>

        <h2>Account Registration</h2>
        <p>When you create an account with us, you are responsible for maintaining the confidentiality of your login details and for all activities that occur under your account. Please notify us immediately of any unauthorised use of your account.</p>

        <h2>Products and Pricing</h2>
        <p>All prices are listed in Indian Rupees (INR) and are inclusive of applicable GST. Prices are subject to change without prior notice. Product images are representative; as our candles and decor items are handcrafted, slight variations in colour, texture, or finish may occur — this is a natural feature of handmade products, not a defect.</p>

        <h2>Order Placement and Confirmation</h2>
        <p>Placing an order on our website constitutes an offer to purchase. We reserve the right to accept or decline any order at our discretion. An order is confirmed only after payment is successfully processed (or, for Cash on Delivery orders, once the order is placed).</p>

        <h2>Payment Terms</h2>
        <p>We accept Cash on Delivery (COD) and online payments via Razorpay, including UPI, credit/debit cards, netbanking, and wallets. All online payments are processed through secure, encrypted payment gateways.</p>

        <h2>Shipping and Delivery</h2>
        <p>We currently ship across India only. Standard delivery takes 4-7 business days after dispatch. Please see our Shipping Policy for full details.</p>

        <h2>Cancellation Policy</h2>
        <p>Orders can be cancelled free of charge before they are shipped. Once an order has been dispatched, it cannot be cancelled; you may instead request a return after delivery in accordance with our Cancellation &amp; Refund Policy.</p>

        <h2>Returns and Refunds</h2>
        <p>Please refer to our Cancellation &amp; Refund Policy for details on returns, exchanges, and refunds.</p>

        <h2>Intellectual Property</h2>
        <p>All content on this website, including text, images, logos, and branding, is the property of Himashva and may not be reproduced or used without our prior written consent.</p>

        <h2>Limitation of Liability</h2>
        <p>Himashva shall not be liable for any indirect, incidental, or consequential damages arising from the use of our website or products, to the fullest extent permitted by law.</p>

        <h2>Governing Law</h2>
        <p>These Terms &amp; Conditions are governed by the laws of India, and any disputes shall be subject to the exclusive jurisdiction of the courts of India.</p>

        <h2>Severability</h2>
        <p>If any provision of these Terms is found to be unenforceable, the remaining provisions will continue in full force and effect.</p>

        <h2>Contact Us</h2>
        <p>For any questions regarding these Terms &amp; Conditions, please reach out to us via our contact page.</p>
        HTML;
    }

    private function cancellationRefundContent(): string
    {
        return <<<'HTML'
        <h2>Cancellation Before Shipment</h2>
        <p>You may cancel your order free of charge within 12 hours of placing it, provided it has not yet been shipped. Please contact our customer service team as soon as possible to request a cancellation, and we will process a full refund.</p>

        <h2>Cancellation After Shipment</h2>
        <p>Once an order has been shipped, it cannot be cancelled. Please wait for the order to be delivered and then initiate a return as described below.</p>

        <h2>Return Eligibility</h2>
        <p>We accept returns within 7 days of delivery, provided the product is unused, undamaged, and returned in its original packaging with all tags and accessories intact.</p>

        <h2>Items Not Eligible for Return</h2>
        <ul>
            <li>Used or burned candles</li>
            <li>Custom or personalised orders</li>
            <li>Items purchased during clearance or final sale</li>
        </ul>

        <h2>How to Initiate a Return</h2>
        <p>Log in to your account and go to Account &gt; Orders to request a return, or contact our customer service team with your order number and reason for return.</p>

        <h2>Return Shipping</h2>
        <p>Return shipping costs are borne by the customer, unless the item received was damaged, defective, or incorrect, in which case we cover the cost of return pickup.</p>

        <h2>Inspection and Approval</h2>
        <p>Once we receive your returned item, our team will inspect it within 3-5 business days and notify you of the approval or rejection of your return.</p>

        <h2>Refund Process</h2>
        <p>Approved refunds for online payments are credited to your original payment method within 5-7 business days of approval.</p>

        <h2>Refunds for COD Orders</h2>
        <p>Since Cash on Delivery orders have no online payment method to refund to, approved refunds are processed via bank transfer. You will be asked to share your bank account details for this purpose.</p>

        <h2>Damaged or Defective Items</h2>
        <p>If you receive a damaged or defective item, please report it within 48 hours of delivery along with photos of the product and packaging. We will arrange a free replacement or a full refund.</p>

        <h2>Wrong Item Received</h2>
        <p>If you receive the wrong item, please report it within 48 hours of delivery. We will arrange a free return pickup and send you the correct item or a full refund, as you prefer.</p>
        HTML;
    }

    private function shippingPolicyContent(): string
    {
        return <<<'HTML'
        <h2>Shipping Coverage</h2>
        <p>We currently ship across India only. International shipping is not available at this time.</p>

        <h2>Shipping Charges</h2>
        <p>Shipping is free on orders above ₹999. Orders below ₹999 are charged a flat shipping fee of ₹49.</p>

        <h2>Processing Time</h2>
        <p>Orders are processed and handed over to our courier partner within 1-2 business days of order confirmation.</p>

        <h2>Delivery Time</h2>
        <p>Once dispatched, orders typically arrive within 4-7 business days, depending on your delivery location.</p>

        <h2>Shipping Partners</h2>
        <p>We work with reliable courier partners to ensure your order reaches you safely and on time.</p>

        <h2>Order Tracking</h2>
        <p>Once your order ships, we will share tracking details via email and SMS. You can also track your order anytime from the Track Order page.</p>

        <h2>Undeliverable Orders</h2>
        <p>Our courier partners will make up to 3 delivery attempts. If delivery is unsuccessful after 3 attempts, the order will be returned to us and our team will contact you to arrange redelivery or a refund.</p>

        <h2>Packaging</h2>
        <p>Every candle is packaged with care using bubble wrap, rigid boxes, and fragile labelling to ensure it reaches you in perfect condition.</p>

        <h2>PO Boxes and APO/FPO Addresses</h2>
        <p>We are currently unable to deliver to PO Boxes or APO/FPO addresses.</p>

        <h2>Multiple Items in One Order</h2>
        <p>Where possible, all items in a single order are shipped together in one package. In some cases, items may be shipped separately.</p>

        <h2>Festive and Peak Season Delays</h2>
        <p>During festive seasons such as Diwali and Christmas, please allow an additional 2-3 business days for delivery due to high order volumes.</p>

        <h2>Contact Us</h2>
        <p>For any shipping-related queries, please reach out to our customer service team via our contact page.</p>
        HTML;
    }

    private function settingValue(string $key, string $default): string
    {
        return SiteSetting::where('key', $key)->value('value') ?: $default;
    }
}
