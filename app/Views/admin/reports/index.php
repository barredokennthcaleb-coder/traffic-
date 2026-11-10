<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Generated Traffic Violation Report<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- Screen Filter Toolbar (Hidden on Print) -->
    <div class="card border-0 shadow-sm mb-4 no-print premium-reveal" style="--reveal-delay: 0s;">
        <div class="rpt-toolbar-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rpt-icon-wrap" title="Generate Reports">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <path d="M8 17v-3"></path>
                        <path d="M12 17v-6"></path>
                        <path d="M16 17v-8"></path>
                    </svg>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold" style="color: var(--text-strong);">Generate Reports</h5>
                    <p class="mb-0 text-muted small">Filter by period, type, or status then print or export</p>
                </div>
            </div>

            <!-- Report Mode Switcher & Period Quick Tabs -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Mode Switcher -->
                <div class="btn-group btn-group-sm shadow-sm" role="group">
                    <a href="<?= base_url('reports') ?>" class="btn btn-primary active fw-bold" title="Currently viewing Citation & Revenue Report">
                        <i class="bi bi-currency-dollar me-1"></i> Revenue Report
                    </a>
                    <a href="<?= base_url('reports/violations') ?>" class="btn btn-outline-danger fw-semibold" title="Switch to Violations Summary Only">
                        <i class="bi bi-shield-exclamation me-1"></i> Violations Only
                    </a>
                    <a href="<?= base_url('reports/violators') ?>" class="btn btn-outline-primary fw-semibold" title="Open the apprehension and sanction report of one violator">
                        <i class="bi bi-person-vcard me-1"></i> Violator Reports
                    </a>
                </div>

            </div>

            <?php if (empty($awaiting_filter)): ?>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <a href="<?= base_url('reports/export-csv?' . http_build_query($_GET)) ?>" class="btn btn-outline-success btn-sm fw-semibold d-flex align-items-center gap-2 px-3">
                    <i class="bi bi-file-earmark-excel-fill"></i> Export CSV
                </a>
                <button onclick="window.print()" class="btn btn-primary btn-sm fw-semibold d-flex align-items-center gap-2 px-3 shadow-sm"
                        title="Prints on A4 portrait at 100% — the sheet is already scaled to the paper">
                    <i class="bi bi-printer-fill"></i> Print / Save PDF
                </button>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-body bg-transparent pt-0 pb-3 px-4 rpt-toolbar-body">
            <div class="text-end mb-3 pb-2 border-bottom">
                <div class="text-muted small" style="font-size: 0.74rem;">
                    <?php if (empty($awaiting_filter)): ?>
                        <i class="bi bi-info-circle me-1 text-primary"></i>Report Period: <strong><?= date('M d, Y', strtotime($start_date)) ?> &ndash; <?= date('M d, Y', strtotime($end_date)) ?></strong>
                    <?php else: ?>
                        <i class="bi bi-info-circle me-1 text-primary"></i>No period selected yet &mdash; pick a report type below.
                    <?php endif; ?>
                </div>
            </div>

            <!-- Responsive Filter Form -->
            <form method="GET" action="<?= base_url('reports') ?>" id="reportFilterForm" class="row g-2 align-items-end">

                <!-- Report Type Dropdown -->
                <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6 col-12">
                    <label for="period_type" class="form-label filter-label">Report Type</label>
                    <select name="period_type" id="period_type" class="form-select form-select-sm" onchange="togglePeriodInputs(this.value, true)">
                        <?php /* Choosing a type only reveals its controls; the
                                 report is built when Filter is pressed. */ ?>
                        <?php if (! empty($awaiting_filter)): ?>
                        <option value="" selected disabled>— Choose report type —</option>
                        <?php endif; ?>
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
                        <span class="input-group-text bg-white text-primary border-end-0"><i class="bi bi-calendar-week"></i></span>
                        <input type="date" name="start_date" id="start_date_week" class="form-control form-control-sm border-start-0" value="<?= esc($start_date) ?>" <?= ($period_type === 'weekly') ? '' : 'disabled' ?>>
                    </div>
                </div>

                <!-- Monthly Selector (Dropdown to Choose Specific Months & Year) -->
                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12 period-box" id="monthlyBox" style="<?= ($period_type === 'monthly') ? '' : 'display:none;' ?>">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="monthSelect" class="form-label filter-label mb-0">Choose Month &amp; Year</label>
                        <span class="badge bg-primary-subtle text-primary fw-semibold px-1.5 py-0.5" style="font-size:0.65rem;" id="monthBadgeText">
                            <?= date('M Y', strtotime("$year-$month-01")) ?>
                        </span>
                    </div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-primary border-end-0 px-2"><i class="bi bi-calendar-month"></i></span>
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
                        <span class="input-group-text bg-white text-primary border-end-0"><i class="bi bi-calendar3"></i></span>
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
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold d-flex align-items-center justify-content-center gap-1.5 py-1.5 shadow-2xs">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                </div>

            </form>

            <!-- Printing happens on the office PC through the browser's own
                 print dialog; this states the settings the sheet was laid
                 out for so nobody has to guess. -->
            <p class="rpt-print-hint mb-0 mt-2">
                <i class="bi bi-printer me-1"></i>
                Prints on <strong><?= $period_type === 'yearly' ? 'Long bond 8.5 &times; 13 in' : 'A4' ?> &middot; Portrait &middot; 100%</strong> from this computer &mdash;
                no scale change needed. In the print dialog, switch off <em>Headers and footers</em> so the page address
                never appears on the official copy. Use <strong>Print this part</strong> to print the
                summary (Parts I&ndash;II) or the detailed records on its own.
            </p>
        </div>
    </div>

    <?php if (! empty($awaiting_filter)): ?>
    <!-- Nothing is built before a period is chosen: the page opens on this
         prompt instead of a full-year document that looks unfiltered. -->
    <div class="card border-0 shadow-sm premium-reveal no-print" id="reportEmptyState" style="--reveal-delay: 0.1s;">
        <div class="card-body text-center py-5 px-4">
            <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:56px;height:56px;border-radius:16px;background:rgba(37,99,235,0.1);color:#2563eb;font-size:1.5rem;">
                <i class="bi bi-funnel"></i>
            </div>
            <h5 class="fw-bold mb-1" style="color: var(--text-strong);">Choose a report period</h5>
            <p class="text-muted small mb-4">The report is built only from the period you ask for — weekly, monthly or full year.</p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <button type="button" onclick="pickPeriod('weekly')" class="btn btn-outline-primary btn-sm fw-semibold px-3"><i class="bi bi-calendar-week me-1"></i>Weekly</button>
                <button type="button" onclick="pickPeriod('monthly')" class="btn btn-outline-primary btn-sm fw-semibold px-3"><i class="bi bi-calendar-month me-1"></i>Monthly</button>
                <button type="button" onclick="pickPeriod('yearly')" class="btn btn-outline-primary btn-sm fw-semibold px-3"><i class="bi bi-calendar3 me-1"></i>Full Year</button>
            </div>
            <p class="text-muted small mt-3 mb-0" id="pickHint">Pick a period, then press <strong>Filter</strong> to build the report.</p>
        </div>
    </div>

    <!-- Pressing Ctrl+P on this screen must not hand out a sheet of controls:
         it states why there is nothing to print instead. -->
    <div class="print-only-note">
        <div class="pon-office">CITY TRAFFIC MANAGEMENT &amp; TRANSPORT REGULATION OFFICE (CTRAMO)</div>
        <h4 class="pon-title">Traffic Citation &amp; Revenue Summary Report</h4>
        <p>No report period has been selected yet, so there is nothing to print.
           Choose <strong>Weekly</strong>, <strong>Monthly</strong> or <strong>Full Year</strong>,
           press <strong>Filter</strong>, then print again.</p>
    </div>
    <?php endif; ?>

    <?php
        // Which period is on the sheet decides how tightly it prints: a single
        // week fits on a page or two, a full year can run to many. This class
        // selects that print scale in the rules at the foot of this view.
        $printPeriodClass = 'print-period-' . ($period_type !== '' ? $period_type : 'custom');
    ?>

    <!-- Printable Official Report Document -->
    <div class="report-document-card premium-reveal <?= esc($printPeriodClass) ?><?= empty($awaiting_filter) ? '' : ' d-none' ?>" id="reportDocument" data-print-period="<?= esc($period_type) ?>" style="--reveal-delay: 0.15s;">

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
                <h2 class="rep-main-title">TRAFFIC CITATION &amp; REVENUE SUMMARY REPORT</h2>
                <div class="rep-period">
                    Covered Period: <strong><?= date('M d, Y', strtotime($start_date)) ?> &ndash; <?= date('M d, Y', strtotime($end_date)) ?></strong>
                    (<?= ucfirst($period_type) ?> Report)
                </div>
            </div>
        </div>

        <!-- Meta info bar -->
        <div class="report-meta-bar d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div><strong>Generated:</strong> <?= date('F d, Y h:i A') ?></div>
            <div><strong>Prepared By:</strong> System Admin (<?= esc(session()->get('username') ?? 'Admin') ?>)</div>
        </div>

        <!-- Summary KPI Cards -->
        <div class="report-kpi-grid">
            <div class="report-kpi-box">
                <div class="kpi-label">Tickets Issued</div>
                <div class="kpi-val"><?= number_format($summary['total_tickets']) ?></div>
                <div class="kpi-sub"><?= number_format($summary['total_violations']) ?> Total Offenses</div>
            </div>
            <div class="report-kpi-box">
                <div class="kpi-label">Total Fines</div>
                <div class="kpi-val">₱<?= number_format($summary['total_assessed'], 2) ?></div>
                <div class="kpi-sub">Gross Citation Value</div>
            </div>
            <div class="report-kpi-box success">
                <div class="kpi-label">Revenue Collected</div>
                <div class="kpi-val text-success">₱<?= number_format($summary['total_paid'], 2) ?></div>
                <div class="kpi-sub"><?= number_format($summary['paid_tickets']) ?> Settled Tickets</div>
            </div>
            <div class="report-kpi-box warning">
                <div class="kpi-label">Collection Rate</div>
                <div class="kpi-val text-primary"><?= $summary['collection_rate'] ?>%</div>
                <div class="kpi-sub">Pending: ₱<?= number_format($summary['total_pending'], 2) ?></div>
            </div>
        </div>

        <?php
            // Offense status split for the period — the same bar the Violations
            // Summary Report carries, so both reports state the status mix the
            // same way. Counts cover every recorded offense; cancelled is void.
            $statusPaid      = (int) ($status_counts['Paid'] ?? 0);
            $statusPending   = (int) ($status_counts['Pending'] ?? 0);
            $statusCancelled = (int) ($status_counts['Cancelled'] ?? 0);
            $statusTotal     = $statusPaid + $statusPending + $statusCancelled;

            $statusPaidPct      = $statusTotal > 0 ? round(($statusPaid / $statusTotal) * 100, 2) : 0.0;
            $statusPendingPct   = $statusTotal > 0 ? round(($statusPending / $statusTotal) * 100, 2) : 0.0;
            $statusCancelledPct = $statusTotal > 0 ? round(($statusCancelled / $statusTotal) * 100, 2) : 0.0;

            // Exact shares for the plotted widths (they must add up to 100%);
            // the printed labels use the rounded figures above.
            $statusPaidWidth      = $statusTotal > 0 ? $statusPaid / $statusTotal * 100 : 0.0;
            $statusPendingWidth   = $statusTotal > 0 ? $statusPending / $statusTotal * 100 : 0.0;
            $statusCancelledWidth = $statusTotal > 0 ? $statusCancelled / $statusTotal * 100 : 0.0;
        ?>

        <!-- Offense status distribution: how the period's offenses stand,
             shown beneath the KPI cards. -->
        <?php if ($statusTotal > 0): ?>
        <div class="status-mix-card" role="group" aria-label="Offense status distribution">
            <div class="status-mix-head">
                <span class="status-mix-title"><i class="bi bi-pie-chart-fill me-1"></i>Offense Status Distribution</span>
                <span class="status-mix-total"><?= number_format($statusTotal) ?> <?= $statusTotal === 1 ? 'offense' : 'offenses' ?> total</span>
            </div>
            <div class="status-mix-bar" role="img"
                 aria-label="Settled <?= number_format($statusPaidPct, 2) ?> percent, pending <?= number_format($statusPendingPct, 2) ?> percent, cancelled <?= number_format($statusCancelledPct, 2) ?> percent">
                <?php if ($statusPaid > 0): ?>
                    <span class="status-mix-seg seg-paid" style="width: <?= number_format($statusPaidWidth, 4, '.', '') ?>%;"></span>
                <?php endif; ?>
                <?php if ($statusPending > 0): ?>
                    <span class="status-mix-seg seg-pending" style="width: <?= number_format($statusPendingWidth, 4, '.', '') ?>%;"></span>
                <?php endif; ?>
                <?php if ($statusCancelled > 0): ?>
                    <span class="status-mix-seg seg-cancelled" style="width: <?= number_format($statusCancelledWidth, 4, '.', '') ?>%;"></span>
                <?php endif; ?>
            </div>
            <div class="status-mix-legend">
                <?php if ($statusPaid > 0): ?>
                    <span class="status-mix-item"><span class="status-mix-dot dot-paid"></span>Paid <strong><?= number_format($statusPaid) ?></strong> <span class="status-mix-pct">(<?= number_format($statusPaidPct, 2) ?>%)</span></span>
                <?php endif; ?>
                <?php if ($statusPending > 0): ?>
                    <span class="status-mix-item"><span class="status-mix-dot dot-pending"></span>Pending <strong><?= number_format($statusPending) ?></strong> <span class="status-mix-pct">(<?= number_format($statusPendingPct, 2) ?>%)</span></span>
                <?php endif; ?>
                <?php if ($statusCancelled > 0): ?>
                    <span class="status-mix-item"><span class="status-mix-dot dot-cancelled"></span>Cancelled <strong><?= number_format($statusCancelled) ?></strong> <span class="status-mix-pct">(<?= number_format($statusCancelledPct, 2) ?>%)</span></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Section 1: Officer Activity -->
        <div class="report-section mb-4" id="secSummary">
            <h5 class="report-sec-title report-sec-title--split">
                <span><i class="bi bi-shield-check me-2"></i>I. Enforcer / Officer Activity Summary</span>
                <button type="button" class="report-sec-print no-print"
                        onclick="printReportPart('summary', 'A4 portrait')"
                        title="Prints Parts I and II on A4">
                    <i class="bi bi-printer-fill"></i><span>Print this part</span>
                </button>
            </h5>
            <div class="table-responsive report-table-scroll">
            <table class="table table-bordered table-sm report-table">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Apprehending Officer</th>
                        <th class="text-center">Citations Issued</th>
                        <th class="text-end">Total Fine Amount (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($officer_breakdown)): ?>
                        <tr><td colspan="4" class="text-center text-muted">No officer citations found.</td></tr>
                    <?php else: ?>
                        <?php $i=1; foreach($officer_breakdown as $ob): ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td class="fw-bold"><?= esc($ob['officer_name']) ?></td>
                            <td class="text-center fw-bold"><?= number_format($ob['count']) ?></td>
                            <td class="text-end font-monospace">₱<?= number_format($ob['amount'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
            <p class="text-muted small mb-0 mt-2"><i class="bi bi-info-circle me-1"></i>Cancelled (void) citations are not counted in this summary.</p>
        </div>

        <!-- Section 2: Nature of Violations Breakdown. It travels with the
             enforcer summary on paper, so it carries no print action of its
             own. -->
        <div class="report-section mb-4" id="secCategories">
            <h5 class="report-sec-title"><i class="bi bi-bar-chart-steps me-2"></i>II. Offense Category Breakdown</h5>
            <div class="report-scroll-hint"><i class="bi bi-arrows-move"></i> Swipe the table sideways to see every column</div>
            <div class="table-responsive report-table-scroll">
            <table class="table table-bordered table-sm report-table report-table-scrollable">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Violation / Offense Nature</th>
                        <th class="text-center">Total Incidents</th>
                        <th class="text-end">Fine Amount (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($nature_breakdown)): ?>
                        <tr><td colspan="4" class="text-center text-muted">No offenses recorded during this period.</td></tr>
                    <?php else: ?>
                        <?php
                            // Column totals so the section states its own whole:
                            // every offense's incidents and every fine added up.
                            // Summed from the rows above, so the total always
                            // equals what the reader can count themselves.
                            $natureIncidentTotal = 0;
                            $natureAmountTotal   = 0.0;

                            foreach ($nature_breakdown as $nb) {
                                $natureIncidentTotal += (int) ($nb['count'] ?? 0);
                                $natureAmountTotal   += (float) ($nb['amount'] ?? 0);
                            }
                        ?>
                        <?php $i=1; foreach($nature_breakdown as $nb): ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td class="fw-bold"><?= esc($nb['violation_type']) ?></td>
                            <td class="text-center fw-bold"><?= number_format($nb['count']) ?></td>
                            <td class="text-end font-monospace">₱<?= number_format($nb['amount'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="report-total-row">
                            <td></td>
                            <td>Total</td>
                            <td class="text-center"><?= number_format($natureIncidentTotal) ?></td>
                            <td class="text-end font-monospace">₱<?= number_format($natureAmountTotal, 2) ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>

        <!-- Section 3: Detailed Ticket Records -->
        <div class="report-section mb-4" id="secCitations">
            <h5 class="report-sec-title report-sec-title--split">
                <span><i class="bi bi-list-check me-2"></i>III. Detailed Citation Records</span>
                <button type="button" class="report-sec-print no-print"
                        onclick="printReportPart('citations')"
                        title="Print only Part III on the report's own paper size">
                    <i class="bi bi-printer-fill"></i><span>Print this part</span>
                </button>
            </h5>
            <div class="report-scroll-hint report-scroll-hint--citations"><i class="bi bi-arrows-move"></i> Swipe the table sideways to see every column</div>
            <div class="table-responsive report-table-scroll report-citations-desktop">
            <table class="table table-bordered table-sm report-table report-table-wide report-citations-table">
                <!-- Widths are set for the printed sheet: the ticket number needs
                     enough room to stay on one line, so it takes a little from
                     the offense and officer columns. -->
                <colgroup>
                    <col style="width: 13%;">
                    <col style="width: 17%;">
                    <col style="width: 10%;">
                    <col style="width: 19%;">
                    <col style="width: 9%;">
                    <col style="width: 11%;">
                    <col style="width: 11%;">
                    <col style="width: 10%;">
                </colgroup>
                <thead>
                    <tr>
                        <th title="Traffic Citation Ticket (TCT) number">TCT No.</th>
                        <th>Driver Information</th>
                        <th class="text-center" title="Official Receipt (OR) number issued when the citation is paid">
                            <span class="citation-th-main">OR</span>
                            <span class="citation-th-sub">Official Receipt</span>
                        </th>
                        <th>Violation Type</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Status</th>
                        <th>Date &amp; Time</th>
                        <th>Officer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($citation_groups)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No detailed citation records found for the selected criteria.</td></tr>
                    <?php else: ?>
                        <?php /* One organized block per violator: the driver cell spans
                                 every offense the person is listed for, and each ticket
                                 spans its own offenses, so a violator with multiple
                                 violations reads as one record. Every offense keeps its
                                 own fine, its own status and the date it was paid — no
                                 merged "Paid ×N" counts. */ ?>
                        <?php foreach($citation_groups as $groupIndex => $group): ?>
                            <?php
                                $groupSpans    = max(1, (int) $group['violation_count']);
                                $isFirstRow    = true;
                                $groupRowClass = 'citation-group-' . ($groupIndex % 2 === 0 ? 'even' : 'odd');
                            ?>
                            <?php foreach($group['tickets'] as $ticket): ?>
                                <?php
                                    $ticketSpans   = max(1, count($ticket['violations']));
                                    $isTicketStart = true;
                                ?>
                                <?php foreach($ticket['violations'] as $offense): ?>
                                    <?php
                                        $statusClass = $offense['status'] === 'Paid'
                                            ? 'bg-success'
                                            : ($offense['status'] === 'Pending' ? 'bg-warning text-dark' : 'bg-secondary');
                                    ?>
                                    <tr class="<?= $groupRowClass ?><?= $isFirstRow ? ' citation-group-start' : '' ?>">
                                        <?php if ($isTicketStart): ?>
                                            <td class="citation-tct font-monospace fw-bold text-danger" rowspan="<?= $ticketSpans ?>"><?= esc($ticket['ticket_id'] !== '' ? $ticket['ticket_id'] : '—') ?></td>
                                        <?php endif; ?>
                                        <?php if ($isFirstRow): ?>
                                            <td class="citation-driver" rowspan="<?= $groupSpans ?>">
                                                <div class="citation-driver-name"><?= esc($group['driver_name']) ?></div>
                                                <?php if($group['license_number'] !== ''): ?>
                                                    <div class="citation-driver-meta">Driver's License (DL): <?= esc($group['license_number']) ?></div>
                                                <?php endif; ?>
                                                <?php if($group['license_plate'] !== ''): ?>
                                                    <div class="citation-driver-plate">Plate: <?= esc($group['license_plate']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                        <?php if ($isTicketStart): ?>
                                            <?php /* The OR column carries the receipt number only;
                                                     payment state reads on the offense status
                                                     badges and on the paid date in Date & Time. */ ?>
                                            <td class="citation-payment text-center font-monospace" rowspan="<?= $ticketSpans ?>">
                                                <?php if ($ticket['receipt_number'] !== ''): ?>
                                                    <span class="or-receipt-number"><?= esc($ticket['receipt_number']) ?></span>
                                                <?php else: ?>
                                                    <span class="or-receipt-missing"><?= !empty($ticket['status_counts']['Paid']) ? 'Not recorded' : '—' ?></span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                        <td class="citation-offenses">
                                            <div class="citation-offense-item"><?= esc($offense['name']) ?></div>
                                        </td>
                                        <td class="citation-amount text-end font-monospace fw-bold">₱<?= number_format((float) $offense['amount'], 2) ?></td>
                                        <td class="text-center citation-status-cell">
                                            <span class="badge <?= $statusClass ?> citation-status-badge"><?= esc($offense['status']) ?></span>
                                        </td>
                                        <?php if ($isTicketStart): ?>
                                            <td class="citation-datetime" rowspan="<?= $ticketSpans ?>">
                                                <?php if (!empty($ticket['violation_date'])): ?>
                                                    <span class="citation-date"><?= date('M d, Y', strtotime($ticket['violation_date'])) ?></span>
                                                    <span class="citation-time"><?= date('h:i A', strtotime($ticket['violation_date'])) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($ticket['paid_date'])): ?>
                                                    <span class="citation-paid-date">
                                                        <i class="bi bi-check-circle-fill" aria-hidden="true"></i>Paid
                                                        <span class="citation-paid-day"><?= date('M d, Y', strtotime($ticket['paid_date'])) ?></span>
                                                        <span class="citation-paid-time"><?= date('h:i A', strtotime($ticket['paid_date'])) ?></span>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="citation-officer small" rowspan="<?= $ticketSpans ?>"><?= esc($ticket['officer_name'] !== '' ? $ticket['officer_name'] : 'System') ?></td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php $isFirstRow = false; $isTicketStart = false; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>

            <!-- Phone view: the same records as cards — the OR and DL meanings
                 are spelled out and nothing needs sideways swiping. Hidden
                 from tablet up and in print (the table is used there). -->
            <div class="report-citations-mobile">
                <?php if (empty($citation_groups)): ?>
                    <div class="rcm-empty">No detailed citation records found for the selected criteria.</div>
                <?php else: ?>
                    <?php foreach($citation_groups as $group): ?>
                        <div class="rcm-card">
                            <div class="rcm-head">
                                <div class="rcm-name"><?= esc($group['driver_name']) ?></div>
                                <?php if($group['license_number'] !== '' || $group['license_plate'] !== ''): ?>
                                    <div class="rcm-meta">
                                        <?php if($group['license_number'] !== ''): ?>
                                            <span>Driver's License (DL): <span class="rcm-mono"><?= esc($group['license_number']) ?></span></span>
                                        <?php endif; ?>
                                        <?php if($group['license_plate'] !== ''): ?>
                                            <span>Plate: <span class="rcm-mono"><?= esc($group['license_plate']) ?></span></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php foreach($group['tickets'] as $ticket): ?>
                                <div class="rcm-ticket">
                                    <div class="rcm-ticket-head">
                                        <span class="rcm-tct"><?= esc($ticket['ticket_id'] !== '' ? $ticket['ticket_id'] : '—') ?></span>
                                        <span class="rcm-or<?= $ticket['receipt_number'] === '' ? ' is-missing' : '' ?>">
                                            OR (Official Receipt): <?= $ticket['receipt_number'] !== '' ? esc($ticket['receipt_number']) : (!empty($ticket['status_counts']['Paid']) ? 'Not recorded' : '—') ?>
                                        </span>
                                    </div>
                                    <?php foreach($ticket['violations'] as $offense): ?>
                                        <?php $statusClass = $offense['status'] === 'Paid'
                                            ? 'bg-success'
                                            : ($offense['status'] === 'Pending' ? 'bg-warning text-dark' : 'bg-secondary'); ?>
                                        <div class="rcm-offense">
                                            <span class="rcm-offense-name"><?= esc($offense['name']) ?></span>
                                            <span class="rcm-offense-right">
                                                <span class="rcm-amount">₱<?= number_format((float) $offense['amount'], 2) ?></span>
                                                <span class="badge <?= $statusClass ?> rcm-badge"><?= esc($offense['status']) ?></span>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="rcm-foot">
                                        <?php if (!empty($ticket['violation_date'])): ?>
                                            <div><span class="rcm-foot-label">Date &amp; Time:</span> <?= date('M d, Y', strtotime($ticket['violation_date'])) ?> · <?= date('h:i A', strtotime($ticket['violation_date'])) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($ticket['paid_date'])): ?>
                                            <div class="rcm-paid"><i class="bi bi-check-circle-fill" aria-hidden="true"></i>Paid <?= date('M d, Y', strtotime($ticket['paid_date'])) ?> · <?= date('h:i A', strtotime($ticket['paid_date'])) ?></div>
                                        <?php endif; ?>
                                        <div><span class="rcm-foot-label">Officer:</span> <?= esc($ticket['officer_name'] !== '' ? $ticket['officer_name'] : 'System') ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
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

<!-- E-Signature Modal (Touch & Mouse Pad + Image Upload) -->
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
                    <!-- Draw Panel -->
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
                    <!-- Upload Panel -->
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
                <button type="button" class="btn btn-primary fw-bold px-4" id="btnApplyReportSig">
                    <i class="bi bi-check-circle-fill me-1"></i> Apply Signature
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Styles -->
<style>
    /* Toolbar Header */
    .rpt-toolbar-header {
        padding: 18px 24px 14px;
        border-bottom: 1px solid #edf1ff;
    }
    .rpt-icon-wrap {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35) !important;
        flex-shrink: 0;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .rpt-icon-wrap:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.45) !important;
    }
    .rpt-icon-wrap svg {
        width: 24px;
        height: 24px;
        color: #ffffff;
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
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 2px solid #0f172a;
        padding-bottom: 16px;
        margin-bottom: 16px;
        min-height: 100px;
    }
    .report-header-seal { position: absolute; left: 0; top: 0; }
    .report-seal-img { width: 80px; height: 80px; object-fit: contain; }
    .report-header-title { text-align: center; line-height: 1.3; }
    .rep-sub  { font-size: 0.92rem; }
    .rep-office{ font-size: 1.1rem; font-weight: bold; }
    .rep-city  { font-size: 0.9rem; }
    .rep-dept  { font-size: 0.82rem; font-weight: bold; margin-top: 4px; color: #334155; }
    .rep-main-title {
        font-family: Arial, sans-serif;
        font-size: 1.25rem;
        font-weight: 900;
        letter-spacing: 0.04em;
        margin: 8px 0 4px;
        color: #0f172a;
    }
    .rep-period { font-size: 0.88rem; font-style: italic; color: #475569; }

    /* Meta Bar */
    .report-meta-bar {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        padding: 8px 16px;
        font-size: 0.86rem;
        font-family: Arial, sans-serif;
        margin-bottom: 22px;
        border-radius: 4px;
    }

    /* KPI Grid */
    .report-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 26px;
    }
    .report-kpi-box {
        border: 1.5px solid #cbd5e1;
        padding: 14px 16px;
        border-radius: 6px;
        background: #fafafa;
        text-align: center;
        font-family: Arial, sans-serif;
        transition: transform 0.25s, box-shadow 0.25s;
    }
    .report-kpi-box:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
    .report-kpi-box.success { border-color: #10b981; background: #f0fdf4; }
    .report-kpi-box.warning { border-color: #eab308; background: #fef9c3; }
    .kpi-label { font-size: 0.76rem; text-transform: uppercase; font-weight: bold; color: #64748b; letter-spacing: 0.04em; }
    .kpi-val   { font-size: 1.3rem; font-weight: 900; margin: 5px 0; color: #0f172a; }
    .kpi-sub   { font-size: 0.73rem; color: #64748b; }

    /* Section Titles */
    .report-sec-title {
        font-family: Arial, sans-serif;
        font-size: 0.98rem;
        font-weight: bold;
        border-bottom: 1.5px solid #0f172a;
        padding-bottom: 6px;
        margin-bottom: 12px;
        color: #0f172a;
    }
    /* The heading keeps its rule across the page while a print action sits at
       its right edge. */
    .report-sec-title--split {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .report-sec-title--split > span {
        display: inline-flex;
        align-items: center;
        min-width: 0;
    }
    /* Print one part at a time: a full year never has to squeeze into a single
       job, so each section can leave the printer on its own sheet size. */
    .report-sec-print {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
        padding: 4px 11px;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #fff;
        color: #334155;
        font-size: 0.72rem;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s, color 0.2s, box-shadow 0.2s;
    }
    .report-sec-print:hover {
        background: #eff6ff;
        border-color: #93c5fd;
        color: #2563eb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12);
    }
    .report-sec-print i { font-size: 0.8rem; }

    /* Tables */
    .report-table { 
        font-size: 0.85rem; 
        font-family: Arial, sans-serif; 
        border: 1.5px solid #000 !important; 
    }
    .report-table thead th {
        background: #e2e8f0 !important;
        color: #0f172a !important;
        font-weight: 800;
        border: 1.5px solid #000 !important;
        font-size: 0.82rem;
        text-transform: uppercase;
        padding: 10px 9px !important;
    }
    .report-table td { 
        vertical-align: middle; 
        padding: 8px 9px; 
        border: 1.5px solid #000 !important;
        color: #000;
    }
    .report-table tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }
    /* A section's own total, stated once under its column of figures. */
    .report-total-row > * {
        font-weight: 800;
        background: #f1f5f9 !important;
        border-top: 2px solid #475569 !important;
    }
    /* Offense status distribution (transferred from the Violations Summary):
       how the period's offenses stand, in one bar. */
    .status-mix-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        padding: 12px 16px 14px;
        margin-bottom: 14px;
        font-family: system-ui, -apple-system, sans-serif;
    }
    .status-mix-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }
    .status-mix-title {
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #334155;
    }
    .status-mix-total { font-size: 0.75rem; color: #64748b; font-weight: 600; }
    .status-mix-bar {
        display: flex;
        height: 12px;
        border-radius: 999px;
        overflow: hidden;
        background: #e2e8f0;
    }
    .status-mix-seg { height: 100%; }
    .seg-paid { background: #10b981; }
    .seg-pending { background: #eab308; }
    .seg-cancelled { background: #94a3b8; }
    .status-mix-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 18px;
        margin-top: 9px;
        font-size: 0.8rem;
        color: #334155;
    }
    .status-mix-item { display: inline-flex; align-items: center; gap: 6px; font-weight: 600; }
    .status-mix-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
    .dot-paid { background: #10b981; }
    .dot-pending { background: #eab308; }
    .dot-cancelled { background: #94a3b8; }
    .status-mix-pct { color: #64748b; font-weight: 600; }
    .report-citations-table {
        width: 100%;
        table-layout: fixed;
        border-color: #94a3b8 !important;
    }
    .report-citations-table th,
    .report-citations-table td {
        border-color: #94a3b8 !important;
    }
    /* Headers stay centered; body cells sit at the top of their row so a
       violator's name and a ticket's OR read from the first line of their
       merged block instead of floating in the middle of it. */
    .report-citations-table th {
        vertical-align: middle;
    }
    .report-citations-table td {
        vertical-align: top;
    }
    /* Headers may wrap at spaces but never mid-word ("OFFIC ER"). */
    .report-citations-table thead th {
        line-height: 1.25;
        letter-spacing: 0.01em;
        text-align: center;
        overflow-wrap: normal;
        word-break: keep-all;
        hyphens: none;
    }
    /* Body text breaks inside long words only when it has to. */
    .report-citations-table td {
        overflow-wrap: break-word;
        word-break: normal;
    }
    .citation-tct {
        white-space: nowrap;
        font-size: 0.8rem;
        text-align: center;
    }
    .citation-driver {
        line-height: 1.35;
    }
    .citation-amount {
        white-space: nowrap;
        font-size: 0.8rem;
    }
    .citation-date,
    .citation-time {
        display: block;
        white-space: nowrap;
        font-size: 0.75rem;
        line-height: 1.3;
    }
    .or-receipt-number {
        color: #b42318 !important;
        font-weight: 700;
        white-space: nowrap;
    }
    .or-receipt-missing {
        color: #64748b;
        font-weight: 400;
    }
    .citation-offenses {
        line-height: 1.35;
    }
    .citation-offense-item {
        padding: 0.15rem 0;
    }
    .citation-offense-item:last-child {
        padding-bottom: 0;
    }
    .citation-status-cell {
        white-space: nowrap;
    }
    .citation-status-badge {
        display: block;
        padding: 0.22rem 0.35rem;
        font-size: 0.68rem;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
    }
    /* The citation list is organized one block per violator, so alternating
       group tints replace the row zebra, which would cut through the merged
       driver and ticket cells. */
    .report-citations-table tbody tr:nth-child(even) {
        background-color: transparent;
    }
    .report-citations-table tbody tr.citation-group-odd > td {
        background-color: #f8fafc;
    }
    .report-citations-table tbody tr.citation-group-start > td {
        border-top: 2px solid #94a3b8 !important;
    }
    .citation-driver-name {
        font-weight: 700;
        line-height: 1.3;
    }
    .citation-driver-meta,
    .citation-driver-plate {
        font-size: 0.72rem;
        color: #64748b;
        line-height: 1.35;
    }
    .citation-driver-plate {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        letter-spacing: 0.02em;
    }
    /* Payment date, stacked under the violation date and time so it never
       clips at the column edge. */
    .citation-paid-date {
        display: block;
        margin-top: 4px;
        padding-top: 3px;
        border-top: 1px dashed #a7f3d0;
        color: #047857;
        font-weight: 700;
        font-size: 0.72rem;
        line-height: 1.3;
    }
    .citation-paid-date i {
        font-size: 0.64rem;
        margin-right: 3px;
    }
    .citation-paid-day,
    .citation-paid-time {
        display: block;
        white-space: nowrap;
        font-weight: 600;
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
        border-radius: 6px;
        scrollbar-width: thin;
    }
    /* Only shown on phones, where the widest table scrolls sideways. */
    .report-scroll-hint {
        display: none;
        font-family: Arial, sans-serif;
        font-size: 0.7rem;
        color: #64748b;
        margin: -4px 0 6px;
    }

    /* The OR header states its meaning: the abbreviation stays on top so the
       column is easy to scan, the full name sits right under it. */
    .citation-th-main {
        display: block;
        font-weight: 800;
    }
    .citation-th-sub {
        display: block;
        margin-top: 1px;
        font-size: 0.6rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        color: #64748b;
    }

    /* ── Phone view of the detailed citation records ──
       The eight-column table keeps its sideways scroll on bigger screens; on
       phones the same records read as one card per violator, with the OR and
       DL meanings spelled out. Formal-document look: thin slate borders,
       small type, monospace numbers. */
    .report-citations-mobile { display: none; }
    .rcm-card {
        border: 1px solid #94a3b8;
        border-radius: 8px;
        background: #fff;
        margin-bottom: 12px;
        overflow: hidden;
    }
    .rcm-head {
        padding: 10px 12px;
        background: #f8fafc;
        border-bottom: 1px solid #cbd5e1;
    }
    .rcm-name {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.9rem;
        line-height: 1.3;
    }
    .rcm-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1px 14px;
        margin-top: 2px;
        font-size: 0.72rem;
        color: #64748b;
    }
    .rcm-mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        letter-spacing: 0.02em;
    }
    .rcm-ticket {
        padding: 10px 12px;
        border-top: 1px dashed #cbd5e1;
    }
    .rcm-ticket:first-of-type { border-top: 0; }
    .rcm-ticket-head {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 2px 14px;
        margin-bottom: 4px;
    }
    .rcm-tct {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-weight: 700;
        color: #b42318;
        font-size: 0.8rem;
        white-space: nowrap;
    }
    .rcm-or {
        font-size: 0.72rem;
        font-weight: 700;
        color: #b42318;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        overflow-wrap: anywhere;
    }
    .rcm-or.is-missing {
        color: #64748b;
        font-weight: 400;
    }
    .rcm-offense {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        padding: 6px 0;
    }
    .rcm-offense + .rcm-offense { border-top: 1px solid #e2e8f0; }
    .rcm-offense-name {
        font-size: 0.8rem;
        color: #334155;
        line-height: 1.35;
    }
    .rcm-offense-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .rcm-amount {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-weight: 700;
        font-size: 0.78rem;
        white-space: nowrap;
    }
    .rcm-badge {
        font-size: 0.64rem;
        padding: 0.2rem 0.35rem;
    }
    .rcm-foot {
        display: grid;
        gap: 2px;
        margin-top: 6px;
        padding-top: 6px;
        border-top: 1px dashed #cbd5e1;
        font-size: 0.72rem;
        color: #475569;
        line-height: 1.35;
    }
    .rcm-foot-label { color: #64748b; }
    .rcm-paid {
        color: #047857;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .rcm-paid i { font-size: 0.64rem; }
    .rcm-empty {
        border: 1px dashed #94a3b8;
        border-radius: 8px;
        padding: 22px 14px;
        text-align: center;
        color: #64748b;
        font-size: 0.8rem;
    }

    /* Sign-off */
    .sig-line { width: 220px; border-bottom: 1.5px solid #000; margin-top: 50px; }

    /* ── SCREEN ONLY: fit the sheet to tablets and phones ──
       The printed document keeps its own A4 rules further down; these only
       shape the on-screen preview so the page reads without sideways drag. */
    /* Below a full desktop the sidebar eats horizontal room, so the sheet and
       its tables tighten up before the eight-column list needs a sideways push. */
    @media screen and (max-width: 1279px) {
        .report-document-card { padding: 30px 20px; }
        .report-table { font-size: 0.76rem; }
        .report-table td { padding: 7px 6px; }
        .report-table thead th { padding: 8px 6px !important; font-size: 0.72rem; }
        .report-scroll-hint { display: block; }
    }

    @media screen and (max-width: 991px) {
        .report-document-card { padding: 26px 16px; }
        .report-kpi-grid { grid-template-columns: repeat(2, 1fr); }
        .report-table { font-size: 0.72rem; }
        .report-table td { padding: 6px 5px; }
        .report-table thead th { padding: 6px 5px !important; font-size: 0.68rem; }
    }

    @media screen and (max-width: 767px) {
        .rpt-toolbar-header { padding: 14px 16px 12px; }
        .rpt-toolbar-body { padding-left: 16px !important; padding-right: 16px !important; }

        .report-document-card {
            padding: 20px 14px;
            border-width: 1.5px;
            border-radius: 10px;
        }

        /* Seal above the letterhead instead of behind it. */
        .report-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 8px;
            min-height: 0;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }
        .report-header-seal { position: static; }
        .report-seal-img { width: 56px; height: 56px; }
        .rep-sub    { font-size: 0.78rem; }
        .rep-office { font-size: 0.92rem; }
        .rep-city   { font-size: 0.76rem; }
        .rep-dept   { font-size: 0.7rem; line-height: 1.3; margin-top: 3px; }
        .rep-main-title { font-size: 1rem; line-height: 1.25; margin: 6px 0 3px; }
        .rep-period { font-size: 0.76rem; }

        .report-meta-bar {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 2px;
            padding: 8px 12px;
            font-size: 0.76rem;
        }

        .report-kpi-grid { gap: 8px; margin-bottom: 18px; }
        .report-kpi-box { padding: 10px 8px; }
        .kpi-label { font-size: 0.66rem; }
        .kpi-val   { font-size: 1.05rem; margin: 3px 0; }
        .kpi-sub   { font-size: 0.65rem; }

        .report-sec-title { font-size: 0.86rem; margin-bottom: 8px; }
        .report-table { font-size: 0.76rem; }
        /* The shared layout forces every .table to 640px on phones; the small
           summary tables fit the sheet, only the detailed list needs to scroll. */
        .report-table-scroll .report-table { min-width: 0; }
        .report-table-scroll .report-table-scrollable { min-width: 460px; }
        .report-table-scroll .report-table-wide { min-width: 640px; }
        .report-table thead th { font-size: 0.68rem; padding: 8px !important; }
        .report-table td { padding: 7px 8px; }
        .report-scroll-hint { display: block; }

        .report-sign-block { margin-top: 1.5rem !important; padding-top: 0 !important; }

        /* Each signature block takes its own line rather than being squeezed
           into half the width of a phone. */
        .report-sign-block .row { flex-wrap: wrap; gap: 18px; }
        .report-sign-block .col-6 { flex: 0 0 100%; max-width: 100%; }
        .report-sign-block .sig-container { width: 100% !important; max-width: 240px; }
        .sig-line { width: 200px; margin-top: 40px; }
    }

    /* Phones: the detailed citation list becomes cards — no sideways swipe,
       and the OR / DL meanings are stated on every card. */
    @media screen and (max-width: 767px) {
        .report-citations-mobile { display: block; }
        .report-citations-desktop { display: none; }
        .report-scroll-hint--citations { display: none; }
    }

    @media screen and (max-width: 359px) {
        .report-kpi-grid { grid-template-columns: 1fr; }
    }

    /* The reveal and hover lifts are decoration, never information, so they
       stay off for readers who ask for reduced motion. */
    @media (prefers-reduced-motion: reduce) {
        .period-box,
        .report-kpi-box,
        .rpt-icon-wrap,
        .report-document-card { animation: none !important; transition: none !important; }
    }

    /* Shown only on paper, when a period has not been chosen yet. */
    .print-only-note { display: none; }

    /* Office print setup note: screen guidance only, never printed. */
    .rpt-print-hint {
        font-size: 0.72rem;
        line-height: 1.45;
        color: var(--text-subtle, #64748b);
    }
    .rpt-print-hint strong { color: #334155; font-weight: 700; }

    /* Print Styling — the paper follows the period. A week or a month fits
       A4; the full-year report carries a year of citations, so it prints on
       long bond (8.5in × 13in) where the extra height keeps more rows to a
       sheet without shrinking the type to a squint. */
    <?php if ($period_type === 'yearly'): ?>
    @page {
        size: 8.5in 13in;
        margin: 10mm 10mm 12mm;
    }
    <?php else: ?>
    @page {
        size: A4 portrait;
        margin: 9mm 9mm 11mm;
    }
    <?php endif; ?>

    @media print {
        /* Hide all UI elements */
        .no-print,
        .sidebar, .sidebar-toggle, .mobile-topbar,
        .desktop-header, .breadcrumb, .btn, .btn-group,
        .alert, nav, header, footer {
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
            grid-template-columns: repeat(4, 1fr) !important;
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
        .kpi-label { font-size: 0.78rem !important; font-weight: 700 !important; }
        .kpi-val   { font-size: 1.32rem !important; font-weight: 700 !important; margin: 3px 0 !important; }
        .kpi-sub   { font-size: 0.76rem !important; }

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
        /* A4 portrait with 10mm side margins leaves ~678px of usable width;
           the tables are set here so every column fits on the paper instead of
           running off the right edge. */
        .report-table {
            border: 1.5px solid #000 !important;
            font-size: 0.84rem !important;
            margin-bottom: 12px !important;
            width: 100% !important;
        }
        .report-table thead th,
        .report-table th {
            background: #e2e8f0 !important;
            color: #000 !important;
            font-weight: 700 !important;
            font-size: 0.76rem !important;
            padding: 7px 5px !important;
            border: 1.5px solid #000 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .report-table td {
            padding: 6px 5px !important;
            font-size: 0.84rem !important;
            line-height: 1.38 !important;
            border: 1.5px solid #000 !important;
            color: #000 !important;
        }
        .report-table tbody tr:nth-child(even) {
            background-color: #f8fafc !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .report-total-row > * {
            background: #e2e8f0 !important;
            border-top: 2px solid #000 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        /* Offense status bar keeps its meaning on paper */
        .status-mix-card {
            border: 1.5px solid #000 !important;
            background: #fff !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .status-mix-bar { border: 1px solid #000 !important; }
        .status-mix-seg,
        .status-mix-dot {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .report-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .report-citations-table {
            width: 100% !important;
            table-layout: fixed !important;
            border-color: #64748b !important;
        }
        .report-citations-table thead {
            display: table-header-group;
        }
        .report-citations-table th,
        .report-citations-table td {
            overflow-wrap: break-word !important;
            word-break: normal !important;
            border-color: #64748b !important;
        }
        .report-citations-table thead th {
            text-align: center !important;
            overflow-wrap: normal !important;
            word-break: keep-all !important;
        }
        .citation-status-badge {
            padding: 1px 5px !important;
            font-size: 0.68rem !important;
            white-space: nowrap !important;
        }
        .citation-amount,
        .citation-tct,
        .citation-date,
        .citation-time {
            white-space: nowrap !important;
        }
        .report-citations-table .or-receipt-number {
            color: #b42318 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        /* Violator blocks keep their organization on paper: alternating group
           tints replace the row zebra, and the payment date stays readable. */
        .report-citations-table tbody tr:nth-child(even) {
            background-color: transparent !important;
        }
        .report-citations-table tbody tr.citation-group-odd > td {
            background-color: #f8fafc !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .report-citations-table tbody tr.citation-group-start > td {
            border-top: 2px solid #475569 !important;
        }
        .citation-paid-date {
            color: #047857 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        /* Paper always uses the full table; the phone cards never print. */
        .report-citations-desktop { display: block !important; }
        .report-citations-mobile { display: none !important; }

        /* Badges keep their screen identity on paper; the rules further down
           restate the pill, fill and letters. */

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

        /* ── One sheet, three periods ───────────────────────────────────
           The same paper carries a one-week report and a full-year one. Rather
           than shrink every sheet to the smallest case, the type steps with the
           period: a week prints large and roomy, a month a step tighter, and a
           full year tighter still so its far longer citation list stays inside
           the printable width and keeps a workable number of rows per page. */

        /* Column geometry is fixed so a long offense name or a wide amount
           wraps inside its column instead of pushing the table off the sheet. */
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
        /* A long report spills onto further sheets; the column headings travel
           with it instead of leaving the reader counting columns. */
        .report-table thead {
            display: table-header-group !important;
        }
        /* A section heading never ends a sheet on its own. */
        .report-sec-title {
            page-break-after: avoid !important;
            break-after: avoid-page !important;
        }
        /* Screen-only affordances mean nothing on paper. */
        .report-scroll-hint,
        .report-scroll-hint--citations,
        .report-citations-mobile,
        #reportSignatureModal,
        .modal-backdrop,
        select, textarea {
            display: none !important;
        }

        /* Week: one or two sheets, so it prints at the fullest readable size. */
        .print-period-weekly .report-table,
        .print-period-weekly .report-table td { font-size: 0.88rem !important; }
        .print-period-weekly .report-table td { padding: 8px 7px !important; line-height: 1.42 !important; }
        .print-period-weekly .report-table thead th { font-size: 0.80rem !important; padding: 8px 7px !important; }
        .print-period-weekly .kpi-val { font-size: 1.42rem !important; }

        /* Month: denser than a week, still comfortably legible. */
        .print-period-monthly .report-table,
        .print-period-monthly .report-table td { font-size: 0.84rem !important; }
        .print-period-monthly .report-table td { padding: 6px 5px !important; line-height: 1.38 !important; }
        .print-period-monthly .report-table thead th { font-size: 0.76rem !important; padding: 6px 5px !important; }

        /* Full year (the overall report): the longest document, so its scale is
           the tightest of the three — but never below a size that survives a
           laser print at arm's length. */
        .print-period-yearly .report-table,
        .print-period-yearly .report-table td { font-size: 0.78rem !important; }
        .print-period-yearly .report-table td { padding: 5px 4px !important; line-height: 1.34 !important; }
        .print-period-yearly .report-table thead th { font-size: 0.73rem !important; padding: 6px 4px !important; }
        .print-period-yearly .report-sec-title { font-size: 0.92rem !important; margin-bottom: 7px !important; }
        .print-period-yearly .report-kpi-grid { gap: 6px !important; margin-bottom: 10px !important; }
        .print-period-yearly .report-kpi-box { padding: 7px 8px !important; }
        .print-period-yearly .kpi-label { font-size: 0.72rem !important; }
        .print-period-yearly .kpi-val { font-size: 1.16rem !important; margin: 2px 0 !important; }
        .print-period-yearly .kpi-sub { font-size: 0.70rem !important; }
        .print-period-yearly .report-header { padding-bottom: 8px !important; margin-bottom: 8px !important; }
        .print-period-yearly .report-meta-bar { margin-bottom: 9px !important; padding: 4px 10px !important; }
        .print-period-yearly .report-seal-img { width: 62px !important; height: 62px !important; }
        .print-period-yearly .status-mix-card { padding: 8px 12px 10px !important; margin-bottom: 9px !important; }

        /* ── Printing one part at a time ─────────────────────────────────
           The toolbar's Print button still delivers the complete report. These
           scopes keep only the chosen section on the sheet, while the letterhead,
           covered period and summary figures travel with it so each part is a
           complete official document on its own. */
        /* Parts I and II print together: the enforcer summary and the offense
           breakdown are one short sheet, so they share a single print action. */
        body.print-scope-summary .report-section:not(#secSummary):not(#secCategories),
        body.print-scope-citations .report-section:not(#secCitations) {
            display: none !important;
        }
        /* The screen-only print actions are hidden by the no-print rule above;
           stated again so a later rule can never put them on paper. */
        .report-sec-print {
            display: none !important;
        }

        /* ── Ink on paper ─────────────────────────────────────────────────
           The screen palette is built for a glowing display: mid-grey labels,
           hairline rules and white letters on saturated badges. On a laser
           printer that reads as a faint smudge. Paper now uses solid black
           type, darker rules and black letters on a pale status tint. */
        body {
            -webkit-font-smoothing: auto !important;
            -moz-osx-font-smoothing: auto !important;
        }
        /* ── Paper typography ───────────────────────────────────────────
           One clean face for the whole sheet, with no synthetic bold, no
           shadows and no condensed monospace at small sizes — those are what
           made thin strokes and narrow counters look soft on paper. */
        .report-document-card,
        .report-document-card * {
            font-family: Arial, Helvetica, sans-serif !important;
            text-shadow: none !important;
            font-synthesis: none !important;
        }
        .report-document-card .font-monospace,
        .report-document-card .citation-tct,
        .report-document-card .citation-amount,
        .report-document-card .citation-driver-plate {
            font-family: Arial, Helvetica, sans-serif !important;
            letter-spacing: 0 !important;
        }
        /* Only the faint greys are forced to ink. Deliberate accents — the
           collected revenue, the collection rate, the status colours — stay,
           because colour is what tells one aspect of the report from another. */
        .report-document-card .kpi-label,
        .report-document-card .kpi-sub,
        .report-document-card .rep-period,
        .report-document-card .status-mix-total,
        .report-document-card .status-mix-pct,
        .report-document-card .text-muted,
        .report-table td {
            color: #000 !important;
        }
        .report-table th,
        .report-table td,
        .report-citations-table th,
        .report-citations-table td {
            border-color: #000 !important;
        }

        /* Names, plates and the officer column carry real weight so they do
           not thin out at print resolution. */
        .citation-driver-name { font-weight: 700 !important; }
        .citation-driver-meta,
        .citation-driver-plate { font-weight: 600 !important; font-size: 0.72rem !important; }
        .citation-officer { font-size: inherit !important; font-weight: 600 !important; }
        /* The peso column is the narrowest, so it keeps a slightly smaller size
           to stay on one line without clipping the decimals. */
        .citation-amount { font-weight: 700 !important; font-size: 0.76rem !important; white-space: nowrap !important; }
        .citation-paid-date { font-weight: 700 !important; border-top: 1px dashed #000 !important; }
        /* The ticket number keeps its column, and stays on one line, in the
           same face as the rest of the sheet. */
        .citation-tct { font-size: 0.74rem !important; font-weight: 700 !important; white-space: nowrap !important; }
        .citation-driver-meta,
        .citation-driver-plate { font-size: 0.74rem !important; }
        .citation-paid-date { font-size: 0.74rem !important; }

        /* Status prints exactly as it reads on screen: same pill, same
           gradient, same white letters. Only the drop shadow is dropped,
           because a shadow prints as a grey smear rather than depth. */
        .badge {
            border: none !important;
            box-shadow: none !important;
            border-radius: 999px !important;
            padding: 0.28em 0.75em !important;
            font-size: 0.7rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.02em !important;
            text-transform: uppercase !important;
            line-height: 1.3 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .badge.bg-success {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%) !important;
            color: #ffffff !important;
        }
        .badge.bg-warning {
            background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%) !important;
            color: #ffffff !important;
        }
        .badge.bg-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
            color: #ffffff !important;
        }
        .citation-status-badge {
            display: inline-block !important;
            white-space: nowrap !important;
        }

        /* The receipt number is the figure the office looks for first. */
        .report-citations-table .or-receipt-number,
        .or-receipt-number { color: #7f1d1d !important; font-weight: 700 !important; }

        /* Printing before a period is chosen states why there is no report. */
        .print-only-note {
            display: block !important;
            font-family: Arial, sans-serif;
            color: #000 !important;
            padding: 18mm 0 0;
            text-align: center;
        }
        .print-only-note .pon-office { font-size: 0.8rem; font-weight: bold; letter-spacing: 0.02em; }
        .print-only-note .pon-title  { font-size: 1.1rem; font-weight: 900; margin: 8px 0 12px; }
        .print-only-note p           { font-size: 0.86rem; line-height: 1.5; margin: 0 auto; max-width: 130mm; }
    }
</style>

<script>
    /* Nothing runs on its own. Choosing a report type, a week, a month or a
       year only fills the form in; the report is built when Filter is pressed,
       so a half-made choice never replaces the document on screen. */

    /* The quick period buttons stand the choice up in the form instead of
       loading a report, leaving the reader on the Filter button. */
    function pickPeriod(val) {
        const periodSelect = document.getElementById('period_type');
        if (!periodSelect) return;

        periodSelect.value = val;
        togglePeriodInputs(val, true);

        if (val === 'custom') {
            const from = document.getElementById('start_date_custom');
            if (from) from.focus();
        }
    }

    /* ── Printing the report in parts ─────────────────────────────────
       One long job is what crowded the details of a full-year report. The
       summary (Parts I and II together) and the detailed records now print on
       their own, each on its own paper size, while the toolbar's Print button
       still delivers the complete document. */

    let printSheetStyle = null;

    function setPrintSheet(sheet) {
        clearPrintSheet();
        if (!sheet) return;

        printSheetStyle = document.createElement('style');
        printSheetStyle.id = 'printSheetOverride';
        printSheetStyle.textContent = '@page { size: ' + sheet + '; margin: 10mm; }';
        // Appended to the body so it lands after this view's own @page rule;
        // equal-specificity page rules are decided by document order.
        document.body.appendChild(printSheetStyle);
    }

    function clearPrintSheet() {
        if (printSheetStyle && printSheetStyle.parentNode) {
            printSheetStyle.parentNode.removeChild(printSheetStyle);
        }
        printSheetStyle = null;
    }

    function printReportPart(scope, sheet) {
        if (!scope) {
            window.print();
            return;
        }

        const scopeClass = 'print-scope-' + scope;
        document.body.classList.add(scopeClass);
        setPrintSheet(sheet || '');

        const restore = function () {
            document.body.classList.remove(scopeClass);
            clearPrintSheet();
        };

        window.addEventListener('afterprint', restore, { once: true });
        window.print();

        // Chrome and Edge block until the dialog closes; this also covers a
        // browser that returns at once, where afterprint may never fire.
        setTimeout(restore, 800);
    }

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
            // Switching to a week starts from today. The box used to keep the
            // month's first day, which produced a Dec 29 – Jan 4 straddle that
            // was neither the chosen month nor the current week.
            if (fromSwitcher) {
                const weekInput = document.getElementById('start_date_week');
                if (weekInput) {
                    weekInput.value = `${curY}-${curM}-${String(now.getDate()).padStart(2, '0')}`;
                }
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

        // Deliberately no submit here: the reader picks the period, then asks
        // for it with Filter.
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
    let currentSigRole = 'prepared'; // 'prepared' or 'approved'
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

        // File upload listener
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

        // Apply signature button handler
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
        if (titleEl) titleEl.innerHTML = `<i class="bi bi-pen-fill text-primary me-2"></i>Sign: ${modalTitle}`;

        // Reset pad & file input
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

        // Persist in localStorage
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

<?= $this->endSection() ?>
