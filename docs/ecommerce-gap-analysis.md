# Aroma House Online — E-Commerce Capability Gap Analysis

Generated: 2026-09-21
Scope: Read-only audit. No code was changed to produce this document.
Method: Direct inspection of `database/migrations`, `app/Models`, `app/Http/Controllers`, `app/Filament`, `routes/web.php`, `tests/Feature`, `composer.json`, `package.json`, `.env.example`. No live database was queried (DB_CONNECTION=sqlite per `.env.example`, driver not verified against a running DB).

Priority key: P0 = foundational, P1 = important, P2 = growth, P3 = future.
Status key: IMPLEMENTED / PARTIAL / MISSING / REQUIRES DECISION.

## 1. Current Architecture Summary (VERIFIED FROM CODE)

- Laravel 13 (`composer.json`: `laravel/framework: ^13.17`), PHP 8.3+, Blade + Alpine.js + Tailwind v4, Vite build.
- Admin panel: **Filament v3** (`filament/filament: ^3.0`), not a hand-rolled admin — this is a major asset, not a gap. 20 Filament Resources/Pages/Widgets already exist under `app/Filament`.
- Auth: Laravel Breeze (`laravel/breeze`), `User.is_admin` boolean gate via `FilamentUser::canAccessPanel()`. **No role/permission granularity** — binary admin/non-admin only.
- Payments: Razorpay SDK (`razorpay/razorpay`) wired through `PaymentController` + `Payment` model. COD supported as a `payment_method` enum value on `Order`.
- Excel export capability present (`maatwebsite/excel`) but **not yet wired to any controller/resource** found in this scan — verify usage before assuming export features exist.
- PDF capability present (`barryvdh/laravel-dompdf`) and IS wired: `OrderInvoiceController` generates invoices.
- Image processing: `intervention/image-laravel` present.
- 23 tables, 22 models, 23 storefront routes (`routes/web.php`), 9 Filament Resources, 6 Filament dashboard Widgets, 2 Feature test files covering admin panel smoke tests + storefront.
- No `app/Services` files were opened directly in this pass beyond `CartService`, `CouponService`, `OrderService`, `CartService` referenced by controllers — **NEEDS CONFIRMATION**: full service layer contents not inventoried in this pass; recommend a follow-up focused read of `app/Services/*` before Phase 1 implementation.

## 2. Feature Gap Matrix

