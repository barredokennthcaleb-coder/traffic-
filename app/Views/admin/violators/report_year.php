<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Full-Year Violator Report<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4 vpr-page">

    <!-- ═══ Toolbar (screen only) ═══ -->
    <div class="vpr-toolbar no-print">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= esc($back_url) ?>" class="vpr-back-btn">
                <i class="bi bi-arrow-left"></i><span>Violator Reports</span>
            </a>
            <span class="vpr-crumb">/</span>
            <span class="vpr-crumb-current">Full Year <?= (int) $year ?></span>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form method="get" class="d-flex align-items-center gap-2 flex-wrap">
                <?php if ($search !== ''): ?>
                    <input type="hidden" name="search" value="<?= esc($search) ?>">
                <?php endif; ?>

                <select name="year" class="form-select form-select-sm vpr-select" aria-label="Year">
                    <?php foreach ($years as $y): ?>
                        <option value="<?= (int) $y ?>" <?= (int) $y === (int) $year ? 'selected' : '' ?>><?= (int) $y ?></option>
                    <?php endforeach; ?>
                </select>

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
    <div class="report-document-card" id="violatorYearReport">

        <div class="report-header">
            <div class="report-header-seal">
                <img src="<?= base_url('img/pic-1.png') ?>" alt="City Logo" class="report-seal-img">
            </div>
            <div class="report-header-title">
                <div class="rep-sub">Republic of the Philippines</div>
                <div class="rep-office">OFFICE OF THE CITY MAYOR</div>
                <div class="rep-city">Kabankalan City, Negros Occidental</div>
                <div class="rep-dept">CITY TRAFFIC MANAGEMENT &amp; TRANSPORT REGULATION OFFICE (CTRAMO)</div>

                <div class="rep-main-title">ANNUAL APPREHENSION &amp; SANCTION REPORT</div>
                <div class="rep-period">
                    Full Year: <strong><?= (int) $year ?></strong>
                    &middot; Monthly Breakdown
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
                <div class="vsum-kpi-sub">citations issued in <?= (int) $year ?></div>
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
                <div class="vsum-kpi-sub">pending settlement for the year</div>
            </div>
        </div>

        <div class="vsum-recon">
            <i class="bi bi-info-circle-fill"></i>
            <span>
                <strong><?= number_format((int) $totals['apprehensions']) ?> apprehension<?= (int) $totals['apprehensions'] === 1 ? '' : 's' ?></strong>
                on <strong><?= number_format((int) $totals['violators']) ?></strong> violator<?= (int) $totals['violators'] === 1 ? '' : 's' ?>,
                grouped into <?= number_format(count($month_detail)) ?> active month<?= count($month_detail) === 1 ? '' : 's' ?> of <?= (int) $year ?>.
                Collected: <strong>&#8369;<?= number_format((float) $totals['paid'], 2) ?></strong>.
                <?= $search !== '' ? 'Filtered by “' . esc($search) . '”.' : '' ?>
            </span>
        </div>

        <!-- ═══ I. The tally grid ═══ -->
        <div class="report-section">
            <h5 class="report-sec-title">
                <i class="bi bi-table me-2"></i>I. Daily Traffic Apprehension Record
            </h5>
            <p class="vsum-sec-note">
                Calendar Year <?= (int) $year ?> &middot; one mark per apprehension recorded on each day across all violators &mdash;
                this sheet counts <strong>tickets, not offense entries</strong>.
            </p>

            <div class="table-responsive vsum-matrix-scroll">
                <table class="table table-bordered table-sm report-table vpr-matrix">
                    <thead>
                        <tr>
                            <th class="vpr-mx-name">MONTH</th>
                            <?php for ($day = 1; $day <= 31; $day++): ?>
                                <th class="vpr-mx-day"><?= $day ?></th>
                            <?php endfor; ?>
                            <th class="vpr-mx-total">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $dayFooter = []; ?>
                        <?php foreach ($month_names as $monthNo => $monthName): ?>
                            <?php
                            $monthMarks = $calendar[$monthNo] ?? [];
                            $monthTotal = (int) ($calendar_totals[$monthNo] ?? 0);
                            ?>
                            <tr class="<?= $monthMarks === [] ? 'vpr-mx-idle' : '' ?>">
                                <td class="vpr-mx-name"><?= esc(mb_substr($monthName, 0, 3)) ?></td>
                                <?php for ($day = 1; $day <= 31; $day++): ?>
                                    <?php
                                    $marks = (int) ($monthMarks[$day] ?? 0);

                                    if ($marks > 0) {
                                        $dayFooter[$day] = ($dayFooter[$day] ?? 0) + $marks;
                                    }
                                    ?>
                                    <td class="vpr-mx-day<?= $marks > 0 ? ' vpr-mx-hit' : '' ?>"><?= $marks > 0 ? $marks : '' ?></td>
                                <?php endfor; ?>
                                <td class="vpr-mx-total"><?= $monthTotal > 0 ? $monthTotal : '&mdash;' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="report-total-row vpr-mx-foot">
                            <td class="vpr-mx-name">TOTAL</td>
                            <?php for ($day = 1; $day <= 31; $day++): ?>
                                <td class="vpr-mx-day"><?= (int) ($dayFooter[$day] ?? 0) ?: '' ?></td>
                            <?php endfor; ?>
                            <td class="vpr-mx-total"><?= number_format((int) $totals['apprehensions']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ═══ II. Month by month ═══ -->
        <div class="report-section">
            <h5 class="report-sec-title">
                <i class="bi bi-calendar3 me-2"></i>II. Monthly Summary
            </h5>
            <p class="vsum-sec-note">
                Every month of <?= (int) $year ?>, with what was apprehended, who was involved and what the year collected.
            </p>

            <table class="table table-bordered table-sm report-table vpr-yearsum-table">
                <thead>
                    <tr>
                        <th>MONTH</th>
                        <th class="text-center">APPREHENSIONS</th>
                        <th class="text-center">VIOLATORS</th>
                        <th class="text-center">OFFENSE ENTRIES</th>
                        <th class="text-end">ASSESSED</th>
                        <th class="text-end">OUTSTANDING</th>
                        <th class="text-center">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($month_summary as $month): ?>
                        <tr class="<?= (int) $month['apprehensions'] === 0 ? 'vpr-mx-idle' : '' ?>">
                            <td><strong><?= esc($month['label']) ?></strong></td>
                            <td class="text-center"><?= (int) $month['apprehensions'] ?: '&mdash;' ?></td>
                            <td class="text-center"><?= (int) $month['violators'] ?: '&mdash;' ?></td>
                            <td class="text-center"><?= (int) $month['entries'] ?: '&mdash;' ?></td>
                            <td class="text-end"><?= (int) $month['apprehensions'] ? '&#8369;' . number_format((float) $month['assessed'], 2) : '&mdash;' ?></td>
                            <td class="text-end"><?= (int) $month['apprehensions'] ? '&#8369;' . number_format((float) $month['pending'], 2) : '&mdash;' ?></td>
                            <td class="text-center">
                                <?php if ((int) $month['apprehensions'] > 0): ?>
                                    <span class="vpr-pill vpr-pill--paid">On record</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td>TOTAL <?= (int) $year ?></td>
                        <td class="text-center"><?= number_format((int) $totals['apprehensions']) ?></td>
                        <td class="text-center"><?= number_format((int) $totals['violators']) ?></td>
                        <td class="text-center"><?= number_format((int) $totals['entries']) ?></td>
                        <td class="text-end">&#8369;<?= number_format((float) $totals['assessed'], 2) ?></td>
                        <td class="text-end">&#8369;<?= number_format((float) $totals['pending'], 2) ?></td>
                        <td class="text-center">&nbsp;</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- ═══ III. Apprehensions month by month ═══ -->
        <div class="report-section">
            <h5 class="report-sec-title">
                <i class="bi bi-clock-history me-2"></i>III. Apprehensions by Month
            </h5>

            <?php if (empty($month_detail)): ?>
                <div class="vpr-empty">
                    <i class="bi bi-inbox"></i>
                    No apprehension was recorded in <?= (int) $year ?>.
                </div>
            <?php else: ?>
                <?php foreach ($month_detail as $month): ?>
                    <div class="vpr-year-month">
                        <div class="vpr-year-month-head">
                            <span class="vpr-year-month-name"><?= esc($month['label']) ?></span>
                            <span class="vpr-year-month-meta">
                                <?= (int) $month['apprehensions'] ?> apprehension<?= (int) $month['apprehensions'] === 1 ? '' : 's' ?>
                                &middot; <?= (int) $month['violators'] ?> violator<?= (int) $month['violators'] === 1 ? '' : 's' ?>
                                &middot; &#8369;<?= number_format((float) $month['assessed'], 2) ?> assessed
                                <?php if ((float) $month['pending'] > 0): ?>
                                    &middot; &#8369;<?= number_format((float) $month['pending'], 2) ?> outstanding
                                <?php endif; ?>
                            </span>
                        </div>

                        <table class="table table-bordered table-sm report-table vpr-record-table">
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
                                <?php foreach ($month['rows'] as $index => $record): ?>
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

        <!-- ═══ IV. Annual totals per violator ═══ -->
        <div class="report-section">
            <h5 class="report-sec-title">
                <i class="bi bi-people me-2"></i>IV. Violator Summary for <?= (int) $year ?>
            </h5>
            <p class="vsum-sec-note">
                Each person's whole year on one line. Open a single violator's full sheet from Reports &rarr; Violator Reports.
            </p>

            <?php if (empty($per_violator)): ?>
                <div class="vpr-empty">
                    <i class="bi bi-person-x"></i>
                    Nobody on record for <?= (int) $year ?>.
                </div>
            <?php else: ?>
                <table class="table table-bordered table-sm report-table vpr-yearv-table">
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
.vpr-year-month { margin-bottom: 14px; }
.vpr-year-month:last-child { margin-bottom: 0; }
.vpr-year-month-head {
    display: flex; align-items: baseline; justify-content: space-between;
    gap: 10px; flex-wrap: wrap;
    border-bottom: 1px solid #94a3b8; padding-bottom: 3px; margin-bottom: 5px;
}
.vpr-year-month-name {
    font-size: .84rem; font-weight: 800; letter-spacing: .03em;
    text-transform: uppercase; color: #0f172a;
}
.vpr-year-month-meta { font-size: .74rem; color: #475569; }

/* Section III: one predictable grid for every month, so dates, times, money
   and status line up down the page instead of re-flowing per table. */
.vpr-record-table { table-layout: fixed; width: 100%; }
.vpr-record-table th,
.vpr-record-table td {
    padding: 3px 5px !important; vertical-align: middle !important;
    line-height: 1.25 !important; border-color: #e9eef5 !important;
}
.vpr-record-table th {
    font-size: .6rem !important; letter-spacing: .02em;
    background: #f4f7fb !important; color: #334155 !important;
}
.vpr-record-table td { font-size: .7rem; color: #1f2937; }
.vpr-record-table th:nth-child(1), .vpr-record-table td:nth-child(1) { width: 3% !important; text-align: center; }
.vpr-record-table th:nth-child(2), .vpr-record-table td:nth-child(2) { width: 11% !important; }
.vpr-record-table th:nth-child(3), .vpr-record-table td:nth-child(3) { width: 7% !important; }
.vpr-record-table th:nth-child(4), .vpr-record-table td:nth-child(4) { width: 13% !important; }
.vpr-record-table th:nth-child(5), .vpr-record-table td:nth-child(5) { width: 8% !important; }
.vpr-record-table th:nth-child(6), .vpr-record-table td:nth-child(6) { width: 17% !important; }
.vpr-record-table th:nth-child(7), .vpr-record-table td:nth-child(7) { width: 13% !important; }
.vpr-record-table th:nth-child(8), .vpr-record-table td:nth-child(8) { width: 9% !important; }
.vpr-record-table th:nth-child(9), .vpr-record-table td:nth-child(9) { width: 9% !important; text-align: right; }
.vpr-record-table th:nth-child(10), .vpr-record-table td:nth-child(10) { width: 10% !important; text-align: center; }

/* Short facts never break: a wrapped date or amount reads as a typo. */
.vpr-record-table td:nth-child(2),
.vpr-record-table td:nth-child(3),
.vpr-record-table td:nth-child(5),
.vpr-record-table td:nth-child(9),
.vpr-record-table td:nth-child(10) { white-space: nowrap !important; }

/* Header words stay whole; only the two long headings wrap. */
.vpr-record-table th { white-space: nowrap !important; }
.vpr-record-table th:nth-child(6),
.vpr-record-table th:nth-child(7) { white-space: normal !important; }
.vpr-record-table td:nth-child(4),
.vpr-record-table td:nth-child(8) { font-weight: 600; }
.vpr-record-table .vpr-time { font-size: .68rem; }
.vpr-record-table .vpr-pill { font-size: .56rem !important; padding: 1px 6px !important; }

@media print {
    .vpr-year-month-head { color: #000 !important; border-bottom: 1px solid #000 !important; }
    .vpr-year-month-name { font-size: .78rem !important; color: #000 !important; }
    .vpr-year-month-meta { font-size: .68rem !important; color: #000 !important; }

    /* Monthly summary: seven columns that must not squeeze their headings.
       Fixed layout keeps the nowrap headings inside A4 instead of widening
       the table past the printable column. */
    .vpr-yearsum-table { table-layout: fixed !important; }
    .vpr-yearsum-table th { font-size: .6rem !important; padding: 3px 4px !important; }
    .vpr-yearsum-table th, .vpr-yearsum-table td { white-space: nowrap !important; }
    .vpr-yearsum-table th:nth-child(1), .vpr-yearsum-table td:nth-child(1) { width: 13% !important; }
    .vpr-yearsum-table th:nth-child(2), .vpr-yearsum-table td:nth-child(2) { width: 14% !important; }
    .vpr-yearsum-table th:nth-child(3), .vpr-yearsum-table td:nth-child(3) { width: 12% !important; }
    .vpr-yearsum-table th:nth-child(4), .vpr-yearsum-table td:nth-child(4) { width: 16% !important; }
    .vpr-yearsum-table th:nth-child(5), .vpr-yearsum-table td:nth-child(5) { width: 17% !important; }
    .vpr-yearsum-table th:nth-child(6), .vpr-yearsum-table td:nth-child(6) { width: 17% !important; }
    .vpr-yearsum-table th:nth-child(7), .vpr-yearsum-table td:nth-child(7) { width: 11% !important; }

    /* Section III keeps the on-screen grid: fixed columns, black rules. */
    .vpr-record-table th { background: #e2e8f0 !important; }
    .vpr-record-table th,
    .vpr-record-table td { border-color: #000 !important; }

    /* A month heading never ends a page on its own. */
    .vpr-year-month-head {
        page-break-after: avoid !important;
        break-after: avoid !important;
    }

    .vpr-yearv-table th {
        white-space: nowrap !important;
        font-size: .56rem !important;
        padding: 3px 4px !important;
    }
    .vpr-yearv-table th:nth-child(1), .vpr-yearv-table td:nth-child(1) { width: 26% !important; }
    .vpr-yearv-table th:nth-child(2), .vpr-yearv-table td:nth-child(2) { width: 11% !important; }
    .vpr-yearv-table th:nth-child(3), .vpr-yearv-table td:nth-child(3) { width: 14% !important; }
    .vpr-yearv-table th:nth-child(4), .vpr-yearv-table td:nth-child(4) { width: 13% !important; }
    .vpr-yearv-table th:nth-child(5), .vpr-yearv-table td:nth-child(5) { width: 12% !important; }
    .vpr-yearv-table th:nth-child(6), .vpr-yearv-table td:nth-child(6) { width: 12% !important; }
    .vpr-yearv-table th:nth-child(7), .vpr-yearv-table td:nth-child(7) { width: 12% !important; }
}
</style>

<?= $this->endSection() ?>
