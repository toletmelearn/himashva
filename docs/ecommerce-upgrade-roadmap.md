# E-Commerce Upgrade Roadmap

Generated: 2026-09-21
Status: PROPOSED — awaiting explicit approval before any implementation begins, per project safety rules.

This roadmap is reordered from the generic 15-phase template to reflect actual codebase dependencies discovered during the audit (see `ecommerce-gap-analysis.md`). Each phase is additive — no destructive migrations, no removal of existing functionality.

## Phase 0 — Immediate Security Fix — ✅ DONE (2026-09-21)
- ✅ Fixed IDOR on `admin/orders/{order}/invoice`: added `abort_unless(auth()->user()?->isAdmin(), 403)` to `OrderInvoiceController`. Regression tests added (`AdminPanelTest::test_non_admin_cannot_download_order_invoice`, `test_admin_can_download_order_invoice`). Full suite green.
- Centralized Policies for account-scoped customer resources (Order/Address/Review authorization from the *storefront* side — e.g. a customer viewing their own order) were **not** touched in this pass; Phase 1 added admin-side Policies for the same models (see below) but the customer-facing "can this user see their own order" check still relies on the pre-existing inline `where('user_id', ...)` filtering in `AccountController`. Flag this as a follow-up if you want it formalized too.

## Phase 1 — Foundation & Data Integrity — ✅ DONE (2026-09-21)
- ✅ Verified `customer_activities`: only `product_view` is currently logged (`ProductController::show`). No search/cart/checkout events yet — noted for Phase 7.
- ~~Add `coupon_usages` table~~ — verified: `per_user_limit` is already enforced via `CouponService::validate()` counting the user's paid/pending orders. No table needed; original gap-analysis entry corrected.
- ✅ Added `audit_logs` table + `App\Models\Concerns\Auditable` trait (create/update/delete logging with before/after diffs, sensitive fields like `password` redacted) wired to `Product`, `Coupon`, `Order`, `User`. Read-only `AuditLogResource` in Filament (super_admin-only via `AuditLogPolicy`). Tests: `AuditLogTest` (3 tests).
- ✅ RBAC implemented using `spatie/laravel-permission` (decision: package, not custom tables). Three roles seeded via `RolesAndPermissionsSeeder`:
  - `super_admin` — full access to everything in this permission set.
  - `manager` — `products.*`, `categories.*`, `brands.*`, `orders.*` (manage + view + update_status), `coupons.*`, `reviews.*`. No settings/users access.
  - `staff` — `orders.view`, `orders.update_status` only. `OrderResource`'s form disables every field except `order_status` for staff (everything else — customer/address/payment/tracking/totals — is read-only for them).
  - Legacy `users.is_admin = true` accounts keep full unrestricted access via a `before()` bypass in every new Policy (`App\Policies\Concerns\BypassesForLegacyAdmin`) — zero behavior change for existing admins, verified by `RbacTest::test_legacy_is_admin_user_retains_full_access_without_roles`.
  - Policies added for `Product`, `Category`, `Brand`, `Order`, `Coupon`, `Review`, `User` (users.\* is super_admin-only — no permission grants it to manager/staff), `AuditLog`.
  - Existing `is_admin=true` users were backfilled with the `super_admin` role in both the seeder (for future runs) and the live database (ran once, verified: `admin@himashva.com` → `super_admin`).
  - Not yet covered by a permission (still super_admin-only, same as before RBAC): Banners, Pages, Newsletter Subscribers, Contact Messages, Chatbot Conversations, Site Settings. Extend the permission set here if you want managers/staff to touch these areas too.
  - Tests: `RbacTest` (9 tests) covering manager/staff resource-level access boundaries and the legacy-admin bypass.
- Full suite: 66/66 passing after all Phase 1 work.

## Phase 2 — Inventory (P0)
- Add `inventory_movements` ledger table (type, quantity delta, reason, actor, timestamp) referencing `products`/`product_variants`.
- Backfill is NOT required/possible for historical changes (none were tracked) — ledger starts from go-live date; document this limitation.
- Wire stock-changing code paths (order placement, cancellation, admin manual adjustment) to write ledger entries instead of (or alongside) direct column updates.