| Area | Capability | Status | Evidence | DB Support | Admin UI | Frontend | Analytics | Priority | Complexity | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| Catalogue | Products, categories (nested), brands | IMPLEMENTED | `products`, `categories` (self-referencing `parent_id`), `brands` tables; `ProductResource`, `CategoryResource`, `BrandResource` | Yes | Yes | Yes | No | — | — | Solid foundation |
| Catalogue | Product variants | PARTIAL | `product_variants` table + `ProductVariant` model + `VariantsRelationManager` | Yes | Yes | NOT VERIFIED (frontend variant selection not inspected) | No | P1 | M | Single-axis variants (name/sku/price/stock) — no attribute×value combination matrix (e.g. Size×Color) |
| Catalogue | Attributes (structured) | PARTIAL | `product_attributes` table is a flat `attribute_name`/`attribute_value` pair per product, no reusable attribute/value catalog, no filtering support implied | Weak | No dedicated resource found | NOT VERIFIED | No | P2 | M | Not attribute-driven variant generation; effectively free-text specs |
| Catalogue | Collections/Tags | MISSING | No table, no model | No | No | No | No | P2 | S | Needed for merchandising ("Trending", seasonal sets) beyond boolean flags |
| Catalogue | Product lifecycle states (Draft/Pending/Active/Archived/Discontinued) | MISSING | Only `is_active` boolean on `products` | No | No | No | No | P1 | S | Binary active/inactive only; no draft workflow, no "out of stock" as a distinct state (inferred from `stock` at runtime only) |
| Catalogue | SEO fields (product/category) | PARTIAL | `products.meta_title/meta_description`, `pages.meta_title/meta_description` exist; `categories` has NO meta fields; no OG fields, no canonical, no structured data confirmed | Partial | Partial | NOT VERIFIED | No | P1 | S | Category SEO metadata gap is a quick, safe addition |
| Catalogue | Catalogue health / data-quality checks | MISSING | No missing-image/missing-SEO/duplicate-SKU detection found | No | No | N/A | No | P1 | M | High ROI, low risk — read-only reporting feature |
| Inventory | Stock quantity per product/variant | IMPLEMENTED | `products.stock`, `product_variants.stock`, `low_stock_threshold` | Yes | Partial (`LowStockProducts` widget exists) | No customer-facing "X left" verified | No | — | — | Single stock number only |
| Inventory | Inventory ledger / stock movements | MISSING | No `inventory_movements` or equivalent table; no audit trail for stock changes | No | No | No | No | P0 | M | Stock changes today are presumably direct column updates with no history — cannot answer "why did stock change" |
| Inventory | Reserved/available stock distinction | MISSING | Single `stock` column; no reservation on cart-add or checkout-pending | No | No | No | No | P1 | M | Risk: overselling under concurrent checkouts (no reservation, no locking observed in `CheckoutController`/`OrderService` in this pass) |
| Inventory | Multi-warehouse | MISSING | Not present; architecture does not currently preclude adding it later | No | No | No | No | P3 | L | Correctly deferred per current business scale |
| Orders | Order lifecycle w/ status enum | IMPLEMENTED | `orders.order_status` enum (`pending…refunded`), `order_status_history` table tracks transitions with actor (`changed_by`) | Yes | Yes | Partial (`OrderTrackController`) | No | — | — | Good foundation; enum is DB-level, not a dedicated state machine class — verify transition validation exists in `OrderService` (NOT VERIFIED) |
| Orders | Order items, snapshotted pricing | IMPLEMENTED | `order_items` stores `product_name`, `sku`, `unit_price` at time of sale (correct pattern — protects historical orders from later product edits) | Yes | Yes | — | No | — | — | Good practice already in place |
| Orders | Invoice/packing slip generation | PARTIAL | `OrderInvoiceController` (PDF invoice) exists; no packing slip / shipping label found | Yes | Partial | N/A | No | P2 | S | |
| Orders | Bulk order actions/export | MISSING | No CSV/Excel export wired to `OrderResource` in this scan (maatwebsite/excel installed but unused here) | N/A | No | N/A | No | P1 | S | Package already installed — cheap win |
| Customers | Address book | IMPLEMENTED | `addresses` table, `AddressController`, `AddressesRelationManager` | Yes | Yes | Yes | No | — | — | |
| Customers | Customer profile/CRM depth (LTV, segments) | MISSING | `User` model has `totalSpent`/`orderCount` computed attributes only; no segmentation, no "at risk"/"COD heavy" classification | Partial | Partial (`UserResource` + `OrdersRelationManager`) | No | No | P1 | M | Good starting primitives (`totalSpent`, `orderCount`) to build segmentation on top of |
| Customers | Wishlist | IMPLEMENTED | `wishlists` table, unique(user,product), `WishlistController` | Yes | No dedicated resource (acceptable — customer-facing feature) | Yes | No | — | — | |
| Customers | Support ticketing | PARTIAL | `contact_messages` table + admin reply/read flags is a lightweight contact form, not a ticketing system (no status/priority/assignment) | Partial | Yes | Yes | No | P2 | M | |
| Analytics | Event tracking (product views, search, cart, checkout funnel) | PARTIAL | `customer_activities` table exists (`activity_type`, polymorphic `subject_type/subject_id`, `data` JSON) — **generic event bus already exists** but NOT VERIFIED which events are actually being recorded (no controller call to log activity was seen in this pass) | Yes (schema) | No | NOT VERIFIED | Weak | P0 | M | This is the single highest-leverage existing asset for Phase AI (Analytics Data Architecture) — confirm what's actually written to it before building new tables |
| Analytics | Search analytics (queries, zero-result, conversion) | MISSING | `ShopController::search` exists but no `search_events`/query-logging table found | No | No | N/A | No | P1 | M | |
| Analytics | Admin executive dashboard | PARTIAL | `OrderStatsOverview`, `RevenueChart`, `OrderStatusChart`, `TopProducts`, `RecentOrders`, `LowStockProducts` widgets exist — covers orders-today/week/month, revenue, new customers | Yes | Yes | N/A | — | — | — | Real, working dashboard — NOT a "hard-coded numbers" placeholder (verified: queries are live Eloquent aggregates) |
| Analytics | AOV, conversion rate, repeat-customer rate, cart abandonment | MISSING | Not present in any widget inspected | No | No | N/A | No | P1 | M | Needs cart/session-level tracking first (see Analytics event gap above) |
| Analytics | Action Centre (prioritized admin alerts) | MISSING | `LowStockProducts` widget is the only alert-like surface; no unified severity-ranked alert feed | Partial | Partial | N/A | No | P1 | M | |
| Marketing | Coupons | IMPLEMENTED | `coupons` table + `Coupon::isValid()`/`calculateDiscount()` business logic, `CouponResource`, wired into `CheckoutController`/cart | Yes | Yes | Yes | No | — | — | Percentage/fixed, min order, max discount, usage caps. **CORRECTION (verified 2026-09-21):** `per_user_limit` IS enforced — `CouponService::validate()` counts the user's paid/pending orders against the coupon (no separate `coupon_usages` table needed; a dedicated table was considered and rejected as unnecessary). Minor known edge case: a cancelled COD order left at `payment_status=pending` still counts toward the limit — low priority, not a missing feature. |
| Marketing | Banners/homepage merchandising | IMPLEMENTED | `banners` table w/ position, scheduling (`starts_at`/`ends_at`), `BannerResource` | Yes | Yes | NOT VERIFIED (Blade usage not inspected) | No | — | — | |
| Marketing | Campaign/promotion performance tracking | MISSING | No impressions/clicks/CTR tracking for banners or coupons | No | No | N/A | No | P2 | M | |
| Marketing | Recommendation engine | MISSING | No related-products/frequently-bought-together logic found | No | No | No | No | P2 | M | |
| Reviews | Ratings & reviews | IMPLEMENTED | `reviews` table (rating, approval workflow, admin reply, verified via nullable `order_id` link), `ReviewResource`, `ReviewController` | Yes | Yes | Yes | Partial | — | — | Product `avg_rating`/`review_count` denormalized columns exist — confirm they're kept in sync on review approve (NOT VERIFIED — check `Review`/`Product` observers) |
| Returns/Refunds | Structured returns workflow | MISSING | `orders.order_status` includes `returned`/`refunded` as terminal states but no `returns` table, no return reason taxonomy, no restocking logic | Partial (status enum only) | No | No | No | P0 | M | Biggest single operational gap for a real store — no way to process a return today beyond changing an order's status label |
| Payments | Payment transaction log | IMPLEMENTED | `payments` table separate from `orders`, stores gateway refs, raw response, signature | Yes | No dedicated resource (visible via order only, NOT VERIFIED) | N/A | No | — | — | Correct separation-of-concerns pattern already followed |
| Payments | Refund processing (gateway-side) | MISSING | No refund API call to Razorpay found; `payment_status` enum has `refunded` value but no verified code path sets it | Partial | No | No | No | P0 | M | |
| Shipping | Shipping charge/free-shipping threshold | IMPLEMENTED | `settings('free_shipping_threshold')`, `settings('flat_shipping_rate')` used in `CheckoutController` | Yes (settings) | Yes | Yes | No | — | — | Single flat rate — no zones |
| Shipping | Pincode serviceability check | IMPLEMENTED | `ShippingController::checkPincode`, `check-pincode` route | NOT VERIFIED | NOT VERIFIED | Yes | No | — | — | |
| Shipping | Carrier/tracking integration | PARTIAL | `orders.tracking_number/tracking_url/shipping_partner` are free-text fields set manually; no carrier API, no shipment-event table | Partial | Manual entry only | Yes (track page) | No | P2 | M | |
| Finance | Revenue/margin reporting | PARTIAL | Order totals/discounts/shipping/tax are captured per order (good raw data); `cost_price` exists on `products` so gross margin IS computable, but no report/widget computes it yet | Yes (raw data) | No | N/A | No | P1 | S | Low-effort win: the data already exists, just needs a report |
| Finance | Tax handling | PARTIAL | `orders.tax_amount` is a single flat field; no tax rate table, no GST/HSN/CGST/SGST breakdown | Partial | No | No | No | P2 | M | REQUIRES DECISION — confirm actual GST obligations with the business before modeling |
| Content | CMS pages, banners | IMPLEMENTED | `pages`, `banners` tables + resources | Yes | Yes | Yes | No | — | — | |
| Content | Blog | MISSING | No table/model | No | No | No | No | P3 | M | |
| RBAC | Admin roles/permissions | MISSING | Single `users.is_admin` boolean; Filament's `canAccessPanel()` is all-or-nothing | No | No | N/A | No | P0 | M | Every admin currently has full access to every resource — real risk once more than one admin exists |
| Security | Audit log of admin actions | MISSING | `order_status_history` is the ONLY audit trail found (order-specific); no generic audit log for product/price/coupon changes | Partial | No | N/A | No | P0 | M | |
| Chatbot | Customer chatbot | IMPLEMENTED | `chatbot_conversations` table (JSON messages), `ChatbotController`, `ChatbotConversationResource` (view-only) | Yes | Yes (view) | Yes | No | — | — | Present but scope/AI-backing NOT VERIFIED — out of scope for this pass |
| Notifications | Order/email notifications | NOT VERIFIED | `app/Notifications` directory not inspected in this pass | Unknown | Unknown | Unknown | No | — | — | Needs a follow-up read before Phase 6 (Notification Centre) planning |

