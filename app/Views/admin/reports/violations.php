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
                    <p class="mb-0 text-muted small">Officer citation tallies for the selected period.</p>
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
                    <a href="<?= base_url('reports/violators') ?>" class="btn btn-outline-primary fw-semibold" title="Open the apprehension and sanction report of one violator">
                        <i class="bi bi-person-vcard me-1"></i> Violator Reports
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
                    <label class="auto-sync-toggle" for="autoSyncToggle" title="Automatically reload the report when new citations are recorded (read-only check, every minute)">
                        <input type="checkbox" id="autoSyncToggle"> Auto-update
                    </label>
                </div>
            </div>

            <?php if (empty($awaiting_filter)): ?>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <a href="<?= base_url('reports/violations-export-csv?' . http_build_query($_GET)) ?>" class="btn btn-outline-success btn-sm fw-semibold d-flex align-items-center gap-2 px-3">
                    <i class="bi bi-file-earmark-excel-fill"></i> Export CSV
                </a>
                <button onclick="window.print()" class="btn btn-primary btn-sm fw-semibold d-flex align-items-center gap-2 px-3 shadow-sm">
                    <i class="bi bi-printer-fill"></i> Print / Save PDF
                </button>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-body bg-transparent pt-0 pb-3 px-4">
            <div class="text-end mb-3 pb-2 border-bottom">
                <div class="text-muted small" style="font-size: 0.74rem;">
                    <?php if (empty($awaiting_filter)): ?>
                        <i class="bi bi-info-circle me-1 text-danger"></i>Report Period: <strong><?= date('M d, Y', strtotime($start_date)) ?> &ndash; <?= date('M d, Y', strtotime($end_date)) ?></strong>
                    <?php else: ?>
                        <i class="bi bi-info-circle me-1 text-danger"></i>No period selected yet &mdash; pick a report type below.
                    <?php endif; ?>
                </div>
            </div>

            <!-- Responsive Filter Form -->
            <form method="GET" action="<?= base_url('reports/violations') ?>" id="reportFilterForm" class="row g-2 align-items-end">

                <!-- Report Type Dropdown -->
                <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6 col-12">
                    <label for="period_type" class="form-label filter-label">Report Type</label>
                    <select name="period_type" id="period_type" class="form-select form-select-sm" onchange="togglePeriodInputs(this.value)">
                        <?php if (! empty($awaiting_filter)): ?>
                        <option value="" selected disabled>— Choose report type —</option>
                        <?php endif; ?>
                        <option value="daily"   <?= ($period_type === 'daily')   ? 'selected' : '' ?>>🗓️ Daily Report</option>
                        <option value="weekly"  <?= ($period_type === 'weekly')  ? 'selected' : '' ?>>📅 Weekly Report</option>
                        <option value="monthly" <?= ($period_type === 'monthly') ? 'selected' : '' ?>>📆 Monthly Report</option>
                        <option value="yearly"  <?= ($period_type === 'yearly')  ? 'selected' : '' ?>>📊 Full Year Report</option>
                        <option value="custom"  <?= ($period_type === 'custom')  ? 'selected' : '' ?>>🎯 Custom Range</option>
                    </select>
                </div>

                <!-- Daily Selector -->
                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12 period-box" id="dailyBox" style="<?= ($period_type === 'daily') ? '' : 'display:none;' ?>">
                    <label for="report_date" class="form-label filter-label">Report Date</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-danger border-end-0"><i class="bi bi-calendar-day"></i></span>
                        <input type="date" name="report_date" id="report_date" class="form-control form-control-sm border-start-0" value="<?= esc($period_type === 'daily' ? $start_date : date('Y-m-d')) ?>" <?= ($period_type === 'daily') ? '' : 'disabled' ?>>
                    </div>
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
                            $selectedMonth = (int)$month;
                            foreach($monthList as $mCode => $mTitle):
                                $mVal = sprintf('%02d', (int)$mCode);
                            ?>
                                <option value="<?= $mVal ?>" <?= ($selectedMonth === (int)$mCode) ? 'selected' : '' ?>><?= $mTitle ?></option>
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
                        <option value="Paid"      <?= ($status_filter === 'Paid')      ? 'selected' : '' ?>>Paid only</option>
                        <option value="Pending"   <?= ($status_filter === 'Pending')   ? 'selected' : '' ?>>Pending</option>
                        <option value="Cancelled" <?= ($status_filter === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
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

    <?php if (! empty($awaiting_filter)): ?>
    <!-- Same rule as the revenue report: the document waits for a period. -->
    <div class="card border-0 shadow-sm premium-reveal" id="reportEmptyState" style="--reveal-delay: 0.1s;">
        <div class="card-body text-center py-5 px-4">
            <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:56px;height:56px;border-radius:16px;background:rgba(220,38,38,0.1);color:#dc2626;font-size:1.5rem;">
                <i class="bi bi-funnel"></i>
            </div>
            <h5 class="fw-bold mb-1" style="color: var(--text-strong);">Choose a report period</h5>
            <p class="text-muted small mb-4">The summary is built only from the period you ask for — daily, weekly, monthly or full year.</p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="<?= base_url('reports/violations?period_type=daily') ?>" class="btn btn-outline-danger btn-sm fw-semibold px-3"><i class="bi bi-calendar-day me-1"></i>Daily</a>
                <a href="<?= base_url('reports/violations?period_type=weekly') ?>" class="btn btn-outline-danger btn-sm fw-semibold px-3"><i class="bi bi-calendar-week me-1"></i>Weekly</a>
                <a href="<?= base_url('reports/violations?period_type=monthly') ?>" class="btn btn-outline-danger btn-sm fw-semibold px-3"><i class="bi bi-calendar-month me-1"></i>Monthly</a>
                <a href="<?= base_url('reports/violations?period_type=yearly') ?>" class="btn btn-outline-danger btn-sm fw-semibold px-3"><i class="bi bi-calendar3 me-1"></i>Full Year</a>
                <a href="<?= base_url('reports/violations?period_type=custom') ?>" class="btn btn-outline-danger btn-sm fw-semibold px-3"><i class="bi bi-calendar-range me-1"></i>Custom Range</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Printable Official Report Document -->
    <div class="report-document-card premium-reveal<?= empty($awaiting_filter) ? '' : ' d-none' ?>" id="reportDocument" style="--reveal-delay: 0.15s;">

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
                <h2 class="rep-main-title">TRAFFIC VIOLATIONS SUMMARY REPORT</h2>
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
        // ── The three units this report counts ────────────────────────────
        // A citation (ticket) is one apprehension and may carry several
        // offenses; a violator is a person; an offense entry is one row in the
        // violations table. They are different quantities, so every figure.
        // below names its unit instead of leaving the reader to guess why
        // "16 citations" sits beside "41 offenses".
        $sumCitations = (int) ($summary['total_tickets'] ?? 0);
        $sumOffenses  = (int) ($summary['total_violations'] ?? 0);
        $sumViolators = (int) ($violator_count ?? 0);
        $sumTypes     = (int) $unique_categories;

        // Offense entries per citation: the ratio that ties the two units
        // together, and the number people ask about first.
        $sumPerCitation = $sumCitations > 0 ? $sumOffenses / $sumCitations : 0;

        $sumTopName  = (string) ($top_offense['violation_type'] ?? '—');
        $sumTopCount = (int) ($top_offense['count'] ?? 0);

        $sumPeakCount = (int) ($peak_period['count'] ?? 0);

        // The peak card names the bucket the chart is actually divided into, so
        // it never says "Peak Month" while showing a day (weekly) or a week
        // (monthly). A full-year report reads Peak Month, as intended.
        $sumPeakTitle = match ($period_type) {
            'yearly'  => 'Peak Month',
            'monthly' => 'Peak Week',
            'weekly', 'daily' => 'Peak Day',
            default   => 'Peak Period',
        };

        // Entry-level status split, straight from the rows the report read.
        $sumPaid      = (int) ($status_counts['Paid'] ?? 0);
        $sumPending   = (int) ($status_counts['Pending'] ?? 0);
        $sumCancelled = (int) ($status_counts['Cancelled'] ?? 0);

        // The dashboard's violation palette (--dash-nature-palette), in the
        // same order. The dashboard paints offense ranks with it, so ranking
        // an offense here by the same count gives it the same color it has on
        // the dashboard: Unlicensed Driver indigo, Invalid/Suspended CR teal,
        // Discourteous Driver green, and so on down the list.
        $naturePalette = [
            '#4f46e5', '#0891b2', '#059669', '#d97706', '#db2777',
            '#dc2626', '#7c3aed', '#0f766e', '#ea580c', '#65a30d',
            '#0284c7', '#9333ea', '#16a34a', '#c2410c', '#be123c',
            '#6d28d9', '#0e7490', '#047857', '#b45309', '#be185d',
        ];
        ?>

        <!-- ═══ Key figures for the period ═══
             Every number below is read from the same record set the dashboard
             uses, so this sheet and the dashboard cannot disagree about how
             many citations, people or offenses the period produced. -->
        <div class="vsum-kpi-grid">
            <div class="vsum-kpi">
                <div class="vsum-kpi-label">Total Citations</div>
                <div class="vsum-kpi-value"><?= number_format($sumCitations) ?></div>
                <div class="vsum-kpi-sub">apprehension tickets issued in the period</div>
            </div>
            <div class="vsum-kpi">
                <div class="vsum-kpi-label">Most Committed Violations</div>
                <div class="vsum-kpi-value vsum-kpi-value--sm"><?= esc($sumTopName) ?></div>
                <div class="vsum-kpi-sub">
                    <?= number_format($sumTopCount) ?> sanctions recorded
                </div>
            </div>
            <div class="vsum-kpi">
                <div class="vsum-kpi-label"><?= esc($sumPeakTitle) ?></div>
                <div class="vsum-kpi-value vsum-kpi-value--sm"><?= esc($peak_period['label'] ?? '—') ?></div>
                <div class="vsum-kpi-sub">
                    <?= number_format($sumPeakCount) ?> sanctions &mdash; the most in the period
                </div>
            </div>
        </div>

        <!-- Reconciliation: how the units relate, in one sentence. A reader
             who only reads this line still understands where 41 offenses come
             from when the sheet says 16 citations. -->
        <div class="vsum-recon">
            <i class="bi bi-link-45deg"></i>
            <span>
                <strong><?= number_format($sumCitations) ?> citations</strong>
                from <strong><?= number_format($sumViolators) ?> violators</strong>
                carry <strong><?= number_format($sumOffenses) ?> offense entries</strong>
                (<?= number_format($sumPerCitation, 1) ?> per citation).
                Status of those entries:
                <strong><?= number_format($sumPaid) ?> paid</strong>,
                <strong><?= number_format($sumPending) ?> pending</strong><?php if ($sumCancelled > 0): ?>,
                <strong><?= number_format($sumCancelled) ?> cancelled (void)</strong><?php endif; ?>.
            </span>
        </div>

        <!-- ═══ I. Enforcer / Officer Activity Summary (Apprehensions Only) ═══
             Leads the sheet: who recorded the citations is the first question
             an office copy is checked against. -->
        <div class="report-section mb-4" id="officerActivitySection">
            <h5 class="report-sec-title report-sec-title--split">
                <span><i class="bi bi-shield-check me-2"></i>I. Enforcer / Officer Activity Summary</span>
                <button type="button" class="report-sec-print no-print"
                        onclick="printReportPart('summary')"
                        title="Prints Parts I and II on their own, with the letterhead and summary figures">
                    <i class="bi bi-printer-fill"></i><span>Print this part</span>
                </button>
            </h5>
            <div class="table-responsive report-table-scroll">
            <table class="table table-bordered table-sm report-table officer-activity-table">
                <colgroup>
                    <col>
                    <col>
                    <col>
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Apprehending Officer</th>
                        <th class="text-center">Citations Issued</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($officer_breakdown)): ?>
                        <tr><td colspan="3" class="text-center text-muted">No officer citations found.</td></tr>
                    <?php else: ?>
                        <?php
                        $i = 1;
                        $officerCitations = array_sum(array_map(static fn ($ob) => (int) $ob['count'], $officer_breakdown));
                        foreach($officer_breakdown as $ob):
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
                            <td class="text-center fw-bold"><span class="badge bg-primary-subtle text-primary rounded-pill py-1"><?= number_format($ob['count']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (! empty($officer_breakdown)): ?>
                <tfoot>
                    <tr class="report-total-row">
                        <td></td>
                        <td>TOTAL CITATIONS</td>
                        <td class="text-center fw-bold"><?= number_format($officerCitations) ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
            </div>
        </div>

        <!-- ═══ II. Yearly Traffic Apprehension Record ═══
             The office tally sheet: the roster down the rows, one column per
             day (or month, depending on the period), one mark per apprehension
             recorded. -->
        <div class="report-section mb-4" id="apprehensionSection">
            <h5 class="report-sec-title"><i class="bi bi-table me-2"></i>II. Yearly Traffic Apprehension Record</h5>
            <p class="vsum-sec-note">
                <?= esc($personnel_matrix['period_label']) ?> &middot;
                one mark per apprehension (ticket) recorded by each personnel &mdash;
                this sheet counts <strong>tickets, not offense entries</strong>, so its grand
                total matches Total Citations above, not the offense totals in Sections III and IV.
            </p>

            <div class="table-responsive vsum-matrix-scroll">
                <table class="table table-bordered table-sm report-table vsum-matrix">
                    <thead>
                        <tr>
                            <th class="vsum-mx-no">No.</th>
                            <th class="vsum-mx-name">PERSONNEL</th>
                            <?php foreach ($personnel_matrix['columns'] as $col): ?>
                                <th class="vsum-mx-day" title="<?= esc($col['title']) ?>">
                                    <?= esc($col['label']) ?>
                                    <?php if (! empty($col['sub'])): ?><span class="vsum-mx-sub"><?= esc($col['sub']) ?></span><?php endif; ?>
                                </th>
                            <?php endforeach; ?>
                            <th class="vsum-mx-total">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $personnelNo = 1; ?>
                        <?php foreach ($personnel_matrix['rows'] as $pRow): ?>
                            <tr class="<?= (int) $pRow['total'] === 0 ? 'vsum-mx-idle' : '' ?>">
                                <td class="vsum-mx-no"><?= $personnelNo++ ?></td>
                                <td class="vsum-mx-name"><?= esc($pRow['name']) ?></td>
                                <?php foreach ($pRow['cells'] as $cell): ?>
                                    <td class="vsum-mx-cell<?= (int) $cell > 0 ? ' vsum-mx-hit' : '' ?>"><?= (int) $cell > 0 ? (int) $cell : '' ?></td>
                                <?php endforeach; ?>
                                <td class="vsum-mx-total"><?= (int) $pRow['total'] > 0 ? (int) $pRow['total'] : '' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="report-total-row">
                            <td></td>
                            <td class="vsum-mx-name">TOTAL</td>
                            <?php foreach ($personnel_matrix['column_totals'] as $total): ?>
                                <td class="vsum-mx-cell"><?= (int) $total > 0 ? (int) $total : '' ?></td>
                            <?php endforeach; ?>
                            <td class="vsum-mx-total"><?= (int) $personnel_matrix['grand_total'] ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <p class="text-muted small mb-0 mt-2">
                <i class="bi bi-info-circle me-1"></i>
                <?= (int) $personnel_matrix['active_officers'] ?> of
                <?= (int) $personnel_matrix['officer_count'] ?> personnel recorded apprehensions in this period.
                <?php if ((int) $personnel_matrix['excluded_cancelled'] > 0): ?>
                    Cancelled (void) citations are excluded.
                <?php endif; ?>
            </p>
        </div>

        <!-- ═══ III. Violation Trend ═══ -->
        <div class="report-section mb-4" id="trendSection">
            <h5 class="report-sec-title report-sec-title--split">
                <span><i class="bi bi-graph-up me-2"></i>III. Violation Trend</span>
                <button type="button" class="report-sec-print no-print"
                        onclick="printReportPart('detail')"
                        title="Prints Parts III and IV on their own, with the letterhead and summary figures">
                    <i class="bi bi-printer-fill"></i><span>Print this part</span>
                </button>
            </h5>
            <p class="vsum-sec-note">
                Offenses recorded per
                <?= $period_type === 'weekly' ? 'day of the week'
                    : ($period_type === 'monthly' ? 'week of the month'
                    : ($period_type === 'yearly' ? 'month of the year'
                    : ($period_type === 'daily' ? 'day of the report' : 'date in the range'))) ?>.
            </p>
            <div class="vsum-chart-box">
                <canvas id="trendChart" aria-label="Offenses per period" role="img"></canvas>
            </div>
        </div>

        <!-- ═══ IV. Most Committed Offenses ═══ -->
        <div class="report-section mb-4" id="topOffenseSection">
            <h5 class="report-sec-title"><i class="bi bi-bar-chart-steps me-2"></i>IV. Most Committed Offenses</h5>
            <?php if (empty($nature_breakdown)): ?>
                <p class="text-muted small mb-0">No offenses recorded for this period.</p>
            <?php else: ?>
                <p class="vsum-sec-note">
                    Ranked by offense entries &mdash; a citation carrying three offenses adds one
                    entry to each of the three. Entries total <?= number_format($sumOffenses) ?>.
                    <?php if (count($nature_breakdown) > 8): ?>The chart shows the top 8; the table lists all <?= number_format($sumTypes) ?>.<?php endif; ?>
                </p>
                <?php $offenseChartRows = min(8, count($nature_breakdown)); ?>
                <div class="vsum-chart-box" style="height: <?= 70 + ($offenseChartRows * 34) ?>px;">
                    <canvas id="offenseChart" aria-label="Incidents per offense" role="img"></canvas>
                </div>

                <!-- Exact counts under the chart: a bar shows which offense
                     leads, this table shows by exactly how much. -->
                <div class="table-responsive report-table-scroll mt-2">
                <table class="table table-bordered table-sm report-table vsum-offense-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:44px;">#</th>
                            <th>Offense</th>
                            <th class="text-center" style="width:130px;">Incidents</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $offNo = 1; foreach ($nature_breakdown as $nb): ?>
                            <?php $nbColor = $naturePalette[($offNo - 1) % count($naturePalette)]; ?>
                            <tr>
                                <td class="text-center"><?= $offNo++ ?></td>
                                <td>
                                    <span class="vsum-offense-dot" style="background: <?= esc($nbColor) ?>;"></span>
                                    <?= esc($nb['violation_type']) ?>
                                </td>
                                <td class="text-center fw-bold"><?= number_format((int) $nb['count']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="report-total-row">
                            <td></td>
                            <td>TOTAL OFFENSE ENTRIES</td>
                            <td class="text-center fw-bold"><?= number_format($sumOffenses) ?></td>
                        </tr>
                    </tfoot>
                </table>
                </div>
            <?php endif; ?>
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

    @media (max-width: 991px) {
        .report-document-card {
            padding: 24px 20px;
        }
    }
    @media (max-width: 575px) {
        .report-header-seal {
            position: static;
            margin-bottom: 8px;
        }
        /* Toolbar: the title, its badge and the description share one line by
           default; on a phone the badge wraps under the title instead of
           pushing the card 27px past the screen edge. */
        .rpt-toolbar-header {
            padding: 14px 16px 12px;
        }
        .rpt-toolbar-header > div {
            min-width: 0;
            flex-wrap: wrap;
        }
        .rpt-toolbar-header > div > div {
            min-width: 0;
        }
        .rpt-toolbar-header h5 {
            font-size: 1rem;
            overflow-wrap: anywhere;
        }
        /* Sign-off: two 240px pads cannot sit side by side on a phone, so they
           stack and the rule shrinks to the card instead of pushing the whole
           page sideways. Printing keeps the side-by-side A4 layout. */
        .report-sign-block .row > [class*="col-"] {
            flex: 0 0 100%;
            max-width: 100%;
        }
        .report-sign-block .row > [class*="col-"] + [class*="col-"] {
            margin-top: 1.25rem;
        }
        .report-sign-block .sig-container {
            width: 100% !important;
        }
        .report-sign-block .sig-container .sig-line {
            width: 100% !important;
            max-width: 220px;
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

    /* ── Key figures + charts on the document ─────────────────────── */
    .vsum-kpi-grid {
        display: grid;
        /* Three figures: citations, the leading offense, and the busiest month. */
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 10px;
    }

    /* Reconciliation line under the figures: ties citations, people and
       offense entries together in one readable sentence. */
    .vsum-recon {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 8px 12px;
        margin-bottom: 18px;
        border: 1px dashed #cbd5e1;
        border-radius: 6px;
        background: #f8fafc;
        font-size: 0.78rem;
        color: #334155;
        line-height: 1.5;
    }
    .vsum-recon > i { color: #dc2626; font-size: 0.95rem; line-height: 1.35; }
    .vsum-recon strong { color: #111827; }

    /* Exact offense counts beneath the bar chart. */
    .vsum-offense-table { font-size: 0.78rem; }
    .vsum-offense-table th,
    .vsum-offense-table td { padding: 5px 10px !important; }
    /* Same color the dashboard uses for that offense, so the row and its bar
       in the chart above read as one figure. */
    .vsum-offense-dot {
        display: inline-block;
        width: 9px;
        height: 9px;
        border-radius: 3px;
        margin-right: 8px;
        vertical-align: middle;
    }
    .vsum-kpi {
        border: 1px solid #e2e8f0;
        border-left: 3px solid #dc2626;
        border-radius: 6px;
        padding: 10px 12px;
        background: #fcfcfd;
    }
    .vsum-kpi-label {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        /* Dark enough to read at a glance; the muted slate it replaced almost
           disappeared on a projector and in print. */
        color: #475569;
    }
    .vsum-kpi-value {
        font-size: 1.28rem;
        font-weight: 800;
        color: #111827;
        margin: 4px 0 2px;
        line-height: 1.18;
    }
    .vsum-kpi-value--sm { font-size: 0.92rem; line-height: 1.3; }
    .vsum-kpi-sub { font-size: 0.74rem; color: #475569; }

    .vsum-sec-note {
        font-size: 0.76rem;
        color: #475569;
        margin: -4px 0 8px;
    }
    .vsum-chart-box {
        position: relative;
        height: 250px;
        width: 100%;
    }

    /* ── Daily apprehension record: personnel × day ───────────────── */
    .vsum-matrix-scroll { overflow-x: auto; }
    .vsum-matrix { margin-bottom: 0; }
    /* The general report header rule pads every th by 10px/12px, which alone
       makes each day column 49px and pushes the TOTAL column out of the card.
       The tally sheet sets its own tighter padding so the whole month fits. */
    .report-table.vsum-matrix thead th { padding: 5px 3px !important; }
    .vsum-matrix th,
    .vsum-matrix td {
        padding: 3px 2px;
        font-size: 0.72rem;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }
    .vsum-matrix .vsum-mx-no { width: 34px; color: #475569; }
    .vsum-matrix .vsum-mx-name { text-align: left; min-width: 180px; white-space: normal; }
    .vsum-matrix .vsum-mx-day { min-width: 26px; font-size: 0.7rem; }
    .vsum-mx-sub { display: block; font-size: 0.58rem; color: #475569; font-weight: 700; }
    .vsum-matrix .vsum-mx-hit { background: #fef2f2; color: #b91c1c; font-weight: 800; }
    .vsum-matrix .vsum-mx-total { min-width: 48px; background: #f8fafc; font-weight: 800; }
    .vsum-matrix tr.vsum-mx-idle td { color: #64748b; }

    /* A section heading with its print action at the right edge. */
    .report-sec-title--split {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .report-sec-title--split > span { display: inline-flex; align-items: center; min-width: 0; }
    .report-sec-print {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
        padding: 4px 11px;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        background: #fff;
        color: #475569;
        font-family: system-ui, -apple-system, sans-serif;
        font-size: 0.72rem;
        font-weight: 700;
        line-height: 1;
        text-transform: none;
        letter-spacing: 0;
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s, color 0.2s;
    }
    .report-sec-print:hover { background: #fef2f2; border-color: #fca5a5; color: #b91c1c; }

    /* ── Letters you can actually read ────────────────────────────────
       The palette stays exactly as it is — neutral ink, one blue accent,
       the tally's red marks — but the type that was mixed too light for a
       white sheet is darkened to a legible contrast. No new colours. */
    .report-document-card .text-muted { color: #475569 !important; }
    .officer-avatar-chip { background: #1d4ed8; }
    .officer-activity-table .badge { color: #1e3a8a !important; background: #dbeafe !important; }
    .report-sign-block .btn-outline-primary { color: #1d4ed8 !important; border-color: #93c5fd !important; }
    .report-sign-block .btn-outline-primary:hover { color: #fff !important; background: #1d4ed8 !important; }

    @media screen and (max-width: 767px) {
        .vsum-kpi-grid { grid-template-columns: repeat(2, 1fr); }
        /* Five figures in two columns would leave a lone card in the last row;
           it takes the full width instead of a ragged gap. */
        .vsum-kpi-grid > .vsum-kpi:last-child:nth-child(odd) { grid-column: 1 / -1; }
        .vsum-chart-box { height: 220px; }
        .officer-activity-table { min-width: 0; }
    }

    /* Table */
    .report-table {
        border: 1.5px solid #000 !important;
        font-size: 0.85rem;
        margin-bottom: 0;
    }
    .officer-activity-table {
        width: 100%;
        min-width: 520px;
        table-layout: fixed;
    }
    .officer-activity-table col:first-child { width: 8%; }
    .officer-activity-table col:nth-child(2) { width: 72%; }
    .officer-activity-table col:last-child { width: 20%; }
    .officer-activity-table td:nth-child(2) { overflow-wrap: anywhere; }
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
    /* The report is a formal document: no row hover lift from the global theme. */
    .report-table tbody tr {
        transition: none !important;
    }
    .report-table tbody tr:hover {
        background-color: transparent !important;
        transform: none !important;
    }
    .report-table tbody tr:nth-child(even):hover {
        background-color: #f8fafc !important;
    }
    /* A wide table scrolls inside the sheet instead of pushing the whole page
       sideways, so every column stays reachable on a phone. */
    .report-table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    /* Sign-off */
    .sig-line { width: 220px; border-bottom: 1.5px solid #000; margin-top: 50px; }

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

        /* Print readability: the on-screen brand blues and greys are too faint
           on an office laser printer, so every letter inside the document
           prints solid black. The offense color dots are backgrounds, so they
           keep their colors; anything that was white text on a filled pill
           becomes black text on white. */
        .report-document-card * { color: #000 !important; }
        .report-document-card .badge {
            background: #fff !important;
            border: 1px solid #000 !important;
            box-shadow: none !important;
            color: #000 !important;
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

        /* ── Paper organization, matching the revenue report ─────────────
           Fixed column geometry so a long offense or officer name wraps
           inside its own column instead of pushing the table off the sheet,
           headings that repeat when a table spills onto a second page, and
           headings that never end a sheet on their own. */
        .report-table {
            table-layout: fixed !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .report-table th,
        .report-table td {
            overflow-wrap: break-word !important;
            word-break: normal !important;
        }
        .report-table thead {
            display: table-header-group !important;
        }
        .report-sec-title {
            page-break-after: avoid !important;
            break-after: avoid-page !important;
        }
        /* Column shares for the two narrow-figure tables. Without them the
           fixed layout would give the name and the count the same width. */
        .officer-activity-table th:nth-child(1),
        .officer-activity-table td:nth-child(1) { width: 8% !important; }
        .officer-activity-table th:nth-child(3),
        .officer-activity-table td:nth-child(3) { width: 26% !important; }
        .vsum-offense-table th:nth-child(1),
        .vsum-offense-table td:nth-child(1) { width: 9% !important; }
        .vsum-offense-table th:nth-child(3),
        .vsum-offense-table td:nth-child(3) { width: 20% !important; }

        /* Badges */
        .badge {
            border: 1px solid #64748b !important;
            color: #000 !important;
            background: transparent !important;
            font-size: 0.72rem !important;
        }
        /* The officer initial circle prints as an outlined badge so its letter
           stays visible on paper (white text on white paper otherwise). */
        .officer-avatar-chip {
            background: #fff !important;
            color: #000 !important;
            border: 1px solid #64748b !important;
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

        /* Key figures and charts on paper: solid rules, no fills competing
           with the figures, and every chart kept whole on one sheet. */
        .vsum-kpi-grid {
            display: grid !important;
            /* Three figures per row keeps every label readable on A4; five in
               a single row would squeeze them into 42mm columns. */
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 8px !important;
            margin-bottom: 8px !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .vsum-recon {
            background: #fff !important;
            border: 1px solid #000 !important;
            color: #000 !important;
            font-size: 0.76rem !important;
            font-weight: 500 !important;
            margin-bottom: 14px !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .vsum-recon strong { color: #000 !important; }
        .vsum-offense-table th,
        .vsum-offense-table td {
            font-size: 0.78rem !important;
            color: #000 !important;
            border: 1px solid #000 !important;
        }
        .vsum-offense-table .vsum-offense-dot {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .vsum-kpi {
            background: #fff !important;
            border: 1px solid #000 !important;
            border-left: 3px solid #000 !important;
            padding: 8px 10px !important;
        }
        .vsum-kpi-label { font-size: 0.72rem !important; color: #000 !important; font-weight: 800 !important; }
        .vsum-kpi-value { font-size: 1.14rem !important; color: #000 !important; margin: 2px 0 !important; }
        .vsum-kpi-value--sm { font-size: 0.86rem !important; }
        .vsum-kpi-sub   { font-size: 0.72rem !important; color: #000 !important; font-weight: 600 !important; }
        .vsum-sec-note  { font-size: 0.76rem !important; color: #000 !important; font-weight: 500 !important; }
        .vsum-chart-box {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        /* Chart.js sizes the canvas in screen pixels. The sheet is narrower
           than the screen, so without this the chart prints as a small block
           in the corner of a tall blank box, with its right-hand labels past
           the printable width. Stretched to the box it already sits in, the
           chart fills the sheet like the revenue report's figures do. */
        .vsum-chart-box canvas {
            display: block !important;
            width: 100% !important;
            height: 100% !important;
        }
        /* Screen heights were tuned for a scrolling window; on paper they
           spent a page's worth of height on air. These keep both charts
           readable and let the document end on one sheet fewer. */
        .vsum-chart-box { height: 200px !important; }
        #topOffenseSection .vsum-chart-box { height: 300px !important; }
        /* Slightly tighter section spacing, as the revenue sheet prints it. */
        .report-section { margin-bottom: 14px !important; }

        /* ── Printing one part at a time ─────────────────────────────────
           The toolbar's Print button still delivers the whole document. These
           two scopes keep only the chosen pair of sections on the sheet, and
           the letterhead, covered period and summary figures travel with it,
           so each part is a complete official copy on its own — the same
           arrangement as the revenue report's per-section print buttons. */
        body.print-scope-summary .report-section:not(#officerActivitySection):not(#apprehensionSection),
        body.print-scope-detail .report-section:not(#trendSection):not(#topOffenseSection) {
            display: none !important;
        }
        .report-sec-print { display: none !important; }

        .vsum-matrix-scroll { overflow: visible !important; }
        /* The screen keeps 26px day cells so the sheet is tappable; that
           minimum alone is wider than A4, which used to push the last day
           columns and TOTAL off the right edge of the paper. On paper the
           table is laid out fixed to the printable width instead, so the
           whole month, the names and the TOTAL column always land on the
           sheet. */
        .vsum-matrix {
            table-layout: fixed !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .vsum-matrix th,
        .vsum-matrix td {
            font-size: 0.58rem !important;
            padding: 2px 0 !important;
            border: 1px solid #000 !important;
            color: #000 !important;
        }
        .vsum-matrix thead { display: table-header-group !important; }
        .vsum-matrix .vsum-mx-name { width: 15% !important; }
        /* Wide enough for the word TOTAL on its heading row, which stays on
           one line like the day numbers beside it. */
        .vsum-matrix .vsum-mx-total { width: 8% !important; }
        .vsum-matrix .vsum-mx-no,
        .vsum-matrix .vsum-mx-day,
        .vsum-matrix .vsum-mx-total {
            min-width: 0 !important;
        }
        .vsum-matrix .vsum-mx-day { width: auto !important; }
        .vsum-matrix .vsum-mx-hit { background: #fff !important; color: #000 !important; font-weight: 800 !important; }
        .vsum-matrix .vsum-mx-total { background: #f1f5f9 !important; }
        .vsum-matrix tr.vsum-mx-idle td { color: #000 !important; }
        .vsum-mx-sub { color: #000 !important; }
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

    /* Print one part on its own: the scope class hides the other sections and
       is removed as soon as the dialog closes, so the screen and any later
       print are untouched. Both parts print portrait on the document's own
       A4 sheet — the tally table is laid out to fit the printable width. */
    function printReportPart(scope) {
        if (!scope) {
            window.print();
            return;
        }

        const scopeClass = 'print-scope-' + scope;
        document.body.classList.add(scopeClass);

        const restore = function () {
            document.body.classList.remove(scopeClass);
        };

        window.addEventListener('afterprint', restore, { once: true });
        window.print();

        // Chrome and Edge block until the dialog closes; this also covers a
        // browser that returns at once, where afterprint may never fire.
        setTimeout(restore, 800);
    }

    function togglePeriodInputs(val, fromSwitcher = false) {
        document.querySelectorAll('.period-box').forEach(el => {
            el.style.display = 'none';
            el.querySelectorAll('input, select').forEach(input => input.disabled = true);
        });

        const now = new Date();
        const curY = now.getFullYear();
        const curM = String(now.getMonth() + 1).padStart(2, '0');

        if (val === 'daily') {
            const box = document.getElementById('dailyBox');
            if (box) {
                box.style.display = '';
                box.querySelectorAll('input, select').forEach(input => input.disabled = false);
            }
        } else if (val === 'weekly') {
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
                    if (mSelect && !mSelect.value) {
                        mSelect.value = curM;
                    }
                    if (ySelect && !ySelect.value) {
                        ySelect.value = curY;
                    }
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // The document keeps its own order: the enforcer activity summary
        // leads the sheet. Nothing is shuffled after load.

        // Live check: compares this page's ticket count with the read-only
        // summary API. It only ever READS — nothing here can change, delete
        // or write to the database.
        const refreshBtn     = document.getElementById('btnQuickRefresh');
        const refreshSpinner = document.getElementById('refreshSpinner');
        const liveSyncStatus = document.getElementById('liveSyncStatus');
        const autoToggle     = document.getElementById('autoSyncToggle');

        const currentTotal = <?= (int)($summary['total_violations'] ?? 0) ?>;
        const summaryUrl   = '<?= base_url('api/nature-summary') ?>';
        const AUTO_KEY     = 'reportAutoSync';
        let autoTimer      = null;

        function checkForUpdates(auto) {
            // A hidden tab does not need to poll. On the single-threaded dev
            // server a background poll can delay navigation in another tab.
            if (auto && document.hidden) return;

            if (refreshSpinner) refreshSpinner.classList.add('spin-animation');
            if (liveSyncStatus && !auto) liveSyncStatus.textContent = 'Checking CTRAMO server...';

            fetch(summaryUrl)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    setTimeout(function () {
                        if (refreshSpinner) refreshSpinner.classList.remove('spin-animation');
                        if (!data || data.status !== 'ok') return;

                        const timeStr = data.updated_at ? data.updated_at.split(' ')[1] : 'Just now';
                        if (liveSyncStatus) liveSyncStatus.textContent = 'Live Synced (' + timeStr + ')';

                        if (Number(data.total) === currentTotal) return;

                        if (liveSyncStatus) {
                            liveSyncStatus.innerHTML = '<span class="text-danger fw-bold">' + Math.abs(data.total - currentTotal) + ' changes detected!</span>';
                        }

                        if (auto) {
                            // Auto mode: quietly reload so the sheets always
                            // match the dashboard the moment data changes.
                            setTimeout(function () { window.location.reload(); }, 1200);
                            return;
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'info',
                                title: 'Real-Time Update Detected',
                                text: 'New traffic citation entries were recorded (' + data.total + ' total). Would you like to reload the report?',
                                showCancelButton: true,
                                confirmButtonText: 'Reload Report',
                                confirmButtonColor: '#ef4444'
                            }).then(function (res) {
                                if (res.isConfirmed) {
                                    window.location.reload();
                                }
                            });
                        }
                    }, 400);
                })
                .catch(function () {
                    if (refreshSpinner) refreshSpinner.classList.remove('spin-animation');
                    if (liveSyncStatus) liveSyncStatus.textContent = 'Connected (Local)';
                });
        }

        refreshBtn?.addEventListener('click', function () { checkForUpdates(false); });

        function setAutoSync(on) {
            if (autoTimer) {
                clearInterval(autoTimer);
                autoTimer = null;
            }
            if (on) {
                // One read-only poll per minute; nothing is written anywhere.
                autoTimer = setInterval(function () { checkForUpdates(true); }, 60000);
            }
            if (autoToggle) {
                autoToggle.checked = !!on;
            }
        }

        if (autoToggle) {
            setAutoSync(localStorage.getItem(AUTO_KEY) === '1');
            autoToggle.addEventListener('change', function () {
                localStorage.setItem(AUTO_KEY, autoToggle.checked ? '1' : '0');
                setAutoSync(autoToggle.checked);
            });

            // Catch up once when the reader returns to the tab.
            document.addEventListener('visibilitychange', function () {
                if (!document.hidden && autoToggle.checked) checkForUpdates(true);
            });
        }
    });
</script>

<?php
    // Chart data comes from the same arrays the tables are built from, so a
    // bar can never tell a different story than the numbers beside it.
    $trendLabels = array_values(array_map(static fn ($b) => (string) ($b['short_label'] ?? ''), $timeline_buckets ?? []));
    $trendCounts = array_values(array_map(static fn ($b) => (int) ($b['count'] ?? 0), $timeline_buckets ?? []));

    $topOffenseRows = array_slice($nature_breakdown ?? [], 0, 8);
    $offenseLabels  = array_values(array_map(static fn ($n) => (string) ($n['violation_type'] ?? ''), $topOffenseRows));
    $offenseCounts  = array_values(array_map(static fn ($n) => (int) ($n['count'] ?? 0), $topOffenseRows));
    // One bar per offense, each in the dashboard's color for that offense.
    $offenseColors  = array_slice($naturePalette, 0, count($topOffenseRows));

    $jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>

<script src="<?= base_url('vendor/chart.js/chart.umd.min.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') return;

        const trendLabels = <?= json_encode($trendLabels, $jsonFlags) ?>;
        const trendCounts = <?= json_encode($trendCounts, $jsonFlags) ?>;

        const trendEl = document.getElementById('trendChart');
        if (trendEl) {
            new Chart(trendEl, {
                type: 'bar',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Offenses',
                        data: trendCounts,
                        // The dashboard's violation color (its Violators KPI and
                        // the lead bar in the offense-nature chart), so a reader
                        // recognises "this is the violation figure" on sight.
                        backgroundColor: '#4f46e5',
                        borderRadius: 4,
                        maxBarThickness: 38
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#334155', font: { size: 11 } },
                            grid: { color: '#eef2f7' }
                        },
                        x: {
                            ticks: { color: '#334155', font: { size: 11 }, autoSkip: false, maxRotation: 60, minRotation: 0 },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        const offenseLabels = <?= json_encode($offenseLabels, $jsonFlags) ?>;
        const offenseCounts = <?= json_encode($offenseCounts, $jsonFlags) ?>;
        const offenseColors = <?= json_encode($offenseColors, $jsonFlags) ?>;

        const offenseEl = document.getElementById('offenseChart');
        if (offenseEl) {
            new Chart(offenseEl, {
                type: 'bar',
                data: {
                    labels: offenseLabels,
                    datasets: [{
                        label: 'Incidents',
                        data: offenseCounts,
                        // Dashboard palette by rank: the same offense carries the
                        // same color on both screens.
                        backgroundColor: offenseColors,
                        borderRadius: 4,
                        maxBarThickness: 22
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#334155', font: { size: 11 } },
                            grid: { color: '#eef2f7' }
                        },
                        y: {
                            ticks: { color: '#334155', font: { size: 11 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    });
</script>

<?= $this->endSection() ?>
