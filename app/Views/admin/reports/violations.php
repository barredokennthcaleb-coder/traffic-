<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Traffic Violations Summary Report<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- Screen Filter Toolbar (Hidden on Print) -->
    <div class="card border-0 shadow-sm mb-4 no-print premium-reveal" style="--reveal-delay: 0s;">
        <div class="rpt-toolbar-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rpt-icon-wrap" style="background: linear-gradient(135deg, #ef4444, #f97316); box-shadow: 0 6px 18px rgba(239,68,68,0.3);">
                    <i class="bi bi-shield-exclamation"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="mb-0 fw-bold" style="color: var(--text-strong);">Violations Summary Report</h5>
                        <span class="badge bg-danger-subtle text-danger fw-bold border border-danger-subtle" style="font-size: 0.72rem;">Violations Only</span>
                    </div>
                    <p class="mb-0 text-muted small">Analyze violation counts, frequency, and offense rankings without revenue data</p>
                </div>
            </div>

            <!-- Report Mode Switcher & Timeframe Tabs -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Mode Switcher -->
                <div class="btn-group btn-group-sm shadow-sm" role="group">
                    <a href="<?= base_url('reports') ?>" class="btn btn-outline-secondary fw-semibold" title="View Full Report with Revenue & Penalties">
                        <i class="bi bi-currency-dollar me-1 text-success"></i> Revenue Report
                    </a>
                    <a href="<?= base_url('reports/violations') ?>" class="btn btn-danger active fw-bold" title="Currently viewing Violations Summary Only">
                        <i class="bi bi-shield-check me-1"></i> Violations Only
                    </a>
                </div>

                <!-- Real-time Status Chip & Quick Refresh -->
                <div class="d-inline-flex align-items-center gap-1.5 ms-md-2">
                    <span class="live-pill" id="liveSyncPill" title="Continuously synchronized with CTRAMO Violations Database">
                        <span class="live-pulse-dot"></span>
                        <span id="liveSyncStatus">Live Synced</span>
                    </span>
                    <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 shadow-2xs" id="btnQuickRefresh" title="Check for real-time ticket additions">
                        <i class="bi bi-arrow-repeat" id="refreshSpinner"></i> <span class="d-none d-lg-inline">Check Updates</span>
                    </button>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap align-items-center">
                <a href="<?= base_url('reports/violations-export-csv?' . http_build_query($_GET)) ?>" class="btn btn-outline-success btn-sm fw-semibold d-flex align-items-center gap-2 px-3">
                    <i class="bi bi-file-earmark-excel-fill"></i> Export CSV
                </a>
                <button onclick="window.print()" class="btn btn-primary btn-sm fw-semibold d-flex align-items-center gap-2 px-3 shadow-sm">
                    <i class="bi bi-printer-fill"></i> Print / Save PDF
                </button>
            </div>
        </div>
        <div class="card-body bg-transparent pt-0 pb-3 px-4">
            <div class="text-end mb-3 pb-2 border-bottom">
                <div class="text-muted small" style="font-size: 0.74rem;">
                    <i class="bi bi-info-circle me-1 text-danger"></i>Report Period: <strong><?= date('M d, Y', strtotime($start_date)) ?> &ndash; <?= date('M d, Y', strtotime($end_date)) ?></strong>
                </div>
            </div>

            <!-- Responsive Filter Form -->
            <form method="GET" action="<?= base_url('reports/violations') ?>" id="reportFilterForm" class="row g-2 align-items-end">

                <!-- Report Type Dropdown -->
                <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6 col-12">
                    <label for="period_type" class="form-label filter-label">Report Type</label>
                    <select name="period_type" id="period_type" class="form-select form-select-sm" onchange="togglePeriodInputs(this.value)">
                        <option value="weekly"  <?= ($period_type === 'weekly')  ? 'selected' : '' ?>>📅 Weekly Report</option>
                        <option value="monthly" <?= ($period_type === 'monthly') ? 'selected' : '' ?>>📆 Monthly Report</option>
                        <option value="yearly"  <?= ($period_type === 'yearly')  ? 'selected' : '' ?>>📊 Full Year Report</option>
                        <option value="custom"  <?= ($period_type === 'custom')  ? 'selected' : '' ?>>🎯 Custom Range</option>
                    </select>
                </div>

                <!-- Weekly Selector -->
                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12 period-box" id="weeklyBox" style="<?= ($period_type === 'weekly') ? '' : 'display:none;' ?>">
                    <label for="start_date_week" class="form-label filter-label">Week Starting Date</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-danger border-end-0"><i class="bi bi-calendar-week"></i></span>
                        <input type="date" name="start_date" id="start_date_week" class="form-control form-control-sm border-start-0" value="<?= esc($start_date) ?>" <?= ($period_type === 'weekly') ? '' : 'disabled' ?>>
                    </div>
                </div>

                <!-- Monthly Selector (Dropdown to Choose Specific Months & Year) -->
                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12 period-box" id="monthlyBox" style="<?= ($period_type === 'monthly') ? '' : 'display:none;' ?>">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="monthSelect" class="form-label filter-label mb-0">Choose Month &amp; Year</label>
                        <span class="badge bg-danger-subtle text-danger fw-semibold px-1.5 py-0.5" style="font-size:0.65rem;" id="monthBadgeText">
                            <?= date('M Y', strtotime("$year-$month-01")) ?>
                        </span>
                    </div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-danger border-end-0 px-2"><i class="bi bi-calendar-month"></i></span>
                        <select name="month" id="monthSelect" class="form-select form-select-sm border-start-0 fw-semibold" onchange="updateMonthBadge()" <?= ($period_type === 'monthly') ? '' : 'disabled' ?>>
                            <?php
                            $monthList = [
                                '01' => 'January',   '02' => 'February', '03' => 'March',     '04' => 'April',
                                '05' => 'May',       '06' => 'June',     '07' => 'July',      '08' => 'August',
                                '09' => 'September', '10' => 'October',  '11' => 'November',  '12' => 'December'
                            ];
                            $selectedMonth = sprintf('%02d', (int)$month);
                            foreach($monthList as $mCode => $mTitle):
                            ?>
                                <option value="<?= $mCode ?>" <?= ($selectedMonth === $mCode) ? 'selected' : '' ?>><?= $mTitle ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="year" id="monthYearSelect" class="form-select form-select-sm px-1" style="max-width: 80px;" onchange="updateMonthBadge()" <?= ($period_type === 'monthly') ? '' : 'disabled' ?>>
                            <?php $curY = date('Y'); for($y = $curY; $y >= $curY - 5; $y--): ?>
                                <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <!-- Yearly Selector -->
                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12 period-box" id="yearlyBox" style="<?= ($period_type === 'yearly') ? '' : 'display:none;' ?>">
                    <label for="year_yearly" class="form-label filter-label">Calendar Year</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-danger border-end-0"><i class="bi bi-calendar3"></i></span>
                        <select name="year" id="year_yearly" class="form-select form-select-sm border-start-0" <?= ($period_type === 'yearly') ? '' : 'disabled' ?>>
                            <?php $cYear = date('Y'); for($y = $cYear; $y >= $cYear - 5; $y--): ?>
                                <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>>CY <?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <!-- Custom Range -->
                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12 period-box" id="customBox" style="<?= ($period_type === 'custom') ? '' : 'display:none;' ?>">
                    <label class="form-label filter-label">Date Range (From &ndash; To)</label>
                    <div class="input-group input-group-sm">
                        <input type="date" name="start_date" id="start_date_custom" class="form-control form-control-sm px-1 text-center" value="<?= esc($start_date) ?>" <?= ($period_type === 'custom') ? '' : 'disabled' ?>>
                        <span class="input-group-text bg-light text-muted px-1.5">&ndash;</span>
                        <input type="date" name="end_date" id="end_date_custom" class="form-control form-control-sm px-1 text-center" value="<?= esc($end_date) ?>" <?= ($period_type === 'custom') ? '' : 'disabled' ?>>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6 col-12">
                    <label for="status" class="form-label filter-label">Citation Status</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="Paid"      <?= ($status_filter === 'Paid')      ? 'selected' : '' ?>>✅ Paid Only</option>
                        <option value="Pending"   <?= ($status_filter === 'Pending')   ? 'selected' : '' ?>>⏳ Pending</option>
                        <option value="Cancelled" <?= ($status_filter === 'Cancelled') ? 'selected' : '' ?>>❌ Cancelled</option>
                    </select>
                </div>

                <!-- Violation Type Filter -->
                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12">
                    <label for="violation_type_id" class="form-label filter-label">Violation Offense</label>
                    <select name="violation_type_id" id="violation_type_id" class="form-select form-select-sm">
                        <option value="">All Offenses</option>
                        <?php foreach($all_types as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= ($type_filter == $t['id']) ? 'selected' : '' ?>><?= esc($t['violation_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="col-xl-2 col-lg-2 col-md-2 col-sm-12 col-12">
                    <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold d-flex align-items-center justify-content-center gap-1.5 py-1.5 shadow-2xs">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Printable Official Report Document -->
    <div class="report-document-card premium-reveal" id="reportDocument" style="--reveal-delay: 0.15s;">

        <!-- Header Section -->
        <div class="report-header">
            <div class="report-header-seal">
                <img src="<?= base_url('img/pic-1.png') ?>" alt="City Logo" class="report-seal-img">
            </div>
            <div class="report-header-title">
                <div class="rep-sub">Republic of the Philippines</div>
                <div class="rep-office">OFFICE OF THE CITY MAYOR</div>
                <div class="rep-city">Kabankalan City, Negros Occidental</div>
                <div class="rep-dept">CITY TRAFFIC MANAGEMENT &amp; TRANSPORT REGULATION OFFICE (CTRAMO)</div>
                <h2 class="rep-main-title">TRAFFIC VIOLATIONS &amp; OFFENSE FREQUENCY SUMMARY REPORT</h2>
                <div class="rep-period">
                    Covered Period: <strong><?= date('M d, Y', strtotime($start_date)) ?> &ndash; <?= date('M d, Y', strtotime($end_date)) ?></strong>
                    (<?= ($period_type === 'yearly') ? "Annual Summary CY " . esc($year) : ucfirst($period_type) . " Summary" ?>)
                </div>
            </div>
        </div>

        <!-- Meta info bar -->
        <div class="report-meta-bar d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div><strong>Generated:</strong> <?= date('F d, Y h:i A') ?></div>
            <div><strong>Prepared By:</strong> System Admin (<?= esc(session()->get('username') ?? 'Admin') ?>)</div>
        </div>

        <?php
            $totalIncidents   = (int)($summary['total_violations'] ?? 0);
            $totalTickets     = (int)($summary['total_tickets'] ?? 0);
            $paidCount        = (int)($status_counts['Paid'] ?? 0);
            $pendingCount     = (int)($status_counts['Pending'] ?? 0);
            $cancelledCount   = (int)($status_counts['Cancelled'] ?? 0);
            $resolutionRate   = $totalIncidents > 0 ? round(($paidCount / $totalIncidents) * 100, 1) : 0;
            $pendingRate      = $totalIncidents > 0 ? round(($pendingCount / $totalIncidents) * 100, 1) : 0;
            $topShare         = $totalIncidents > 0 ? round(((int)($top_offense['count'] ?? 0) / $totalIncidents) * 100, 1) : 0;
            // Charts grow with the category count: the full-year report lists every
            // offense, and each wrapped category label needs room for two lines.
            $chartHeight      = max(230, (int) $unique_categories * 28);
        ?>

        <!-- Summary KPI Cards (Incident Status Aligned with Dashboard) -->
        <div class="report-kpi-grid">
            <div class="report-kpi-box border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between text-muted mb-1">
                    <span class="kpi-label">Total Incidents</span>
                    <i class="bi bi-file-earmark-text text-primary opacity-75 fs-6"></i>
                </div>
                <div class="kpi-val text-primary"><?= number_format($totalIncidents) ?></div>
                <div class="kpi-sub d-flex align-items-center justify-content-between">
                    <span><?= number_format($totalTickets) ?> Citations Issued</span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5" style="font-size:0.68rem;">All Records</span>
                </div>
            </div>

            <div class="report-kpi-box success border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between text-muted mb-1">
                    <span class="kpi-label">Settled / Paid Status</span>
                    <i class="bi bi-check-circle-fill text-success opacity-75 fs-6"></i>
                </div>
                <div class="kpi-val text-success"><?= number_format($paidCount) ?></div>
                <div class="kpi-sub d-flex align-items-center justify-content-between">
                    <span>Resolution Rate</span>
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 fw-bold" style="font-size:0.68rem;"><?= $resolutionRate ?>% Settled</span>
                </div>
            </div>

            <div class="report-kpi-box warning border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between text-muted mb-1">
                    <span class="kpi-label">Pending / Unsettled</span>
                    <i class="bi bi-clock-fill text-warning-emphasis opacity-75 fs-6"></i>
                </div>
                <div class="kpi-val text-warning-emphasis"><?= number_format($pendingCount) ?></div>
                <div class="kpi-sub d-flex align-items-center justify-content-between">
                    <span>Awaiting Payment</span>
                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2 py-0.5 fw-bold" style="font-size:0.68rem;"><?= $pendingRate ?>% Pending</span>
                </div>
            </div>

            <div class="report-kpi-box border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between text-muted mb-1">
                    <span class="kpi-label">Most Prevalent Offense</span>
                    <i class="bi bi-exclamation-octagon text-danger opacity-75 fs-6"></i>
                </div>
                <div class="kpi-val kpi-val-offense text-danger" title="<?= esc($top_offense['violation_type'] ?? 'None') ?>">
                    <?= esc($top_offense['violation_type'] ?? 'None') ?>
                </div>
                <div class="kpi-sub d-flex align-items-center justify-content-between">
                    <span><?= number_format($top_offense['count'] ?? 0) ?> Occurrences</span>
                    <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-0.5 fw-bold" style="font-size:0.68rem;"><?= $topShare ?>% Share</span>
                </div>
            </div>
        </div>

        <!-- ═══ Section: Violation Frequency & Category Breakdown (Fully Formatted - No Buttons) ═══ -->
        <div class="report-section mb-4" id="reportTrendSection">
            <div class="mb-2">
                <h5 class="report-sec-title mb-1">
                    <i class="bi bi-bar-chart-fill me-2 text-danger"></i>Incident Frequency &amp; Offense Distribution Analysis
                </h5>
                <p class="text-muted small mb-0">
                    <?php if ($period_type === 'weekly'): ?>
                        Weekly Analysis: Daily incident breakdown across the week (Monday through Sunday) alongside ranked offense categories.
                    <?php elseif ($period_type === 'monthly'): ?>
                        Monthly Analysis: Weekly incident breakdown across <?= date('F Y', strtotime($start_date)) ?> alongside ranked offense categories.
                    <?php elseif ($period_type === 'yearly'): ?>
                        Annual Analysis: Monthly incident breakdown across CY <?= esc($year) ?> (January through December) alongside ranked offense categories.
                    <?php else: ?>
                        Timeline incident breakdown alongside ranked offense categories across the reporting period.
                    <?php endif; ?>
                </p>
            </div>

            <?php if (!empty($peak_period) && $peak_period['count'] > 0): ?>
            <!-- Smart Insight Callout -->
            <div class="p-2.5 rounded mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: rgba(239,68,68,0.06); border-left: 3.5px solid #ef4444;">
                <div class="small text-dark">
                    <i class="bi bi-lightning-charge-fill text-warning me-1.5"></i>
                    <strong>Peak Activity Period:</strong> <span class="fw-semibold text-danger"><?= esc($peak_period['label']) ?></span>
                    (<?= number_format($peak_period['count']) ?> incidents recorded)
                    &nbsp;&bull;&nbsp;
                    <strong>Top Offense:</strong> <span class="badge bg-danger-subtle text-danger fw-bold"><?= esc($peak_period['top']) ?></span> (<?= $peak_period['top_count'] ?> occurrences)
                </div>
                <div class="small text-muted fw-semibold" style="font-size: 0.78rem;">
                    <i class="bi bi-graph-up-arrow text-danger me-1"></i>Comprehensive Situation Overview
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($nature_breakdown)): ?>
                <div class="p-4 text-center border rounded bg-white text-muted">
                    <i class="bi bi-bar-chart fs-1 d-block opacity-25 mb-1"></i>
                    No violation incidents recorded for this reporting period.
                </div>
            <?php else: ?>
                <!-- Side-by-Side Dual Visualizations (Timeline on Left, Offenses on Right) -->
                <div class="row g-3">
                    <!-- Left: Timeline Breakdown (Daily for Weekly, Weekly for Monthly, Monthly for Yearly) -->
                    <div class="col-lg-6 col-12">
                        <div class="p-3 border rounded bg-white h-100 shadow-2xs">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                                <span class="fw-bold small text-dark">
                                    <i class="bi bi-calendar3 me-1 text-primary"></i>
                                    <?php if ($period_type === 'weekly'): ?>
                                        Weekly Timeline (Daily Breakdown: Mon &ndash; Sun)
                                    <?php elseif ($period_type === 'monthly'): ?>
                                        Monthly Timeline (Weekly Breakdown)
                                    <?php elseif ($period_type === 'yearly'): ?>
                                        Annual Timeline (Monthly Breakdown: Jan &ndash; Dec)
                                    <?php else: ?>
                                        Incident Timeline Trend
                                    <?php endif; ?>
                                </span>
                                <span class="badge bg-light text-muted border" style="font-size:0.68rem;">Incident Count</span>
                            </div>
                            <div style="position: relative; height: <?= $chartHeight ?>px;">
                                <canvas id="reportTimelineCanvas"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Offense Category Ranking -->
                    <div class="col-lg-6 col-12">
                        <div class="p-3 border rounded bg-white h-100 shadow-2xs">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                                <span class="fw-bold small text-dark">
                                    <i class="bi bi-bar-chart-steps me-1 text-danger"></i>Offense Category Ranking &amp; Distribution
                                </span>
                                <span class="badge bg-light text-muted border" style="font-size:0.68rem;"><?= $unique_categories ?> Categories</span>
                            </div>
                            <div style="position: relative; height: <?= $chartHeight ?>px;">
                                <canvas id="reportRankingCanvas"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ═══ Section I: Enforcer / Officer Activity Summary (Apprehensions Only) ═══ -->
        <div class="report-section mb-4">
            <h5 class="report-sec-title"><i class="bi bi-shield-check me-2"></i>I. Enforcer / Officer Activity Summary</h5>
            <table class="table table-bordered table-sm report-table">
                <thead>
                    <tr>
                        <th style="width:50px;" class="text-center">#</th>
                        <th>Apprehending Officer</th>
                        <th class="text-center" style="width: 150px;">Citations Issued</th>
                        <th class="text-center" style="min-width: 170px;">% Enforcement Share</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($officer_breakdown)): ?>
                        <tr><td colspan="4" class="text-center text-muted">No officer citations found.</td></tr>
                    <?php else: ?>
                        <?php 
                        $totalOfficerCitations = array_sum(array_column($officer_breakdown, 'count'));
                        $i=1; 
                        foreach($officer_breakdown as $ob): 
                            $offShare = $totalOfficerCitations > 0 ? round(($ob['count'] / $totalOfficerCitations) * 100, 1) : 0;
                            $offInitial = strtoupper(substr(trim($ob['officer_name'] ?? 'O'), 0, 1));
                        ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="officer-avatar-chip"><?= esc($offInitial) ?></span>
                                    <span class="fw-bold"><?= esc($ob['officer_name']) ?></span>
                                </div>
                            </td>
                            <td class="text-center fw-bold"><span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1"><?= number_format($ob['count']) ?></span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 8px; background: #e2e8f0; border-radius: 6px; overflow: hidden;">
                                        <div class="progress-bar bg-primary" style="width: <?= $offShare ?>%; border-radius: 6px;"></div>
                                    </div>
                                    <span class="font-monospace fw-semibold small text-end" style="min-width: 42px;"><?= $offShare ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ═══ Section II: Offense Category Breakdown & Distribution (NO Revenue) ═══ -->
        <div class="report-section mb-4">
            <h5 class="report-sec-title"><i class="bi bi-bar-chart-steps me-2"></i>II. Offense Category Breakdown &amp; Percentage Distribution</h5>
            <table class="table table-bordered table-sm report-table">
                <thead>
                    <tr>
                        <th style="width:50px;" class="text-center">#</th>
                        <th>Violation / Offense Nature</th>
                        <th class="text-center">Total Incidents</th>
                        <th class="text-center" style="min-width: 170px;">Visual Distribution &amp; % Share</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($nature_breakdown)): ?>
                        <tr><td colspan="4" class="text-center text-muted">No offenses recorded during this period.</td></tr>
                    <?php else: ?>
                        <?php $i=1; foreach($nature_breakdown as $nb):
                            $share = ($summary['total_violations'] ?? 0) > 0 ? round(($nb['count'] / $summary['total_violations']) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td class="fw-bold"><?= esc($nb['violation_type']) ?></td>
                            <td class="text-center fw-bold"><span class="badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1"><?= number_format($nb['count']) ?></span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 10px; background: #e2e8f0; border-radius: 6px; overflow: hidden;">
                                        <div class="progress-bar <?= $share >= 50 ? 'bg-danger' : ($share >= 25 ? 'bg-primary' : 'bg-info') ?>" 
                                             style="width: <?= $share ?>%; border-radius: 6px;"></div>
                                    </div>
                                    <span class="font-monospace fw-bold small text-end" style="min-width: 44px;"><?= $share ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ═══ Section III: Detailed Incident Records (Violations Only - NO Amount) ═══ -->
        <div class="report-section mb-4" id="detailedRecordsSection">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <h5 class="report-sec-title mb-0"><i class="bi bi-list-check me-2"></i>III. Detailed Citation Records</h5>
                <div class="no-print d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="max-width: 320px;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="recordSearchInput" class="form-control form-control-sm border-start-0" placeholder="Filter driver, plate, officer, TCT..." aria-label="Search records">
                    </div>
                    <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold" id="recordCountBadge"><?= count($records) ?> records</span>
                </div>
            </div>
            <table class="table table-bordered table-sm report-table" id="citationRecordsTable">
                <thead>
                    <tr>
                        <th style="width: 145px;">TCT No.</th>
                        <th>Driver Information</th>
                        <th style="width: 90px;">Plate No.</th>
                        <th>Violation Committed</th>
                        <th class="text-center" style="width: 100px;">Status</th>
                        <th style="width: 150px;">Date &amp; Time</th>
                        <th style="width: 120px;">Officer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr id="emptyRecordsRow"><td colspan="7" class="text-center py-4 text-muted">No detailed citation records found for the selected criteria.</td></tr>
                    <?php else: ?>
                        <?php foreach($records as $r): ?>
                        <tr class="citation-row" 
                            data-ticket="<?= strtolower(esc($r['ticket_id'])) ?>"
                            data-driver="<?= strtolower(esc($r['driver_name'])) ?>"
                            data-plate="<?= strtolower(esc($r['license_plate'])) ?>"
                            data-violation="<?= strtolower(esc($r['violation_name'] ?: $r['violation_type'])) ?>"
                            data-officer="<?= strtolower(esc($r['officer_name'] ?? '')) ?>"
                            data-status="<?= strtolower(esc($r['status'])) ?>">
                            <td class="font-monospace fw-bold text-danger">
                                <span><?= esc($r['ticket_id']) ?></span>
                                <button type="button" class="copy-tct-btn no-print" onclick="copyTicketId('<?= esc($r['ticket_id']) ?>', this)" title="Copy Ticket Number">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </td>
                            <td>
                                <div><strong><?= esc($r['driver_name']) ?></strong></div>
                                <?php if(!empty($r['license_number'])): ?>
                                    <small class="text-muted font-monospace">DL: <?= esc($r['license_number']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="font-monospace fw-semibold"><?= esc($r['license_plate']) ?></td>
                            <td class="fw-semibold text-dark"><?= esc($r['violation_name'] ?: $r['violation_type']) ?></td>
                            <td class="text-center">
                                <?php if($r['status'] === 'Paid'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1" style="font-size: 0.7rem;"></i>Paid
                                    </span>
                                <?php elseif($r['status'] === 'Pending'): ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1">
                                        <i class="bi bi-clock-fill me-1" style="font-size: 0.7rem;"></i>Pending
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">
                                        <i class="bi bi-x-circle-fill me-1" style="font-size: 0.7rem;"></i>Cancelled
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?= date('M d, Y h:i A', strtotime($r['violation_date'])) ?></td>
                            <td class="small fw-semibold"><?= esc($r['officer_name'] ?? 'System') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr id="noMatchRow" style="display:none;"><td colspan="7" class="text-center py-3 text-muted">No matching citations found for your search query.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Sign-off Block with E-Signature Support -->
        <div class="report-sign-block mt-5 pt-3">
            <div class="row">
                <div class="col-6 text-center">
                    <div class="sig-container position-relative d-inline-block" style="width: 240px; min-height: 70px;">
                        <img id="imgPreparedSig" src="" alt="Prepared E-Signature" class="report-sig-img" style="display: none; max-height: 65px; position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%); z-index: 2;">
                        <div class="sig-line mx-auto" style="margin-top: 50px;"></div>
                    </div>
                    <div class="fw-bold mt-1">Prepared By</div>
                    <div class="text-muted small">Traffic System Administrator</div>
                    <div class="no-print mt-2">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1" onclick="openSignatureModal('prepared')">
                            <i class="bi bi-pen-fill me-1"></i> <span id="btnPreparedLabel">Sign Prepared By</span>
                        </button>
                        <button type="button" class="btn btn-link text-danger btn-sm p-0 ms-1 align-middle" id="btnClearPrepared" style="display:none;" onclick="clearSignature('prepared')" title="Remove Signature">
                            <i class="bi bi-x-circle-fill fs-5"></i>
                        </button>
                    </div>
                </div>
                <div class="col-6 text-center">
                    <div class="sig-container position-relative d-inline-block" style="width: 240px; min-height: 70px;">
                        <img id="imgApprovedSig" src="" alt="Approved E-Signature" class="report-sig-img" style="display: none; max-height: 65px; position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%); z-index: 2;">
                        <div class="sig-line mx-auto" style="margin-top: 50px;"></div>
                    </div>
                    <div class="fw-bold mt-1">Approved By</div>
                    <div class="text-muted small">Head, CTRAMO Department</div>
                    <div class="no-print mt-2">
                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1" onclick="openSignatureModal('approved')">
                            <i class="bi bi-pen-fill me-1"></i> <span id="btnApprovedLabel">Sign Approved By</span>
                        </button>
                        <button type="button" class="btn btn-link text-danger btn-sm p-0 ms-1 align-middle" id="btnClearApproved" style="display:none;" onclick="clearSignature('approved')" title="Remove Signature">
                            <i class="bi bi-x-circle-fill fs-5"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- E-Signature Modal -->
<div class="modal fade" id="reportSignatureModal" tabindex="-1" aria-labelledby="reportSignatureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="reportSignatureModalLabel">
                    <i class="bi bi-pen-fill text-primary me-2"></i>Apply Digital E-Signature
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <ul class="nav nav-pills nav-justified mb-3" id="sigTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="draw-tab" data-bs-toggle="tab" data-bs-target="#draw-panel" type="button" role="tab">
                            <i class="bi bi-fingerprint me-1"></i> Draw Signature
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-panel" type="button" role="tab">
                            <i class="bi bi-upload me-1"></i> Upload Image
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="sigTabContent">
                    <div class="tab-pane fade show active" id="draw-panel" role="tabpanel">
                        <p class="text-muted small mb-2">Use your mouse, touch pen, or finger to draw your signature inside the pad below:</p>
                        <div class="border rounded-3 p-2 bg-light text-center position-relative">
                            <canvas id="reportSigCanvas" width="420" height="170" style="touch-action: none; cursor: crosshair; background: #fff; border: 1px dashed #cbd5e1; border-radius: 8px; width: 100%; max-width: 420px; height: 170px;"></canvas>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCanvasClear">
                                <i class="bi bi-eraser-fill me-1"></i> Clear Pad
                            </button>
                            <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Smooth Digital Ink</span>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="upload-panel" role="tabpanel">
                        <p class="text-muted small mb-2">Select a transparent PNG or JPG image of your signature:</p>
                        <input type="file" id="sigFileInput" class="form-control" accept="image/png, image/jpeg, image/webp">
                        <div class="text-center mt-3 p-3 border rounded-3 bg-light" id="uploadPreviewBox" style="display:none;">
                            <img id="uploadPreviewImg" src="" alt="Preview" style="max-height: 100px;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger fw-bold px-4" id="btnApplyReportSig">
                    <i class="bi bi-check-circle-fill me-1"></i> Apply Signature
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Styles -->
<style>
    .rpt-toolbar-header {
        padding: 18px 24px 14px;
        border-bottom: 1px solid #edf1ff;
    }
    .rpt-icon-wrap {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        color: #fff;
        flex-shrink: 0;
    }
    .filter-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7fa8;
        margin-bottom: 4px;
    }
    .period-box {
        animation: fadeInBox 0.22s ease-out;
    }
    @keyframes fadeInBox {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Report Document */
    .report-document-card {
        max-width: 960px;
        margin: 0 auto;
        background: #fff;
        border: 2px solid #1e293b;
        padding: 36px 44px;
        font-family: 'Times New Roman', Times, serif;
        color: #0f172a;
        box-shadow: 0 14px 35px rgba(0,0,0,0.1);
        border-radius: 6px;
    }

    /* Header */
    .report-header {
        position: relative;
        text-align: center;
        border-bottom: 2px solid #000;
        padding-bottom: 14px;
        margin-bottom: 18px;
        min-height: 110px;
    }
    .report-header-seal {
        position: absolute;
        left: 20px;
        top: 0;
    }
    .report-seal-img {
        width: 82px;
        height: 82px;
        object-fit: contain;
    }
    .report-header-title {
        display: inline-block;
        max-width: 700px;
    }
    .rep-sub { font-size: 0.85rem; letter-spacing: 0.05em; text-transform: uppercase; }
    .rep-office { font-size: 0.95rem; font-weight: bold; letter-spacing: 0.04em; }
    .rep-city { font-size: 0.85rem; }
    .rep-dept { font-size: 0.95rem; font-weight: bold; margin-top: 4px; letter-spacing: 0.03em; }
    .rep-main-title {
        font-size: 1.25rem;
        font-weight: 900;
        letter-spacing: 0.05em;
        margin: 10px 0 4px;
        text-decoration: underline;
    }
    .rep-period { font-size: 0.85rem; }

    /* Meta Bar */
    .report-meta-bar {
        font-size: 0.82rem;
        padding: 6px 12px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        border-radius: 4px;
        margin-bottom: 20px;
        font-family: system-ui, -apple-system, sans-serif;
    }

    /* Live Pulse Status */
    .live-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.25);
        color: #065f46;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.02em;
    }
    .live-pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: livePulse 1.8s infinite;
    }
    @keyframes livePulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .spin-animation {
        display: inline-block;
        animation: spinAnim 0.75s linear infinite;
    }
    @keyframes spinAnim {
        100% { transform: rotate(360deg); }
    }
    .officer-avatar-chip {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #3b82f6;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .copy-tct-btn {
        background: transparent;
        border: none;
        padding: 0 4px;
        color: #94a3b8;
        cursor: pointer;
        transition: all 0.2s;
    }
    .copy-tct-btn:hover {
        color: #ef4444;
        transform: scale(1.15);
    }

    /* KPI Grid */
    .report-kpi-grid {
        display: grid;
        /* minmax(0, ...) so a long offense name cannot widen its own column:
           every card stays an equal quarter of the row. */
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 24px;
        font-family: system-ui, -apple-system, sans-serif;
    }
    .report-kpi-box {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 12px 14px;
        min-width: 0;
        background: #fff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .report-kpi-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(0,0,0,0.06);
    }
    .report-kpi-box.success { border-color: #10b981; }
    .report-kpi-box.warning { border-color: #f59e0b; }
    .kpi-label { font-size: 0.7rem; font-weight: bold; text-transform: uppercase; color: #64748b; }
    .kpi-val { font-size: 1.55rem; font-weight: 800; line-height: 1.2; margin: 4px 0 2px; }
    /* Long offense names wrap inside the card instead of overflowing it. */
    .kpi-val-offense {
        font-size: 1.05rem;
        line-height: 1.3;
        white-space: normal;
        overflow-wrap: anywhere;
        margin-bottom: 4px;
    }
    .kpi-sub { font-size: 0.72rem; color: #94a3b8; }

    @media (max-width: 991px) {
        .report-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .report-document-card {
            padding: 24px 20px;
        }
    }
    @media (max-width: 575px) {
        .report-kpi-grid {
            grid-template-columns: minmax(0, 1fr);
        }
        .report-header-seal {
            position: static;
            margin-bottom: 8px;
        }
    }

    /* Section Titles */
    .report-sec-title {
        font-size: 1rem;
        font-weight: bold;
        border-bottom: 1.5px solid #000;
        padding-bottom: 4px;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    /* Table */
    .report-table {
        border: 1.5px solid #000 !important;
        font-size: 0.85rem;
        margin-bottom: 0;
    }
    .report-table thead th {
        background: #e2e8f0 !important;
        color: #000 !important;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.78rem;
        border: 1.5px solid #000 !important;
        vertical-align: middle;
        padding: 10px 12px !important;
    }
    .report-table td { 
        vertical-align: middle; 
        padding: 8px 12px; 
        border: 1.5px solid #000 !important;
        color: #000;
    }
    .report-table tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }

    /* Sign-off */
    .sig-line { width: 220px; border-bottom: 1.5px solid #000; margin-top: 50px; }

    /* Print Styling */
    /* Print Styling */
    @page {
        margin: 8mm 10mm;
        size: A4 portrait;
    }

    @media print {
        /* Hide all UI elements */
        .no-print,
        .sidebar, .sidebar-toggle, .mobile-topbar,
        .desktop-header, .breadcrumb, .btn, .btn-group,
        .alert, nav, header, footer, .live-pill, #btnQuickRefresh {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
            padding: 0 !important;
        }
        .container-fluid { padding: 0 !important; }
        body {
            background: #fff !important;
            color: #0f172a !important;
        }

        /* Root Document Card - Uncompressed, Full Size */
        .report-document-card {
            max-width: 100% !important;
            width: 100% !important;
            border: 2px solid #000 !important;
            box-shadow: none !important;
            padding: 16px 20px !important;
            margin: 0 !important;
            background: #fff !important;
            zoom: 1 !important;
            transform: none !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Header - Standard natural proportions */
        .report-header {
            min-height: auto !important;
            padding-bottom: 12px !important;
            margin-bottom: 12px !important;
            border-bottom: 2px solid #000 !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .report-seal-img {
            width: 75px !important;
            height: 75px !important;
        }
        .rep-sub   { font-size: 0.90rem !important; }
        .rep-office{ font-size: 1.08rem !important; font-weight: bold !important; }
        .rep-city  { font-size: 0.88rem !important; }
        .rep-dept  { font-size: 0.82rem !important; font-weight: bold !important; margin-top: 3px !important; color: #000 !important; }
        .rep-main-title {
            font-size: 1.22rem !important;
            font-weight: 900 !important;
            letter-spacing: 0.03em !important;
            margin: 6px 0 3px !important;
            color: #000 !important;
        }
        .rep-period { font-size: 0.86rem !important; color: #334155 !important; }

        /* Meta Bar */
        .report-meta-bar {
            background: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            padding: 6px 14px !important;
            font-size: 0.84rem !important;
            margin-bottom: 14px !important;
            border-radius: 4px !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        /* KPI Grid - Uncompressed, clear */
        .report-kpi-grid {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 10px !important;
            margin-bottom: 16px !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .report-kpi-box {
            padding: 10px 12px !important;
            border: 1.5px solid #000 !important;
            background: #fafafa !important;
            box-shadow: none !important;
            border-radius: 6px !important;
        }
        .kpi-label { font-size: 0.74rem !important; font-weight: bold !important; }
        .kpi-val   { font-size: 1.25rem !important; font-weight: 900 !important; margin: 3px 0 !important; }
        .kpi-sub   { font-size: 0.72rem !important; }

        /* Section Titles */
        .report-sec-title {
            font-size: 0.95rem !important;
            font-weight: bold !important;
            border-bottom: 1.5px solid #000 !important;
            padding-bottom: 4px !important;
            margin-bottom: 8px !important;
            color: #000 !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        /* Tables - Normal readable size */
        .table-responsive {
            overflow: visible !important;
        }
        .report-table {
            border: 1.5px solid #000 !important;
            font-size: 0.85rem !important;
            margin-bottom: 12px !important;
            width: 100% !important;
        }
        .report-table thead th,
        .report-table th {
            background: #e2e8f0 !important;
            color: #000 !important;
            font-weight: 800 !important;
            font-size: 0.80rem !important;
            padding: 8px 10px !important;
            border: 1.5px solid #000 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .report-table td {
            padding: 8px 10px !important;
            font-size: 0.85rem !important;
            line-height: 1.3 !important;
            border: 1.5px solid #000 !important;
            color: #000 !important;
        }
        .report-table tbody tr:nth-child(even) {
            background-color: #f8fafc !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .report-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        /* Badges */
        .badge {
            border: 1px solid #64748b !important;
            color: #000 !important;
            background: transparent !important;
            font-size: 0.72rem !important;
        }

        /* Sign-off section - Only eliminate the excessive 114px dead whitespace */
        .report-sign-block {
            margin-top: 18px !important;
            padding-top: 0 !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .report-sign-block .row {
            display: flex !important;
            flex-wrap: nowrap !important;
        }
        .report-sign-block .sig-container {
            min-height: 55px !important;
        }
        .report-sign-block .sig-line {
            width: 220px !important;
            margin-top: 30px !important;
            border-bottom: 1.5px solid #000 !important;
        }
        .report-sig-img {
            max-height: 55px !important;
        }
        .report-sign-block .fw-bold {
            font-size: 0.90rem !important;
        }
        .report-sign-block .text-muted {
            font-size: 0.78rem !important;
            color: #000 !important;
        }

        #reportTrendSection {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        #reportTrendSection .row {
            display: flex !important;
            flex-wrap: nowrap !important;
        }
        #reportTrendSection .col-lg-6 {
            width: 50% !important;
            flex: 0 0 50% !important;
            max-width: 50% !important;
        }
    }
</style>

<script>
    function updateMonthBadge() {
        const mSelect = document.getElementById('monthSelect');
        const ySelect = document.getElementById('monthYearSelect');
        const badge = document.getElementById('monthBadgeText');
        if (mSelect && ySelect && badge) {
            const mText = mSelect.options[mSelect.selectedIndex]?.text || '';
            const yVal = ySelect.value;
            badge.textContent = `${mText} ${yVal}`;
        }
    }

    function togglePeriodInputs(val, fromSwitcher = false) {
        document.querySelectorAll('.period-box').forEach(el => {
            el.style.display = 'none';
            el.querySelectorAll('input, select').forEach(input => input.disabled = true);
        });

        const now = new Date();
        const curY = now.getFullYear();
        const curM = String(now.getMonth() + 1).padStart(2, '0');

        if (val === 'weekly') {
            const box = document.getElementById('weeklyBox');
            if (box) {
                box.style.display = '';
                box.querySelectorAll('input, select').forEach(input => input.disabled = false);
            }
        } else if (val === 'monthly') {
            const box = document.getElementById('monthlyBox');
            if (box) {
                box.style.display = '';
                box.querySelectorAll('input, select').forEach(input => input.disabled = false);

                const mSelect = document.getElementById('monthSelect');
                const ySelect = document.getElementById('monthYearSelect');
                if (fromSwitcher) {
                    if (mSelect) mSelect.value = curM;
                    if (ySelect) ySelect.value = curY;
                    updateMonthBadge();
                }
            }
        } else if (val === 'yearly') {
            const box = document.getElementById('yearlyBox');
            if (box) {
                box.style.display = '';
                box.querySelectorAll('input, select').forEach(input => input.disabled = false);
            }
        } else if (val === 'custom') {
            const box = document.getElementById('customBox');
            if (box) {
                box.style.display = '';
                box.querySelectorAll('input, select').forEach(input => input.disabled = false);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const periodSelect = document.getElementById('period_type');
        if (periodSelect) {
            togglePeriodInputs(periodSelect.value);
        }
        updateMonthBadge();
        loadSavedSignatures();
    });

    // Signature Pad & E-Signature Logic
    let currentSigRole = 'prepared';
    let canvas, ctx;
    let isDrawing = false;
    let hasDrawnOnPad = false;
    let uploadedSigDataUrl = '';

    function initReportSigCanvas() {
        canvas = document.getElementById('reportSigCanvas');
        if (!canvas) return;
        ctx = canvas.getContext('2d');

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            let clientX = e.clientX;
            let clientY = e.clientY;
            if (e.touches && e.touches.length > 0) {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            }
            return {
                x: (clientX - rect.left) * (canvas.width / rect.width),
                y: (clientY - rect.top) * (canvas.height / rect.height)
            };
        }

        function startDrawing(e) {
            e.preventDefault();
            isDrawing = true;
            hasDrawnOnPad = true;
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#0f172a';
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }

        function stopDrawing(e) {
            if (!isDrawing) return;
            isDrawing = false;
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDrawing);

        document.getElementById('btnCanvasClear')?.addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hasDrawnOnPad = false;
        });

        document.getElementById('sigFileInput')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    uploadedSigDataUrl = evt.target.result;
                    document.getElementById('uploadPreviewImg').src = uploadedSigDataUrl;
                    document.getElementById('uploadPreviewBox').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('btnApplyReportSig')?.addEventListener('click', function() {
            let sigData = '';
            const activeTab = document.querySelector('#sigTab .nav-link.active')?.id;

            if (activeTab === 'upload-tab') {
                if (!uploadedSigDataUrl) {
                    Swal.fire({ icon: 'warning', title: 'No File Selected', text: 'Please select an image file first.' });
                    return;
                }
                sigData = uploadedSigDataUrl;
            } else {
                if (!hasDrawnOnPad) {
                    Swal.fire({ icon: 'warning', title: 'Empty Signature Pad', text: 'Please draw your signature before applying.' });
                    return;
                }
                sigData = canvas.toDataURL('image/png');
            }

            applySignatureToRole(currentSigRole, sigData);
            
            const modalEl = document.getElementById('reportSignatureModal');
            const bsModal = bootstrap.Modal.getInstance(modalEl);
            bsModal?.hide();
        });
    }

    function openSignatureModal(role) {
        currentSigRole = role;
        const modalTitle = role === 'prepared' ? 'Prepared By Signature' : 'Approved By Signature';
        const titleEl = document.getElementById('reportSignatureModalLabel');
        if (titleEl) titleEl.innerHTML = `<i class="bi bi-pen-fill text-danger me-2"></i>Sign: ${modalTitle}`;

        if (!canvas) initReportSigCanvas();
        if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasDrawnOnPad = false;
        uploadedSigDataUrl = '';
        const fileInput = document.getElementById('sigFileInput');
        if (fileInput) fileInput.value = '';
        const previewBox = document.getElementById('uploadPreviewBox');
        if (previewBox) previewBox.style.display = 'none';

        const modalEl = document.getElementById('reportSignatureModal');
        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
    }

    function applySignatureToRole(role, dataUrl) {
        const capitalizedRole = role.charAt(0).toUpperCase() + role.slice(1);
        const imgEl = document.getElementById(`img${capitalizedRole}Sig`);
        const labelEl = document.getElementById(`btn${capitalizedRole}Label`);
        const clearBtn = document.getElementById(`btnClear${capitalizedRole}`);

        if (imgEl) {
            imgEl.src = dataUrl;
            imgEl.style.display = 'block';
        }
        if (labelEl) labelEl.innerText = `Change ${capitalizedRole} Sig`;
        if (clearBtn) clearBtn.style.display = 'inline-block';

        localStorage.setItem(`report_sig_${role}`, dataUrl);
    }

    function clearSignature(role) {
        const capitalizedRole = role.charAt(0).toUpperCase() + role.slice(1);
        const imgEl = document.getElementById(`img${capitalizedRole}Sig`);
        const labelEl = document.getElementById(`btn${capitalizedRole}Label`);
        const clearBtn = document.getElementById(`btnClear${capitalizedRole}`);

        if (imgEl) {
            imgEl.src = '';
            imgEl.style.display = 'none';
        }
        if (labelEl) labelEl.innerText = `Sign ${capitalizedRole} By`;
        if (clearBtn) clearBtn.style.display = 'none';

        localStorage.removeItem(`report_sig_${role}`);
    }

    function loadSavedSignatures() {
        const prepData = localStorage.getItem('report_sig_prepared');
        if (prepData) applySignatureToRole('prepared', prepData);

        const appData = localStorage.getItem('report_sig_approved');
        if (appData) applySignatureToRole('approved', appData);
    }
</script>

<!-- Chart.js CDN for Violation Frequency Analysis -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Copy TCT Number Helper
    function copyTicketId(tct, btn) {
        if (!navigator.clipboard) return;
        navigator.clipboard.writeText(tct).then(() => {
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = 'bi bi-check2 text-success';
                setTimeout(() => { icon.className = 'bi bi-clipboard'; }, 1500);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const timelineData   = <?= json_encode($timeline_buckets ?? []) ?>;
        const natureData     = <?= json_encode($nature_breakdown ?? []) ?>;
        const topTypes       = <?= json_encode($top_types ?? []) ?>;
        const totalIncidents = <?= (int)($summary['total_violations'] ?? 0) ?>;

        const timelineCanvas = document.getElementById('reportTimelineCanvas');
        const rankingCanvas  = document.getElementById('reportRankingCanvas');

        const chartColors = [
            '#ef4444', '#f97316', '#f59e0b', '#10b981', '#06b6d4',
            '#6366f1', '#8b5cf6', '#ec4899', '#64748b', '#3b82f6'
        ];

        // 1. Render Left Timeline Chart (Daily for weekly, Weekly for monthly, Monthly for yearly)
        if (timelineCanvas && timelineData && timelineData.length > 0) {
            const ctxTimeline = timelineCanvas.getContext('2d');
            const datasets = [];

            // Group by top offense types so stacked bars show breakdown cleanly
            if (topTypes && topTypes.length > 0) {
                topTypes.forEach((vType, idx) => {
                    const color = chartColors[idx % chartColors.length];
                    const dataPoints = timelineData.map(b => (b.by_type && b.by_type[vType]) ? parseInt(b.by_type[vType]) : 0);

                    datasets.push({
                        label: vType,
                        data: dataPoints,
                        backgroundColor: color,
                        borderRadius: 4,
                        borderSkipped: false,
                        stack: 'timelineStack',
                        maxBarThickness: 34
                    });
                });

                const allTypes = (natureData || []).map(n => n.violation_type);
                const otherTypes = allTypes.filter(t => !topTypes.includes(t));
                if (otherTypes.length > 0) {
                    const otherPoints = timelineData.map(b => {
                        let sum = 0;
                        otherTypes.forEach(t => {
                            sum += (b.by_type && b.by_type[t]) ? parseInt(b.by_type[t]) : 0;
                        });
                        return sum;
                    });

                    datasets.push({
                        label: 'Other Offenses',
                        data: otherPoints,
                        backgroundColor: '#94a3b8',
                        borderRadius: 4,
                        borderSkipped: false,
                        stack: 'timelineStack',
                        maxBarThickness: 34
                    });
                }
            }

            // Fallback dataset if topTypes is empty
            if (datasets.length === 0) {
                datasets.push({
                    label: 'Incidents Recorded',
                    data: timelineData.map(b => parseInt(b.count || 0)),
                    backgroundColor: '#ef4444',
                    borderRadius: 4,
                    borderSkipped: false,
                    maxBarThickness: 34
                });
            }

            new Chart(ctxTimeline, {
                type: 'bar',
                data: {
                    labels: timelineData.map(b => b.short_label || b.label),
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 400 },
                    plugins: {
                        legend: {
                            display: datasets.length > 1,
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                boxHeight: 10,
                                font: { size: 10, weight: '600' },
                                padding: 8
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const val = context.raw || 0;
                                    return ` ${context.dataset.label || 'Incidents'}: ${Number(val).toLocaleString('en-PH')}`;
                                },
                                afterBody: function(context) {
                                    if (context.length > 0) {
                                        const idx = context[0].dataIndex;
                                        const bucket = timelineData[idx];
                                        if (bucket) {
                                            let info = `Total in period: ${bucket.count || 0}`;
                                            if (bucket.top_violation && bucket.top_violation !== 'None') {
                                                info += `\nTop: ${bucket.top_violation} (${bucket.top_violation_count})`;
                                            }
                                            return info;
                                        }
                                    }
                                    return '';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: { display: false },
                            ticks: {
                                font: { size: 10, weight: 'bold' }
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.06)' },
                            ticks: {
                                precision: 0,
                                stepSize: 1,
                                font: { size: 10 }
                            }
                        }
                    }
                }
            });
        }

        // 2. Render Right Offense Category Ranking Chart
        if (rankingCanvas && natureData && natureData.length > 0) {
            const ctxRanking = rankingCanvas.getContext('2d');
            const sorted = [...natureData].sort((a, b) => parseInt(b.count || 0) - parseInt(a.count || 0));

            // Long offense names are wrapped onto at most two lines so the widest
            // category fits the value axis instead of being clipped at the canvas
            // edge (the full-year report lists every category).
            const wrapOffenseLabel = (text) => {
                text = String(text);
                if (text.length <= 24) {
                    return text;
                }
                const words = text.split(' ');
                let best = null;
                let bestGap = Infinity;
                for (let i = 1; i < words.length; i++) {
                    const gap = Math.abs(words.slice(0, i).join(' ').length - words.slice(i).join(' ').length);
                    if (gap < bestGap) {
                        bestGap = gap;
                        best = [words.slice(0, i).join(' '), words.slice(i).join(' ')];
                    }
                }
                return best || text;
            };

            new Chart(ctxRanking, {
                type: 'bar',
                data: {
                    labels: sorted.map(n => wrapOffenseLabel(n.violation_type)),
                    datasets: [{
                        label: 'Incidents',
                        data: sorted.map(n => parseInt(n.count || 0)),
                        backgroundColor: sorted.map((_, i) => chartColors[i % chartColors.length]),
                        borderRadius: 4,
                        borderSkipped: false,
                        maxBarThickness: 24
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 400 },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: function(items) {
                                    const idx = items.length ? items[0].dataIndex : -1;
                                    return idx >= 0 && sorted[idx] ? sorted[idx].violation_type : '';
                                },
                                label: function(context) {
                                    const val = context.raw || 0;
                                    const pct = totalIncidents > 0 ? ((val / totalIncidents) * 100).toFixed(1) : 0;
                                    return ` ${val} incident(s) (${pct}% of total)`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.06)' },
                            ticks: {
                                precision: 0,
                                stepSize: 1,
                                font: { size: 10 }
                            }
                        },
                        y: {
                            grid: { display: false },
                            // Never auto-skip: a full-year report lists every category,
                            // and skipping silently hides offense names from the ranking.
                            ticks: {
                                autoSkip: false,
                                padding: 2,
                                font: { size: 10, weight: 'bold' }
                            }
                        }
                    }
                }
            });
        }

        // Realtime Quick Refresh & Polling check
        const refreshBtn = document.getElementById('btnQuickRefresh');
        const refreshSpinner = document.getElementById('refreshSpinner');
        const liveSyncStatus = document.getElementById('liveSyncStatus');

        refreshBtn?.addEventListener('click', function() {
            if (refreshSpinner) refreshSpinner.classList.add('spin-animation');
            if (liveSyncStatus) liveSyncStatus.textContent = 'Checking CTRAMO server...';

            fetch('<?= base_url('api/nature-summary') ?>')
                .then(r => r.json())
                .then(data => {
                    setTimeout(() => {
                        if (refreshSpinner) refreshSpinner.classList.remove('spin-animation');
                        if (data && data.status === 'ok') {
                            const timeStr = data.updated_at ? data.updated_at.split(' ')[1] : 'Just now';
                            if (liveSyncStatus) liveSyncStatus.textContent = `Live Synced (${timeStr})`;

                            const currentTotal = <?= (int)($summary['total_violations'] ?? 0) ?>;
                            if (data.total !== currentTotal) {
                                if (liveSyncStatus) {
                                    liveSyncStatus.innerHTML = `<span class="text-danger fw-bold">${Math.abs(data.total - currentTotal)} changes detected!</span>`;
                                }
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Real-Time Update Detected',
                                        text: `New traffic citation entries were recorded (${data.total} total). Would you like to reload the report?`,
                                        showCancelButton: true,
                                        confirmButtonText: 'Reload Report',
                                        confirmButtonColor: '#ef4444'
                                    }).then(res => {
                                        if (res.isConfirmed) {
                                            window.location.reload();
                                        }
                                    });
                                }
                            }
                        }
                    }, 400);
                })
                .catch(() => {
                    if (refreshSpinner) refreshSpinner.classList.remove('spin-animation');
                    if (liveSyncStatus) liveSyncStatus.textContent = 'Connected (Local)';
                });
        });

        // Instant in-table search for Section III records
        const recordSearchInput = document.getElementById('recordSearchInput');
        const recordCountBadge = document.getElementById('recordCountBadge');
        const citationRows = document.querySelectorAll('.citation-row');
        const noMatchRow = document.getElementById('noMatchRow');

        recordSearchInput?.addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            let visibleCount = 0;

            citationRows.forEach(row => {
                const searchable = (
                    row.getAttribute('data-ticket') + ' ' +
                    row.getAttribute('data-driver') + ' ' +
                    row.getAttribute('data-plate') + ' ' +
                    row.getAttribute('data-violation') + ' ' +
                    row.getAttribute('data-officer') + ' ' +
                    row.getAttribute('data-status')
                ).toLowerCase();

                if (q === '' || searchable.includes(q)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noMatchRow) {
                noMatchRow.style.display = (visibleCount === 0 && q !== '') ? '' : 'none';
            }
            if (recordCountBadge) {
                recordCountBadge.textContent = q === '' ? `${citationRows.length} records` : `${visibleCount} of ${citationRows.length} matched`;
            }
        });
    });
</script>

<?= $this->endSection() ?>
