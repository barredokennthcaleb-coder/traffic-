<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($period_name) ?> Violator Report<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4 vpr-page">

    <!-- ═══ Toolbar (screen only) ═══ -->
    <div class="vpr-toolbar no-print">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= esc($back_url) ?>" class="vpr-back-btn">
                <i class="bi bi-arrow-left"></i><span>Violator Reports</span>
            </a>
            <span class="vpr-crumb">/</span>
            <span class="vpr-crumb-current"><?= esc($period_name) ?> Report &middot; <?= esc($range_label) ?></span>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- The same Report Type chooser the directory carries, so a week
                 can become a month — or a full year — without going back. -->
            <form method="get" action="<?= base_url('reports/violators/period') ?>" class="d-flex align-items-center gap-2 flex-wrap" id="vprPeriodForm">
                <?php if ($search !== ''): ?>
                    <input type="hidden" name="search" value="<?= esc($search) ?>">
                <?php endif; ?>

                <select name="period_type" id="period_type" class="form-select form-select-sm vpr-select" required
                        aria-label="Report type" onchange="vprTogglePeriod(this.value)">
                    <option value="daily"   <?= $period_type === 'daily'   ? 'selected' : '' ?>>Daily</option>
                    <option value="weekly"  <?= $period_type === 'weekly'  ? 'selected' : '' ?>>Weekly</option>
                    <option value="monthly" <?= $period_type === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                    <option value="yearly">Full Year</option>
                    <option value="custom"  <?= $period_type === 'custom'  ? 'selected' : '' ?>>Custom Range</option>
                </select>

                <span class="vpr-period-box" data-period="daily" style="<?= $period_type === 'daily' ? '' : 'display:none;' ?>">
                    <input type="date" name="report_date" class="form-control form-control-sm vpr-date"
                           value="<?= $period_type === 'daily' ? esc($start_date) : date('Y-m-d') ?>"
                           aria-label="Report date" title="Report date"
                           <?= $period_type === 'daily' ? '' : 'disabled' ?>>
                </span>

                <span class="vpr-period-box" data-period="weekly" style="<?= $period_type === 'weekly' ? '' : 'display:none;' ?>">
                    <input type="date" name="start_date" class="form-control form-control-sm vpr-date"
                           value="<?= $period_type === 'weekly' ? esc($start_date) : date('Y-m-d') ?>"
                           aria-label="Week starting date" title="Week starting date (the week runs Monday to Sunday)"
                           <?= $period_type === 'weekly' ? '' : 'disabled' ?>>
                </span>

                <span class="vpr-period-box" data-period="monthly" style="<?= $period_type === 'monthly' ? '' : 'display:none;' ?>">
                    <select name="month" class="form-select form-select-sm vpr-select" aria-label="Month" <?= $period_type === 'monthly' ? '' : 'disabled' ?>>
                        <?php foreach ($month_names as $mNo => $mName): ?>
                            <option value="<?= (int) $mNo ?>" <?= (int) $mNo === (int) $month ? 'selected' : '' ?>><?= esc($mName) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="year" class="form-select form-select-sm vpr-select" aria-label="Year" <?= $period_type === 'monthly' ? '' : 'disabled' ?>>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= (int) $y ?>" <?= (int) $y === (int) $year ? 'selected' : '' ?>><?= (int) $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </span>

                <span class="vpr-period-box" data-period="yearly" style="display:none;">
                    <select name="year" class="form-select form-select-sm vpr-select" aria-label="Year" disabled>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= (int) $y ?>" <?= (int) $y === (int) $year ? 'selected' : '' ?>><?= (int) $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </span>

                <span class="vpr-period-box" data-period="custom" style="<?= $period_type === 'custom' ? '' : 'display:none;' ?>">
                    <input type="date" name="start_date" class="form-control form-control-sm vpr-date"
                           value="<?= $period_type === 'custom' ? esc($start_date) : '' ?>"
                           aria-label="Range start" <?= $period_type === 'custom' ? '' : 'disabled' ?>>
                    <span class="vpr-dash">&ndash;</span>
                    <input type="date" name="end_date" class="form-control form-control-sm vpr-date"
                           value="<?= $period_type === 'custom' ? esc($end_date) : '' ?>"
                           aria-label="Range end" <?= $period_type === 'custom' ? '' : 'disabled' ?>>
                </span>

                <button type="submit" class="btn btn-sm btn-outline-primary fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Apply
                </button>
            </form>

            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm fw-semibold d-flex align-items-center gap-2 px-3 shadow-sm">
                <i class="bi bi-printer-fill"></i> Print / Save PDF
            </button>
        </div>
    </div>

    <!-- ═══ Printable document ═══ -->
    <div class="report-document-card" id="violatorPeriodReport">

        <div class="report-header">
            <div class="report-header-seal">
                <img src="<?= base_url('img/pic-1.png') ?>" alt="City Logo" class="report-seal-img">
            </div>
            <div class="report-header-title">
                <div class="rep-sub">Republic of the Philippines</div>
                <div class="rep-office">OFFICE OF THE CITY MAYOR</div>
                <div class="rep-city">Kabankalan City, Negros Occidental</div>
                <div class="rep-dept">CITY TRAFFIC MANAGEMENT &amp; TRANSPORT REGULATION OFFICE (CTRAMO)</div>

                <div class="rep-main-title">APPREHENSION &amp; SANCTION REPORT</div>
                <div class="rep-period">
                    <?= esc($period_name) ?> Report: <strong><?= esc($range_label) ?></strong>
                    &middot; Daily Breakdown
                </div>
            </div>
        </div>

        <div class="report-meta-bar">
            <div><strong>Generated:</strong> <?= date('F d, Y h:i A') ?></div>
            <div><strong>Prepared By:</strong> System Admin (<?= esc(session()->get('username') ?? 'Admin') ?>)</div>
        </div>

        <div class="vsum-kpi-grid">
            <div class="vsum-kpi">
                <div class="vsum-kpi-label">Apprehensions</div>
                <div class="vsum-kpi-value"><?= number_format((int) $totals['apprehensions']) ?></div>
                <div class="vsum-kpi-sub">citations issued in the period</div>
            </div>
            <div class="vsum-kpi">
                <div class="vsum-kpi-label">Violators</div>
                <div class="vsum-kpi-value"><?= number_format((int) $totals['violators']) ?></div>
                <div class="vsum-kpi-sub"><?= number_format((int) $totals['entries']) ?> offense entries in total</div>
            </div>
            <div class="vsum-kpi">
                <div class="vsum-kpi-label">Assessed</div>
                <div class="vsum-kpi-value">&#8369;<?= number_format((float) $totals['assessed'], 2) ?></div>
                <div class="vsum-kpi-sub">paid plus still due; voided entries excluded</div>
            </div>
            <div class="vsum-kpi">
                <div class="vsum-kpi-label">Outstanding</div>
                <div class="vsum-kpi-value">&#8369;<?= number_format((float) $totals['pending'], 2) ?></div>
                <div class="vsum-kpi-sub">pending settlement for the period</div>
            </div>
        </div>

        <div class="vsum-recon">
            <i class="bi bi-info-circle-fill"></i>
            <span>
                <strong><?= number_format((int) $totals['apprehensions']) ?> apprehension<?= (int) $totals['apprehensions'] === 1 ? '' : 's' ?></strong>
                on <strong><?= number_format((int) $totals['violators']) ?></strong> violator<?= (int) $totals['violators'] === 1 ? '' : 's' ?>,
                recorded on <?= number_format((int) $totals['active_days']) ?> active day<?= (int) $totals['active_days'] === 1 ? '' : 's' ?> of
                <?= esc($range_label) ?>.
                Collected: <strong>&#8369;<?= number_format((float) $totals['paid'], 2) ?></strong>.
                <?= $search !== '' ? 'Filtered by “' . esc($search) . '”.' : '' ?>
            </span>
        </div>

        <!-- ═══ I. Day by day ═══ -->
        <div class="report-section" id="periodDailySummarySection">
            <h5 class="report-sec-title report-sec-title--split">
                <span><i class="bi bi-calendar3 me-2"></i>I. Daily Summary</span>
                <button type="button" class="report-sec-print no-print"
                        onclick="printViolatorReportSection(this)"
                        title="Print only this section with the report header and summary figures">
                    <i class="bi bi-printer-fill"></i><span>Print this section</span>
                </button>
            </h5>
            <p class="vsum-sec-note">
                Every day of the period — one line per date, with what was apprehended and what it assessed.
            </p>

            <table class="table table-bordered table-sm report-table vpp-day-table">
                <thead>
                    <tr>
                        <th>DATE</th>
                        <th class="text-center">DAY</th>
                        <th class="text-center">APPREHENSIONS</th>
                        <th class="text-center">VIOLATORS</th>
                        <th class="text-center">OFFENSE ENTRIES</th>
                        <th class="text-end">ASSESSED</th>
                        <th class="text-end">OUTSTANDING</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($day_summary as $day): ?>
                        <tr class="<?= (int) $day['apprehensions'] === 0 ? 'vpp-idle' : '' ?>">
                            <td><strong><?= esc($day['label']) ?></strong></td>
                            <td class="text-center"><?= esc($day['weekday']) ?></td>
                            <td class="text-center"><?= (int) $day['apprehensions'] ?: '&mdash;' ?></td>
                            <td class="text-center"><?= (int) $day['violators'] ?: '&mdash;' ?></td>
                            <td class="text-center"><?= (int) $day['entries'] ?: '&mdash;' ?></td>
                            <td class="text-end"><?= (int) $day['apprehensions'] ? '&#8369;' . number_format((float) $day['assessed'], 2) : '&mdash;' ?></td>
                            <td class="text-end"><?= (int) $day['apprehensions'] ? '&#8369;' . number_format((float) $day['pending'], 2) : '&mdash;' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td>TOTAL</td>
                        <td class="text-center">&nbsp;</td>
                        <td class="text-center"><?= number_format((int) $totals['apprehensions']) ?></td>
                        <td class="text-center"><?= number_format((int) $totals['violators']) ?></td>
                        <td class="text-center"><?= number_format((int) $totals['entries']) ?></td>
                        <td class="text-end">&#8369;<?= number_format((float) $totals['assessed'], 2) ?></td>
                        <td class="text-end">&#8369;<?= number_format((float) $totals['pending'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- ═══ II. Apprehensions day by day ═══ -->
        <div class="report-section" id="periodApprehensionsByDaySection">
            <h5 class="report-sec-title report-sec-title--split">
                <span><i class="bi bi-clock-history me-2"></i>II. Apprehensions by Day</span>
                <button type="button" class="report-sec-print no-print"
                        onclick="printViolatorReportSection(this)"
                        title="Print only this section with the report header and summary figures">
                    <i class="bi bi-printer-fill"></i><span>Print this section</span>
                </button>
            </h5>

            <?php if (empty($day_detail)): ?>
                <div class="vpr-empty">
                    <i class="bi bi-inbox"></i>
                    No apprehension was recorded in this period.
                </div>
            <?php else: ?>
                <?php foreach ($day_detail as $day): ?>
                    <div class="vpp-day-group">
                        <div class="vpp-day-group-head">
                            <span class="vpp-day-group-name"><?= esc($day['label']) ?></span>
                            <span class="vpp-day-group-meta">
                                <?= (int) $day['apprehensions'] ?> apprehension<?= (int) $day['apprehensions'] === 1 ? '' : 's' ?>
                                &middot; <?= (int) $day['violators'] ?> violator<?= (int) $day['violators'] === 1 ? '' : 's' ?>
                                &middot; &#8369;<?= number_format((float) $day['assessed'], 2) ?> assessed
                                <?php if ((float) $day['pending'] > 0): ?>
                                    &middot; &#8369;<?= number_format((float) $day['pending'], 2) ?> outstanding
                                <?php endif; ?>
                            </span>
                        </div>

                        <table class="table table-bordered table-sm report-table vpp-record-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>DATE</th>
                                    <th>TIME</th>
                                    <th>VIOLATOR</th>
                                    <th>PLATE</th>
                                    <th>VIOLATION(S)</th>
                                    <th>WHERE COMMITTED</th>
                                    <th>OFFICER</th>
                                    <th class="text-end">FINE</th>
                                    <th>STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($day['rows'] as $index => $record): ?>
                                    <?php
                                    $tone = $record['status'] === 'Paid'
                                        ? 'paid'
                                        : ($record['status'] === 'Cancelled' ? 'cancelled' : 'pending');
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $index + 1 ?></td>
                                        <td><?= esc($record['date']) ?></td>
                                        <td><span class="vpr-time"><?= esc($record['time']) ?></span></td>
                                        <td><strong><?= esc($record['violator']) ?></strong></td>
                                        <td><?= $record['plate'] !== '' ? esc(strtoupper($record['plate'])) : '&mdash;' ?></td>
                                        <td><?= esc($record['offenses']) ?></td>
                                        <td><?= esc($record['location']) ?></td>
                                        <td><?= esc($record['officer']) ?></td>
                                        <td class="text-end">&#8369;<?= number_format((float) $record['amount'], 2) ?></td>
                                        <td>
                                            <span class="vpr-pill vpr-pill--<?= esc($tone) ?>">
                                                <?= $tone === 'paid' ? 'Paid' : ($tone === 'cancelled' ? 'Void' : 'Pending') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ═══ III. Totals per violator ═══ -->
        <div class="report-section" id="periodViolatorSummarySection">
            <h5 class="report-sec-title report-sec-title--split">
                <span><i class="bi bi-people me-2"></i>III. Violator Summary for the Period</span>
                <button type="button" class="report-sec-print no-print"
                        onclick="printViolatorReportSection(this)"
                        title="Print only this section with the report header and summary figures">
                    <i class="bi bi-printer-fill"></i><span>Print this section</span>
                </button>
            </h5>
            <p class="vsum-sec-note">
                Each person's whole period on one line. Open a single violator's full sheet from Reports &rarr; Violator Reports.
            </p>

            <?php if (empty($per_violator)): ?>
                <div class="vpr-empty">
                    <i class="bi bi-person-x"></i>
                    Nobody on record for this period.
                </div>
            <?php else: ?>
                <table class="table table-bordered table-sm report-table vpp-summary-table">
                    <thead>
                        <tr>
                            <th>VIOLATOR</th>
                            <th>PLATE</th>
                            <th class="text-center">APPREHENSIONS</th>
                            <th class="text-center">OFFENSE ENTRIES</th>
                            <th class="text-end">ASSESSED</th>
                            <th class="text-end">PAID</th>
                            <th class="text-end">OUTSTANDING</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($per_violator as $person): ?>
                            <tr>
                                <td><strong><?= esc($person['name']) ?></strong></td>
                                <td><?= $person['plate'] !== '' ? esc(strtoupper($person['plate'])) : '&mdash;' ?></td>
                                <td class="text-center"><?= (int) $person['apprehensions'] ?></td>
                                <td class="text-center"><?= (int) $person['entries'] ?></td>
                                <td class="text-end">&#8369;<?= number_format((float) $person['assessed'], 2) ?></td>
                                <td class="text-end">&#8369;<?= number_format((float) $person['paid'], 2) ?></td>
                                <td class="text-end">&#8369;<?= number_format((float) $person['pending'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="report-total-row">
                            <td>TOTAL</td>
                            <td><?= number_format((int) $totals['violators']) ?> violators</td>
                            <td class="text-center"><?= number_format((int) $totals['apprehensions']) ?></td>
                            <td class="text-center"><?= number_format((int) $totals['entries']) ?></td>
                            <td class="text-end">&#8369;<?= number_format((float) $totals['assessed'], 2) ?></td>
                            <td class="text-end">&#8369;<?= number_format((float) $totals['paid'], 2) ?></td>
                            <td class="text-end">&#8369;<?= number_format((float) $totals['pending'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
        </div>

        <!-- ═══ Sign-off ═══ -->
        <div class="report-sign-block">
            <div class="row">
                <div class="col-6">
                    <div class="sig-container">
                        <div class="sig-line"></div>
                        <div class="fw-bold">Prepared By</div>
                        <div class="text-muted small">CTRAMO Records Officer</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="sig-container">
                        <div class="sig-line"></div>
                        <div class="fw-bold">Verified By</div>
                        <div class="text-muted small">City Traffic Management Officer</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?= $this->include('admin/violators/_report_styles') ?>