## 3. Database Gaps Summary (VERIFIED FROM CODE)

Present and solid: `categories` (nested), `brands`, `products`, `product_images`, `product_variants`, `product_attributes` (flat), `addresses`, `coupons`, `orders`, `order_items`, `order_status_history`, `reviews`, `wishlists`, `cart_items`, `contact_messages`, `newsletter_subscribers`, `banners`, `pages`, `site_settings`, `customer_activities`, `payments`, `chatbot_conversations`, `users`.

Absent (confirmed NOT FOUND via migration file listing):
- `inventory_movements` / stock ledger
- `returns` / `refunds` (structured)
- `search_events`
- `product_views` / dedicated analytics event tables beyond the generic `customer_activities`
- `roles` / `permissions` / `role_user` (no RBAC package, no pivot tables)
- `audit_logs`
- `shipments` / `shipment_events`
- `tax_rates` / `tax_classes`
- `attribute` / `attribute_value` (reusable attribute catalog — current `product_attributes` is free-text only)
- `collections` / `tags`
- `blog_posts` / `blog_categories`
- `banner_impressions` / campaign performance tables

## 4. Security Gaps (VERIFIED FROM CODE, this pass only — not a full security audit)

- **No RBAC**: `is_admin` boolean is the only access control primitive for the entire Filament panel (`User::canAccessPanel()`). REQUIRES CONFIRMATION before scaling admin headcount.
- **No audit log** for price changes, stock adjustments, coupon creation, or admin permission changes — only order status changes are tracked.
- Razorpay signature verification IS implemented correctly (`verifyPaymentSignature`) — good.
- Did not check CSRF/mass-assignment/authorization policies in this pass in depth — flagged as follow-up, not claimed as safe or unsafe.

