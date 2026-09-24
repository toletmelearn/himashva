# Data Collection Matrix

Generated: 2026-09-21
Scope: Proposed first-party data points, evaluated against what already exists in the schema. Nothing here is implemented — this is a planning document pending approval.

Legend — Status: EXISTS (table/column already captures this) | PARTIAL (some capture, incomplete) | PROPOSED (not built).

| Data Point | Source (event) | Table | Status | Purpose | Business Question Answered | Retention | Privacy Sensitivity | Admin Visibility | Priority |
|---|---|---|---|---|---|---|---|---|---|
| Order placed | checkout | `orders`, `order_items` | EXISTS | Revenue, catalogue demand | "What sold, when, for how much?" | Indefinite (financial record) | Medium (name/email/address) | Full | P0 |
| Order status change | admin/customer action | `order_status_history` | EXISTS | Fulfilment audit trail | "Where did this order get stuck?" | Indefinite | Low | Full | P0 |
| Payment transaction | gateway callback | `payments` | EXISTS | Reconciliation | "Did this payment actually succeed?" | Indefinite | Medium (no card data stored — good) | Full | P0 |
| Coupon redemption (per order) | checkout | `orders.coupon_id`, `coupons.used_count` | PARTIAL | Discount impact | "How much discount did we give out?" | Indefinite | Low | Full | P1 |
| Coupon redemption (per user) | checkout | *(none — proposed `coupon_usages`)* | PROPOSED | Enforce `per_user_limit` | "Is this customer abusing the coupon?" | 1 year | Low | Full | P0 |
| Review submitted | account/review form | `reviews` | EXISTS | Product quality signal | "What are customers saying about this product?" | Indefinite | Low (public content) | Full | — |
| Wishlist add | product page | `wishlists` | EXISTS | Purchase intent signal | "What do customers want but haven't bought?" | Indefinite | Low | Partial (no report built) | P2 |
| Cart add/remove | cart actions | `cart_items` | EXISTS (current state only, no event history) | Cart composition | "What's in carts right now?" | Live (overwritten) | Low | Partial | — |
| Cart abandonment | inferred from `cart_items` age + no order | *(none — proposed event or scheduled query)* | PROPOSED | Checkout friction | "How many carts never convert?" | 90 days | Low | None | P1 |
| Product view | product page load | *(none directly — `customer_activities` schema could hold this)* | PARTIAL (schema exists, usage unverified) | Interest measurement | "Which products attract attention but don't convert?" | 180 days | Low | None yet | P0 |
| Search query | search box submit | *(none — proposed `search_events`)* | PROPOSED | Demand discovery | "What are customers searching for that we don't have?" | 180 days | Low | None yet | P1 |
| Search zero-result | search with 0 matches | *(none — part of proposed `search_events`)* | PROPOSED | Catalogue gap detection | "What are we missing?" | 180 days | Low | None yet | P1 |
| Checkout started | checkout page load | *(none — could reuse `customer_activities`)* | PROPOSED | Funnel measurement | "Where do customers drop off before paying?" | 90 days | Low | None yet | P1 |
| Login / session activity | auth events | `users.last_login_at`, `login_count` | EXISTS (aggregate only, no per-session log) | Engagement | "Is this customer still active?" | Indefinite (aggregate) | Low | Full | — |
| Newsletter subscription | footer/checkout opt-in | `newsletter_subscribers` | EXISTS | Marketing consent | "Can we email this person?" | Until unsubscribe + legal minimum | Medium (consent record) | Full | — |
| Contact form submission | contact page | `contact_messages` | EXISTS | Support/lead capture | "What are customers asking about?" | 1–2 years | Medium (PII) | Full | — |
| Generic customer activity (polymorphic) | various | `customer_activities` (`activity_type`, `subject_type/id`, `data` JSON) | PARTIAL (schema ready, write-paths unverified) | General-purpose event bus | Multiple — depends on what's actually logged | 180 days recommended | Depends on `data` payload | None yet | P0 — verify and wire first |

## Explicit exclusions (per Phase AO — do not collect without a stated business reason)

- Device fingerprinting / cross-site tracking — not needed for a single-storefront COD+Razorpay business.
- Precise geolocation beyond pincode/city/state already in addresses.
- Full session replay / keystroke-level analytics.
- Any card/payment credential storage — Razorpay handles this; `payments.raw_response` should be reviewed to confirm no PAN/CVV is ever persisted (NEEDS CONFIRMATION — inspect actual Razorpay webhook payload shape before enabling raw storage in production).

## Immediate recommendation

Before creating any new analytics tables, confirm what `customer_activities` is actually used for today (grep controllers for `CustomerActivity::create` — not done in this pass). Two schemas already exist that could carry most of Phase H/I/J (customer behaviour, search, product analytics) with additive work rather than new tables: `customer_activities` (generic events) and could be extended with a `search_events`-specific table only if the generic one proves awkward for search-specific reporting (zero-result rate, click-through).
