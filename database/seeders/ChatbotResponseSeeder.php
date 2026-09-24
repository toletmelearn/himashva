<?php

namespace Database\Seeders;

use App\Models\ChatbotResponse;
use Illuminate\Database\Seeder;

class ChatbotResponseSeeder extends Seeder
{
    /**
     * Default Q&A pairs, migrated from the previously hardcoded array in
     * ChatbotController. Listed highest priority first; sort_order is
     * assigned in descending steps so admins can slot new entries in between.
     *
     * @return array<int, array{category: string, keywords: array<int, string>, response: string}>
     */
    protected function defaults(): array
    {
        return [
            ['category' => 'shipping', 'keywords' => ['shipping', 'delivery charge', 'delivery cost'], 'response' => 'Free shipping on orders above ₹{free_shipping_threshold}. Flat ₹{flat_shipping_rate} for orders below that.'],
            ['category' => 'payment', 'keywords' => ['payment', 'pay', 'cod', 'upi', 'card'], 'response' => 'We accept COD, UPI, and Razorpay (Credit/Debit cards, Net Banking, Wallets).'],
            ['category' => 'tracking', 'keywords' => ['track', 'tracking', 'where is my order'], 'response' => 'Visit /track-order and enter your order number and email to see live status.'],
            ['category' => 'returns', 'keywords' => ['return', 'refund', 'exchange'], 'response' => 'We offer 7-day returns on undamaged products. See our Cancellation & Refund policy page for details.'],
            ['category' => 'general', 'keywords' => ['contact', 'phone', 'email', 'reach you'], 'response' => 'WhatsApp: {whatsapp}, Email: {email}, or use our Contact page.'],
            ['category' => 'general', 'keywords' => ['cancel'], 'response' => 'You can cancel an order before it ships from your Account > Orders page, or contact us directly.'],
            ['category' => 'products', 'keywords' => ['candle', 'material', 'wax', 'ingredient'], 'response' => 'Our candles are hand-poured using 100% natural soy wax with cotton wicks and premium fragrance oils.'],
            ['category' => 'products', 'keywords' => ['burn time', 'how long'], 'response' => 'Most of our candles have a burn time of 40-45 hours depending on size.'],
            ['category' => 'general', 'keywords' => ['coupon', 'discount', 'offer', 'promo code'], 'response' => 'Use code WELCOME10 for 10% off your first order, or check our homepage for active offers.'],
            ['category' => 'general', 'keywords' => ['order status', 'confirm', 'confirmation'], 'response' => "You'll receive an order confirmation by email right after checkout. You can also check status under Account > Orders."],
            ['category' => 'products', 'keywords' => ['size', 'weight', 'dimension'], 'response' => 'Product size and weight details are listed under the "Additional Info" tab on each product page.'],
            ['category' => 'products', 'keywords' => ['gift', 'hamper', 'combo'], 'response' => 'Check out our Hampers & Combo category for curated gift sets, perfect for any occasion!'],
            ['category' => 'shipping', 'keywords' => ['international', 'outside india', 'abroad'], 'response' => 'Currently we only ship within India. International shipping is coming soon!'],
            ['category' => 'general', 'keywords' => ['bulk', 'wholesale', 'corporate'], 'response' => 'For bulk or corporate orders, please WhatsApp us at {whatsapp} for special pricing.'],
            ['category' => 'general', 'keywords' => ['account', 'login', 'sign up', 'register'], 'response' => 'You can create an account or log in from the account icon at the top right of any page.'],
            ['category' => 'general', 'keywords' => ['wishlist', 'save for later'], 'response' => 'Click the heart icon on any product to add it to your wishlist. View it anytime under Account > Wishlist.'],
            ['category' => 'returns', 'keywords' => ['damaged', 'broken', 'wrong item'], 'response' => 'Sorry about that! Please contact us within 48 hours of delivery with photos and we will arrange a replacement.'],
            ['category' => 'general', 'keywords' => ['store', 'shop location', 'address'], 'response' => 'We are an online-only store currently, shipping across India from our workshop.'],
            ['category' => 'products', 'keywords' => ['scent', 'fragrance', 'smell'], 'response' => 'We offer a wide range of fragrances — floral, woody, citrus, and festive spice blends. Check individual product pages for scent notes.'],
            ['category' => 'greeting', 'keywords' => ['hello', 'hi', 'hey'], 'response' => 'Hi there! How can I help you today — shipping, orders, returns, or something else?'],
        ];
    }

    public function run(): void
    {
        $entries = $this->defaults();
        $count = count($entries);

        foreach ($entries as $i => $entry) {
            ChatbotResponse::updateOrCreate(
                ['response' => $entry['response']],
                [
                    'category' => $entry['category'],
                    'keywords' => $entry['keywords'],
                    'is_active' => true,
                    'sort_order' => ($count - $i) * 10,
                ]
            );
        }
    }
}
