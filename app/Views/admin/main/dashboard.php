<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Admin Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header with Year Filter & Generate Report Button -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 premium-reveal" style="--reveal-delay: 0s;">
        <div>
            <h4 class="mb-1 fw-bold" style="color: var(--text-strong);"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard &amp; Analytics</h4>
            <p class="text-muted mb-0 small">Comprehensive insights into system activity, violation trends, and revenue.</p>
            <div id="liveClock" class="d-inline-flex align-items-center gap-2 mt-1 px-3 py-1 rounded-pill small fw-semibold" style="background: rgba(99,102,241,0.1); color: var(--brand-primary); font-size: 0.78rem;">
                <i class="bi bi-clock-fill"></i> <span id="clockText">Loading...</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Generate Reports Split Button -->
            <div class="btn-group shadow-sm">
                <a href="<?= base_url('reports') ?>" class="btn btn-primary btn-sm fw-bold px-3 py-2 d-inline-flex align-items-center">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Generate Reports
                </a>
                <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split px-2" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="min-width: 220px; z-index: 1050;">
                    <li><h6 class="dropdown-header text-uppercase fw-bold text-muted px-2" style="font-size: 0.7rem;">Quick Reports</h6></li>
                    <li>
                        <a class="dropdown-item rounded py-2 d-flex align-items-center" href="<?= base_url('reports?period_type=weekly') ?>">
                            <i class="bi bi-calendar-week text-primary me-2 fs-6"></i> Weekly Report
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item rounded py-2 d-flex align-items-center" href="<?= base_url('reports?period_type=monthly') ?>">
                            <i class="bi bi-calendar-month text-success me-2 fs-6"></i> Monthly Report
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item rounded py-2 d-flex align-items-center" href="<?= base_url('reports?period_type=custom') ?>">
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
        <button type="button" id="adminAnalyticsToggleBtn" class="btn-admin-analytics-toggle ms-1" title="Toggle Analytics">
            <i class="bi bi-eye-slash me-1" id="adminAnalyticsToggleIcon"></i>
            <span id="adminAnalyticsToggleText">Hide</span>
        </button>
        <span class="admin-analytics-visible-pill" id="adminAnalyticsVisiblePill"></span>
    </div>

    <!-- ═══ ANALYTICS SECTION ══════════════════════════════════════ -->
    <div id="adminAnalyticsSection" class="admin-analytics-section-wrap">
    <div class="admin-analytics-inner">
    <!-- Quick Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <a href="<?= base_url('penalties/all') ?>" class="text-decoration-none">
            <div class="card premium-kpi premium-reveal text-white border-0" style="--reveal-delay: 0.04s; --float-delay: 0s; cursor:pointer; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="text-uppercase mb-0 fw-bold opacity-75 small" style="letter-spacing: 0.5px;">Total Violations</h6>
                            </div>
                            <h3 class="mb-0 fw-bolder kpi-countup" data-target="<?= $summary['total_violations'] ?>" data-prefix="" data-suffix="">0</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-2.5 d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-file-earmark-text text-white fs-4 lh-1"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-2 border-top border-white border-opacity-25">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="small opacity-75 fw-semibold" style="font-size: 0.75rem;">All recorded tickets</span>
                            <i class="bi bi-arrow-right-circle opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            </a>
        </div>
        
        <div class="col-md-3">
            <a href="<?= base_url('penalties/history') ?>" class="text-decoration-none">
            <div class="card premium-kpi premium-reveal text-white border-0" style="--reveal-delay: 0.07s; --float-delay: 0.4s; cursor:pointer; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
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
            <a href="<?= base_url('penalties') ?>" class="text-decoration-none">
            <div class="card premium-kpi premium-reveal text-white border-0" style="--reveal-delay: 0.10s; --float-delay: 0.8s; cursor:pointer; background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                <div class="card-body p-3 p-xl-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="text-uppercase mb-0 fw-bold opacity-75 small" style="letter-spacing: 0.5px;">Collection Rate</h6>
                            </div>
                            <h3 class="mb-0 fw-bolder kpi-countup" data-target="<?= $summary['collection_rate'] ?>" data-prefix="" data-suffix="%">0%</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-2.5 d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-pie-chart text-white fs-4 lh-1"></i>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small opacity-75 fw-semibold" style="font-size: 0.75rem;">Paid vs Total Violators</span>
                            <i class="bi bi-arrow-right-circle opacity-50 ms-auto"></i>
                        </div>
                        <div class="progress" style="height: 6px; background-color: rgba(255,255,255,0.2); border-radius: 10px;">
                            <div class="progress-bar bg-white rounded-pill" role="progressbar" style="width: <?= $summary['collection_rate'] ?>%;" aria-valuenow="<?= $summary['collection_rate'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
            </a>
        </div>
        <div class="col-md-3">
            <button type="button" class="d-block w-100 border-0 p-0 bg-transparent text-start h-100"
                data-bs-toggle="modal" data-bs-target="#topRankingsModal">
            <div class="card premium-kpi premium-reveal text-white border-0 h-100" style="--reveal-delay: 0.13s; --float-delay: 1.2s; cursor:pointer; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="text-uppercase mb-0 fw-bold opacity-75 small" style="letter-spacing: 0.5px;">Top Violators</h6>
                            </div>
                            <h3 class="mb-0 fw-bolder"><?= number_format(count($top_violators_list)) ?></h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-2 d-inline-flex">
                            <i class="bi bi-person-lines-fill text-white fs-5 lh-1"></i>
                        </div>
                    </div>
                    <?php if (!empty($top_violators_list)): ?>
                    <div class="mb-2">
                        <div class="fw-bold text-truncate" style="font-size:0.95rem;">🥇 <?= esc($top_violators_list[0]['driver_name']) ?></div>
                        <span class="badge bg-white text-warning fw-bold rounded-pill px-2 mt-1" style="font-size:0.75rem;"><?= $top_violators_list[0]['total_violations'] ?> violations</span>
                    </div>
                    <?php endif; ?>
                    <div class="pt-2 border-top border-white border-opacity-25">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="small opacity-75 fw-semibold" style="font-size: 0.75rem;">Click to view rankings</span>
                            <i class="bi bi-arrow-right-circle opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            </button>
        </div>

    </div>

    <!-- Analytics Charts Row 1: Weekly & Monthly -->
    <div class="row g-4 mb-4">
        <!-- Weekly Trend -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 premium-reveal" style="--reveal-delay: 0.15s;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-week me-2 text-primary"></i>Weekly Trend (Last 12 Weeks)</h5>
                </div>
                <div class="card-body">
                    <canvas id="weeklyChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Monthly Breakdown -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 premium-reveal" style="--reveal-delay: 0.17s;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-month me-2 text-success"></i>Monthly Breakdown (<?= esc($year) ?>)</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="300"></canvas>
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
                            <button class="btn btn-sm btn-light border shadow-sm" id="btnNatureRefresh" title="Refresh now">
                                <i class="bi bi-arrow-clockwise" id="natureRefreshIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ── KPI Summary Strip ── -->
                    <div class="d-flex gap-2 mt-3 flex-wrap" id="natureKpiStrip"></div>

                    <!-- ── Search Bar ── -->
                    <div class="mt-2 position-relative">
                        <i class="bi bi-search position-absolute" style="left:10px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:0.8rem;"></i>
                        <input type="text" id="natureSearchInput"
                            class="form-control form-control-sm ps-4 border-0 bg-light rounded-pill"
                            placeholder="Search violation type…"
                            style="font-size:0.82rem;">
                    </div>

                    <!-- ── Last Updated ── -->
                    <div class="mt-1 text-end" style="font-size:0.72rem; color:#94a3b8;">
                        <i class="bi bi-clock me-1"></i>
                        Last updated: <span id="natureLastUpdated">—</span>
                        &nbsp;·&nbsp; <span id="natureRecordCount">0</span> violation type(s)
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
                            <div style="position: relative; height: 340px;">
                                <canvas id="natureBarChart"></canvas>
                            </div>
                        </div>
                        <!-- Doughnut + Scrollable Legend -->
                        <div class="col-md-5">
                            <div class="d-flex flex-column align-items-center gap-2">
                                <div style="width:200px; height:200px; position:relative;">
                                    <canvas id="natureDoughnutChart"></canvas>
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
                        <i class="bi bi-pie-chart me-2 text-success"></i>Violator Status
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
                    <div style="width: 100%; max-width: 300px;">
                        <canvas id="secondaryAnalyticsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div> <!-- End Collapsible Analytics Inner & Wrapper -->