## Phase 3 — Returns & Refunds (P0)
- Add `returns` table: order_id, order_item_id, reason (structured enum/taxonomy), status, refund_amount, restocked (bool), timestamps.
- Add refund gateway integration (Razorpay refund API call) — currently `payment_status` has a `refunded` value but no verified code path sets it.
- Wire `Order.order_status` transitions (`return_requested` is missing from the current enum — currently jumps straight to `returned`/`refunded`; confirm with business whether an intermediate "requested" state is needed).

## Phase 4 — Catalogue Depth (P1)
- Category SEO metadata fields (parity with `products.meta_title/meta_description`).
- Catalogue health checks (missing image/SEO/duplicate SKU) — read-only report, no schema change needed, can reuse existing columns.
- Product lifecycle states beyond `is_active` boolean (Draft/Active/Archived) — REQUIRES DECISION on exact states needed for this business's workflow.
- Reusable attribute/value catalog to replace free-text `product_attributes`, IF the business actually needs faceted filtering (confirm demand before building — avoid speculative complexity).

## Phase 5 — Order Operations (P1)
- Bulk order export (CSV/Excel) using the already-installed `maatwebsite/excel` package — cheap win, package exists but is unused.
- Packing slip / shipping label generation alongside existing invoice PDF.

## Phase 6 — Finance & Reporting (P1)
- Gross margin report using existing `products.cost_price` + `order_items` — data already exists, just needs a Filament widget/report page.
- Discount-impact and refund-impact reports.
- Tax breakdown — REQUIRES DECISION: confirm actual GST/HSN obligations with the business before modeling `tax_rates`/`tax_classes`; do not guess tax logic.

## Phase 7 — Analytics & Event Tracking (P0/P1, sequenced after Phase 1 verification)
- Confirm `customer_activities` usage (Phase 1) before adding new tables.
- If needed: dedicated `search_events` table (query, normalized query, result_count, clicked_result_id) for search analytics (zero-result searches, high-conversion searches).
- Cart abandonment reporting (scheduled query over `cart_items` age vs. no matching order — no new table needed initially).
- Admin Action Centre: unify `LowStockProducts` widget + new alerts (return-rate spikes, payment failures) into one severity-ranked feed.

## Phase 8 — Marketing & Merchandising (P2)
- Banner/campaign performance tracking (impressions/clicks) if merchandising investment increases.
- Recommendation engine (rule-based: "frequently bought together" from `order_items` co-occurrence) — start simple, no ML.

## Phase 9 — Customer CRM (P2)
- Customer segmentation built on existing `User::totalSpent`/`orderCount` computed attributes (already present — extend, don't replace).
- Support ticketing upgrade from current `contact_messages` (add status/priority/assignment) IF volume justifies it — REQUIRES CONFIRMATION of current message volume.

## Phase 10 — Shipping & Fulfilment (P2)
- Shipping zones (currently a single flat rate + free-shipping threshold).
- Carrier API integration to replace manually-entered `tracking_number`/`tracking_url`.

## Phase 11 — Content & SEO (P2/P3)
- Blog (currently absent) — only if content marketing is part of the business plan; confirm before building.
- Structured data (Product/Organization schema.org) for existing product/page views.

## Phase 12 — Performance & Security Hardening (Ongoing, P1)
- Full N+1/index audit of `app/Services` and Filament resources (not completed in this pass — needs a dedicated follow-up read).
- Queue heavy exports/reports once Phase 5/6 exports are in use (currently no export jobs exist, so not yet a bottleneck).

## Explicitly deferred (P3, correctly out of scope for now)
- Multi-warehouse inventory.
- A/B testing infrastructure.
- ML-based recommendations.
- Microservices — this remains a modular monolith by design; do not split services prematurely.

## Sequencing rationale

Phases 1–3 are ordered first because they are the areas with the **highest operational risk today**: no audit trail, no inventory history, and no real return/refund process for a store that already takes COD and online payments. Phases 4–6 unlock quick, low-risk wins using data/packages that already exist. Phases 7+ are genuine new capability and are sequenced after the foundational integrity work so new analytics tables aren't built on top of an unverified event-logging assumption.

## Next step

This roadmap, the gap analysis, and the data collection matrix are ready for your review. Per project rules, implementation will not begin until you explicitly approve a phase and confirm the REQUIRES DECISION items flagged above (RBAC approach, product lifecycle states, tax modeling, blog/ticketing need).