## 5. Performance/Scalability Notes (VERIFIED FROM CODE, cursory)

- No `app/Services` deep-dive done for N+1 queries in this pass — NEEDS CONFIRMATION before Phase 15.
- Dashboard widgets use live aggregate queries (`Order::whereDate(...)->count()`), not cached — fine at current scale, will need caching/queueing once order volume grows (P2, not urgent).
- No queue-backed export/report jobs found — `maatwebsite/excel` is installed but unused, so this isn't yet a bottleneck.

## 5b. Addendum — Routing/Auth Layer and Background-Processing Findings (VERIFIED FROM CODE, separate inspection pass)

These findings come from a dedicated inspection of `routes/`, `app/Http/Controllers`, `app/Http/Middleware`, `app/Policies`, `app/Http/Requests`, and `app/Jobs|Events|Listeners|Notifications|Mail|Console`, which were out of scope for the database-focused pass above.

**CRITICAL — live security bug (IDOR):** `GET admin/orders/{order}/invoice` (→ `App\Http\Controllers\Admin\OrderInvoiceController`) is registered under an `admin.*` route prefix but the route group middleware is only `auth` — **not** an admin/`isAdmin()` check — and the controller itself performs no ownership or role check before streaming the PDF. **Any authenticated (non-admin) customer can download any other customer's invoice by guessing/incrementing the order ID.** This is a real vulnerability in the live application today, not a roadmap gap. Recommend fixing this immediately (add `isAdmin()` check or ownership check) rather than deferring to a phase — it is a minimal, isolated, additive fix (one route/controller) with no risk to existing data. Awaiting your approval to patch it.