<!-- Top Rankings Modal -->
<div class="modal fade" id="topRankingsModal" tabindex="-1" aria-labelledby="topRankingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <div class="d-flex flex-column w-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="modal-title text-white fw-bold mb-0" id="topRankingsModalLabel">
                                <i class="bi bi-trophy-fill me-2"></i>Top Rankings
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <ul class="nav nav-tabs border-0" id="rankingTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-semibold text-white border-0 border-bottom border-white" id="modalTabViolators"
                                    data-bs-toggle="tab" data-bs-target="#modalPaneViolators" type="button" role="tab"
                                    style="background:transparent; border-bottom: 3px solid white !important; border-radius:0;">
                                    <i class="bi bi-person-lines-fill me-1"></i> Top Violators
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-semibold text-white border-0 opacity-75" id="modalTabViolations"
                                    data-bs-toggle="tab" data-bs-target="#modalPaneViolations" type="button" role="tab"
                                    style="background:transparent; border-radius:0;">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Top Violations
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="modal-body p-0">
                    <div class="tab-content">

                        <!-- Tab: Top Violators -->
                        <div class="tab-pane fade show active" id="modalPaneViolators" role="tabpanel">
                            <?php $maxModalV = !empty($top_violators_list) ? (int)$top_violators_list[0]['total_violations'] : 1; ?>
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4" style="width:55px;">#</th>
                                        <th>Driver Information</th>
                                        <th style="width:42%;">Violations</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($top_violators_list)): ?>
                                        <tr><td colspan="3" class="text-center py-5 text-muted"><i class="bi bi-person-slash fs-1 d-block mb-2 opacity-25"></i>No records found.</td></tr>
                                    <?php else: ?>
                                        <?php $r = 1; $medals = ['🥇','🥈','🥉']; foreach ($top_violators_list as $vl):
                                            $p = $maxModalV > 0 ? round(($vl['total_violations'] / $maxModalV) * 100) : 0;
                                            $rl = $r <= 3 ? $medals[$r-1] : $r;
                                        ?>
                                        <tr style="cursor:pointer;" onclick="window.location='<?= base_url('penalties/search?q='.urlencode($vl['driver_name'])) ?>'">
                                            <td class="ps-4 text-center fw-bold text-muted" style="font-size:1.1rem;"><?= $rl ?></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= esc($vl['driver_name']) ?></div>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace small"><?= esc($vl['license_plate']) ?></span>
                                            </td>
                                            <td class="pe-4">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="flex-grow-1"><div class="progress" style="height:8px;border-radius:4px;"><div class="progress-bar bg-warning" style="width:<?= $p ?>%;"></div></div></div>
                                                    <span class="badge bg-warning text-dark rounded-pill fw-bold px-2" style="min-width:32px;"><?= $vl['total_violations'] ?></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php $r++; endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Tab: Top Violations -->
                        <div class="tab-pane fade" id="modalPaneViolations" role="tabpanel">
                            <?php $maxModalVio = !empty($top_violations_list) ? (int)$top_violations_list[0]['total_count'] : 1; ?>
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4" style="width:55px;">#</th>
                                        <th>Violation Type</th>
                                        <th style="width:42%;">Occurrences</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($top_violations_list)): ?>
                                        <tr><td colspan="3" class="text-center py-5 text-muted"><i class="bi bi-slash-circle fs-1 d-block mb-2 opacity-25"></i>No records found.</td></tr>
                                    <?php else: ?>
                                        <?php $r = 1; $medals = ['🥇','🥈','🥉']; foreach ($top_violations_list as $vio):
                                            $p = $maxModalVio > 0 ? round(($vio['total_count'] / $maxModalVio) * 100) : 0;
                                            $rl = $r <= 3 ? $medals[$r-1] : $r;
                                        ?>
                                        <tr style="cursor:pointer;" onclick="window.location='<?= base_url('penalties/search?q='.urlencode($vio['violation_type'])) ?>'">
                                            <td class="ps-4 text-center fw-bold text-muted" style="font-size:1.1rem;"><?= $rl ?></td>
                                            <td class="fw-bold text-dark"><?= esc($vio['violation_type']) ?></td>
                                            <td class="pe-4">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="flex-grow-1"><div class="progress" style="height:8px;border-radius:4px;"><div class="progress-bar bg-danger" style="width:<?= $p ?>%;"></div></div></div>
                                                    <span class="badge bg-danger text-white rounded-pill fw-bold px-2" style="min-width:32px;"><?= $vio['total_count'] ?></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php $r++; endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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
            const duration  = 1400;
            const step      = 16;
            const steps     = Math.ceil(duration / step);
            let current     = 0;
            const increment = target / steps;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) { current = target; clearInterval(timer); }
                el.textContent = prefix + (decimals > 0 ? current.toLocaleString('en-PH', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) : Math.round(current).toLocaleString('en-PH')) + suffix;
            }, step);
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

        // Shared Chart Settings
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#64748b';
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.9)';
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;

        const gridOptions = {
            color: 'rgba(226, 232, 240, 0.5)',
            drawBorder: false,
        };

        // --- Weekly Trend Chart (Combo: Bar + Line) ---
        const weeklyData = <?= json_encode($weekly_trend) ?>;
        const weekLabels = weeklyData.map(w => w.label);
        const weekCounts = weeklyData.map(w => w.count);
        const weekRevenues = weeklyData.map(w => w.revenue);

        new Chart(document.getElementById('weeklyChart'), {
            type: 'bar',
            data: {
                labels: weekLabels,
                datasets: [
                    {
                        label: 'Violations',
                        data: weekCounts,
                        backgroundColor: '#3b82f6', // blue-500
                        borderRadius: 4,
                        order: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Revenue ($)',
                        data: weekRevenues,
                        type: 'line',
                        borderColor: '#10b981', // emerald-500
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.4,
                        pointBackgroundColor: '#10b981',
                        order: 1,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: { grid: { display: false } },
                    y: { 
                        beginAtZero: true, 
                        grid: gridOptions,
                        title: { display: true, text: 'Count' }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Revenue' }
                    }
                }
            }
        });

        // --- Monthly Breakdown Chart (Line) ---
        const monthlyData = <?= json_encode($monthly_breakdown) ?>;
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const monthlyCounts = Object.values(monthlyData).map(m => m.count);
        const monthlyPaid = Object.values(monthlyData).map(m => m.paid);
        const monthlyPending = Object.values(monthlyData).map(m => m.pending);

        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: monthNames,
                datasets: [
                    {
                        label: 'Total Issued',
                        data: monthlyCounts,
                        borderColor: '#6366f1', // indigo-500
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3
                    },
                    {
                        label: 'Paid',
                        data: monthlyPaid,
                        borderColor: '#10b981', // emerald-500
                        borderDash: [5, 5],
                        tension: 0.4,
                        borderWidth: 2
                    },
                    {
                        label: 'Pending',
                        data: monthlyPending,
                        borderColor: '#f59e0b', // amber-500
                        borderDash: [2, 4],
                        tension: 0.4,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: gridOptions }
                }
            }
        });

        // ═══════════════════════════════════════════════════════════════
        // NATURE OF VIOLATION — REAL-TIME ENGINE
        // ═══════════════════════════════════════════════════════════════
        const NATURE_API_URL   = '<?= base_url('api/nature-summary') ?>';
        const NATURE_REFRESH_MS = 30000; // 30 seconds

        // Full extended palette for all violation types
        const naturePalette = [
            '#4f46e5','#06b6d4','#10b981','#f59e0b','#ec4899',
            '#ef4444','#8b5cf6','#14b8a6','#f97316','#84cc16',
            '#0ea5e9','#a855f7','#22c55e','#fb923c','#e11d48',
            '#7c3aed','#0891b2','#059669','#d97706','#db2777'
        ];

        let natureMode         = 'count';   // 'count' | 'revenue'
        let natureLiveData     = <?= json_encode($nature_summary ?? []) ?>;
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
                    <span class="fw-bold" style="color: #b45309;">₱${Number(totalPend).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                    <span class="text-muted">Pending</span>
                </div>
                ${topType ? `
                <div class="nature-kpi-chip nature-kpi-top" title="Top violation by ${natureMode === 'revenue' ? 'collected revenue' : 'incidents'}">
                    <i class="bi bi-trophy-fill text-danger"></i>
                    <span class="fw-bold text-truncate" style="max-width:140px;" title="${topType.violation_type}">${topType.violation_type}</span>
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
            if (!data || data.length === 0) return;
            const prevMap = buildPrevMap(prevData);

            // Apply search filter
            const filter = natureFilterText.toLowerCase().trim();
            const filtered = filter
                ? data.filter(d => d.violation_type.toLowerCase().includes(filter))
                : data;

            // Sort by active mode
            const sortedData = [...filtered].sort((a, b) => natureVal(b, natureMode) - natureVal(a, natureMode));

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
                    animation: { duration: 600, easing: 'easeInOutQuart' },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: { boxWidth: 12, font: { size: 11 }, padding: 6 }
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
                            ticks: { callback: v => natureMode === 'revenue' ? '₱' + Number(v).toLocaleString() : v }
                        },
                        y: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 11 },
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
                    animation: { animateRotate: true, duration: 800, easing: 'easeInOutQuart' },
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
                    <div class="nature-legend-row ${idx === 0 ? 'nature-legend-top' : ''}" title="${lbl}">
                        <div class="d-flex align-items-center gap-1 text-truncate" style="max-width:160px;">
                            <span class="nature-legend-dot" style="background:${col}"></span>
                            <span class="text-truncate ${isTop} text-dark" style="font-size:0.78rem;">${lbl}</span>
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
        const statusData = <?= json_encode($status_distribution) ?>;
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

            rightChartTitle.innerHTML = '<i class="bi bi-pie-chart me-2 text-success"></i>Violator Status';
            document.getElementById('btnStatus').classList.add('active');
            document.getElementById('btnRole').classList.remove('active');

            secondaryChart = new Chart(secondaryCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusValues,
                        backgroundColor: statusLabels.map(label => {
                            if (label === 'Pending') return '#ffc107';
                            if (label === 'Paid') return '#198754';
                            return '#dc3545';
                        })
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

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
                        backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#6c757d', '#dc3545'].slice(0, roleLabels.length),
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        // Initialize Secondary Chart
        showStatusChart();

        // Event Listeners for Secondary Chart
        document.getElementById('btnStatus').addEventListener('click', showStatusChart);
        document.getElementById('btnRole').addEventListener('click', showRoleChart);
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

/* Spike Alert Banner */
.nature-spike-alert {
    background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%);
    border-left: 4px solid #f59e0b;
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
    width: 9px; height: 9px;
    border-radius: 50%;
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
})();
</script>
<?= $this->endSection() ?>
