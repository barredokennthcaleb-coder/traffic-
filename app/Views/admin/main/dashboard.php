<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Admin Dashboard<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    :root {
        --dash-surface: #ffffff;
        --dash-panel: #f8fafc;
        --dash-text: #0f172a;
        --dash-muted: #64748b;
        --dash-blue: #2563eb;
        --dash-green: #047857;
        --dash-kpi-blue-start: #2e5cf7;
        --dash-kpi-blue-end: #0040e5;
        --dash-kpi-green-start: #10b981;
        --dash-kpi-green-end: #047857;
        --dash-kpi-cyan-start: #06b6d4;
        --dash-kpi-cyan-end: #0891b2;
        --dash-kpi-amber-start: #d97706;
        --dash-kpi-amber-end: #92400e;
        --dash-kpi-white: #ffffff;
        --dash-indigo: #6366f1;
        --dash-chart-amber: #ca8a04;
        --dash-chart-red: #ef4444;
        --dash-status-pending: #d6a52f;
        --dash-status-paid: #047857;
        --dash-status-neutral: #64748b;
        --dash-role-admin: #0066ff;
        --dash-role-enforcer: #047857;
        --dash-role-driver: #64748b;
        --dash-nature-palette: #4f46e5, #0891b2, #059669, #d97706, #db2777, #dc2626, #7c3aed, #0f766e, #ea580c, #65a30d, #0284c7, #9333ea, #16a34a, #c2410c, #be123c, #6d28d9, #0e7490, #047857, #b45309, #be185d;
        --dash-skeleton: #e2e8f0;
        --dash-chart-grid: rgba(148, 163, 184, 0.22);
        --dash-highlight: rgba(37, 99, 235, 0.07);
        --dash-ease: cubic-bezier(.22, .61, .36, 1);
    }

    :root[data-bs-theme="dark"] {
        --dash-surface: #0f172a;
        --dash-panel: #111b2f;
        --dash-text: #f8fafc;
        --dash-muted: #cbd5e1;
        --dash-skeleton: #334155;
        --dash-chart-grid: rgba(148, 163, 184, 0.3);
        --dash-highlight: rgba(96, 165, 250, 0.12);
    }

    .admin-dashboard .dashboard-kpi-row {
        --bs-gutter-x: 1.5rem;
        --bs-gutter-y: 1.5rem;
    }

    .admin-dashboard .dashboard-kpi-grid {
        --bs-gutter-x: clamp(0.75rem, 1.5vw, 1.5rem);
        --bs-gutter-y: clamp(0.75rem, 1.5vw, 1.5rem);
    }

    .admin-dashboard .dashboard-kpi-grid > .col-md-3 > a {
        display: block;
        height: 100%;
    }

    .admin-dashboard .premium-kpi {
        transition: transform 240ms cubic-bezier(0.2, 0.7, 0.2, 1), box-shadow 240ms ease;
    }

    /* The KPI cards float on an infinite keyframe animation and carry
       `will-change: transform`, so each one is its own stacking context. This
       header is one too (it animates in via .premium-reveal). With both at
       z-index auto they paint in tree order, so these later cards covered the
       header — which is what put the Generate Reports menu behind them. A
       positive z-index lifts the whole header, dropdown included, above the
       grid; a z-index on the menu itself could never escape its parent's
       stacking context. */
    .admin-dashboard .dashboard-page-heading {
        position: relative;
        z-index: 20;
    }

    .admin-dashboard .premium-kpi i {
        transition: transform 220ms cubic-bezier(0.2, 0.7, 0.2, 1), opacity 220ms ease;
    }

    @media (hover: hover) and (prefers-reduced-motion: no-preference) {
        .admin-dashboard .premium-kpi:hover {
            transform: translateY(-3px) scale(1.005) !important;
            box-shadow: 0 16px 32px -14px rgba(15, 23, 42, 0.38) !important;
        }

        .admin-dashboard .premium-kpi:hover i {
            transform: scale(1.06);
        }
    }

    .admin-dashboard .dashboard-kpi-grid > .col-md-3 > a:focus-visible .premium-kpi {
        transform: translateY(-2px) !important;
        box-shadow: 0 0 0 3px #ffffff, 0 0 0 5px #2563eb !important;
    }

    .admin-dashboard .dashboard-kpi-grid > .col-md-3 > a:active .premium-kpi {
        transform: scale(0.99) !important;
    }

    .admin-dashboard .premium-kpi .kpi-top-violation-name {
        display: -webkit-box;
        overflow: hidden;
        white-space: normal;
        overflow-wrap: anywhere;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        font-size: clamp(0.9rem, 2.2vw, 1.35rem);
        line-height: 1.15;
    }

    @media (min-width: 576px) and (max-width: 1199.98px) {
        .admin-dashboard .dashboard-kpi-grid > .col-md-3 {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    @media (max-width: 575.98px) {
        .admin-dashboard .dashboard-kpi-grid > .col-md-3 {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    .admin-dashboard .dashboard-kpi {
        --mouse-x: 50%;
        --mouse-y: 50%;
        position: relative;
        overflow: hidden;
        min-height: 236px;
        color: var(--dash-kpi-white) !important;
        background: linear-gradient(135deg, var(--dash-kpi-blue-start), var(--dash-kpi-blue-end)) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 22px;
        box-shadow: 0 10px 25px -5px rgba(46, 92, 247, 0.2);
        text-decoration: none;
        display: flex;
        flex-direction: column;
        transition: transform 280ms var(--dash-ease), box-shadow 280ms var(--dash-ease), opacity 280ms var(--dash-ease), filter 280ms var(--dash-ease);
        opacity: 0;
        transform: translateY(16px);
        outline: none !important;
    }

    .admin-dashboard .dashboard-kpi:focus-visible {
        box-shadow: 0 0 0 3px var(--dash-surface), 0 0 0 6px var(--dash-kpi-white);
        transform: translateY(-5px);
    }

    .admin-dashboard .dashboard-kpi.is-revealed {
        opacity: 1;
        transform: translateY(0);
    }

    .admin-dashboard:has(.dashboard-kpi:hover) .dashboard-kpi:not(:hover) {
        opacity: 0.82;
        filter: saturate(0.8) brightness(0.95);
    }

    @media (pointer: coarse) {
        .admin-dashboard:has(.dashboard-kpi:hover) .dashboard-kpi:not(:hover) {
            opacity: 1;
            filter: none;
        }
    }

    .admin-dashboard .dashboard-kpi::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at var(--mouse-x) var(--mouse-y), rgba(255, 255, 255, 0.05), transparent 55%);
        opacity: 0;
        transition: opacity 320ms var(--dash-ease);
        pointer-events: none;
        z-index: 2;
    }

    .admin-dashboard .dashboard-kpi:hover::before {
        opacity: 1;
    }

    .admin-dashboard .dashboard-kpi::after {
        content: "";
        position: absolute;
        top: 0; left: -150%;
        width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.06), transparent);
        transform: skewX(-25deg);
        pointer-events: none;
        z-index: 3;
    }

    .admin-dashboard .dashboard-kpi:hover::after {
        animation: dashboard-kpi-sweep 900ms var(--dash-ease) forwards;
    }

    @keyframes dashboard-kpi-sweep {
        from { left: -150%; }
        to { left: 150%; }
    }

    .admin-dashboard .dashboard-kpi:hover {
        transform: translateY(-5px);
        box-shadow: 0 22px 42px -12px rgba(46, 92, 247, 0.45);
    }

    .admin-dashboard .dashboard-kpi:active {
        transform: translateY(-2px) scale(0.99);
        box-shadow: 0 10px 20px -5px rgba(46, 92, 247, 0.3);
    }

    .admin-dashboard .dashboard-kpi--revenue {
        background: linear-gradient(135deg, var(--dash-kpi-green-start), var(--dash-kpi-green-end)) !important;
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.2);
    }
    .admin-dashboard .dashboard-kpi--revenue:hover { box-shadow: 0 20px 40px -10px rgba(16, 185, 129, 0.4); }
    .admin-dashboard .dashboard-kpi--revenue:active { box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.3); }

    .admin-dashboard .dashboard-kpi--collection {
        background: linear-gradient(135deg, var(--dash-kpi-cyan-start), var(--dash-kpi-cyan-end)) !important;
        box-shadow: 0 10px 25px -5px rgba(8, 145, 178, 0.2);
    }
    .admin-dashboard .dashboard-kpi--collection:hover { box-shadow: 0 20px 40px -10px rgba(8, 145, 178, 0.4); }
    .admin-dashboard .dashboard-kpi--collection:active { box-shadow: 0 10px 20px -5px rgba(8, 145, 178, 0.3); }

    .admin-dashboard .dashboard-kpi--drivers {
        background: linear-gradient(135deg, var(--dash-kpi-amber-start), var(--dash-kpi-amber-end)) !important;
        box-shadow: 0 10px 25px -5px rgba(146, 64, 14, 0.2);
    }
    .admin-dashboard .dashboard-kpi--drivers:hover { box-shadow: 0 22px 42px -12px rgba(120, 53, 15, 0.45); }
    .admin-dashboard .dashboard-kpi--drivers:active { box-shadow: 0 10px 20px -5px rgba(120, 53, 15, 0.3); }

    /* ── Uniform internal structure: top row ── */
    .admin-dashboard .dashboard-kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .admin-dashboard .dashboard-kpi h6 {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        opacity: 0.9;
        margin-bottom: 0.35rem;
    }

    .admin-dashboard .dashboard-kpi h3 {
        font-size: 2.2rem;
        font-weight: 900;
        letter-spacing: -0.02em;
        line-height: 1;
        margin-bottom: 0;
        font-variant-numeric: tabular-nums;
    }

    .admin-dashboard .dashboard-kpi-icon {
        flex: 0 0 auto;
        width: 46px;
        height: 46px;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        transition: transform 280ms var(--dash-ease);
    }

    .admin-dashboard .dashboard-kpi:hover .dashboard-kpi-icon {
        transform: scale(1.1) rotate(6deg);
    }

    /* ── Short rounded chip under the number ── */
    .admin-dashboard .dashboard-kpi-chip {
        align-self: flex-start;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 0.85rem;
        padding: 0.28rem 0.7rem;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        transition: transform 280ms var(--dash-ease);
    }

    .admin-dashboard .dashboard-kpi:hover .dashboard-kpi-chip {
        transform: translateX(3px);
    }

    /* ── Middle area (larger sparkline / progress) ── */
    .admin-dashboard .dashboard-kpi-middle {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.6rem;
        padding: 0.9rem 0 0.4rem;
        min-height: 88px;
    }

    .admin-dashboard .dashboard-sparkline {
        width: 100%;
        height: 72px;
        overflow: visible;
    }

    .admin-dashboard .dashboard-sparkline path {
        fill: none;
        stroke: currentColor;
        stroke-width: 2.5;
        stroke-linecap: round;
        stroke-linejoin: round;
        transition: stroke-width 280ms var(--dash-ease);
        stroke-dasharray: 400;
        stroke-dashoffset: 400;
    }

    .admin-dashboard .dashboard-kpi.is-revealed .dashboard-sparkline path {
        animation: sparkline-draw 1s var(--dash-ease) forwards;
    }

    @keyframes sparkline-draw {
        to { stroke-dashoffset: 0; }
    }

    .admin-dashboard .dashboard-kpi:hover .dashboard-sparkline path {
        stroke-width: 4;
    }

    .admin-dashboard .sparkline-dot {
        fill: currentColor;
        transform-box: fill-box;
        transform-origin: center;
        opacity: 0;
    }

    .admin-dashboard .dashboard-kpi.is-revealed .sparkline-dot {
        transition: opacity 300ms var(--dash-ease) 1s;
        opacity: 1;
    }

    .admin-dashboard .dashboard-kpi:hover .sparkline-dot {
        animation: sparkline-dot-pulse 2s var(--dash-ease) infinite;
    }

    @keyframes sparkline-dot-pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.9); opacity: 0.45; }
        100% { transform: scale(1); opacity: 1; }
    }

    .admin-dashboard .dashboard-collection-amounts {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.5rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .admin-dashboard .dashboard-collection-amounts .is-primary { font-size: 1.05rem; }
    .admin-dashboard .dashboard-collection-amounts .is-secondary { font-size: 0.78rem; opacity: 0.82; }

    /* ── Footer: divider, text left, arrow right ── */
    .admin-dashboard .dashboard-kpi-footer {
        padding-top: 0.85rem;
        border-top: 1px solid rgba(255, 255, 255, 0.18);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
    }

    .admin-dashboard .dashboard-kpi-footer-text {
        flex: 1 1 auto;
        min-width: 0;
        font-size: 13px;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.96);
        line-height: 1.3;
    }

    .admin-dashboard .dashboard-kpi-arrow {
        flex: 0 0 auto;
        transition: transform 280ms var(--dash-ease);
    }

    .admin-dashboard .dashboard-kpi:hover .dashboard-kpi-arrow {
        transform: translateX(5px);
    }

    .admin-dashboard .dashboard-collection-track {
        width: 100%;
        height: 8px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 999px;
        overflow: hidden;
    }

    .admin-dashboard .dashboard-collection-progress {
        height: 100%;
        background: var(--dash-kpi-white);
        width: 0%;
        border-radius: 999px;
        transition: width 1s var(--dash-ease);
    }

    /* Keep both analytics card headers on one line so the charts below
       start at the same height. The month hint could otherwise squeeze
       the title into a three-line stack on narrower desktops. */
    .admin-dashboard .dashboard-chart-title {
        font-size: 1.0625rem;
        white-space: nowrap;
    }

    /* Just above the four-up breakpoint the two chart cards are at their
       narrowest. A small step down keeps each header on one line. */
    @media (min-width: 1200px) and (max-width: 1279.98px) {
        .admin-dashboard .dashboard-chart-title { font-size: 1rem; }
    }

    /* ── Chart shells ────────────────────────────────────────────
       Chart.js runs with maintainAspectRatio:false, so each canvas
       takes its height from this wrapper. Without an explicit height
       the wrapper grows with the canvas, and Chart.js keeps resizing
       it taller on every frame — the runaway "melting" bars. */
    .admin-dashboard .dashboard-chart-shell {
        position: relative;
        width: 100%;
        height: 320px;
        min-height: 240px;
        overflow: hidden;
    }

    .admin-dashboard .dashboard-chart-shell > canvas {
        display: block;
        max-width: 100%;
    }

    .admin-dashboard .chart-panel-muted {
        background: var(--surface-muted, var(--dash-panel));
    }

    /* Skeleton + empty-state overlays */
    .admin-dashboard .chart-skeleton,
    .admin-dashboard .chart-empty-state {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 1rem;
        text-align: center;
        background: var(--surface-card, var(--dash-surface));
        border-radius: 12px;
        pointer-events: none;
        transition: opacity 200ms var(--dash-ease), visibility 200ms var(--dash-ease);
    }

    .admin-dashboard .chart-skeleton {
        opacity: 1;
        visibility: visible;
    }

    .admin-dashboard .dashboard-chart-shell.is-loaded .chart-skeleton {
        opacity: 0;
        visibility: hidden;
    }

    .admin-dashboard .chart-empty-state {
        opacity: 0;
        visibility: hidden;
    }

    .admin-dashboard .dashboard-chart-shell.is-empty .chart-empty-state {
        opacity: 1;
        visibility: visible;
    }

    .admin-dashboard .dashboard-chart-shell.is-empty > canvas {
        visibility: hidden;
    }

    .admin-dashboard .chart-empty-state i {
        font-size: 1.6rem;
        color: var(--dash-muted);
        opacity: 0.6;
    }

    .admin-dashboard .chart-empty-state strong {
        font-size: 0.92rem;
        color: var(--text-strong, var(--dash-text));
    }

    .admin-dashboard .chart-empty-state span {
        max-width: 28ch;
        font-size: 0.78rem;
        color: var(--dash-muted);
    }

    .admin-dashboard .chart-skeleton-bars {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        width: min(70%, 340px);
        height: 52%;
    }

    .admin-dashboard .chart-skeleton-bars span {
        flex: 1 1 0;
        height: 60%;
        border-radius: 6px 6px 2px 2px;
        background: var(--surface-muted, var(--dash-skeleton));
        transform-origin: bottom;
        animation: chart-skeleton-pulse 1.5s var(--dash-ease) infinite;
    }

    .admin-dashboard .chart-skeleton-bars span:nth-child(2n) { height: 82%; animation-delay: 0.12s; }
    .admin-dashboard .chart-skeleton-bars span:nth-child(3n) { height: 100%; animation-delay: 0.24s; }
    .admin-dashboard .chart-skeleton-bars span:nth-child(4n) { height: 46%; animation-delay: 0.36s; }

    .admin-dashboard .chart-skeleton-line {
        width: min(70%, 340px);
        height: 8px;
        margin-top: 12px;
        border-radius: 999px;
        background: var(--surface-muted, var(--dash-skeleton));
    }

    @keyframes chart-skeleton-pulse {
        0%, 100% { opacity: 0.55; transform: scaleY(0.85); }
        50%      { opacity: 1; transform: scaleY(1); }
    }

    @media (max-width: 575.98px) {
        .admin-dashboard .dashboard-chart-shell { height: 260px; }
        .admin-dashboard .chart-skeleton-bars,
        .admin-dashboard .chart-skeleton-line { width: 82%; }
    }

    @media (prefers-reduced-motion: reduce) {
        .admin-dashboard .chart-skeleton-bars span { animation: none; }
    }

    @media (max-width: 1199.98px) {
        .col-lg-3 { width: 50%; }
    }

    @media (max-width: 575.98px) {
        .col-lg-3 { width: 100%; }
        .admin-dashboard .dashboard-kpi h3 { font-size: 1.85rem; }
    }

    @media (prefers-reduced-motion: reduce) {
        .admin-dashboard .dashboard-kpi,
        .admin-dashboard .dashboard-kpi::after,
        .admin-dashboard .dashboard-kpi-icon,
        .admin-dashboard .dashboard-kpi-chip,
        .admin-dashboard .dashboard-sparkline path,
        .admin-dashboard .sparkline-dot,
        .admin-dashboard .dashboard-kpi-arrow,
        .admin-dashboard .dashboard-collection-progress {
            animation: none !important;
            transition: none !important;
            opacity: 1 !important;
            transform: none !important;
            stroke-dashoffset: 0 !important;
        }
        .admin-dashboard .dashboard-kpi { opacity: 1 !important; transform: none !important; }
        .admin-dashboard .dashboard-kpi::before { opacity: 0 !important; }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4 admin-dashboard">
    <?php
        // The report buttons carry the dashboard's own filters. Without this
        // the Revenue Report always opened on the current calendar year even
        // when the dashboard was showing an earlier one, so the printed
        // records never matched the screen the admin clicked from.
        $reportYear  = (int) $year;
        $reportMonth = ! empty($selected_month) ? sprintf('%02d', (int) $selected_month) : date('m');

        $reportWeekStart = $reportYear === (int) date('Y')
            ? date('Y-m-d', strtotime('monday this week'))
            : date('Y-m-d', strtotime('first monday of january ' . $reportYear));

        $reportYearlyUrl  = base_url('reports?' . http_build_query(['period_type' => 'yearly',  'year' => $reportYear]));
        $reportWeeklyUrl  = base_url('reports?' . http_build_query(['period_type' => 'weekly',  'start_date' => $reportWeekStart]));
        $reportMonthlyUrl = base_url('reports?' . http_build_query(['period_type' => 'monthly', 'year' => $reportYear, 'month' => $reportMonth]));
        $reportCustomUrl  = base_url('reports?' . http_build_query(['period_type' => 'custom',  'start_date' => sprintf('%04d-01-01', $reportYear), 'end_date' => sprintf('%04d-12-31', $reportYear)]));
    ?>
    <!-- Header with Year Filter & Generate Report Button -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 premium-reveal dashboard-page-heading" style="--reveal-delay: 0s;">
        <div class="dashboard-title-block">
            <h4 class="mb-1 fw-bold" style="color: var(--text-strong);"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard &amp; Analytics</h4>
            <p class="text-muted mb-0 small">Comprehensive insights into system activity, violation trends, and revenue.</p>
            <div id="liveClock" class="d-inline-flex align-items-center gap-2 mt-2 px-3 py-1 rounded-pill small fw-semibold dashboard-clock">
                <i class="bi bi-clock-fill"></i> <span id="clockText">Loading...</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end dashboard-actions">
            <!-- Generate Reports Split Button -->
            <div class="btn-group shadow-sm">
                <a href="<?= $reportYearlyUrl ?>" class="btn btn-primary btn-sm fw-bold px-3 py-2 d-inline-flex align-items-center">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Generate Reports
                </a>
                <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split px-2" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More report options">
                    <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="min-width: 220px; z-index: 1050;">
                    <li><h6 class="dropdown-header text-uppercase fw-bold text-muted px-2" style="font-size: 0.7rem;">Quick Reports</h6></li>
                    <li>
                        <a class="dropdown-item rounded py-2 d-flex align-items-center" href="<?= $reportWeeklyUrl ?>">
                            <i class="bi bi-calendar-week text-primary me-2 fs-6"></i> Weekly Report
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item rounded py-2 d-flex align-items-center" href="<?= $reportMonthlyUrl ?>">
                            <i class="bi bi-calendar-month text-success me-2 fs-6"></i> Monthly Report
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item rounded py-2 d-flex align-items-center" href="<?= $reportCustomUrl ?>">
                            <i class="bi bi-sliders text-warning me-2 fs-6"></i> Custom Filter Report
                        </a>
                    </li>
                </ul>
            </div>

            <form method="GET" class="d-flex align-items-center gap-2 bg-white p-2 rounded shadow-sm border border-primary-subtle">
                <label for="year" class="form-label mb-0 fw-semibold text-primary px-2">Year:</label>
                <select name="year" id="year" class="form-select form-select-sm border-0 bg-light" style="width: 100px; font-weight: bold;" onchange="this.form.submit()">
                    <?php $currentYear = date('Y'); ?>
                    <?php for($y = $currentYear; $y >= $currentYear - 4; $y--): ?>
                        <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- ═══ ANALYTICS TOGGLE BAR ════════════════════════════════════ -->
    <div class="admin-analytics-toggle-bar d-flex align-items-center gap-2 mb-3">
        <span class="admin-analytics-toggle-label">
            <i class="bi bi-bar-chart-line me-1 text-primary"></i>
            <span class="fw-bold" style="font-size:0.95rem;">Dashboard Analytics</span>
        </span>
        <button type="button" id="adminAnalyticsToggleBtn" class="btn-admin-analytics-toggle ms-1" title="Toggle Analytics" aria-label="Toggle dashboard analytics">
            <i class="bi bi-eye-slash me-1" id="adminAnalyticsToggleIcon"></i>
            <span id="adminAnalyticsToggleText">Hide</span>
        </button>
        <span class="admin-analytics-visible-pill" id="adminAnalyticsVisiblePill"></span>
    </div>

    <!-- ═══ ANALYTICS SECTION ══════════════════════════════════════ -->
    <div id="adminAnalyticsSection" class="admin-analytics-section-wrap">
    <div class="admin-analytics-inner">
    <div class="row g-4 mb-4 dashboard-kpi-grid">
        <div class="col-md-3">
            <a href="<?= base_url('violators') ?>" class="text-decoration-none d-block h-100" aria-label="Open the violator directory">
            <div class="card premium-kpi premium-reveal text-white border-0 h-100" style="--reveal-delay: 0.04s; --float-delay: 0s; cursor:pointer; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="text-uppercase mb-0 fw-bold opacity-75 small" style="letter-spacing: 0.5px;">Violators (<?= esc((string) $year) ?>)</h6>
                            </div>
                            <h3 class="mb-0 fw-bolder kpi-countup" data-target="<?= (int) ($summary['total_violators'] ?? count($top_violators_list)) ?>" data-prefix="" data-suffix="">0</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-2.5 d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-people-fill text-white fs-4 lh-1"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-2 border-top border-white border-opacity-25">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="small opacity-75 fw-semibold" style="font-size: 0.75rem;"><?= number_format((int) ($summary['total_violations'] ?? 0)) ?> cited tickets · Open violator directory</span>
                            <i class="bi bi-arrow-right-circle opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="<?= base_url('penalties/history') ?>" class="text-decoration-none d-block h-100">
            <div class="card premium-kpi premium-reveal text-white border-0 h-100" style="--reveal-delay: 0.07s; --float-delay: 0.4s; cursor:pointer; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="text-uppercase mb-0 fw-bold opacity-75 small" style="letter-spacing: 0.5px;">Total Revenue</h6>
                            </div>
                            <h3 class="mb-0 fw-bolder kpi-countup" data-target="<?= $summary['total_collected'] ?>" data-prefix="₱" data-suffix="" data-decimals="2">₱0.00</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-2.5 d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-cash-stack text-white fs-4 lh-1"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-2 border-top border-white border-opacity-25">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="small opacity-75 fw-semibold" style="font-size: 0.75rem;">Pending: ₱<?= number_format($summary['total_pending'], 2) ?></span>
                            <i class="bi bi-arrow-right-circle opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="<?= base_url('penalties') ?>" class="text-decoration-none d-block h-100">
            <div class="card premium-kpi premium-reveal text-white border-0 h-100" style="--reveal-delay: 0.10s; --float-delay: 0.8s; cursor:pointer; background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="text-uppercase mb-0 fw-bold opacity-75 small" style="letter-spacing: 0.5px;">Collection Rate</h6>
                            </div>
                            <h3 class="mb-0 fw-bolder kpi-countup" data-target="<?= $summary['collection_rate'] ?>" data-prefix="" data-suffix="%" data-decimals="1">0%</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-2.5 d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-pie-chart text-white fs-4 lh-1"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="progress" style="height: 6px; background-color: rgba(255,255,255,0.2); border-radius: 10px;">
                            <div class="progress-bar bg-white rounded-pill" role="progressbar" style="width: <?= $summary['collection_rate'] ?>%;" aria-valuenow="<?= $summary['collection_rate'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
            </a>
        </div>
        <div class="col-md-3">
            <?php
                $topViolationName = trim((string) ($top_violations_list[0]['violation_type'] ?? ''));
                $topViolationCount = (int) ($top_violations_list[0]['total_count'] ?? 0);
            ?>
            <a href="#topRankingsModal" class="text-decoration-none d-block h-100" data-bs-toggle="modal" data-rankings-tab="violations" aria-label="Open the most violations committed rankings">
            <div class="card premium-kpi premium-reveal text-white border-0 h-100" style="--reveal-delay: 0.13s; --float-delay: 1.2s; cursor:pointer; background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%);">
                <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="text-uppercase mb-0 fw-bold opacity-75 small" style="letter-spacing: 0.5px;">Most Violations Committed</h6>
                            </div>
                            <h3 class="mb-0 fw-bolder kpi-top-violation-name"><?= esc($topViolationName !== '' ? $topViolationName : 'No violations yet') ?></h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-2 d-inline-flex">
                            <i class="bi bi-exclamation-triangle-fill text-white fs-5 lh-1"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-2 border-top border-white border-opacity-25">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <span class="small opacity-75 fw-semibold" style="font-size: 0.75rem;"><?= number_format($topViolationCount) ?> cases recorded in <?= esc((string) $year) ?></span>
                            <i class="bi bi-arrow-right-circle opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            </a>
        </div>
    </div>

    <!-- Analytics Charts Row 1: Weekly & Monthly -->
    <div class="row g-4 mb-4">
        <!-- Weekly Trend -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 premium-reveal" style="--reveal-delay: 0.15s;">
                <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0 fw-bold dashboard-chart-title">
                        <i class="bi bi-calendar-week me-2 text-primary"></i>Weekly Trend 
                        <small class="fw-normal text-muted" style="font-size:0.85rem;">
                            <?= !empty($selected_month_name) ? '(' . esc($selected_month_name) . ' ' . esc($year) . ')' : ($year === (int) date('Y') ? '(Last 12 Weeks)' : '(Last 12 Weeks of ' . esc($year) . ')') ?>
                        </small>
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <form method="GET" class="m-0 d-flex align-items-center gap-1">
                            <input type="hidden" name="year" value="<?= esc($year) ?>">
                            <select name="month" class="form-select form-select-sm border-0 bg-light fw-medium" style="width: 132px; border-radius: 8px;" aria-label="Select month for weekly trend" onchange="this.form.submit()">
                                <option value="" <?= empty($selected_month) ? 'selected' : '' ?>>Last 12 Weeks</option>
                                <optgroup label="<?= esc($year) ?> Breakdown">
                                    <?php 
                                    $adminMonths = [
                                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                    ];
                                    foreach ($adminMonths as $mIdx => $mName): ?>
                                        <option value="<?= $mIdx ?>" <?= ($selected_month == $mIdx) ? 'selected' : '' ?>>
                                            <?= $mName ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </form>
                        <?php if (!empty($selected_month)): ?>
                            <a href="?year=<?= esc($year) ?>" class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-2" style="font-size:0.75rem;" title="Reset to Last 12 Weeks">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($selected_month_name)): ?>
                        <?php 
                            $monthTotalTickets = array_sum(array_column($weekly_trend, 'count'));
                            $monthTotalRevenue = array_sum(array_column($weekly_trend, 'revenue'));
                            $monthPaidRevenue  = array_sum(array_column($weekly_trend, 'paid_revenue'));
                        ?>
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 px-3 py-2 chart-panel-muted rounded-3 border border-light-subtle gap-2">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                                    <i class="bi bi-ticket-perforated me-1"></i><?= $monthTotalTickets ?> Violations in <?= esc($selected_month_name) ?>
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                    <i class="bi bi-cash-stack me-1"></i>₱<?= number_format($monthPaidRevenue, 2) ?> Paid
                                </span>
                            </div>
                            <span class="small text-muted" style="font-size: 0.78rem;">
                                <i class="bi bi-calendar-check me-1"></i>Weekly Breakdown
                            </span>
                        </div>
                    <?php endif; ?>
                    <div class="dashboard-chart-shell" data-dashboard-chart-shell="weekly">
                        <canvas id="weeklyChart" role="img" aria-label="Weekly ticket count and collected revenue trend"></canvas>
                        <div class="chart-skeleton" aria-hidden="true"><div class="chart-skeleton-bars"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div><div class="chart-skeleton-line"></div></div>
                        <div class="chart-empty-state" role="status"><i class="bi bi-bar-chart"></i><strong>No weekly data yet</strong><span>Weekly activity will appear here when available.</span></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Monthly Breakdown -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 premium-reveal" style="--reveal-delay: 0.17s;">
                <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="mb-0 fw-bold dashboard-chart-title"><i class="bi bi-calendar-month me-2 text-success"></i>Monthly Breakdown (<?= esc($year) ?>)</h5>
                        <span class="badge bg-light text-muted border border-light-subtle rounded-pill px-2 py-0.5 d-none d-sm-inline chart-month-hint" style="font-size: 0.7rem;" title="Click any month to inspect its weekly trend">
                            <i class="bi bi-cursor me-1"></i>Click month
                            <button type="button" class="chart-hint-dismiss ms-1" aria-label="Dismiss month chart hint"><i class="bi bi-x" aria-hidden="true"></i></button>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="dashboard-chart-shell" data-dashboard-chart-shell="monthly">
                        <canvas id="monthlyChart" role="img" aria-label="Monthly citation, paid and pending totals"></canvas>
                        <div class="chart-skeleton" aria-hidden="true"><div class="chart-skeleton-bars"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div><div class="chart-skeleton-line"></div></div>
                        <div class="chart-empty-state" role="status"><i class="bi bi-calendar-x"></i><strong>No monthly data yet</strong><span>Monthly activity will appear here when available.</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Row 2: Nature of Violation and Status/Roles -->
    <div class="row g-4 mb-4">
        <!-- Nature of Violation (Real-Time) -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100 premium-reveal" id="natureViolationCard" style="--reveal-delay: 0.19s;">

                <!-- ── Card Header ── -->
                <div class="card-header bg-white py-3 border-bottom-0">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-diagram-3 fs-5 text-info"></i>
                            <h5 class="mb-0 fw-bold">Nature of Violation</h5>
                            <!-- Live Pulse -->
                            <span class="nature-live-badge" id="natureLiveBadge" title="Auto-refreshes every 30 seconds">
                                <span class="nature-live-dot"></span> LIVE
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <!-- Mode Toggle -->
                            <div class="btn-group btn-group-sm shadow-sm" role="group">
                                <button type="button" class="btn btn-outline-primary active" id="btnNatureCount" title="View by incident count">
                                    <i class="bi bi-hash"></i> Incidents
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="btnNatureRevenue" title="View by revenue">
                                    <i class="bi bi-cash"></i> Revenue (₱)
                                </button>
                            </div>
                            <!-- Manual Refresh -->
                            <button class="btn btn-sm btn-light border shadow-sm" id="btnNatureRefresh" title="Refresh now" aria-label="Refresh violation chart">
                                <i class="bi bi-arrow-clockwise" id="natureRefreshIcon" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ── KPI Summary Strip ── -->
                    <div class="d-flex gap-2 mt-3 flex-wrap" id="natureKpiStrip"></div>

                    <!-- ── Search Bar ── -->
                    <div class="mt-2 position-relative">
                        <i class="bi bi-search position-absolute" style="left:10px; top:50%; transform:translateY(-50%); color:var(--text-subtle, #5c6b80); font-size:0.8rem;"></i>
                        <input type="text" id="natureSearchInput"
                            class="form-control form-control-sm ps-4 border-0 bg-light rounded-pill"
                            placeholder="Search violation type…"
                            style="font-size:0.82rem;">
                    </div>

                    <!-- ── Last Updated ── -->
                    <div class="mt-1 text-end" style="font-size:0.72rem; color:var(--text-subtle, #5c6b80);">
                        <i class="bi bi-clock me-1"></i>
                        Last updated: <span id="natureLastUpdated">—</span>
                        &nbsp;·&nbsp; <span id="natureRecordCount">0</span> violation type(s)
                        &nbsp;·&nbsp; CY <?= esc($summary['report_year'] ?? $year) ?>
                    </div>
                </div>

                <!-- ── Spike Alert Banner (hidden by default) ── -->
                <div id="natureSpikeAlert" class="nature-spike-alert d-none mx-3 mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <span id="natureSpikeMsg">A spike was detected!</span>
                </div>

                <!-- ── Chart Body ── -->
                <div class="card-body pt-2">
                    <div class="row align-items-start g-3">
                        <!-- Bar Chart -->
                        <div class="col-md-7">
                            <div class="dashboard-chart-shell" data-dashboard-chart-shell="nature-bar" style="height:340px">
                                <canvas id="natureBarChart" role="img" aria-label="Violation types by incident count"></canvas>
                                <div class="chart-skeleton" aria-hidden="true"><div class="chart-skeleton-bars"><span></span><span></span><span></span><span></span><span></span><span></span></div><div class="chart-skeleton-line"></div></div>
                                <div class="chart-empty-state" role="status"><i class="bi bi-clipboard2-check"></i><strong>No violation data yet</strong><span>Records will appear when citations are available.</span></div>
                            </div>
                        </div>
                        <!-- Doughnut + Scrollable Legend -->
                        <div class="col-md-5">
                            <div class="d-flex flex-column align-items-center gap-2">
                                <div class="dashboard-chart-shell" data-dashboard-chart-shell="nature-doughnut" style="width:200px;height:200px">
                                    <canvas id="natureDoughnutChart" role="img" aria-label="Violation type distribution"></canvas>
                                    <div class="chart-skeleton" aria-hidden="true"><div class="chart-skeleton-bars"><span></span><span></span><span></span><span></span><span></span></div></div>
                                    <div class="chart-empty-state" role="status"><strong>No data</strong></div>
                                </div>
                                <!-- Scrollable Legend -->
                                <div class="nature-legend-scroll w-100" id="natureLegendContainer"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Status and Role Combined Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 premium-reveal" style="--reveal-delay: 0.21s;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold" id="rightChartTitle">
                        <i class="bi bi-pie-chart me-2 text-success"></i>Violator Status (<?= esc((string) $year) ?>)
                    </h5>
                    <div class="btn-group btn-group-sm shadow-sm" role="group">
                        <button type="button" class="btn btn-outline-success active" id="btnStatus" title="Show Violator Status">
                            <i class="bi bi-check-circle"></i> Status
                        </button>
                        <button type="button" class="btn btn-outline-success" id="btnRole" title="Show Users by Role">
                            <i class="bi bi-people"></i> Roles
                        </button>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div class="dashboard-chart-shell" data-dashboard-chart-shell="secondary" style="width:100%;max-width:300px;height:300px">
                        <canvas id="secondaryAnalyticsChart" role="img" aria-label="Violator status distribution"></canvas>
                        <div class="chart-skeleton" aria-hidden="true"><div class="chart-skeleton-bars"><span></span><span></span><span></span><span></span><span></span></div></div>
                        <div class="chart-empty-state" role="status"><i class="bi bi-pie-chart"></i><strong>No chart data yet</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div> <!-- End Collapsible Analytics Inner & Wrapper -->

<!-- Top Rankings Modal -->
<style>
    #topRankingsModal {
        --dash-modal-surface: #ffffff;
        --dash-modal-panel: #f8fafc;
        --dash-modal-text: #0f172a;
        --dash-modal-muted: #64748b;
        --dash-modal-border: #e2e8f0;
        --dash-modal-hover: #f1f5f9;
        --dash-modal-amber-start: #d97706;
        --dash-modal-amber-end: #b45309;
        --dash-modal-amber-bg: #fef3c7;
        --dash-modal-amber-text: #92400e;
        --dash-modal-red-bg: #fee2e2;
        --dash-modal-red-text: #b91c1c;
    }

    :root[data-theme="dark"] #topRankingsModal {
        --dash-modal-surface: #111827;
        --dash-modal-panel: #1f2937;
        --dash-modal-text: #f8fafc;
        --dash-modal-muted: #cbd5e1;
        --dash-modal-border: #374151;
        --dash-modal-hover: #273449;
        --dash-modal-amber-bg: #422006;
        --dash-modal-amber-text: #fcd34d;
        --dash-modal-red-bg: #450a0a;
        --dash-modal-red-text: #fca5a5;
    }

    /* One compact panel: the people and the offenses side by side on a wide
       screen, stacked on a phone. Rows are links, so a name is one tap away
       from its record and a violation one tap from its citations. */
    #topRankingsModal .modal-content {
        border-radius: 18px;
        overflow: hidden;
        color: var(--dash-modal-text);
        background: var(--dash-modal-surface);
        opacity: 1;
    }
    #topRankingsModal .trk-head {
        display: flex; align-items: center; gap: 0.7rem;
        padding: 0.85rem 1.1rem;
        background: linear-gradient(135deg, var(--dash-modal-amber-start) 0%, var(--dash-modal-amber-end) 100%);
        color: #fff;
    }
    #topRankingsModal .trk-head-icon {
        width: 34px; height: 34px; border-radius: 11px; flex: none;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgba(255, 255, 255, 0.22); font-size: 0.95rem;
    }
    #topRankingsModal .trk-title { font-size: 1rem; font-weight: 800; line-height: 1.15; margin: 0; }
    #topRankingsModal .trk-sub { font-size: 0.7rem; opacity: 0.85; }
    #topRankingsModal .trk-switch {
        display: flex; gap: 0.4rem; padding: 0.55rem 0.9rem;
        background: var(--dash-modal-panel); border-bottom: 1px solid var(--dash-modal-border);
    }
    #topRankingsModal .trk-tab {
        flex: 1 1 0; display: inline-flex; align-items: center; justify-content: center;
        gap: 0.4rem; padding: 0.42rem 0.6rem;
        border: 1px solid var(--dash-modal-border); border-radius: 9px;
        background: var(--dash-modal-surface); color: var(--dash-modal-muted);
        font-size: 0.73rem; font-weight: 700; line-height: 1.2;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }
    #topRankingsModal .trk-tab.is-active {
        background: var(--dash-modal-amber-bg); border-color: var(--dash-modal-amber-start); color: var(--dash-modal-amber-text);
    }
    #topRankingsModal .trk-tab[data-trk-tab="violations"].is-active {
        background: var(--dash-modal-red-bg); border-color: var(--dash-modal-red-text); color: var(--dash-modal-red-text);
    }
    #topRankingsModal .trk-pane { display: none; }
    #topRankingsModal .trk-pane.is-active { display: block; }
    #topRankingsModal .trk-col-head {
        display: flex; align-items: center; gap: 0.4rem;
        padding: 0.5rem 1rem; background: var(--dash-modal-panel);
        border-bottom: 1px solid var(--dash-modal-border);
        font-size: 0.66rem; font-weight: 800; letter-spacing: 0.05em;
        text-transform: uppercase; color: var(--dash-modal-muted);
    }
    #topRankingsModal .trk-list { padding: 0.3rem 0.5rem 0.6rem; }
    #topRankingsModal .trk-row {
        display: flex; align-items: center; gap: 0.55rem;
        padding: 0.4rem 0.5rem; border-radius: 10px;
        text-decoration: none; color: inherit;
        transition: background 0.15s ease;
    }
    #topRankingsModal .trk-row:hover,
    #topRankingsModal .trk-row:focus-visible { background: var(--dash-modal-hover); outline: none; }
    #topRankingsModal .trk-rank {
        width: 26px; flex: none; text-align: center;
        font-size: 0.8rem; font-weight: 800; color: var(--dash-modal-muted);
    }
    #topRankingsModal .trk-main { min-width: 0; flex: 1 1 auto; }
    #topRankingsModal .trk-name {
        display: block; font-size: 0.84rem; font-weight: 700; color: var(--dash-modal-text);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    #topRankingsModal .trk-note {
        display: flex; align-items: center; gap: 0.3rem;
        font-size: 0.68rem; color: var(--dash-modal-muted);
    }
    #topRankingsModal .trk-plate {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 0.64rem; font-weight: 700; color: var(--dash-modal-muted);
        background: var(--dash-modal-panel); border: 1px solid var(--dash-modal-border);
        border-radius: 6px; padding: 0 0.3rem;
    }
    #topRankingsModal .trk-bar {
        display: block; height: 3px; margin-top: 0.25rem;
        border-radius: 2px; background: var(--dash-modal-panel); overflow: hidden;
    }
    #topRankingsModal .trk-bar > span { display: block; height: 100%; border-radius: 2px; }
    #topRankingsModal .trk-figure {
        flex: none; font-size: 0.72rem; font-weight: 800;
        border-radius: 999px; padding: 0.08rem 0.45rem;
        background: var(--dash-modal-amber-bg); color: var(--dash-modal-amber-text);
    }
    #topRankingsModal .trk-figure--vio { background: var(--dash-modal-red-bg); color: var(--dash-modal-red-text); }
    #topRankingsModal .trk-empty { padding: 1.1rem 1rem; text-align: center; font-size: 0.78rem; color: var(--dash-modal-muted); }