- **Zero custom Middleware** (`app/Http/Middleware` is empty) and **zero Policies** (`app/Policies` is empty). All authorization for account-scoped resources (orders, addresses, reviews) is presumed to rely on manual inline `where('user_id', ...)` filtering inside controllers rather than centralized Policies — consistent with the IDOR finding above and worth a full authorization audit before RBAC (Phase 1) is built.
- Only 2 dedicated Form Request classes exist (`LoginRequest`, `ProfileUpdateRequest`) despite ~15+ controllers accepting user input (Cart, Checkout, Review, Address, Contact, Newsletter, Payment) — validation is scattered inline, harder to test and audit.
- `MustVerifyEmail` is commented out on the `User` model — email verification routes/controllers exist (Breeze scaffold) but are not enforced at registration.
- No API routes file exists — confirms no REST API layer yet (relevant to future mobile/headless plans; not urgent).

**No background-processing infrastructure exists at all**: `app/Jobs`, `app/Events`, `app/Listeners`, `app/Notifications`, `app/Mail`, and `app/Console` directories do not exist. `QUEUE_CONNECTION=database` is set in `.env.example` but nothing uses the queue. This means:
- No order confirmation emails, shipment emails, or low-stock alert emails are sent today (`MAIL_MAILER=log` — mail is only logged, not delivered).
- No scheduled tasks exist (no automated low-stock digest, no abandoned-cart follow-up, no report generation).
- `config/services.php` and the real `.env` have **no Razorpay or Shiprocket configuration entries**, even though `razorpay/razorpay` is a Composer dependency and `ShiprocketService`/`ShippingServiceInterface` exist in `app/Services`. Combined with `tests/Feature/StorefrontTest.php` only exercising the COD order-placement path, this strongly suggests **online payment and shipping-carrier integration are scaffolded in code but not actually wired/configured** — the store likely operates COD-only in practice today, despite Razorpay code paths existing. **NEEDS CONFIRMATION with the business**: is Razorpay actually live, or code-complete-but-unconfigured?

**Test coverage gaps** (from `tests/Feature/StorefrontTest.php`, `AdminPanelTest.php`, Breeze `Auth/*` tests — otherwise solid coverage of auth and basic admin/storefront smoke tests): no tests for online/Razorpay payment flow, coupon application, wishlist add/remove, review submission, order status transitions/cancellation/return, Shiprocket/shipping integration, inventory stock decrement on order placement, or search relevance. No unit tests exist for any Service class in isolation.

## 6. Recommended First Implementation Phase (P0 only, pending your approval)

1. **Inventory ledger** (`inventory_movements` table) — additive migration, no data loss risk, unlocks Phase F (Inventory) and Phase AC (Audit Log) simultaneously.
2. **Returns/refunds structured workflow** (`returns` table + reason taxonomy) — currently the single largest operational blind spot for a live store.
3. **RBAC** (roles/permissions on the admin panel) — safety-critical before more admins are added; Filament has an official Shield plugin option to evaluate (REQUIRES DECISION: build custom vs. adopt `filament/spatie-laravel-permission`-based package).
4. **Generic admin audit log** — pairs naturally with #3.
5. ~~Coupon per-user usage tracking~~ — verified already enforced via `CouponService::validate()`; no schema change needed (see correction in section 2).
6. **Confirm and wire `customer_activities` event logging** — since the schema already exists, verify what's actually captured today before designing new analytics tables (avoids Phase AI duplication).

All are additive (new tables / new columns with defaults), none require altering or dropping existing data.
