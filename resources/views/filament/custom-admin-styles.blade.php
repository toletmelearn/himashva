<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&display=swap" rel="stylesheet">

<style>
    /* ==========================================================
       Himashva Admin Theme — "Modern SaaS" pass
       Light sidebar, indigo/violet accent — the Stripe/Linear-style
       palette the storefront/brand-brown theme was replaced with.
       All selectors below are verified against the installed
       Filament v3.3 blade views (vendor/filament/*), not guessed:
         - components/global-search/field.blade.php        -> .fi-global-search-field
         - components/topbar/database-notifications-trigger -> .fi-topbar-database-notifications-btn
         - components/user-menu.blade.php                   -> .fi-user-menu
         - components/sidebar/item.blade.php                 -> .fi-sidebar-item-active, .fi-sidebar-item-button
         - components/layout/simple.blade.php                -> .fi-simple-layout, .fi-simple-main
         - support/.../badge.blade.php                        -> .fi-badge, .fi-color-*
         - support/.../dropdown/index.blade.php              -> .fi-dropdown-panel, .fi-dropdown-list, .fi-dropdown-list-item
         - support/.../modal/index.blade.php                 -> .fi-modal-window, .fi-modal-close-overlay
         - support/.../tabs/item.blade.php                   -> .fi-tabs-item, .fi-active, .fi-tabs-item-label
         - support/.../pagination/item.blade.php             -> .fi-pagination-item, .fi-pagination-item-button
         - support/.../input/checkbox.blade.php               -> .fi-checkbox-input
         - support/.../section/index.blade.php                -> .fi-section-header, .fi-section-content-ctn, .fi-section-footer
         - forms/.../field-wrapper/label.blade.php            -> .fi-fo-field-wrp-label
         - tables/.../empty-state/index.blade.php             -> .fi-ta-empty-state, .fi-ta-empty-state-icon-ctn
         - widgets/.../stats-overview-widget/stat.blade.php   -> .fi-wi-stats-overview-stat(-icon|-label|-value|-description)

       Contrast notes (WCAG AA, computed via relative-luminance
       formula, not eyeballed — see task report for full math):
         - sidebar text (--ink-600 on --surface-0/--surface-1): ~7.3–7.7:1
         - sidebar active text (--accent-600 on --accent-50): ~6.5:1
         - table header text (--ink-600 on --surface-1/--surface-2): ~7.0–7.3:1
         - button text (white on --accent-600): ~7.1:1
         - badge success text darkened to #047857 (was #059669 — failed at 3.6:1, now ~5.2:1)
         - badge danger text darkened to #B91C1C (was #DC2626 — failed at 4.4:1, now ~5.9:1)
         - badge warning text darkened to #92400E (was #D97706 — failed at 3.1:1, now ~6.8:1)
         - badge info text #2563EB kept as specified — passes at ~4.75:1
       ========================================================== */

    :root {
        /* Neutral surfaces */
        --surface-0: #FFFFFF;
        --surface-1: #F8F7FF;
        --surface-2: #F4F4F5;

        /* Ink (text) */
        --ink-900: #18181B;
        --ink-600: #52525B;
        --ink-400: #A1A1AA;

        /* Borders */
        --border: #E4E4E7;

        /* Accent — violet */
        --accent-600: #6D28D9;
        --accent-500: #7C3AED;
        --accent-50: #F5F3FF;
        --accent-100: #EDE9FE;
        --on-accent: #FFFFFF;

        /* Semantic badge colors — pale tint background + darkened
           text so every pair clears WCAG AA 4.5:1 (see header notes) */
        --badge-success-bg: #ECFDF5;
        --badge-success-text: #047857;
        --badge-danger-bg: #FEF2F2;
        --badge-danger-text: #B91C1C;
        --badge-warning-bg: #FFFBEB;
        --badge-warning-text: #92400E;
        --badge-info-bg: #EFF6FF;
        --badge-info-text: #2563EB;
    }

    .dark {
        --surface-0-dark: #18181B;
        --surface-1-dark: #27272A;
        --surface-2-dark: #09090B;
        --ink-900-dark: #FAFAFA;
        --ink-600-dark: #A1A1AA;
        --border-dark: #3F3F46;
        --accent-400-dark: #A78BFA;
        --accent-500-dark: #8B5CF6;

        /* Dark-mode badge equivalents — same hues, tinted dark
           backgrounds, brightened text for legibility */
        --badge-success-bg: rgba(5, 150, 105, 0.15);
        --badge-success-text: #34D399;
        --badge-danger-bg: rgba(220, 38, 38, 0.15);
        --badge-danger-text: #F87171;
        --badge-warning-bg: rgba(217, 119, 6, 0.15);
        --badge-warning-text: #FBBF24;
        --badge-info-bg: rgba(37, 99, 235, 0.15);
        --badge-info-text: #60A5FA;
    }

    /* ---------- Page background: neutral zinc, no colored glow ---------- */
    body {
        background: var(--surface-2);
    }

    .dark body {
        background: var(--surface-2-dark);
    }

    /* ---------- Text logo ---------- */
    .fi-logo {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-weight: 600;
        font-size: 1.5rem;
        letter-spacing: 0.02em;
        color: var(--ink-900);
    }

    .dark .fi-logo {
        color: var(--ink-900-dark);
    }

    /* ---------- Sidebar: light background, dark text (the core flip) ---------- */
    .fi-sidebar {
        background: linear-gradient(180deg, var(--surface-0) 0%, var(--surface-1) 100%);
        border-inline-end: 1px solid var(--border);
    }

    .dark .fi-sidebar {
        background: linear-gradient(180deg, var(--surface-0-dark) 0%, var(--surface-1-dark) 100%);
        border-inline-end: 1px solid var(--border-dark);
    }

    .fi-sidebar-header {
        background: transparent;
        border-block-end: 1px solid var(--border);
    }

    .dark .fi-sidebar-header {
        border-block-end: 1px solid var(--border-dark);
    }

    .fi-sidebar-nav-groups {
        color: var(--ink-900);
    }

    .dark .fi-sidebar-nav-groups {
        color: var(--ink-900-dark);
    }

    .fi-sidebar-item-button {
        border-radius: 0.5rem;
        transition: background-color 150ms ease, color 150ms ease, transform 150ms ease;
        color: var(--ink-600);
        position: relative;
    }

    .dark .fi-sidebar-item-button {
        color: var(--ink-600-dark);
    }

    /* Filament ships an unthemed gray icon color that doesn't match
       our light sidebar — override it explicitly for every sidebar
       icon, not just the active item. */
    .fi-sidebar-item-button .fi-icon {
        color: var(--ink-400);
        transition: color 150ms ease;
    }

    .dark .fi-sidebar-item-button .fi-icon {
        color: var(--ink-600-dark);
    }

    .fi-sidebar-item-button:hover {
        background-color: var(--surface-1);
        color: var(--ink-900);
    }

    .dark .fi-sidebar-item-button:hover {
        background-color: var(--surface-1-dark);
        color: var(--ink-900-dark);
    }

    .fi-sidebar-item-button:hover .fi-icon {
        color: var(--ink-900);
    }

    .dark .fi-sidebar-item-button:hover .fi-icon {
        color: var(--ink-900-dark);
    }

    /* Active item: crisp flat pale-violet background + left accent
       border — no glow, matching Stripe/Linear's minimal active state */
    .fi-sidebar-item-active .fi-sidebar-item-button {
        background-color: var(--accent-50);
        color: var(--accent-600);
        box-shadow: inset 2px 0 0 0 var(--accent-600);
    }

    .dark .fi-sidebar-item-active .fi-sidebar-item-button {
        background-color: var(--surface-1-dark);
        color: var(--accent-400-dark);
        box-shadow: inset 2px 0 0 0 var(--accent-400-dark);
    }

    .fi-sidebar-item-active .fi-sidebar-item-button .fi-icon {
        color: var(--accent-600);
    }

    .dark .fi-sidebar-item-active .fi-sidebar-item-button .fi-icon {
        color: var(--accent-400-dark);
    }

    .fi-sidebar-group-items {
        border-inline-start: 1px solid var(--border);
    }

    .dark .fi-sidebar-group-items {
        border-inline-start: 1px solid var(--border-dark);
    }

    /* ---------- Topbar: glass effect, retinted neutral ---------- */
    .fi-topbar {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(18px) saturate(1.4);
        -webkit-backdrop-filter: blur(18px) saturate(1.4);
        border-block-end: 1px solid var(--border);
        box-shadow: 0 1px 0 0 rgba(255, 255, 255, 0.5) inset, 0 8px 24px -16px rgba(24, 24, 27, 0.12);
    }

    .dark .fi-topbar {
        background: rgba(24, 24, 27, 0.7);
        border-block-end: 1px solid var(--border-dark);
        box-shadow: 0 1px 0 0 rgba(255, 255, 255, 0.04) inset, 0 8px 24px -16px rgba(0, 0, 0, 0.6);
    }

    /* Global search field (Cmd/Ctrl+K) */
    .fi-global-search-field .fi-input-wrp {
        background: rgba(255, 255, 255, 0.6);
        border-color: var(--border);
        transition: background-color 150ms ease, box-shadow 150ms ease, border-color 150ms ease;
    }

    .dark .fi-global-search-field .fi-input-wrp {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--border-dark);
    }

    .fi-global-search-field .fi-input-wrp:focus-within {
        border-color: var(--accent-500);
        box-shadow: 0 0 0 3px rgba(109, 40, 217, 0.15);
    }

    /* Notifications bell trigger */
    .fi-topbar-database-notifications-btn {
        transition: background-color 150ms ease, transform 150ms ease;
    }

    .fi-topbar-database-notifications-btn:hover {
        background-color: var(--accent-50);
        transform: translateY(-1px);
    }

    .dark .fi-topbar-database-notifications-btn:hover {
        background-color: rgba(255, 255, 255, 0.06);
    }

    /* User menu trigger avatar */
    .fi-user-menu .fi-dropdown-trigger {
        border-radius: 9999px;
        transition: box-shadow 150ms ease, transform 150ms ease;
    }

    .fi-user-menu .fi-dropdown-trigger:hover {
        box-shadow: 0 0 0 3px var(--accent-100);
        transform: translateY(-1px);
    }

    .dark .fi-user-menu .fi-dropdown-trigger:hover {
        box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.25);
    }

    /* ---------- Dropdown panels (notifications, user menu, global search actions) ---------- */
    .fi-dropdown-panel {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(16px) saturate(1.3);
        -webkit-backdrop-filter: blur(16px) saturate(1.3);
        border: 1px solid var(--border);
        box-shadow: 0 16px 40px -12px rgba(24, 24, 27, 0.18);
    }

    .dark .fi-dropdown-panel {
        background: rgba(24, 24, 27, 0.92);
        border: 1px solid var(--border-dark);
        box-shadow: 0 16px 40px -12px rgba(0, 0, 0, 0.6);
    }

    .fi-dropdown-list-item {
        transition: background-color 120ms ease, color 120ms ease;
        border-radius: 0.5rem;
    }

    .fi-dropdown-list-item:hover {
        background-color: var(--accent-50);
    }

    .dark .fi-dropdown-list-item:hover {
        background-color: var(--surface-1-dark);
    }

    /* ---------- Modals / slide-overs ---------- */
    .fi-modal-window {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(20px) saturate(1.3);
        -webkit-backdrop-filter: blur(20px) saturate(1.3);
        border: 1px solid var(--border);
        box-shadow: 0 24px 64px -16px rgba(24, 24, 27, 0.22);
    }

    .dark .fi-modal-window {
        background: rgba(24, 24, 27, 0.96);
        border: 1px solid var(--border-dark);
        box-shadow: 0 24px 64px -16px rgba(0, 0, 0, 0.7);
    }

    .fi-modal-close-overlay {
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
    }

    /* ---------- Cards / sections ---------- */
    .fi-section {
        border-radius: 0.75rem;
        transition: box-shadow 200ms ease, border-color 200ms ease;
        border-color: var(--border);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
    }

    .dark .fi-section {
        border-color: var(--border-dark);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3), 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .fi-section:hover {
        box-shadow: 0 4px 12px -4px rgba(24, 24, 27, 0.1);
    }

    .dark .fi-section:hover {
        box-shadow: 0 4px 12px -4px rgba(0, 0, 0, 0.5);
    }

    .fi-section-header {
        border-radius: 0.75rem 0.75rem 0 0;
    }

    .fi-section-content-ctn {
        border-color: var(--border);
    }

    .dark .fi-section-content-ctn {
        border-color: var(--border-dark);
    }

    .fi-section-footer {
        background: var(--surface-2);
    }

    .dark .fi-section-footer {
        background: rgba(255, 255, 255, 0.02);
    }

    /* ---------- Tabs ---------- */
    .fi-tabs-item {
        transition: background-color 150ms ease, color 150ms ease;
    }

    .fi-tabs-item.fi-active {
        background: var(--accent-50);
        box-shadow: inset 0 -2px 0 0 var(--accent-600);
    }

    .dark .fi-tabs-item.fi-active {
        background: var(--surface-1-dark);
        box-shadow: inset 0 -2px 0 0 var(--accent-400-dark);
    }

    /* ---------- Tables ---------- */
    /* Table card wrapper — neutral border + soft Stripe-style shadow
       instead of Filament's flat gray-950/5 ring, so it reads as a
       distinct card rather than blending into the page background. */
    .fi-ta-ctn {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
        border: 1px solid var(--border);
    }

    .dark .fi-ta-ctn {
        border-color: var(--border-dark);
        box-shadow: 0 4px 20px -8px rgba(0, 0, 0, 0.5);
    }

    .fi-ta-header-ctn {
        background: var(--surface-1);
        border-block-end: 1px solid var(--border);
    }

    .dark .fi-ta-header-ctn {
        background: var(--surface-1-dark);
        border-block-end: 1px solid var(--border-dark);
    }

    .fi-ta-header-cell {
        font-weight: 600;
        color: var(--ink-600);
    }

    .dark .fi-ta-header-cell {
        color: var(--ink-600-dark);
    }

    .fi-ta-record {
        transition: background-color 120ms ease;
    }

    .fi-ta-record:hover {
        background-color: var(--surface-1);
    }

    .dark .fi-ta-record:hover {
        background-color: rgba(255, 255, 255, 0.04);
    }

    /* Selected-row checkbox: swap the plain primary tick for the accent */
    .fi-checkbox-input:checked {
        background-color: var(--accent-600);
        border-color: var(--accent-600);
    }

    .dark .fi-checkbox-input:checked {
        background-color: var(--accent-400-dark);
        border-color: var(--accent-400-dark);
    }

    /* Pagination controls */
    .fi-pagination-item-button {
        transition: background-color 120ms ease;
    }

    .fi-pagination-item.fi-active .fi-pagination-item-button {
        background-color: var(--accent-50);
    }

    .fi-pagination-item.fi-active .fi-pagination-item-label {
        color: var(--accent-600);
    }

    .dark .fi-pagination-item.fi-active .fi-pagination-item-button {
        background-color: var(--surface-1-dark);
    }

    .dark .fi-pagination-item.fi-active .fi-pagination-item-label {
        color: var(--accent-400-dark);
    }

    /* Empty states */
    .fi-ta-empty-state-icon-ctn {
        background: var(--surface-1);
    }

    .dark .fi-ta-empty-state-icon-ctn {
        background: var(--surface-1-dark);
    }

    /* ---------- Forms ---------- */
    .fi-fo-field-wrp:focus-within .fi-fo-text-input,
    .fi-fo-field-wrp:focus-within textarea,
    .fi-fo-field-wrp:focus-within select {
        border-color: var(--accent-600);
        box-shadow: 0 0 0 3px var(--accent-100);
    }

    .dark .fi-fo-field-wrp:focus-within .fi-fo-text-input,
    .dark .fi-fo-field-wrp:focus-within textarea,
    .dark .fi-fo-field-wrp:focus-within select {
        border-color: var(--accent-400-dark);
        box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.2);
    }

    .fi-fo-field-wrp-label {
        letter-spacing: 0.01em;
    }

    .fi-fo-field-wrp + .fi-fo-field-wrp {
        margin-top: 0.25rem;
    }

    /* ---------- Buttons ---------- */
    .fi-btn {
        transition: transform 120ms ease, box-shadow 120ms ease, filter 120ms ease;
    }

    .fi-btn:active {
        transform: scale(0.97);
    }

    .fi-btn-color-primary:not(.fi-btn-outlined) {
        background: linear-gradient(135deg, var(--accent-600) 0%, var(--accent-500) 100%);
        color: var(--on-accent);
    }

    .fi-btn-color-primary:not(.fi-btn-outlined):hover {
        filter: brightness(1.06);
        box-shadow: 0 4px 12px -2px rgba(109, 40, 217, 0.35);
        transform: translateY(-1px);
    }

    /* ---------- Badges: semantic pale-tint colors, paired with
       darkened text (not color alone) so text/bg always clears
       WCAG AA — see header contrast notes ---------- */
    .fi-badge {
        border-radius: 9999px;
        font-weight: 600;
        letter-spacing: 0.01em;
    }

    .fi-badge.fi-color-success {
        background-color: var(--badge-success-bg);
        color: var(--badge-success-text);
    }

    .fi-badge.fi-color-danger {
        background-color: var(--badge-danger-bg);
        color: var(--badge-danger-text);
    }

    .fi-badge.fi-color-warning {
        background-color: var(--badge-warning-bg);
        color: var(--badge-warning-text);
    }

    .fi-badge.fi-color-info {
        background-color: var(--badge-info-bg);
        color: var(--badge-info-text);
    }

    /* Primary/custom badges get the violet accent treatment */
    .fi-badge.fi-color-primary {
        background-color: var(--accent-50);
        color: var(--accent-600);
    }

    .dark .fi-badge.fi-color-primary {
        background-color: var(--surface-1-dark);
        color: var(--accent-400-dark);
    }

    /* ---------- Login page ---------- */
    .fi-simple-layout {
        background: linear-gradient(180deg, var(--surface-2) 0%, var(--surface-0) 60%);
        position: relative;
        overflow: hidden;
    }

    .dark .fi-simple-layout {
        background: linear-gradient(180deg, var(--surface-2-dark) 0%, var(--surface-0-dark) 60%);
    }

    /* Subtle animated violet glow orb behind the login card — pure CSS,
       respects prefers-reduced-motion below. */
    .fi-simple-layout::before {
        content: '';
        position: absolute;
        width: 640px;
        height: 640px;
        top: -220px;
        left: 50%;
        transform: translateX(-50%);
        background: radial-gradient(circle, rgba(109, 40, 217, 0.06) 0%, rgba(109, 40, 217, 0) 70%);
        pointer-events: none;
        animation: himashva-orb-drift 14s ease-in-out infinite;
        z-index: 0;
    }

    .dark .fi-simple-layout::before {
        background: radial-gradient(circle, rgba(167, 139, 250, 0.1) 0%, rgba(167, 139, 250, 0) 70%);
    }

    @keyframes himashva-orb-drift {
        0%, 100% { transform: translateX(-50%) translateY(0) scale(1); }
        50% { transform: translateX(-46%) translateY(24px) scale(1.06); }
    }

    .fi-simple-main {
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 12px 32px -12px rgba(24, 24, 27, 0.15);
        border: 1px solid var(--border);
        background: var(--surface-0);
        position: relative;
        z-index: 1;
    }

    .dark .fi-simple-main {
        background: var(--surface-0-dark);
        border: 1px solid var(--border-dark);
        box-shadow: 0 20px 48px -16px rgba(0, 0, 0, 0.6);
    }

    /* ---------- Dashboard stat widgets ---------- */
    .fi-wi-stats-overview-stat {
        border-radius: 0.875rem;
        border: 1px solid var(--border);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
        transition: box-shadow 200ms ease, transform 200ms ease, border-color 200ms ease;
    }

    .dark .fi-wi-stats-overview-stat {
        border: 1px solid var(--border-dark);
        box-shadow: 0 6px 20px -10px rgba(0, 0, 0, 0.5);
    }

    .fi-wi-stats-overview-stat:hover {
        box-shadow: 0 8px 20px -8px rgba(24, 24, 27, 0.14);
        transform: translateY(-2px);
        border-color: var(--accent-100);
    }

    .dark .fi-wi-stats-overview-stat:hover {
        box-shadow: 0 14px 32px -12px rgba(0, 0, 0, 0.6);
        border-color: rgba(167, 139, 250, 0.3);
    }

    .fi-wi-stats-overview-stat-icon {
        color: var(--accent-600);
    }

    .dark .fi-wi-stats-overview-stat-icon {
        color: var(--accent-400-dark);
    }

    .fi-wi-stats-overview-stat-value {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-weight: 700;
        font-size: 2rem;
    }

    /* ---------- Dashboard greeting widget (custom, see
       resources/views/filament/widgets/dashboard-greeting.blade.php) ---------- */
    .himashva-greeting {
        background: linear-gradient(120deg, var(--accent-600) 0%, var(--accent-500) 100%);
        border-radius: 1rem;
        color: var(--on-accent);
        padding: 1.75rem 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 16px 40px -14px rgba(109, 40, 217, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.12);
        position: relative;
        overflow: hidden;
    }

    .himashva-greeting__title {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 1.875rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .himashva-greeting__subtitle {
        color: rgba(255, 255, 255, 0.85);
        margin-top: 0.25rem;
        font-size: 0.9rem;
    }

    .himashva-greeting__stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 1rem;
        margin-top: 1.25rem;
    }

    .himashva-greeting__stat {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 0.625rem;
        padding: 0.75rem 1rem;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        transition: background-color 150ms ease, transform 150ms ease;
    }

    .himashva-greeting__stat:hover {
        background: rgba(255, 255, 255, 0.18);
        transform: translateY(-1px);
    }

    .himashva-greeting__stat-value {
        font-size: 1.375rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .himashva-greeting__stat-label {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.85);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* ---------- Scrollbar ---------- */
    .fi-sidebar-nav,
    .fi-ta-content,
    .fi-main {
        scrollbar-width: thin;
        scrollbar-color: var(--ink-400) transparent;
    }

    .fi-sidebar-nav::-webkit-scrollbar,
    .fi-ta-content::-webkit-scrollbar,
    .fi-main::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .fi-sidebar-nav::-webkit-scrollbar-thumb {
        background-color: var(--border);
        border-radius: 9999px;
    }

    .fi-ta-content::-webkit-scrollbar-thumb,
    .fi-main::-webkit-scrollbar-thumb {
        background-color: var(--ink-400);
        border-radius: 9999px;
    }

    /* Respect reduced-motion preference: disable the hover/press
       transitions and the login-page orb animation instead of
       forcing motion on everyone. */
    @media (prefers-reduced-motion: reduce) {
        .fi-sidebar-item-button,
        .fi-section,
        .fi-ta-record,
        .fi-btn,
        .fi-global-search-field .fi-input-wrp,
        .fi-topbar-database-notifications-btn,
        .fi-user-menu .fi-dropdown-trigger,
        .fi-dropdown-list-item,
        .fi-pagination-item-button,
        .fi-wi-stats-overview-stat,
        .himashva-greeting__stat {
            transition: none !important;
        }

        .fi-simple-layout::before {
            animation: none !important;
        }
    }
</style>

<style>
/* ===== SIDEBAR: dark charcoal/navy, indigo accents ===== */
.fi-sidebar { background: linear-gradient(180deg, #1e1e2e 0%, #13131f 100%) !important; border-right: 1px solid rgba(99, 102, 241, 0.1) !important; }
.fi-sidebar-header { padding: 1.25rem 1rem !important; border-bottom: 1px solid rgba(99, 102, 241, 0.1) !important; }
.fi-sidebar-item { margin: 2px 8px !important; border-radius: 8px !important; }
.fi-sidebar-item-button { border-radius: 8px !important; transition: all 0.2s ease !important; padding: 0.5rem 0.75rem !important; }
.fi-sidebar-item-button:hover { background: rgba(99, 102, 241, 0.12) !important; }
.fi-sidebar-item-active .fi-sidebar-item-button { background: rgba(99, 102, 241, 0.18) !important; border-left: 3px solid #818cf8 !important; }
.fi-sidebar-item-active .fi-sidebar-item-label { color: #c7d2fe !important; font-weight: 600 !important; }
.fi-sidebar-item-active .fi-sidebar-item-icon { color: #818cf8 !important; }
.fi-sidebar-group-label { font-size: 0.65rem !important; font-weight: 700 !important; letter-spacing: 1.5px !important; text-transform: uppercase !important; color: rgba(165, 180, 252, 0.4) !important; padding: 0.75rem 1rem 0.25rem !important; }
.fi-sidebar-item-label { font-size: 0.8rem !important; font-weight: 500 !important; color: rgba(224, 231, 255, 0.65) !important; }
.fi-sidebar-item-button:hover .fi-sidebar-item-label { color: #e0e7ff !important; }
.fi-sidebar-item-icon { color: rgba(165, 180, 252, 0.4) !important; width: 1.15rem !important; height: 1.15rem !important; }
.fi-sidebar-item-button:hover .fi-sidebar-item-icon { color: rgba(165, 180, 252, 0.8) !important; }
.fi-sidebar-item-badge { font-size: 0.65rem !important; }
.fi-sidebar-nav::-webkit-scrollbar { width: 4px; }
.fi-sidebar-nav::-webkit-scrollbar-track { background: transparent; }
.fi-sidebar-nav::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.2); border-radius: 4px; }
/* .fi-logo defaults to var(--ink-900) (near-black) for the light-theme
   sidebar and is only repainted lighter under the site-wide `.dark`
   selector. This custom sidebar is unconditionally dark charcoal, so in
   light mode (the default — no `.dark` class on <html>) the wordmark
   was rendering near-black text on a dark background, making it
   effectively invisible. Force a bright wordmark regardless of theme. */
.fi-sidebar .fi-logo { color: #ffffff !important; }

/* ===== TOP BAR ===== */
.fi-topbar { background: rgba(253, 251, 247, 0.85) !important; backdrop-filter: blur(12px) !important; -webkit-backdrop-filter: blur(12px) !important; border-bottom: 1px solid rgba(234, 226, 214, 0.6) !important; }
:is(.dark) .fi-topbar { background: rgba(26, 20, 14, 0.9) !important; border-bottom-color: rgba(61, 50, 40, 0.5) !important; }
.fi-global-search-field input { border-radius: 24px !important; background: rgba(248, 243, 236, 0.8) !important; border: 1px solid rgba(234, 226, 214, 0.6) !important; font-size: 0.8rem !important; }
:is(.dark) .fi-global-search-field input { background: rgba(42, 33, 24, 0.8) !important; border-color: rgba(61, 50, 40, 0.5) !important; }

/* ===== MAIN CONTENT ===== */
.fi-main { background: #FDFBF7 !important; }
:is(.dark) .fi-main { background: #1A140E !important; }
.fi-header-heading { font-family: 'Georgia', serif !important; font-weight: 600 !important; color: #2C2018 !important; }
:is(.dark) .fi-header-heading { color: #E8DDD0 !important; }

/* ===== STAT CARDS ===== */
.fi-wi-stats-overview-stat { border-radius: 12px !important; border: 1px solid rgba(234, 226, 214, 0.5) !important; background: white !important; box-shadow: 0 1px 3px rgba(44, 32, 24, 0.04) !important; transition: all 0.3s ease !important; }
.fi-wi-stats-overview-stat:hover { box-shadow: 0 4px 12px rgba(44, 32, 24, 0.08) !important; transform: translateY(-2px) !important; }
:is(.dark) .fi-wi-stats-overview-stat { background: #2A2118 !important; border-color: rgba(61, 50, 40, 0.4) !important; }
.fi-wi-stats-overview-stat-value { font-size: 1.75rem !important; font-weight: 700 !important; font-family: 'Georgia', serif !important; }
.fi-wi-stats-overview-stat-label { font-size: 0.75rem !important; font-weight: 500 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; color: #6B5D50 !important; }

/* ===== TABLES ===== */
.fi-ta-table { border-radius: 12px !important; overflow: hidden !important; }
.fi-ta-row { transition: background-color 0.15s ease !important; }
.fi-ta-row:hover { background-color: rgba(248, 243, 236, 0.5) !important; }
:is(.dark) .fi-ta-row:hover { background-color: rgba(42, 33, 24, 0.5) !important; }
.fi-ta-header-cell { font-size: 0.7rem !important; font-weight: 700 !important; text-transform: uppercase !important; letter-spacing: 0.8px !important; color: #9C8E80 !important; }

/* ===== FORMS ===== */
.fi-input { border-radius: 8px !important; transition: border-color 0.2s ease, box-shadow 0.2s ease !important; }
.fi-input:focus { box-shadow: 0 0 0 3px rgba(139, 94, 60, 0.1) !important; }

/* ===== BUTTONS ===== */
.fi-btn { border-radius: 8px !important; font-weight: 600 !important; font-size: 0.8rem !important; transition: all 0.2s ease !important; }
.fi-btn:hover { transform: translateY(-1px) !important; box-shadow: 0 2px 8px rgba(139, 94, 60, 0.15) !important; }

/* ===== BADGES ===== */
.fi-badge { border-radius: 6px !important; font-size: 0.65rem !important; font-weight: 700 !important; letter-spacing: 0.3px !important; }

/* ===== MODAL ===== */
.fi-modal-window { border-radius: 16px !important; }

/* ===== LOGIN PAGE ===== */
.fi-simple-layout { background: linear-gradient(135deg, #FDFBF7 0%, #F8F3EC 50%, #EAE2D6 100%) !important; }
:is(.dark) .fi-simple-layout { background: linear-gradient(135deg, #1A140E 0%, #221B14 50%, #2A2118 100%) !important; }
.fi-simple-main-ctn { max-width: 420px !important; }
.fi-simple-page { border-radius: 16px !important; border: 1px solid rgba(234, 226, 214, 0.5) !important; box-shadow: 0 8px 30px rgba(44, 32, 24, 0.08) !important; }
:is(.dark) .fi-simple-page { border-color: rgba(61, 50, 40, 0.4) !important; background: #2A2118 !important; }

/* ===== SECTION HEADINGS ===== */
.fi-section-header-heading { font-family: 'Georgia', serif !important; font-size: 1rem !important; font-weight: 600 !important; }

/* ===== CHART/TABLE WIDGETS ===== */
.fi-wi-chart, .fi-wi-table { border-radius: 12px !important; border: 1px solid rgba(234, 226, 214, 0.5) !important; overflow: hidden !important; }
:is(.dark) .fi-wi-chart, :is(.dark) .fi-wi-table { border-color: rgba(61, 50, 40, 0.4) !important; }

/* ===== GREETING BANNER: vibrant indigo/purple gradient ===== */
.himashva-greeting { background: linear-gradient(135deg, #1e1b4b 0%, #312e81 30%, #4338ca 60%, #6366f1 100%); border-radius: 16px; padding: 2rem 2.5rem; margin-bottom: 1.5rem; color: white; position: relative; overflow: hidden; }
.himashva-greeting::before { content: ''; position: absolute; top: -50%; right: -20%; width: 300px; height: 300px; background: radial-gradient(circle, rgba(129, 140, 248, 0.25), transparent 70%); border-radius: 50%; pointer-events: none; }
.himashva-greeting::after { content: ''; position: absolute; bottom: -60px; left: 20%; width: 220px; height: 220px; background: radial-gradient(circle, rgba(255, 255, 255, 0.08), transparent 70%); border-radius: 50%; pointer-events: none; }
.himashva-greeting__title { font-family: 'Georgia', serif; font-size: 1.5rem; font-weight: 600; margin-bottom: 0.25rem; position: relative; z-index: 1; }
.himashva-greeting__subtitle { font-size: 0.85rem; opacity: 0.8; position: relative; z-index: 1; }
.himashva-greeting__stats { display: flex; gap: 2rem; margin-top: 1rem; position: relative; z-index: 1; flex-wrap: wrap; }
.himashva-greeting__stat { text-align: center; background: rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 0.75rem 1.25rem; backdrop-filter: blur(4px); border: 1px solid rgba(255, 255, 255, 0.08); min-width: 120px; }
.himashva-greeting__stat-value { font-family: 'Georgia', serif; font-size: 1.75rem; font-weight: 700; }
.himashva-greeting__stat-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.7; }
@media (max-width: 768px) { .himashva-greeting { padding: 1.25rem 1.5rem; } .himashva-greeting__title { font-size: 1.15rem; } .himashva-greeting__stats { gap: 0.75rem; } .himashva-greeting__stat { min-width: 80px; padding: 0.5rem 0.75rem; } .himashva-greeting__stat-value { font-size: 1.25rem; } }
</style>

<style>
/* ===== QUICK ACCESS CARDS ===== */
/* This panel has no custom Filament theme registered (no ->theme() call
   in AdminPanelProvider), so it ships Filament's prebuilt/purged vendor
   CSS bundle rather than a build that scans this app's Blade views.
   Tailwind utility classes used only inside our own widget views (like
   `grid-cols-2 sm:grid-cols-4 lg:grid-cols-8`) never get compiled in and
   are silently absent at runtime — confirmed live: the grid container
   computed to a single ~full-width column. Scoped, hand-written CSS
   here (loaded via the panels::head.end renderHook) is guaranteed to
   ship regardless of any Tailwind build/purge step. */
.hv-quick-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
@media (min-width: 640px) { .hv-quick-grid { grid-template-columns: repeat(4, 1fr); } }
@media (min-width: 1024px) { .hv-quick-grid { grid-template-columns: repeat(4, 1fr); } }
@media (min-width: 1280px) { .hv-quick-grid { grid-template-columns: repeat(8, 1fr); } }
.quick-card { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.25rem 1rem; border-radius: 12px; color: white; text-decoration: none; transition: all 0.3s ease; position: relative; overflow: hidden; min-height: 100px; min-width: 0; }
.quick-card::before { content: ''; position: absolute; top: -50%; right: -50%; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.08); border-radius: 50%; transition: all 0.5s ease; }
.quick-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); }
.quick-card:hover::before { transform: scale(2); }
.quick-card-icon { margin-bottom: 0.5rem; opacity: 0.9; }
.quick-card-label { font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; }
.quick-card-badge { position: absolute; top: 8px; right: 10px; background: rgba(255, 255, 255, 0.25); padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; font-weight: 700; }
/* ===== BUSINESS STAT CARDS ===== */
/* Grid tracks defined with `1fr` still size to each item's intrinsic
   min-content width before distributing free space (a flex/grid item's
   automatic minimum size is `auto`, not 0). .bstat-card is a flex
   container whose label text ("PENDING RETURNS" etc, uppercase +
   letter-spacing) refused to shrink, so tracks grew past their 1fr
   share and the grid's scrollWidth exceeded its container — confirmed
   live (six columns measuring 179–201px each vs. an 835px container,
   overflowing to 1139px and clipping the last column/cards on the
   right). `min-width: 0` lets grid/flex items shrink below their
   content size so the track — and the card's own label — can wrap
   instead of overflowing. */
.bstat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; overflow: hidden; }
@media (min-width: 768px) { .bstat-grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 1280px) { .bstat-grid { grid-template-columns: repeat(6, 1fr); } }
.bstat-card { background: white; border-radius: 12px; padding: 1.25rem; border: 1px solid rgba(234, 226, 214, 0.5); display: flex; align-items: center; gap: 1rem; transition: all 0.3s ease; min-width: 0; }
:is(.dark) .bstat-card { background: #2A2118; border-color: rgba(61, 50, 40, 0.4); }
.bstat-card:hover { box-shadow: 0 4px 15px rgba(44, 32, 24, 0.08); transform: translateY(-2px); }
.bstat-card > div { min-width: 0; }
.bstat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.bstat-icon svg { width: 24px; height: 24px; color: white; }
.bstat-value { font-family: 'Georgia', serif; font-size: 1.5rem; font-weight: 700; color: #2C2018; line-height: 1.2; }
:is(.dark) .bstat-value { color: #E8DDD0; }
.bstat-label { font-size: 0.7rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; color: #9C8E80; margin-top: 2px; overflow-wrap: break-word; }
.bstat-trend { display: inline-flex; align-items: center; gap: 2px; font-size: 0.65rem; font-weight: 600; padding: 2px 6px; border-radius: 4px; margin-top: 4px; }
.bstat-trend-up { color: #059669; background: #D1FAE5; }
.bstat-trend-down { color: #DC2626; background: #FEE2E2; }
/* ===== ACTIVITY TIMELINE ===== */
.activity-timeline { list-style: none; padding: 0; margin: 0; }
.activity-item { display: flex; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid rgba(234, 226, 214, 0.3); font-size: 0.8rem; }
:is(.dark) .activity-item { border-bottom-color: rgba(61, 50, 40, 0.3); }
.activity-item:last-child { border-bottom: none; }
.activity-dot { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.activity-dot svg { width: 16px; height: 16px; color: white; }
.activity-text { color: #6B5D50; line-height: 1.4; }
:is(.dark) .activity-text { color: rgba(232, 221, 208, 0.7); }
.activity-time { font-size: 0.7rem; color: #9C8E80; margin-top: 2px; }
/* ===== TOP PRODUCTS MINI TABLE ===== */
.top-product-row { display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0; border-bottom: 1px solid rgba(234, 226, 214, 0.3); }
:is(.dark) .top-product-row { border-bottom-color: rgba(61, 50, 40, 0.3); }
.top-product-row:last-child { border-bottom: none; }
.top-product-img { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; background: #F3F0EB; }
.top-product-info { flex: 1; min-width: 0; }
.top-product-name { font-size: 0.8rem; font-weight: 600; color: #2C2018; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
:is(.dark) .top-product-name { color: #E8DDD0; }
.top-product-bar { height: 4px; border-radius: 2px; background: #F3F0EB; margin-top: 4px; overflow: hidden; }
:is(.dark) .top-product-bar { background: rgba(61, 50, 40, 0.4); }
.top-product-bar-fill { height: 100%; border-radius: 2px; background: linear-gradient(90deg, #8B5E3C, #C9A77D); transition: width 0.6s ease; }
.top-product-stat { text-align: right; flex-shrink: 0; }
.top-product-sold { font-size: 0.8rem; font-weight: 700; color: #2C2018; }
:is(.dark) .top-product-sold { color: #E8DDD0; }
.top-product-revenue { font-size: 0.65rem; color: #9C8E80; }
/* ===== DOUGHNUT CENTER OVERLAY ===== */
.chart-center-overlay-wrap { position: relative; }
.chart-center-overlay { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none; }
.chart-center-overlay-value { font-family: 'Georgia', serif; font-size: 1.75rem; font-weight: 700; color: #2C2018; line-height: 1; }
:is(.dark) .chart-center-overlay-value { color: #E8DDD0; }
.chart-center-overlay-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px; color: #9C8E80; margin-top: 2px; }
/* ===== SPARKLINE ===== */
.sparkline-bars { display: flex; align-items: flex-end; gap: 3px; height: 60px; }
.sparkline-bar { flex: 1; background: linear-gradient(180deg, #C9A77D, #8B5E3C); border-radius: 3px 3px 0 0; min-height: 3px; transition: opacity 0.2s ease; }
.sparkline-bar:hover { opacity: 0.75; }
.sparkline-caption { text-align: center; font-size: 0.75rem; color: #9C8E80; margin-top: 0.5rem; }
</style>