</style>
<div class="modal fade" id="topRankingsModal" tabindex="-1" aria-labelledby="topRankingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="trk-head">
                <span class="trk-head-icon"><i class="bi bi-trophy-fill"></i></span>
                <div class="me-auto">
                    <h5 class="trk-title" id="topRankingsModalLabel">Top Rankings</h5>
                    <div class="trk-sub">Who is cited most often, and for what</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="trk-switch" role="tablist" aria-label="Ranking view">
                <button type="button" class="trk-tab is-active" data-trk-tab="violations" role="tab"
                        aria-selected="true" aria-controls="trkPaneViolations">
                    <i class="bi bi-exclamation-triangle-fill"></i> Most Violations Committed
                </button>
                <button type="button" class="trk-tab" data-trk-tab="violators" role="tab"
                        aria-selected="false" aria-controls="trkPaneViolators">
                    <i class="bi bi-person-lines-fill"></i> Top Apprehended Drivers
                </button>
            </div>

            <div class="trk-body">

                <!-- Leaderboard: one row per top apprehended driver (repeat offenders). -->
                <div class="trk-pane" id="trkPaneViolators" role="tabpanel">
                    <div class="trk-col-head">
                        <i class="bi bi-person-lines-fill text-warning"></i>
                        <span>Top Apprehended Drivers &middot; CY <?= esc((string) $year) ?></span>
                        <span class="ms-auto text-lowercase fw-semibold" style="letter-spacing:0;">sanction</span>
                    </div>
                    <?php $maxModalV = !empty($top_violators_list) ? (int) $top_violators_list[0]['total_violations'] : 1; ?>
                    <div class="trk-list">
                        <?php if (empty($top_violators_list)): ?>
                            <div class="trk-empty"><i class="bi bi-person-slash d-block fs-4 mb-1 opacity-50"></i>No repeat offenders yet (minimum 3 sanctions in <?= esc((string) $year) ?>).</div>
                        <?php else: ?>
                            <?php $r = 1; $medals = ['🥇', '🥈', '🥉']; foreach ($top_violators_list as $vl):
                                $p             = $maxModalV > 0 ? round(($vl['total_violations'] / $maxModalV) * 100) : 0;
                                $rl            = $r <= 3 ? $medals[$r - 1] : $r;
                                $profileUrl    = base_url('penalties/violator/' . (int) $vl['violator_id']);
                                $violatorName  = trim((string) ($vl['driver_name'] ?? '')) ?: 'Unknown Violator';
                                $violatorPlate = trim((string) ($vl['license_plate'] ?? ''));
                                $hasPlate      = $violatorPlate !== '' && ! \App\Libraries\ViolatorIdentity::isPlaceholder($violatorPlate);
                            ?>
                            <a class="trk-row" href="<?= $profileUrl ?>" title="Open <?= esc($violatorName, 'attr') ?>'s profile">
                                <span class="trk-rank"><?= $rl ?></span>
                                <span class="trk-main">
                                    <span class="trk-name"><?= esc($violatorName) ?></span>
                                    <?php if ($hasPlate): ?>
                                        <span class="trk-note"><span class="trk-plate"><?= esc(strtoupper($violatorPlate)) ?></span></span>
                                    <?php endif; ?>
                                    <span class="trk-bar"><span style="width:<?= $p ?>%; background:var(--dash-modal-amber-start);"></span></span>
                                </span>
                                <span class="trk-figure"><?= number_format((int) $vl['total_violations']) ?></span>
                            </a>
                            <?php $r++; endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- What those citations were for, ranked the same way. -->
                <div class="trk-pane is-active" id="trkPaneViolations" role="tabpanel">
                    <div class="trk-col-head">
                        <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                        <span>Most Violations Committed &middot; CY <?= esc((string) $year) ?></span>
                        <span class="ms-auto text-lowercase fw-semibold" style="letter-spacing:0;">cases</span>
                    </div>
                    <?php $maxModalVio = !empty($top_violations_list) ? (int) $top_violations_list[0]['total_count'] : 1; ?>
                    <div class="trk-list">
                        <?php if (empty($top_violations_list)): ?>
                            <div class="trk-empty"><i class="bi bi-slash-circle d-block fs-4 mb-1 opacity-50"></i>No records yet.</div>
                        <?php else: ?>
                            <?php $r = 1; $medals = ['🥇', '🥈', '🥉']; foreach ($top_violations_list as $vio):
                                $p  = $maxModalVio > 0 ? round(($vio['total_count'] / $maxModalVio) * 100) : 0;
                                $rl = $r <= 3 ? $medals[$r - 1] : $r;
                            ?>
                            <a class="trk-row" href="<?= base_url('penalties/search?q=' . urlencode($vio['violation_type'])) ?>"
                               title="Find every citation for <?= esc($vio['violation_type'], 'attr') ?>">
                                <span class="trk-rank"><?= $rl ?></span>
                                <span class="trk-main">
                                    <span class="trk-name"><?= esc($vio['violation_type']) ?></span>
                                    <span class="trk-bar"><span style="width:<?= $p ?>%; background:var(--dash-chart-red);"></span></span>
                                </span>
                                <span class="trk-figure trk-figure--vio"><?= number_format((int) $vio['total_count']) ?></span>
                            </a>
                            <?php $r++; endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<script>
    // The modal answers one question at a time: pick violators or violations
    // and that list appears, with no page load and no stacked scrolling.
    (function () {
        const modal = document.getElementById('topRankingsModal');
        if (!modal) return;

        const tabs  = Array.from(modal.querySelectorAll('.trk-tab'));
        const panes = Array.from(modal.querySelectorAll('.trk-pane'));

        modal.addEventListener('show.bs.modal', function (event) {
            const requestedTab = event.relatedTarget?.dataset.rankingsTab;
            const selectedTab = tabs.find(tab => tab.dataset.trkTab === requestedTab) || tabs[0];
            if (selectedTab && !selectedTab.classList.contains('is-active')) selectedTab.click();
        });

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (other) {
                    const on = other === tab;
                    other.classList.toggle('is-active', on);
                    other.setAttribute('aria-selected', on ? 'true' : 'false');
                });

                const wanted = 'trkPane' + (tab.dataset.trkTab || '').charAt(0).toUpperCase() + (tab.dataset.trkTab || '').slice(1);
                panes.forEach(function (pane) {
                    pane.classList.toggle('is-active', pane.id === wanted);
                });
            });
        });
    })();
