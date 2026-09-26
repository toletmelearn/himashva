# Himashva

Himashva is a full-featured e-commerce platform built on Laravel 13, with a Filament-powered admin panel and Razorpay payments.

## Features

**Storefront**
- Product catalog with categories, brands, variants, images/videos, and size guides
- Search, product comparison, wishlist, and cart with coupon support
- Guest and registered checkout, Razorpay payment integration
- Order tracking, cancellations, and returns
- Reviews with media and helpfulness voting
- Newsletter signup, contact form, and an on-site chatbot
- Sitemap generation and a health check endpoint

**Admin (Filament)**
- Products, categories, brands, coupons, banners, pages, and size guides
- Orders, returns, inventory movements, and shipping providers
- Customers, abandoned carts, reviews, and newsletter subscribers
- Chatbot conversations/responses and unanswered-question tracking
- Audit log, site settings, and a conversion funnel dashboard

## Tech Stack

- **Backend:** PHP 8.3, Laravel 13
- **Admin Panel:** Filament 3
- **Frontend:** Blade, Tailwind CSS, Alpine.js, Vite
- **Payments:** Razorpay
- **Other:** Spatie Laravel Permission, Laravel DomPDF, Intervention Image, Maatwebsite Excel

## Requirements

- PHP >= 8.3
- Composer
- Node.js & npm
- A database (SQLite by default; MySQL/PostgreSQL also supported)

## Getting Started

```bash
git clone https://github.com/toletmelearn/himashva.git
cd himashva

composer install
npm install

cp .env.example .env
php artisan key:generate

# configure DB_* and RAZORPAY_* in .env, then:
php artisan migrate --seed

npm run build
php artisan serve
```

For local development with hot-reloading and the queue/log workers running together:

```bash
composer run dev
```

## Testing

```bash
php artisan test
```

## Admin Access

The Filament admin panel is available at `/admin`. Create an admin user via a seeder or `php artisan tinker`, and grant the appropriate role via Spatie Laravel Permission.

## License

This project is proprietary and not licensed for public use.
