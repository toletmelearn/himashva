# Target Architecture — Aroma House Online

Generated: 2026-09-21
Status: PROPOSED, additive-only. Nothing here has been implemented. See `ecommerce-gap-analysis.md` for evidence and `ecommerce-upgrade-roadmap.md` for sequencing.

Architectural stance: **modular monolith**, not microservices. The current Laravel 13 app already follows this shape (Filament admin + Blade/Alpine storefront + a thin `app/Services` layer sharing one database) and nothing in the gap analysis justifies splitting it. Keep one deployable app; organize by domain module inside it.

## 1. Domain Modules (logical, not new folders — group existing + new code under these boundaries)

- **Catalogue** — `Product`, `ProductVariant`, `ProductImage`, `Category`, `Brand`, `ProductAttribute` (existing). Add: catalogue-health checks, product lifecycle states, category SEO fields.
- **Inventory** — new: `inventory_movements` ledger. Reads/writes mediated by a single `InventoryService` so every stock change (order placement, cancellation, admin adjustment, return restock) goes through one code path instead of scattered column updates.
- **Orders** — `Order`, `OrderItem`, `OrderStatusHistory` (existing, solid). Add: `returns` table, refund gateway call, packing-slip/export generation.
- **Customers** — `User`, `Address`, `Wishlist`, `Review` (existing). Add: segmentation built on existing `totalSpent`/`orderCount` computed attributes — no new schema needed initially.
- **Marketing** — `Coupon`, `Banner` (existing). Add: `coupon_usages` (per-user redemption tracking), campaign performance counters.
- **Analytics** — `customer_activities` (existing, schema-ready, usage unverified — verify first). Add `search_events` only if the generic event table proves awkward for search-specific reporting.
- **Payments** — `Payment` (existing, correctly separated from `Order`). Add: refund API integration, verify Razorpay/Shiprocket are actually configured (currently no `.env`/`config/services.php` entries despite code existing).
- **Content** — `Page`, `Banner`, `SiteSetting` (existing, Filament-managed).
- **Admin/Platform** — RBAC (new), audit log (new), Policies (new — currently `app/Policies` is empty).

## 2. Database Architecture

- Stay on the existing single MySQL/SQLite schema (23 tables today) — no sharding, no separate analytics database needed at current scale.
- All new tables are additive: `inventory_movements`, `returns`, `coupon_usages`, `audit_logs`, `roles`/`permissions`/`role_user` (or equivalent package tables), optionally `search_events`.
- No existing table is dropped, renamed, or has a column removed. `order_status` enum may need one additive value (`return_requested`) — confirm with business first.

## 3. Services

Existing: `CartService`, `CouponService`, `OrderService`, `ShippingServiceInterface`, `ShiprocketService` — this pattern is correct and should be extended, not replaced.

New services to add (same pattern — one class per bounded responsibility, injected via constructor DI, called from Controllers/Filament resources, never bypassed with direct Eloquent writes for the concern they own):
- `InventoryService` — the single entry point for stock mutation + ledger writes.
- `ReturnService` — return creation, restock decision, refund trigger.
- `AuditLogService` — wraps admin write actions (or use Filament/Eloquent observers — evaluate both before building).

## 4. Events, Jobs, Notifications (currently entirely absent — this is new infrastructure, not an extension)

`app/Jobs`, `app/Events`, `app/Listeners`, `app/Notifications`, `app/Mail` do not exist today. Introduce them incrementally, tied to real needs from the roadmap, not speculatively:
- `OrderPlaced`, `OrderStatusChanged`, `ReturnRequested` domain events → listeners send notifications (order confirmation, shipment, return-status emails) — currently `MAIL_MAILER=log`, so no email is actually delivered today; this needs a mail driver decision before notifications are useful in production.
- Queue-backed jobs for anything slow: bulk CSV/Excel export (package already installed, unused), report generation. `QUEUE_CONNECTION=database` is already set but nothing runs on it — first real job unlocks the existing queue worker setup.
- Scheduled tasks (`app/Console/Commands` + scheduler in `bootstrap/app.php`, which doesn't exist yet): low-stock digest, cart-abandonment digest — only once the underlying data (inventory ledger, cart-age tracking) exists.

## 5. Admin Architecture (Filament)

Already well-established — 12 Resources, 6 widgets, relation managers for Order/Product. Extend this pattern rather than introducing a parallel custom admin UI:
- New Resources needed: `ReturnResource`, `InventoryMovementResource` (or a read-only ledger view), `AuditLogResource` (read-only), `RoleResource`/`PermissionResource` if RBAC is custom-built (or use a package's own UI if adopted).
- New widgets: gross-margin report, Action Centre (unify `LowStockProducts` + new alerts into one severity-ranked feed).
- RBAC gate point: `User::canAccessPanel()` currently checks a single `is_admin` boolean — this becomes the integration point for role/permission checks once RBAC is decided.

## 6. API Boundaries

No `routes/api.php` exists today — correctly deferred, no mobile/headless requirement yet. When it becomes necessary, the existing `app/Services` layer is the natural reuse point: API controllers should call the same Services as web Controllers, not duplicate business logic. Do not build an API layer speculatively.

## 7. Security

- Close the live IDOR on `admin/orders/{order}/invoice` first (Phase 0 — see roadmap), independent of everything else here.
- Introduce Policies (`OrderPolicy`, `AddressPolicy`, `ReviewPolicy`) to replace presumed inline `where('user_id', ...)` authorization — `app/Policies` is currently empty.
- Introduce custom Middleware only where a cross-cutting concern actually needs it (e.g. an explicit `EnsureUserIsAdmin` middleware for any future non-Filament admin routes, closing the exact class of bug found in the IDOR).
- RBAC and audit log are prerequisites before admin headcount grows beyond a single trusted admin.

## 8. Caching, Queues, Search

- Caching: not urgent at current scale — dashboard widgets run live aggregate queries today, which is fine until order volume grows meaningfully (defer, P2).
- Queues: infrastructure exists (`QUEUE_CONNECTION=database`) but unused — first consumer should be exports/reports (Phase 5/6), not built speculatively ahead of a real job.
- Search: current search is presumably a basic DB query (`ShopController::search` — not deep-audited). No dedicated search engine (Meilisearch/Algolia/Scout) is justified yet; revisit only if catalogue size or query complexity grows, or if search analytics (Phase 7) reveals a relevance problem.

## 9. Reporting

Build on data that already exists rather than new pipelines: `products.cost_price` + `order_items` already support a gross-margin report; `orders` already has discount/tax/shipping breakdown for a revenue report. No separate reporting database or ETL is needed at this scale — direct Eloquent aggregate queries (as the existing dashboard widgets already do) are sufficient, moving to queued/cached generation only if/when report volume or complexity grows.

## Explicitly not doing (and why)

- Microservices — modular monolith remains correct at this scale; the existing Service-layer pattern gives enough separation.
- Separate analytics database/warehouse — `customer_activities` polymorphic table (once verified and wired) covers the near-term need.
- Multi-warehouse inventory — architecture doesn't preclude adding it later; building it now would be speculative.
- ML-based recommendations — start rule-based (order_items co-occurrence) per the roadmap; ML is a later, evidence-driven step.
