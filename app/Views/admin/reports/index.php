<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Generated Traffic Violation Report<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- Screen Filter Toolbar (Hidden on Print) -->
    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-bold text-primary">
                <i class="bi bi-file-earmark-bar-graph me-2"></i>Generate Reports
            </h5>
            <div class="d-flex gap-2">
                <a href="<?= base_url('reports/export-csv?' . http_build_query($_GET)) ?>" class="btn btn-outline-success btn-sm fw-semibold">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel / CSV
                </a>
                <button onclick="window.print()" class="btn btn-primary btn-sm fw-semibold shadow-sm">
                    <i class="bi bi-printer me-1"></i> Print / Save PDF
                </button>
            </div>
        </div>
        <div class="card-body bg-light-subtle">
            <form method="GET" action="<?= base_url('reports') ?>" id="reportFilterForm" class="row g-3 align-items-end">
                
                <div class="col-md-2">
                    <label for="period_type" class="form-label small fw-bold">Report Type</label>
                    <select name="period_type" id="period_type" class="form-select form-select-sm" onchange="togglePeriodInputs(this.value)">
                        <option value="weekly" <?= ($period_type === 'weekly') ? 'selected' : '' ?>>Weekly Report</option>
                        <option value="monthly" <?= ($period_type === 'monthly') ? 'selected' : '' ?>>Monthly Report</option>
                        <option value="custom" <?= ($period_type === 'custom') ? 'selected' : '' ?>>Custom Date Range</option>
                    </select>
                </div>

                <!-- Weekly Selector -->
                <div class="col-md-3 period-box" id="weeklyBox" style="<?= ($period_type !== 'monthly' && $period_type !== 'custom') ? '' : 'display:none;' ?>">
                    <label for="start_date_week" class="form-label small fw-bold">Week Starting Date</label>
                    <input type="date" name="start_date" id="start_date_week" class="form-control form-control-sm" value="<?= esc($start_date) ?>">
                </div>

                <!-- Monthly Selector -->
                <div class="col-md-2 period-box" id="monthlyMonthBox" style="<?= ($period_type === 'monthly') ? '' : 'display:none;' ?>">
                    <label for="month" class="form-label small fw-bold">Select Month</label>
                    <select name="month" id="month" class="form-select form-select-sm">
                        <?php 
                        $months = [
                            '01'=>'January', '02'=>'February', '03'=>'March', '04'=>'April',
                            '05'=>'May', '06'=>'June', '07'=>'July', '08'=>'August',
                            '09'=>'September', '10'=>'October', '11'=>'November', '12'=>'December'
                        ];
                        foreach($months as $num => $name):
                        ?>
                            <option value="<?= $num ?>" <?= ($month == $num) ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 period-box" id="monthlyYearBox" style="<?= ($period_type === 'monthly') ? '' : 'display:none;' ?>">
                    <label for="year" class="form-label small fw-bold">Year</label>
                    <select name="year" id="year" class="form-select form-select-sm">
                        <?php $cYear = date('Y'); for($y = $cYear; $y >= $cYear - 5; $y--): ?>
                            <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Custom Range -->
                <div class="col-md-2 period-box" id="customStartBox" style="<?= ($period_type === 'custom') ? '' : 'display:none;' ?>">
                    <label for="start_date_custom" class="form-label small fw-bold">From Date</label>
                    <input type="date" name="start_date" id="start_date_custom" class="form-control form-control-sm" value="<?= esc($start_date) ?>" <?= ($period_type === 'custom') ? '' : 'disabled' ?>>
                </div>
                <div class="col-md-2 period-box" id="customEndBox" style="<?= ($period_type === 'custom') ? '' : 'display:none;' ?>">
                    <label for="end_date_custom" class="form-label small fw-bold">To Date</label>
                    <input type="date" name="end_date" id="end_date_custom" class="form-control form-control-sm" value="<?= esc($end_date) ?>">
                </div>

                <div class="col-md-2">
                    <label for="status" class="form-label small fw-bold">Status Filter</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="Paid" <?= ($status_filter === 'Paid') ? 'selected' : '' ?>>Paid Only</option>
                        <option value="Pending" <?= ($status_filter === 'Pending') ? 'selected' : '' ?>>Pending Only</option>
                        <option value="Cancelled" <?= ($status_filter === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="violation_type_id" class="form-label small fw-bold">Violation Type</label>
                    <select name="violation_type_id" id="violation_type_id" class="form-select form-select-sm">
                        <option value="">All Offenses</option>
                        <?php foreach($all_types as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= ($type_filter == $t['id']) ? 'selected' : '' ?>><?= esc($t['violation_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Printable Official Report Document -->
    <div class="report-document-card" id="reportDocument">
        
        <!-- Header Section -->
        <div class="report-header">
            <div class="report-header-seal">
                <img src="<?= base_url('img/pic 1.png') ?>" alt="City Logo" class="report-seal-img">
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
        <div class="report-meta-bar d-flex justify-content-between align-items-center">
            <div>
                <strong>Generated Date:</strong> <?= date('F d, Y h:i A') ?>
            </div>
            <div>
                <strong>Prepared By:</strong> System Admin (<?= esc(session()->get('username') ?? 'Admin') ?>)
            </div>
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
                        <th style="width: 50px;">#</th>
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

        <!-- Section 2: Apprehending Officers Activity -->
        <div class="report-section mb-4">
            <h5 class="report-sec-title"><i class="bi bi-shield-check me-2"></i>II. Enforcer / Officer Activity Summary</h5>
            <table class="table table-bordered table-sm report-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
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

        <!-- Section 3: Detailed Ticket Records Log -->
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

        <!-- Sign-off Block -->
        <div class="report-sign-block mt-5 pt-3">
            <div class="row">
                <div class="col-6 text-center">
                    <div class="sig-line mx-auto"></div>
                    <div class="fw-bold mt-1">Prepared By</div>
                    <div class="text-muted small">Traffic System Administrator</div>
                </div>
                <div class="col-6 text-center">
                    <div class="sig-line mx-auto"></div>
                    <div class="fw-bold mt-1">Approved By</div>
                    <div class="text-muted small">Head, CTRAMO Department</div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Styles -->
<style>
    .report-document-card {
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        border: 2px solid #1e293b;
        padding: 32px 40px;
        font-family: 'Times New Roman', Times, serif, Arial, sans-serif;
        color: #0f172a;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        border-radius: 4px;
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
    .report-header-seal {
        position: absolute;
        left: 0;
        top: 0;
    }
    .report-seal-img {
        width: 80px;
        height: 80px;
        object-fit: contain;
    }
    .report-header-title {
        text-align: center;
        line-height: 1.25;
    }
    .rep-sub { font-size: 0.95rem; }
    .rep-office { font-size: 1.1rem; font-weight: bold; }
    .rep-city { font-size: 0.95rem; }
    .rep-dept { font-size: 0.85rem; font-weight: bold; margin-top: 4px; color: #334155; }
    .rep-main-title {
        font-family: Arial, sans-serif;
        font-size: 1.3rem;
        font-weight: 900;
        letter-spacing: 0.03em;
        margin-top: 8px;
        margin-bottom: 4px;
        color: #0f172a;
    }
    .rep-period {
        font-size: 0.9rem;
        font-style: italic;
        color: #475569;
    }

    /* Meta Bar */
    .report-meta-bar {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        padding: 8px 16px;
        font-size: 0.88rem;
        font-family: Arial, sans-serif;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    /* KPI Grid */
    .report-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 24px;
    }
    .report-kpi-box {
        border: 1.5px solid #cbd5e1;
        padding: 12px 14px;
        border-radius: 4px;
        background: #fafafa;
        text-align: center;
        font-family: Arial, sans-serif;
    }
    .report-kpi-box.success { border-color: #10b981; background: #f0fdf4; }
    .report-kpi-box.warning { border-color: #f59e0b; background: #fffbeb; }
    .kpi-label { font-size: 0.78rem; text-uppercase: uppercase; font-weight: bold; color: #64748b; }
    .kpi-val { font-size: 1.3rem; font-weight: 900; margin: 4px 0; color: #0f172a; }
    .kpi-sub { font-size: 0.75rem; color: #64748b; }

    /* Section Titles */
    .report-sec-title {
        font-family: Arial, sans-serif;
        font-size: 1rem;
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
        border-color: #000 !important;
    }
    .report-table thead th {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        font-weight: bold;
        border-bottom: 2px solid #000 !important;
        font-size: 0.82rem;
        text-transform: uppercase;
    }
    .report-table td {
        vertical-align: middle;
        padding: 6px 10px;
    }

    /* Sign-off */
    .sig-line {
        width: 220px;
        border-bottom: 1.5px solid #000;
        margin-top: 50px;
    }

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
        .container-fluid {
            padding: 0 !important;
        }
        body {
            background: #fff !important;
        }
        .report-document-card {
            max-width: 100%;
            border: 2px solid #000;
            box-shadow: none;
            padding: 20px;
        }
        @page {
            margin: 10mm 12mm;
            size: A4 portrait;
        }
    }
</style>

<script>
    function togglePeriodInputs(val) {
        document.querySelectorAll('.period-box').forEach(el => el.style.display = 'none');
        document.getElementById('start_date_custom').disabled = true;

        if (val === 'weekly') {
            document.getElementById('weeklyBox').style.display = '';
        } else if (val === 'monthly') {
            document.getElementById('monthlyMonthBox').style.display = '';
            document.getElementById('monthlyYearBox').style.display = '';
        } else if (val === 'custom') {
            document.getElementById('customStartBox').style.display = '';
            document.getElementById('customEndBox').style.display = '';
            document.getElementById('start_date_custom').disabled = false;
        }
    }
</script>

<?= $this->endSection() ?>
