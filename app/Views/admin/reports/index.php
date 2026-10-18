<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Generated Traffic Violation Report<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- Screen Filter Toolbar (Hidden on Print) -->
    <div class="card border-0 shadow-sm mb-4 no-print premium-reveal" style="--reveal-delay: 0s;">
        <div class="rpt-toolbar-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="rpt-icon-wrap">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold" style="color: var(--text-strong);">Generate Reports</h5>
                    <p class="mb-0 text-muted small">Filter by period, type, or status then print or export</p>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= base_url('reports/export-csv?' . http_build_query($_GET)) ?>" class="btn btn-outline-success btn-sm fw-semibold d-flex align-items-center gap-2 px-3">
                    <i class="bi bi-file-earmark-excel-fill"></i> Export CSV
                </a>
                <button onclick="window.print()" class="btn btn-primary btn-sm fw-semibold d-flex align-items-center gap-2 px-3 shadow-sm">
                    <i class="bi bi-printer-fill"></i> Print / Save PDF
                </button>
            </div>
        </div>
        <div class="card-body bg-transparent pt-0 pb-3 px-4">
            <form method="GET" action="<?= base_url('reports') ?>" id="reportFilterForm" class="row g-3 align-items-end">

                <div class="col-md-2">
                    <label for="period_type" class="form-label filter-label">Report Type</label>
                    <select name="period_type" id="period_type" class="form-select form-select-sm" onchange="togglePeriodInputs(this.value)">
                        <option value="weekly"  <?= ($period_type === 'weekly')  ? 'selected' : '' ?>>📅 Weekly Report</option>
                        <option value="monthly" <?= ($period_type === 'monthly') ? 'selected' : '' ?>>📆 Monthly Report</option>
                        <option value="custom"  <?= ($period_type === 'custom')  ? 'selected' : '' ?>>🎯 Custom Range</option>
                    </select>
                </div>

                <!-- Weekly Selector -->
                <div class="col-md-3 period-box" id="weeklyBox" style="<?= ($period_type !== 'monthly' && $period_type !== 'custom') ? '' : 'display:none;' ?>">
                    <label for="start_date_week" class="form-label filter-label">Week Starting Date</label>
                    <input type="date" name="start_date" id="start_date_week" class="form-control form-control-sm" value="<?= esc($start_date) ?>">
                </div>

                <!-- Monthly Selector -->
                <div class="col-md-2 period-box" id="monthlyMonthBox" style="<?= ($period_type === 'monthly') ? '' : 'display:none;' ?>">
                    <label for="month" class="form-label filter-label">Month</label>
                    <select name="month" id="month" class="form-select form-select-sm">
                        <?php
                        $months = [
                            '01'=>'January','02'=>'February','03'=>'March','04'=>'April',
                            '05'=>'May','06'=>'June','07'=>'July','08'=>'August',
                            '09'=>'September','10'=>'October','11'=>'November','12'=>'December'
                        ];
                        foreach($months as $num => $name):
                        ?>
                            <option value="<?= $num ?>" <?= ($month == $num) ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 period-box" id="monthlyYearBox" style="<?= ($period_type === 'monthly') ? '' : 'display:none;' ?>">
                    <label for="year" class="form-label filter-label">Year</label>
                    <select name="year" id="year" class="form-select form-select-sm">
                        <?php $cYear = date('Y'); for($y = $cYear; $y >= $cYear - 5; $y--): ?>
                            <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Custom Range -->
                <div class="col-md-2 period-box" id="customStartBox" style="<?= ($period_type === 'custom') ? '' : 'display:none;' ?>">
                    <label for="start_date_custom" class="form-label filter-label">From Date</label>
                    <input type="date" name="start_date" id="start_date_custom" class="form-control form-control-sm" value="<?= esc($start_date) ?>" <?= ($period_type === 'custom') ? '' : 'disabled' ?>>
                </div>
                <div class="col-md-2 period-box" id="customEndBox" style="<?= ($period_type === 'custom') ? '' : 'display:none;' ?>">
                    <label for="end_date_custom" class="form-label filter-label">To Date</label>
                    <input type="date" name="end_date" id="end_date_custom" class="form-control form-control-sm" value="<?= esc($end_date) ?>">
                </div>

                <div class="col-md-2">
                    <label for="status" class="form-label filter-label">Status</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="Paid"      <?= ($status_filter === 'Paid')      ? 'selected' : '' ?>>✅ Paid Only</option>
                        <option value="Pending"   <?= ($status_filter === 'Pending')   ? 'selected' : '' ?>>⏳ Pending</option>
                        <option value="Cancelled" <?= ($status_filter === 'Cancelled') ? 'selected' : '' ?>>❌ Cancelled</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="violation_type_id" class="form-label filter-label">Violation Type</label>
                    <select name="violation_type_id" id="violation_type_id" class="form-select form-select-sm">
                        <option value="">All Offenses</option>
                        <?php foreach($all_types as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= ($type_filter == $t['id']) ? 'selected' : '' ?>><?= esc($t['violation_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold d-flex align-items-center justify-content-center gap-1">
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

        <!-- Section 1: Nature of Violations Breakdown -->
        <div class="report-section mb-4">
            <h5 class="report-sec-title"><i class="bi bi-bar-chart-steps me-2"></i>I. Offense Category Breakdown</h5>
            <table class="table table-bordered table-sm report-table">
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

        <!-- Section 2: Officer Activity -->
        <div class="report-section mb-4">
            <h5 class="report-sec-title"><i class="bi bi-shield-check me-2"></i>II. Enforcer / Officer Activity Summary</h5>
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

        <!-- Section 3: Detailed Ticket Records -->
        <div class="report-section mb-4">
            <h5 class="report-sec-title"><i class="bi bi-list-check me-2"></i>III. Detailed Citation Records</h5>
            <table class="table table-bordered table-sm report-table">
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
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: var(--brand-gradient, linear-gradient(135deg, #5865f2, #7f56d9));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        color: #fff;
        box-shadow: 0 6px 18px rgba(88,101,242,0.3);
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
    .report-table { font-size: 0.83rem; font-family: Arial, sans-serif; border-color: #000 !important; }
    .report-table thead th {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        font-weight: bold;
        border-bottom: 2px solid #000 !important;
        font-size: 0.8rem;
        text-transform: uppercase;
    }
    .report-table td { vertical-align: middle; padding: 6px 10px; }

    /* Sign-off */
    .sig-line { width: 220px; border-bottom: 1.5px solid #000; margin-top: 50px; }

    /* Print Styling */
    @media print {
        .no-print,
        .sidebar, .sidebar-toggle, .mobile-topbar,
        .desktop-header, .breadcrumb, .btn,
        .alert, nav, header, footer {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
            padding: 0 !important;
        }
        .container-fluid { padding: 0 !important; }
        body { background: #fff !important; }
        .report-document-card {
            max-width: 100%;
            border: 2px solid #000;
            box-shadow: none;
            padding: 18px 22px;
        }
        @page { margin: 10mm 12mm; size: A4 portrait; }
    }
</style>

<script>
    function togglePeriodInputs(val) {
        // Hide all period boxes and disable inputs inside hidden boxes to avoid sending conflicting parameters
        document.querySelectorAll('.period-box').forEach(el => {
            el.style.display = 'none';
            el.querySelectorAll('input, select').forEach(input => input.disabled = true);
        });

        if (val === 'weekly') {
            const box = document.getElementById('weeklyBox');
            if (box) {
                box.style.display = '';
                box.querySelectorAll('input, select').forEach(input => input.disabled = false);
            }
        } else if (val === 'monthly') {
            ['monthlyMonthBox', 'monthlyYearBox'].forEach(id => {
                const box = document.getElementById(id);
                if (box) {
                    box.style.display = '';
                    box.querySelectorAll('input, select').forEach(input => input.disabled = false);
                }
            });
        } else if (val === 'custom') {
            ['customStartBox', 'customEndBox'].forEach(id => {
                const box = document.getElementById(id);
                if (box) {
                    box.style.display = '';
                    box.querySelectorAll('input, select').forEach(input => input.disabled = false);
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const periodSelect = document.getElementById('period_type');
        if (periodSelect) {
            togglePeriodInputs(periodSelect.value);
        }
        // Load saved signatures from localStorage
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