</script>
<!-- End Top Rankings Modal -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap Tooltips
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
            new bootstrap.Tooltip(el);
        });
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // === Live Clock ===
        function updateClock() {
            const now = new Date();
            const opts = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            const el = document.getElementById('clockText');
            if (el) el.textContent = now.toLocaleString('en-PH', opts);
        }
        updateClock();
        setInterval(updateClock, 1000);

        // === Count-Up Animation ===
        function countUp(el) {
            const target    = parseFloat(el.dataset.target) || 0;
            const prefix    = el.dataset.prefix  || '';
            const suffix    = el.dataset.suffix  || '';
            const decimals  = parseInt(el.dataset.decimals || '0');
            const duration = Number.parseInt(el.dataset.duration || '900', 10);
            const format = value => prefix + (decimals > 0
                ? value.toLocaleString('en-PH', { minimumFractionDigits: decimals, maximumFractionDigits: decimals })
                : Math.round(value).toLocaleString('en-PH')) + suffix;
            if (prefersReducedMotion) {
                el.textContent = format(target);
                return;
            }
            const startedAt = performance.now();
            const tick = now => {
                const progress = Math.min((now - startedAt) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = format(target * eased);
                if (progress < 1) window.requestAnimationFrame(tick);
                else el.textContent = format(target);
            };
            window.requestAnimationFrame(tick);
        }

        // Trigger count-up when visible
        const kpiObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    countUp(entry.target);
                    kpiObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        document.querySelectorAll('.kpi-countup').forEach(el => kpiObserver.observe(el));

        // === KPI Card Reveal (staggered fade-up) ===
        Array.from(document.querySelectorAll('.dashboard-kpi')).forEach((card, index) => {
            if (prefersReducedMotion) {
                card.classList.add('is-revealed');
                return;
            }
            card.style.transitionDelay = (index * 90) + 'ms';
            window.requestAnimationFrame(() => card.classList.add('is-revealed'));
            window.setTimeout(() => { card.style.transitionDelay = ''; }, index * 90 + 420);
        });

        // Shared Chart Settings
        const getCssToken = token => getComputedStyle(document.documentElement).getPropertyValue(token).trim();
        const reduceChartMotion = prefersReducedMotion;
        const chartTextColor = getCssToken('--dash-muted');
        const chartGridColor = getCssToken('--dash-chart-grid');
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.font.size = 13;
        Chart.defaults.color = chartTextColor;
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.92)';
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;

        const gridOptions = {
            color: chartGridColor,
            drawBorder: false,
        };
        const markChartShell = (key, hasData) => {
            const shell = document.querySelector(`[data-dashboard-chart-shell="${key}"]`);
            if (!shell) return;
            shell.classList.add('is-loaded');
            shell.classList.toggle('is-empty', !hasData);
        };
        const dashboardHighlightBand = {
            id: 'dashboardHighlightBand',
            beforeDatasetsDraw(chart) {
                const active = chart.tooltip && chart.tooltip.getActiveElements();
                if (!active || active.length === 0 || !chart.scales.x || chart.data.labels.length === 0) return;
                const index = active[0].index;
                const xScale = chart.scales.x;
                const center = xScale.getPixelForValue(index);
                const next = index < chart.data.labels.length - 1
                    ? Math.abs(xScale.getPixelForValue(index + 1) - center)
                    : 0;
                const previous = index > 0
                    ? Math.abs(center - xScale.getPixelForValue(index - 1))
                    : 0;
                const step = next || previous || chart.chartArea.right - chart.chartArea.left;
                const left = Math.max(chart.chartArea.left, center - step / 2);
                const right = Math.min(chart.chartArea.right, center + step / 2);
                chart.ctx.save();
                chart.ctx.fillStyle = getCssToken('--dash-highlight');
                chart.ctx.fillRect(
                    left,
                    chart.chartArea.top,
                    right - left,
                    chart.chartArea.bottom - chart.chartArea.top
                );
                chart.ctx.restore();
            }
        };

        // --- Weekly Trend Chart (Combo: Bar + Line) ---
        const weeklyData = <?= json_encode($weekly_trend) ?>;
        const weekLabels = weeklyData.map(w => w.label);
        const weekCounts = weeklyData.map(w => w.count);
        // Revenue means money actually collected, which is how the Revenue
        // Report counts it: pending fines are owed, not received.
        const weekRevenues = weeklyData.map(w => w.paid_revenue);

        const weeklyChart = new Chart(document.getElementById('weeklyChart'), {
            type: 'bar',
            data: {
                labels: weekLabels,
                datasets: [
                    {
                        label: 'Violations',
                        data: weekCounts,
                        backgroundColor: getCssToken('--dash-blue'),
                        borderRadius: 4,
                        order: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Revenue (₱ collected)',
                        data: weekRevenues,
                        type: 'line',
                        borderColor: getCssToken('--dash-green'),
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: getCssToken('--dash-green'),
                        order: 1,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: reduceChartMotion ? false : { duration: 800, easing: 'easeOutQuart' },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', onClick: Chart.defaults.plugins.legend.onClick, labels: { font: { size: 13 }, padding: 18 } },
                    tooltip: {
                        callbacks: {
                            title: function(items) {
                                if (!items.length) return '';
                                const idx = items[0].dataIndex;
                                const item = weeklyData[idx];
                                if (item && item.date_range) {
                                    return item.label + ' (' + item.date_range + ')';
                                }
                                if (item && item.week_start && item.week_end) {
                                    return item.label + ' (' + item.week_start + ' to ' + item.week_end + ')';
                                }
                                return items[0].label;
                            },
                            label: function(ctx) {
                                if (ctx.dataset.yAxisID === 'y1') {
                                    const item = weeklyData[ctx.dataIndex] || {};
                                    let txt = ' Collected: ₱' + Number(ctx.raw || 0).toLocaleString();
                                    if (item.revenue !== undefined) {
                                        txt += ' · Fines: ₱' + Number(item.revenue || 0).toLocaleString();
                                    }
                                    return txt;
                                }
                                return ' Tickets: ' + Number(ctx.raw || 0);
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { 
                        beginAtZero: true, 
                        grid: gridOptions,
                        ticks: { font: { size: 13 } },
                        title: { display: true, text: 'Count', font: { size: 13, weight: '600' } }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: {
                            font: { size: 13 },
                            callback: v => '₱' + Number(v).toLocaleString()
                        },
                        title: { display: true, text: 'Revenue (₱)', font: { size: 13, weight: '600' } }
                    }
                }
            },
            plugins: [dashboardHighlightBand]
        });
        markChartShell('weekly', weekCounts.some(Number) || weekRevenues.some(Number));

        // --- Monthly Breakdown Chart (Line) ---
        const monthlyData = <?= json_encode($monthly_breakdown) ?>;
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const monthlyCounts = Object.values(monthlyData).map(m => m.count);
        const monthlyPaid = Object.values(monthlyData).map(m => m.paid);
        const monthlyPending = Object.values(monthlyData).map(m => m.pending);

        const monthlyChart = new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: monthNames,
                datasets: [
                    {
                        label: 'Total Issued',
                        data: monthlyCounts,
                        borderColor: getCssToken('--dash-indigo'),
                        backgroundColor: getCssToken('--dash-highlight'),
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: getCssToken('--dash-indigo')
                    },
                    {
                        label: 'Paid',
                        data: monthlyPaid,
                        borderColor: getCssToken('--dash-green'),
                        borderDash: [5, 5],
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: getCssToken('--dash-green')
                    },
                    {
                        label: 'Pending',
                        data: monthlyPending,
                        borderColor: getCssToken('--dash-chart-amber'),
                        borderDash: [2, 4],
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: getCssToken('--dash-chart-amber')
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: reduceChartMotion ? false : { duration: 800, easing: 'easeOutQuart' },
                interaction: { mode: 'index', intersect: false },
                onClick: (e, elements) => {
                    if (elements && elements.length > 0) {
                        const monthIndex = elements[0].index;
                        const monthNum = monthIndex + 1;
                        const url = new URL(window.location.href);
                        url.searchParams.set('year', '<?= esc($year) ?>');
                        url.searchParams.set('month', monthNum);
                        window.location.href = url.toString();
                    }
                },
                onHover: (e, elements) => {
                    if (e.native && e.native.target) {
                        e.native.target.style.cursor = elements.length ? 'pointer' : 'default';
                    }
                },
                plugins: {
                    legend: { position: 'top', onClick: Chart.defaults.plugins.legend.onClick, labels: { font: { size: 13 }, padding: 18 } },
                    tooltip: {
                        callbacks: {
                            title: function(items) {
                                if (!items.length) return '';
                                return items[0].label + ' (Click to view weekly breakdown)';
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: gridOptions, ticks: { font: { size: 13 } }, title: { display: true, text: 'Count', font: { size: 13, weight: '600' } } }
                }
            },
            plugins: [dashboardHighlightBand]
        });
        markChartShell('monthly', monthlyCounts.some(Number) || monthlyPaid.some(Number) || monthlyPending.some(Number));
        document.querySelectorAll('.dashboard-collection-progress').forEach(bar => {
            const width = Math.max(0, Math.min(Number(bar.dataset.targetWidth) || 0, 100));
            if (reduceChartMotion) bar.style.width = width + '%';
            else requestAnimationFrame(() => { bar.style.width = width + '%'; });
        });
        document.querySelector('.chart-hint-dismiss')?.addEventListener('click', () => {
            document.querySelector('.chart-month-hint')?.remove();
        });

        // ═══════════════════════════════════════════════════════════════
        // NATURE OF VIOLATION — REAL-TIME ENGINE
        // ═══════════════════════════════════════════════════════════════
        const NATURE_API_URL   = '<?= base_url('api/nature-summary') ?>?year=<?= (int) ($summary['report_year'] ?? $year) ?>';
        const NATURE_REFRESH_MS = 30000; // 30 seconds

        // Full extended palette for all violation types
        let naturePalette = getCssToken('--dash-nature-palette').split(',').map(color => color.trim());

        let natureMode         = 'count';   // 'count' | 'revenue'
        let natureLiveData     = <?= json_encode($nature_summary ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        let naturePrevData     = null;       // previous fetch for spike detection
        let natureBarInstance  = null;
        let natureDoughnutInstance = null;
        let natureFilterText   = '';
        let natureRefreshTimer = null;

        // ── Helpers ────────────────────────────────────────────────────
        function natureFmt(val, mode) {
            return mode === 'revenue'
                ? '₱' + Number(val).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                : Number(val).toLocaleString('en-PH');
        }

        function natureVal(row, mode) {
            // Revenue mode uses paid_amount ONLY (matches the KPI card "Total Revenue")
            // Incidents mode uses count
            return mode === 'revenue' ? (parseFloat(row.paid_amount) || 0) : (parseInt(row.count) || 0);
        }

        // Escapes user-entered labels before they are interpolated into HTML.
        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, ch => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[ch]));
        }

        // ── KPI Summary Strip ──────────────────────────────────────────
        function renderNatureKpi(data) {
            const strip = document.getElementById('natureKpiStrip');
            if (!strip || !data.length) return;

            const totalInc   = data.reduce((s, d) => s + (parseInt(d.count) || 0), 0);
            const totalPaid  = data.reduce((s, d) => s + (parseFloat(d.paid_amount) || 0), 0);
            const totalPend  = data.reduce((s, d) => s + (parseFloat(d.pending_amount) || 0), 0);
            const topType    = [...data].sort((a,b) => natureVal(b, natureMode) - natureVal(a, natureMode))[0];
            const typeCount  = data.length;

            strip.innerHTML = `
                <div class="nature-kpi-chip" title="Total distinct violation types">
                    <i class="bi bi-list-ul text-primary"></i>
                    <span class="fw-bold">${typeCount}</span>
                    <span class="text-muted">Types</span>
                </div>
                <div class="nature-kpi-chip" title="Total recorded violations">
                    <i class="bi bi-exclamation-circle text-warning"></i>
                    <span class="fw-bold">${totalInc.toLocaleString('en-PH')}</span>
                    <span class="text-muted">Incidents</span>
                </div>
                <div class="nature-kpi-chip" title="Collected revenue from Paid violations (matches Total Revenue KPI)">
                    <i class="bi bi-cash-stack text-success"></i>
                    <span class="fw-bold">₱${Number(totalPaid).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                    <span class="text-muted">Revenue (Paid)</span>
                </div>
                <div class="nature-kpi-chip" title="Outstanding fines from Pending violations">
                    <i class="bi bi-hourglass-split text-warning"></i>
                    <span class="fw-bold nature-pending-value">₱${Number(totalPend).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                    <span class="text-muted">Pending</span>
                </div>
                ${topType ? `
                <div class="nature-kpi-chip nature-kpi-top" title="Top violation by ${natureMode === 'revenue' ? 'collected revenue' : 'incidents'}">
                    <i class="bi bi-trophy-fill text-danger"></i>
                    <span class="fw-bold text-truncate" style="max-width:140px;" title="${escapeHtml(topType.violation_type)}">${escapeHtml(topType.violation_type)}</span>
                    <span class="text-muted">Top</span>
                </div>` : ''}
            `;
        }

        // ── Spike Detection ────────────────────────────────────────────
        function detectSpike(prev, curr) {
            if (!prev || !prev.length) return null;
            const prevMap = {};
            prev.forEach(r => { prevMap[r.violation_type] = parseInt(r.count) || 0; });
            let worst = null, worstDelta = 0;
            curr.forEach(r => {
                const old = prevMap[r.violation_type] || 0;
                const now = parseInt(r.count) || 0;
                if (old > 0 && now > old) {
                    const delta = now - old;
                    if (delta > worstDelta) { worstDelta = delta; worst = r.violation_type; }
                } else if (old === 0 && now > 2) {
                    if (now > worstDelta) { worstDelta = now; worst = r.violation_type; }
                }
            });
            return worst ? { type: worst, delta: worstDelta } : null;
        }

        function showSpikeAlert(spike) {
            const el  = document.getElementById('natureSpikeAlert');
            const msg = document.getElementById('natureSpikeMsg');
            if (!el || !spike) { if (el) el.classList.add('d-none'); return; }
            msg.textContent = `⚠ Spike detected: "${spike.type}" increased by ${spike.delta} incident${spike.delta > 1 ? 's' : ''} since last refresh.`;
            el.classList.remove('d-none');
            setTimeout(() => el.classList.add('d-none'), 12000);
        }

        // ── Trend Badge helper ─────────────────────────────────────────
        function trendBadge(prev, curr) {
            if (prev === null) return '';
            if (curr > prev) return `<span class="badge bg-danger-subtle text-danger ms-1" style="font-size:0.6rem;">▲ +${curr - prev}</span>`;
            if (curr < prev) return `<span class="badge bg-success-subtle text-success ms-1" style="font-size:0.6rem;">▼ -${prev - curr}</span>`;
            return '';
        }

        // ── Build prev count map ───────────────────────────────────────
        function buildPrevMap(data) {
            const m = {};
            if (data) data.forEach(r => { m[r.violation_type] = parseInt(r.count) || 0; });
            return m;
        }

        // ── Main Render ────────────────────────────────────────────────
        function renderNatureCharts(data, prevData) {
            if (!data || data.length === 0) {
                markChartShell('nature-bar', false);
                markChartShell('nature-doughnut', false);
                const legend = document.getElementById('natureLegendContainer');
                if (legend) legend.innerHTML = '<div class="empty-state"><i class="bi bi-clipboard2-check"></i><div class="empty-state-title">No violation data yet</div><span>Violation records will appear here when available.</span></div>';
                const count = document.getElementById('natureRecordCount');
                if (count) count.textContent = '0';
                return;
            }
            const prevMap = buildPrevMap(prevData);

            // Apply search filter
            const filter = natureFilterText.toLowerCase().trim();
            const filtered = filter
                ? data.filter(d => d.violation_type.toLowerCase().includes(filter))
                : data;

            // Sort by active mode
            const sortedData = [...filtered].sort((a, b) => natureVal(b, natureMode) - natureVal(a, natureMode));
            const hasNatureValues = sortedData.some(row => natureVal(row, natureMode) > 0);

            // Update meta
            const countEl = document.getElementById('natureRecordCount');
            if (countEl) countEl.textContent = data.length;

            renderNatureKpi(data);

            // ── 1. Horizontal Bar Chart ────────────────────────────────
            const barLabels = sortedData.map(d => d.violation_type);
            const barValues = sortedData.map(d => natureVal(d, natureMode));
            const barColors = sortedData.map((_, i) => naturePalette[i % naturePalette.length]);

            if (natureBarInstance) natureBarInstance.destroy();
            const barCtx = document.getElementById('natureBarChart').getContext('2d');
            natureBarInstance = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: barLabels,
                    datasets: [{
                        label: natureMode === 'revenue' ? 'Collected Revenue (₱)' : 'Total Incidents',
                        data: barValues,
                        backgroundColor: barColors.map(c => c + 'cc'),
                        borderColor: barColors,
                        borderWidth: 1.5,
                        borderRadius: 5,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: reduceChartMotion ? false : { duration: 800, easing: 'easeOutQuart' },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            onClick: Chart.defaults.plugins.legend.onClick,
                            labels: { boxWidth: 12, font: { size: 13 }, padding: 12 }
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const row = sortedData[ctx.dataIndex];
                                    const pct = row ? Math.round((natureVal(row, natureMode) / sortedData.reduce((s,r) => s+natureVal(r,natureMode),0)) * 100) : 0;
                                    return ` ${natureFmt(ctx.parsed.x, natureMode)} (${pct}%)`;
                                },
                                afterLabel: ctx => {
                                    const row = sortedData[ctx.dataIndex];
                                    if (!row) return '';
                                    const parts = [];
                                    if (natureMode === 'count') {
                                        parts.push(`Paid: ${row.paid_count || 0}`, `Pending: ${row.pending_count || 0}`);
                                    } else {
                                        // Revenue mode: show collected vs outstanding
                                        parts.push(
                                            `Collected: ₱${Number(row.paid_amount || 0).toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2})}`,
                                            `Pending:   ₱${Number(row.pending_amount || 0).toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2})}`
                                        );
                                    }
                                    const old = prevMap[row.violation_type];
                                    if (old !== undefined && old !== parseInt(row.count)) {
                                        const diff = parseInt(row.count) - old;
                                        parts.push(`Change: ${diff > 0 ? '+' : ''}${diff} since last refresh`);
                                    }
                                    return parts;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: gridOptions,
                            ticks: { font: { size: 13 }, callback: v => natureMode === 'revenue' ? '₱' + Number(v).toLocaleString() : v }
                        },
                        y: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 13 },
                                callback: function(val, idx) {
                                    const lbl = barLabels[idx] || '';
                                    return lbl.length > 24 ? lbl.substring(0, 22) + '…' : lbl;
                                }
                            }
                        }
                    }
                }
            });

            // ── 2. Full-Ring Doughnut (all violation types, unique colors) ──
            const doughnutLabels = sortedData.map(d => d.violation_type);
            const doughnutValues = sortedData.map(d => natureVal(d, natureMode));
            const totalSum = doughnutValues.reduce((a, b) => a + b, 0);

            if (natureDoughnutInstance) natureDoughnutInstance.destroy();
            const dCtx = document.getElementById('natureDoughnutChart').getContext('2d');
            natureDoughnutInstance = new Chart(dCtx, {
                type: 'doughnut',
                data: {
                    labels: doughnutLabels,
                    datasets: [{
                        data: doughnutValues,
                        backgroundColor: doughnutLabels.map((_, i) => naturePalette[i % naturePalette.length]),
                        borderWidth: 2.5,
                        borderColor: '#ffffff',
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    animation: reduceChartMotion ? false : { animateRotate: true, duration: 800, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const val = ctx.parsed;
                                    const pct = totalSum > 0 ? Math.round((val / totalSum) * 100) : 0;
                                    return ` ${ctx.label}: ${natureFmt(val, natureMode)} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
            markChartShell('nature-bar', hasNatureValues);
            markChartShell('nature-doughnut', hasNatureValues);

            // ── 3. Scrollable Legend ───────────────────────────────────
            const legendBox = document.getElementById('natureLegendContainer');
            if (legendBox) {
                let html = '';
                doughnutLabels.forEach((lbl, idx) => {
                    const val    = doughnutValues[idx];
                    const pct    = totalSum > 0 ? Math.round((val / totalSum) * 100) : 0;
                    const col    = naturePalette[idx % naturePalette.length];
                    const row    = sortedData[idx];
                    const oldCnt = prevMap[lbl];
                    const trend  = (oldCnt !== undefined && natureMode === 'count') ? trendBadge(oldCnt, parseInt(row.count) || 0) : '';
                    const isTop  = idx === 0 ? 'fw-bold' : '';
                    const paidPending = natureMode === 'count'
                        ? `<span class="text-muted" style="font-size:0.64rem;">Paid: ${row.paid_count||0} &bull; Pend: ${row.pending_count||0}</span>`
                        : `<span class="text-muted" style="font-size:0.64rem;">Pend: ₱${Number(row.pending_amount||0).toLocaleString('en-PH')}</span>`;
                    html += `
                    <div class="nature-legend-row ${idx === 0 ? 'nature-legend-top' : ''}" title="${escapeHtml(lbl)}">
                        <div class="d-flex align-items-center gap-1 text-truncate" style="max-width:160px;">
                            <span class="nature-legend-dot" style="background:${col}"></span>
                            <span class="text-truncate ${isTop} text-dark" style="font-size:0.78rem;">${escapeHtml(lbl)}</span>
                            ${trend}
                        </div>
                        <div class="text-end" style="flex-shrink:0;">
                            <div class="fw-bold" style="font-size:0.78rem;">${natureFmt(val, natureMode)}</div>
                            ${paidPending}
                            <span class="badge rounded-pill" style="background:${col}22; color:${col}; font-size:0.6rem;">${pct}%</span>
                        </div>
                    </div>`;
                });
                legendBox.innerHTML = html || '<p class="text-muted small text-center py-3">No violations match your search.</p>';
            }
        }

        // ── Fetch & Refresh ────────────────────────────────────────────
        function natureSetRefreshing(on) {
            const icon = document.getElementById('natureRefreshIcon');
            const badge = document.getElementById('natureLiveBadge');
            if (icon) icon.className = on ? 'bi bi-arrow-clockwise nature-spinning' : 'bi bi-arrow-clockwise';
            if (badge) badge.classList.toggle('nature-live-syncing', on);
        }

        async function natureFetch() {
            natureSetRefreshing(true);
            try {
                const res  = await fetch(NATURE_API_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await res.json();
                if (json.status === 'ok') {
                    const spike = detectSpike(natureLiveData, json.data);
                    naturePrevData = natureLiveData;
                    natureLiveData = json.data;

                    renderNatureCharts(natureLiveData, naturePrevData);
                    showSpikeAlert(spike);

                    const el = document.getElementById('natureLastUpdated');
                    if (el) el.textContent = new Date().toLocaleTimeString('en-PH');
                }
            } catch(e) {
                console.warn('Nature summary fetch error:', e);
            } finally {
                natureSetRefreshing(false);
            }
        }

        // Initial render with server-side data
        renderNatureCharts(natureLiveData, null);
        const initialUpdated = document.getElementById('natureLastUpdated');
        if (initialUpdated) initialUpdated.textContent = new Date().toLocaleTimeString('en-PH');

        // Start auto-refresh
        natureRefreshTimer = setInterval(natureFetch, NATURE_REFRESH_MS);

        // Manual refresh button
        const btnRefresh = document.getElementById('btnNatureRefresh');
        if (btnRefresh) btnRefresh.addEventListener('click', natureFetch);

        // Search filter
        const searchInput = document.getElementById('natureSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                natureFilterText = this.value;
                renderNatureCharts(natureLiveData, naturePrevData);
            });
        }

        // Mode Toggles
        const btnCount   = document.getElementById('btnNatureCount');
        const btnRevenue = document.getElementById('btnNatureRevenue');
        if (btnCount && btnRevenue) {
            btnCount.addEventListener('click', function() {
                if (natureMode === 'count') return;
                natureMode = 'count';
                btnCount.classList.add('active');
                btnRevenue.classList.remove('active');
                renderNatureCharts(natureLiveData, naturePrevData);
            });
            btnRevenue.addEventListener('click', function() {
                if (natureMode === 'revenue') return;
                natureMode = 'revenue';
                btnRevenue.classList.add('active');
                btnCount.classList.remove('active');
                renderNatureCharts(natureLiveData, naturePrevData);
            });
        }

        // --- Secondary Chart Controller (Status and Roles) ---
        // Cancelled violators are left out of this doughnut: the chart reports
        // only what is still Pending or already Paid.
        const statusData = <?= json_encode($status_distribution) ?>.filter(s => s.status !== 'Cancelled');
        const statusLabels = statusData.map(s => s.status);
        const statusValues = statusData.map(s => s.count);

        const roleData = <?= json_encode($user_role_distribution ?? []) ?>;
        const roleLabels = roleData.map(r => r.role);
        const roleCounts = roleData.map(r => r.count);

        let secondaryChart;
        const secondaryCtx = document.getElementById('secondaryAnalyticsChart').getContext('2d');
        const rightChartTitle = document.getElementById('rightChartTitle');

        function showStatusChart() {
            if (secondaryChart) secondaryChart.destroy();

            rightChartTitle.innerHTML = '<i class="bi bi-pie-chart me-2 text-success"></i>Violator Status (<?= (int) $year ?>)';
            document.getElementById('btnStatus').classList.add('active');
            document.getElementById('btnRole').classList.remove('active');

            secondaryChart = new Chart(secondaryCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusValues,
                        backgroundColor: statusLabels.map(label => {
                            if (label === 'Pending') return getCssToken('--dash-status-pending');
                            if (label === 'Paid')    return getCssToken('--dash-status-paid');
                            return getCssToken('--dash-status-neutral');
                        })
                    }]
                },
                options: {
                    responsive: true,
                    animation: reduceChartMotion ? false : { duration: 800, easing: 'easeOutQuart' },
                    plugins: { legend: { position: 'bottom', labels: { font: { size: 13 }, padding: 14 } } }
                }
            });
            markChartShell('secondary', statusValues.some(Number));
        }

        // Role identity colors: admin is electric blue, enforcer is lime green.
        const roleColorFor = (role) => {
            const key = String(role || '').toLowerCase();
            if (key === 'admin') return getCssToken('--dash-role-admin');
            if (key === 'enforcer') return getCssToken('--dash-role-enforcer');
            return getCssToken('--dash-role-driver');
        };

        function showRoleChart() {
            if (secondaryChart) secondaryChart.destroy();

            rightChartTitle.innerHTML = '<i class="bi bi-people me-2 text-secondary"></i>Users by Role';
            document.getElementById('btnRole').classList.add('active');
            document.getElementById('btnStatus').classList.remove('active');

            secondaryChart = new Chart(secondaryCtx, {
                type: 'doughnut',
                data: {
                    labels: roleLabels,
                    datasets: [{
                        data: roleCounts,
                        backgroundColor: roleLabels.map(roleColorFor),
                    }]
                },
                options: {
                    responsive: true,
                    animation: reduceChartMotion ? false : { duration: 800, easing: 'easeOutQuart' },
                    plugins: { legend: { position: 'bottom', labels: { font: { size: 13 }, padding: 14 } } }
                }
            });
            markChartShell('secondary', roleCounts.some(Number));
        }

        // Initialize Secondary Chart
        showStatusChart();

        // Event Listeners for Secondary Chart
        document.getElementById('btnStatus').addEventListener('click', showStatusChart);
        document.getElementById('btnRole').addEventListener('click', showRoleChart);

        window.addEventListener('themechange', () => {
            Chart.defaults.color = getCssToken('--dash-muted');
            gridOptions.color = getCssToken('--dash-chart-grid');
            weeklyChart.data.datasets[0].backgroundColor = getCssToken('--dash-blue');
            weeklyChart.data.datasets[1].borderColor = getCssToken('--dash-green');
            weeklyChart.data.datasets[1].pointBackgroundColor = getCssToken('--dash-green');
            weeklyChart.update('none');
            monthlyChart.data.datasets[0].backgroundColor = getCssToken('--dash-highlight');
            monthlyChart.data.datasets[0].borderColor = getCssToken('--dash-indigo');
            monthlyChart.data.datasets[0].pointBackgroundColor = getCssToken('--dash-indigo');
            monthlyChart.data.datasets[1].borderColor = getCssToken('--dash-green');
            monthlyChart.data.datasets[1].pointBackgroundColor = getCssToken('--dash-green');
            monthlyChart.data.datasets[2].borderColor = getCssToken('--dash-chart-amber');
            monthlyChart.data.datasets[2].pointBackgroundColor = getCssToken('--dash-chart-amber');
            monthlyChart.update('none');
            naturePalette = getCssToken('--dash-nature-palette').split(',').map(color => color.trim());
            renderNatureCharts(natureLiveData, naturePrevData);
            if (document.getElementById('btnRole').classList.contains('active')) showRoleChart();
            else showStatusChart();
        });
    });
</script>

<style>
/* ── ADMIN ANALYTICS TOGGLE BAR (SEAMLESS INLINE) ───────── */
.admin-analytics-toggle-bar {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 0.2rem;
}
.admin-analytics-toggle-label {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    color: var(--text-strong, #1e293b);
    font-size: 0.95rem;
    font-weight: 700;
}
.admin-analytics-visible-pill {
    display: none;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    background: rgba(148, 163, 184, 0.14);
    color: #64748b;
    border: 1px solid rgba(148, 163, 184, 0.3);
}
.admin-analytics-visible-pill.hidden-state {
    display: inline-flex;
    align-items: center;
}
.btn-admin-analytics-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    font-weight: 600;
    padding: 0.38rem 0.9rem;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #475569;
    cursor: pointer;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    white-space: nowrap;
}
.btn-admin-analytics-toggle:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12);
}
.btn-admin-analytics-toggle:active { transform: scale(0.97); }
.btn-admin-analytics-toggle.is-hidden { background: #f0fdf4; border-color: #86efac; color: #059669; }
.btn-admin-analytics-toggle.is-hidden:hover { background: #dcfce7; border-color: #4ade80; color: #047857; }

/* ── ZERO-LAG CSS GRID COLLAPSE & EXPAND ANIMATION ────── */
.admin-analytics-section-wrap {
    display: grid;
    grid-template-rows: 1fr;
    transition: grid-template-rows 0.36s cubic-bezier(0.16, 1, 0.3, 1),
                opacity 0.26s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.36s cubic-bezier(0.16, 1, 0.3, 1);
    opacity: 1; transform: translateY(0); transform-origin: top center;
    will-change: grid-template-rows, opacity, transform;
}
.admin-analytics-section-wrap.analytics-collapsed {
    grid-template-rows: 0fr; opacity: 0; transform: translateY(-6px); pointer-events: none;
}
.admin-analytics-inner { overflow: hidden; min-height: 0; }

/* ════════════════════════════════════════════════════════════
   NATURE OF VIOLATION — REAL-TIME UI
   ════════════════════════════════════════════════════════════ */

/* Live Badge */
.nature-live-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #16a34a;
    background: #dcfce7;
    border: 1px solid #86efac;
    border-radius: 20px;
    padding: 0.18rem 0.55rem;
    cursor: default;
    user-select: none;
    transition: opacity 0.3s;
}
.nature-live-badge.nature-live-syncing { opacity: 0.55; }
.nature-live-dot {
    display: inline-block;
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #16a34a;
    animation: nature-pulse 1.4s ease-in-out infinite;
}
@keyframes nature-pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.45; transform: scale(0.75); }
}

/* Spin animation for refresh icon */
@keyframes nature-spin { to { transform: rotate(360deg); } }
.nature-spinning { animation: nature-spin 0.7s linear infinite; display: inline-block; }

/* KPI Chips */
.nature-kpi-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.25rem 0.65rem;
    font-size: 0.78rem;
    white-space: nowrap;
}
.nature-kpi-top {
    background: #fff7ed;
    border-color: #fed7aa;
    color: #c2410c;
}
.nature-pending-value {
    color: #854d0e;
}
/* Dark palette: the chips are light surfaces by default, which read as holes
   in the dark card. */
[data-bs-theme="dark"] .nature-kpi-chip {
    background: #0f2a44;
    border-color: #1e4976;
    color: #cbd5e1;
}
[data-bs-theme="dark"] .nature-kpi-top {
    background: rgba(251, 146, 60, 0.12);
    border-color: rgba(251, 146, 60, 0.35);
    color: #fdba74;
}
[data-bs-theme="dark"] .nature-pending-value {
    color: #fdba74;
}
[data-bs-theme="dark"] .nature-legend-row {
    color: #e2e8f0;
}
[data-bs-theme="dark"] .nature-legend-row:hover,
[data-bs-theme="dark"] .nature-legend-top {
    background: #1e293b;
}
[data-bs-theme="dark"] .nature-legend-row .text-dark {
    color: #f8fafc !important;
}
/* Header accents that are inline-coloured for the light palette. */
[data-bs-theme="dark"] #liveClock {
    color: #93c5fd !important;
    background: rgba(99, 102, 241, 0.22) !important;
}
[data-bs-theme="dark"] .btn-admin-analytics-toggle {
    color: #cbd5e1;
    border-color: var(--border-soft);
    background: var(--surface-muted);
}

/* Spike Alert Banner */
.nature-spike-alert {
    background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%);
    border-left: 4px solid #eab308;
    border-radius: 8px;
    padding: 0.55rem 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #92400e;
    margin-bottom: 0.5rem;
    animation: slideInDown 0.4s ease;
}
@keyframes slideInDown {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Scrollable Legend */
.nature-legend-scroll {
    max-height: 220px;
    overflow-y: auto;
    padding-right: 2px;
    scrollbar-width: thin;
    scrollbar-color: #e2e8f0 transparent;
}
.nature-legend-scroll::-webkit-scrollbar { width: 4px; }
.nature-legend-scroll::-webkit-scrollbar-track { background: transparent; }
.nature-legend-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

/* Legend Rows */
.nature-legend-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 6px;
    padding: 0.3rem 0.4rem;
    border-radius: 6px;
    transition: background 0.15s;
    cursor: default;
}
.nature-legend-row:hover { background: #f8fafc; }
.nature-legend-top { background: #fafaf5; }
.nature-legend-dot {
    display: inline-block;
    width: 10px; height: 10px;
    border-radius: 2px;
    flex-shrink: 0;
    margin-top: 3px;
}
</style>

<script>
// ═══ ADMIN ANALYTICS SECTION TOGGLE (ZERO-DELAY & SMOOTH ANIMATION) ═══
(function () {
    const STORAGE_KEY = 'admin_analytics_hidden';
    const section = document.getElementById('adminAnalyticsSection');
    const btn     = document.getElementById('adminAnalyticsToggleBtn');
    const icon    = document.getElementById('adminAnalyticsToggleIcon');
    const text    = document.getElementById('adminAnalyticsToggleText');
    const pill    = document.getElementById('adminAnalyticsVisiblePill');

    if (!section || !btn) return;

    // Direct landing or clicking on Dashboard resets hidden state so the dashboard opens visibly with no delay
    try {
        localStorage.removeItem(STORAGE_KEY);
    } catch(e) {}

    function setHidden(hidden, animate) {
        if (hidden) {
            section.classList.add('analytics-collapsed');
            if (icon) icon.className = 'bi bi-eye me-1';
            if (text) text.textContent = 'Show';
            if (pill) {
                pill.textContent = 'Hidden';
                pill.classList.add('hidden-state');
            }
            btn.classList.add('is-hidden');
            try { localStorage.setItem(STORAGE_KEY, '1'); } catch(e) {}
        } else {
            section.classList.remove('analytics-collapsed');
            if (icon) icon.className = 'bi bi-eye-slash me-1';
            if (text) text.textContent = 'Hide';
            if (pill) {
                pill.textContent = '';
                pill.classList.remove('hidden-state');
            }
            btn.classList.remove('is-hidden');
            try { localStorage.removeItem(STORAGE_KEY); } catch(e) {}

            // Smoothly redraw & scale charts to ensure exact crisp bounds
            setTimeout(function () {
                window.dispatchEvent(new Event('resize'));
            }, 80);
            setTimeout(function () {
                window.dispatchEvent(new Event('resize'));
            }, 360);
        }
    }

    // Always start visible immediately without flash or delay
    setHidden(false, false);

    // Interactive animated click
    btn.addEventListener('click', function () {
        const isCollapsed = section.classList.contains('analytics-collapsed');
        setHidden(!isCollapsed, true);
    });

    // ═══ KPI SHINE OBSERVER ═══
    if ('IntersectionObserver' in window) {
        const shineObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('shining');
                    // Remove after animation finishes to allow re-trigger
                    setTimeout(() => entry.target.classList.remove('shining'), 1500);
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('.dashboard-kpi').forEach(kpi => {
            shineObserver.observe(kpi);

            // ═══ DYNAMIC MOUSE-FOLLOWING SHINE ═══
            kpi.addEventListener('mousemove', e => {
                const rect = kpi.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                kpi.style.setProperty('--mouse-x', `${x}px`);
                kpi.style.setProperty('--mouse-y', `${y}px`);
            });
        });
    }
})();
</script>
<?= $this->endSection() ?>
