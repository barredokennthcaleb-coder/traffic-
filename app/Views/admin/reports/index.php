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
                </div>

            </div>

            <div class="d-flex gap-2 flex-wrap align-items-center">
                <a href="<?= base_url('reports/export-csv?' . http_build_query($_GET)) ?>" class="btn btn-outline-success btn-sm fw-semibold d-flex align-items-center gap-2 px-3">
                    <i class="bi bi-file-earmark-excel-fill"></i> Export CSV
                </a>
                <button onclick="window.print()" class="btn btn-primary btn-sm fw-semibold d-flex align-items-center gap-2 px-3 shadow-sm">
                    <i class="bi bi-printer-fill"></i> Print / Save PDF
                </button>
            </div>
        </div>
        <div class="card-body bg-transparent pt-0 pb-3 px-4 rpt-toolbar-body">
            <div class="text-end mb-3 pb-2 border-bottom">
                <div class="text-muted small" style="font-size: 0.74rem;">
                    <i class="bi bi-info-circle me-1 text-primary"></i>Report Period: <strong><?= date('M d, Y', strtotime($start_date)) ?> &ndash; <?= date('M d, Y', strtotime($end_date)) ?></strong>
                </div>
            </div>

            <!-- Responsive Filter Form -->
            <form method="GET" action="<?= base_url('reports') ?>" id="reportFilterForm" class="row g-2 align-items-end">

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
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold d-flex align-items-center justify-content-center gap-1.5 py-1.5 shadow-2xs">
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
                <div class="kpi-label">Assessed Penalties</div>
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

        <!-- Section 1: Officer Activity -->
        <div class="report-section mb-4">
            <h5 class="report-sec-title"><i class="bi bi-shield-check me-2"></i>I. Enforcer / Officer Activity Summary</h5>
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
        </div>

        <!-- Section 2: Nature of Violations Breakdown -->
        <div class="report-section mb-4">
            <h5 class="report-sec-title"><i class="bi bi-bar-chart-steps me-2"></i>II. Offense Category Breakdown</h5>
            <div class="report-scroll-hint"><i class="bi bi-arrows-move"></i> Swipe the table sideways to see every column</div>
            <div class="table-responsive report-table-scroll">
            <table class="table table-bordered table-sm report-table report-table-scrollable">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Violation / Offense Nature</th>
                        <th class="text-center">Total Incidents</th>
                        <th class="text-end">Assessed Amount (₱)</th>
                        <th class="text-center">% Share</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($nature_breakdown)): ?>
                        <tr><td colspan="5" class="text-center text-muted">No offenses recorded during this period.</td></tr>
                    <?php else: ?>
                        <?php $i=1; foreach($nature_breakdown as $nb):
                            $share = $summary['total_violations'] > 0 ? round(($nb['count'] / $summary['total_violations']) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td class="fw-bold"><?= esc($nb['violation_type']) ?></td>
                            <td class="text-center fw-bold"><?= number_format($nb['count']) ?></td>
                            <td class="text-end font-monospace">₱<?= number_format($nb['amount'], 2) ?></td>
                            <td class="text-center font-monospace"><?= $share ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>

        <!-- Section 3: Detailed Ticket Records -->
        <div class="report-section mb-4">
            <h5 class="report-sec-title"><i class="bi bi-list-check me-2"></i>III. Detailed Citation Records</h5>
            <div class="report-scroll-hint"><i class="bi bi-arrows-move"></i> Swipe the table sideways to see every column</div>
            <div class="table-responsive report-table-scroll">
            <table class="table table-bordered table-sm report-table report-table-wide">
                <thead>
                    <tr>
                        <th>TCT No.</th>
                        <th>Driver Information</th>
                        <th>Plate No.</th>
                        <th>Violation Type</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Status</th>
                        <th>Date &amp; Time</th>
                        <th>Officer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No detailed citation records found for the selected criteria.</td></tr>
                    <?php else: ?>
                        <?php foreach($records as $r): ?>
                        <tr>
                            <td class="font-monospace fw-bold text-danger"><?= esc($r['ticket_id']) ?></td>
                            <td>
                                <div><strong><?= esc($r['driver_name']) ?></strong></div>
                                <?php if(!empty($r['license_number'])): ?>
                                    <small class="text-muted">DL: <?= esc($r['license_number']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="font-monospace"><?= esc($r['license_plate']) ?></td>
                            <td><?= esc($r['violation_name'] ?: $r['violation_type']) ?></td>
                            <td class="text-end font-monospace fw-bold">₱<?= number_format((float)$r['penalty_amount'], 2) ?></td>
                            <td class="text-center">
                                <?php if($r['status'] === 'Paid'): ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php elseif($r['status'] === 'Pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= date('M d, Y h:i A', strtotime($r['violation_date'])) ?></td>
                            <td class="small"><?= esc($r['officer_name'] ?? 'System') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
    .report-kpi-box.warning { border-color: #f59e0b; background: #fffbeb; }
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
        /* A4 portrait with 10mm side margins leaves ~678px of usable width;
           the tables are set here so every column fits on the paper instead of
           running off the right edge. */
        .report-table {
            border: 1.5px solid #000 !important;
            font-size: 0.74rem !important;
            margin-bottom: 12px !important;
            width: 100% !important;
        }
        .report-table thead th,
        .report-table th {
            background: #e2e8f0 !important;
            color: #000 !important;
            font-weight: 800 !important;
            font-size: 0.7rem !important;
            padding: 6px 4px !important;
            border: 1.5px solid #000 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .report-table td {
            padding: 6px 4px !important;
            font-size: 0.74rem !important;
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