<style>
.vpr-period-box { display: inline-flex; align-items: center; gap: 4px; }
.vpr-date { width: auto; }
.vpr-dash { color: var(--text-subtle, #94a3b8); }

.vpp-day-group { margin-bottom: 14px; }
.vpp-day-group:last-child { margin-bottom: 0; }
.vpp-day-group-head {
    display: flex; align-items: baseline; justify-content: space-between;
    gap: 10px; flex-wrap: wrap;
    border-bottom: 1px solid #94a3b8; padding-bottom: 3px; margin-bottom: 5px;
}
.vpp-day-group-name {
    font-size: .84rem; font-weight: 800; letter-spacing: .03em;
    text-transform: uppercase; color: #0f172a;
}
.vpp-day-group-meta { font-size: .74rem; color: #475569; }

.vpp-day-table tr.vpp-idle td { color: #94a3b8; }

/* One predictable grid for every day's tickets, so dates, times, money and
   status line up down the page instead of re-flowing per table. */
.vpp-record-table { table-layout: fixed; width: 100%; }
.vpp-record-table th,
.vpp-record-table td {
    padding: 3px 5px !important; vertical-align: middle !important;
    line-height: 1.25 !important; border-color: #e9eef5 !important;
}
.vpp-record-table th {
    font-size: .6rem !important; letter-spacing: .02em;
    background: #f4f7fb !important; color: #334155 !important;
}
.vpp-record-table td { font-size: .7rem; color: #1f2937; }
.vpp-record-table th:nth-child(1), .vpp-record-table td:nth-child(1) { width: 3% !important; text-align: center; }
.vpp-record-table th:nth-child(2), .vpp-record-table td:nth-child(2) { width: 11% !important; }
.vpp-record-table th:nth-child(3), .vpp-record-table td:nth-child(3) { width: 7% !important; }
.vpp-record-table th:nth-child(4), .vpp-record-table td:nth-child(4) { width: 13% !important; }
.vpp-record-table th:nth-child(5), .vpp-record-table td:nth-child(5) { width: 8% !important; }
.vpp-record-table th:nth-child(6), .vpp-record-table td:nth-child(6) { width: 17% !important; }
.vpp-record-table th:nth-child(7), .vpp-record-table td:nth-child(7) { width: 13% !important; }
.vpp-record-table th:nth-child(8), .vpp-record-table td:nth-child(8) { width: 9% !important; }
.vpp-record-table th:nth-child(9), .vpp-record-table td:nth-child(9) { width: 9% !important; text-align: right; }
.vpp-record-table th:nth-child(10), .vpp-record-table td:nth-child(10) { width: 10% !important; text-align: center; }

/* Short facts never break: a wrapped date or amount reads as a typo. */
.vpp-record-table td:nth-child(2),
.vpp-record-table td:nth-child(3),
.vpp-record-table td:nth-child(5),
.vpp-record-table td:nth-child(9),
.vpp-record-table td:nth-child(10) { white-space: nowrap !important; }

/* Header words stay whole; only the two long headings wrap. */
.vpp-record-table th { white-space: nowrap !important; }
.vpp-record-table th:nth-child(6),
.vpp-record-table th:nth-child(7) { white-space: normal !important; }
.vpp-record-table td:nth-child(4),
.vpp-record-table td:nth-child(8) { font-weight: 600; }
.vpp-record-table .vpr-time { font-size: .68rem; }
.vpp-record-table .vpr-pill { font-size: .56rem !important; padding: 1px 6px !important; }

@media print {
    .vpp-day-group-head { color: #000 !important; border-bottom: 1px solid #000 !important; }
    .vpp-day-group-name { font-size: 10pt !important; color: #000 !important; }
    .vpp-day-group-meta { font-size: 8pt !important; color: #000 !important; }

    /* The daily summary: seven columns that must not squeeze their headings. */
    .vpp-day-table { table-layout: fixed !important; }
    .vpp-day-table th { font-size: 7.5pt !important; padding: 3px 4px !important; }
    .vpp-day-table th, .vpp-day-table td { white-space: nowrap !important; }
    .vpp-day-table th:nth-child(1), .vpp-day-table td:nth-child(1) { width: 14% !important; }
    .vpp-day-table th:nth-child(2), .vpp-day-table td:nth-child(2) { width: 8% !important; }
    .vpp-day-table th:nth-child(3), .vpp-day-table td:nth-child(3) { width: 14% !important; }
    .vpp-day-table th:nth-child(4), .vpp-day-table td:nth-child(4) { width: 12% !important; }
    .vpp-day-table th:nth-child(5), .vpp-day-table td:nth-child(5) { width: 16% !important; }
    .vpp-day-table th:nth-child(6), .vpp-day-table td:nth-child(6) { width: 18% !important; }
    .vpp-day-table th:nth-child(7), .vpp-day-table td:nth-child(7) { width: 18% !important; }

    /* The ticket grid keeps the on-screen layout: fixed columns, black rules. */
    .vpp-record-table th { background: #e2e8f0 !important; }
    .vpp-record-table th,
    .vpp-record-table td { border-color: #000 !important; }

    /* A day heading never ends a page on its own. */
    .vpp-day-group-head {
        page-break-after: avoid !important;
        break-after: avoid !important;
    }

    .vpp-summary-table th {
        white-space: nowrap !important;
        font-size: 7.5pt !important;
        padding: 3px 4px !important;
    }
    .vpp-summary-table th:nth-child(1), .vpp-summary-table td:nth-child(1) { width: 26% !important; }
    .vpp-summary-table th:nth-child(2), .vpp-summary-table td:nth-child(2) { width: 11% !important; }
    .vpp-summary-table th:nth-child(3), .vpp-summary-table td:nth-child(3) { width: 14% !important; }
    .vpp-summary-table th:nth-child(4), .vpp-summary-table td:nth-child(4) { width: 13% !important; }
    .vpp-summary-table th:nth-child(5), .vpp-summary-table td:nth-child(5) { width: 12% !important; }
    .vpp-summary-table th:nth-child(6), .vpp-summary-table td:nth-child(6) { width: 12% !important; }
    .vpp-summary-table th:nth-child(7), .vpp-summary-table td:nth-child(7) { width: 12% !important; }
}
</style>

<script>
// One period box at a time: only the fields of the chosen report type stay
// enabled, so the GET that regenerates the report carries just that period.
function vprTogglePeriod(type) {
    document.querySelectorAll('#vprPeriodForm .vpr-period-box').forEach(function (box) {
        var active = box.getAttribute('data-period') === type;

        box.style.display = active ? 'inline-flex' : 'none';

        box.querySelectorAll('input, select').forEach(function (field) {
            field.disabled = !active;
        });
    });
}

function printViolatorReportSection(button) {
    const section = button.closest('.report-section');
    if (!section) {
        throw new Error('Cannot print report section: section container not found.');
    }

    section.classList.add('vpr-print-target');
    document.body.classList.add('vpr-print-section');

    const restore = function () {
        section.classList.remove('vpr-print-target');
        document.body.classList.remove('vpr-print-section');
    };

    window.addEventListener('afterprint', restore, { once: true });
    window.print();
    window.setTimeout(restore, 800);
}
</script>

<?= $this->endSection() ?>
