<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Admin Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header with Year Filter & Generate Report Button -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1 text-primary fw-bold"><i class="bi bi-speedometer2 me-2"></i>Dashboard &amp; Analytics</h4>
            <p class="text-muted mb-0 small">Comprehensive insights into system activity, violation trends, and revenue.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Generate Reports Dropdown Button -->
            <div class="dropdown">
                <button class="btn btn-primary btn-sm shadow-sm dropdown-toggle fw-bold px-3 py-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Generate Reports
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="min-width: 220px;">
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

    <!-- Quick Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <a href="<?= base_url('penalties/all') ?>" class="text-decoration-none">
            <div class="card premium-kpi premium-reveal text-white border-0" style="--reveal-delay: 0.1s; --float-delay: 0s; cursor:pointer; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .3s cubic-bezier(0.4, 0, 0.2, 1);" onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 20px 25px -5px rgba(99, 102, 241, 0.4), 0 8px 10px -6px rgba(99, 102, 241, 0.2)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="text-uppercase mb-0 fw-bold opacity-75 small" style="letter-spacing: 0.5px;">Total Violations</h6>
                            </div>
                            <h3 class="mb-0 fw-bolder"><?= number_format($summary['total_violations']) ?></h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-2 d-inline-flex">
                            <i class="bi bi-file-earmark-text text-white fs-5 lh-1"></i>
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
            <div class="card premium-kpi premium-reveal text-white border-0" style="--reveal-delay: 0.2s; --float-delay: 0.4s; cursor:pointer; background: linear-gradient(135deg, #10b981 0%, #059669 100%); transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .3s cubic-bezier(0.4, 0, 0.2, 1);" onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 20px 25px -5px rgba(16, 185, 129, 0.4), 0 8px 10px -6px rgba(16, 185, 129, 0.2)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                <div class="card-body p-3 p-xl-4 d-flex flex-column justify-content-between h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="text-uppercase mb-0 fw-bold opacity-75 small" style="letter-spacing: 0.5px;">Total Revenue</h6>
                            </div>
                            <h3 class="mb-0 fw-bolder">₱<?= number_format($summary['total_collected'], 2) ?></h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-2 d-inline-flex">
                            <i class="bi bi-cash-stack text-white fs-5 lh-1"></i>
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
            <div class="card premium-kpi premium-reveal text-white border-0" style="--reveal-delay: 0.3s; --float-delay: 0.8s; cursor:pointer; background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .3s cubic-bezier(0.4, 0, 0.2, 1);" onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 20px 25px -5px rgba(6, 182, 212, 0.4), 0 8px 10px -6px rgba(6, 182, 212, 0.2)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                <div class="card-body p-3 p-xl-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="text-uppercase mb-0 fw-bold opacity-75 small" style="letter-spacing: 0.5px;">Collection Rate</h6>
                            </div>
                            <h3 class="mb-0 fw-bolder"><?= $summary['collection_rate'] ?>%</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-2 d-inline-flex">
                            <i class="bi bi-pie-chart text-white fs-5 lh-1"></i>
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
            <div class="card premium-kpi premium-reveal text-white border-0 h-100" style="--reveal-delay: 0.4s; --float-delay: 1.2s; cursor:pointer; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .3s cubic-bezier(0.4, 0, 0.2, 1);"
                onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 20px 25px -5px rgba(245, 158, 11, 0.4), 0 8px 10px -6px rgba(245, 158, 11, 0.2)'"
                onmouseout="this.style.transform='';this.style.boxShadow=''">
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
            <div class="card border-0 shadow-sm h-100 premium-reveal" style="--reveal-delay: 0.5s;">
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
            <div class="card border-0 shadow-sm h-100 premium-reveal" style="--reveal-delay: 0.6s;">
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
        <!-- Nature of Violation -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100 premium-reveal" style="--reveal-delay: 0.7s;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-diagram-3 me-2 text-info"></i>Nature of Violation</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <canvas id="natureBarChart" height="350"></canvas>
                        </div>
                        <div class="col-md-4 d-flex align-items-center justify-content-center">
                            <div style="width: 100%; max-width: 300px;">
                                <canvas id="natureDoughnutChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status and Role Combined Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 premium-reveal" style="--reveal-delay: 0.8s;">
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

    <!-- Recent Activity -->
    <div class="card border-0 shadow-sm premium-reveal" style="--reveal-delay: 0.9s;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold"><i class="bi bi-activity me-2 text-primary"></i>Recent Violators</h5>
                <?php if (!empty($recent_violations)): ?>
                <small class="text-muted" style="font-size:0.78rem;">Showing last <?= count($recent_violations) ?> records &mdash; click a row to view details</small>
                <?php endif; ?>
            </div>
            <a href="<?= base_url('penalties/all') ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-list-ul me-1"></i>View All
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Ticket ID</th>
                            <th>Driver</th>
                            <th>Violations</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="pe-4">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_violations)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                    No recent violations found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_violations as $v):
                                $rowId = $v['id'] ?? null;
                                $rowUrl = $rowId ? base_url('penalties/view/' . $rowId) : base_url('penalties/all');
                                // Build tooltip: breakdown of per-violation amounts
                                $rawTypes = !empty($v['concatenated_violations']) ? $v['concatenated_violations'] : $v['violation_type'];
                                $types    = explode('||', $rawTypes);
                                $breakdown = [];
                                foreach ($types as $t) {
                                    $parts = explode('::', $t);
                                    $vName = trim($parts[0]);
                                    $vAmt  = isset($parts[1]) && $parts[1] !== '' ? '₱' . number_format((float)$parts[1], 2) : '—';
                                    $breakdown[] = $vName . ': ' . $vAmt;
                                }
                                $tooltipText = implode(' | ', $breakdown);
                            ?>
                            <tr style="cursor:pointer; transition: background .15s;"
                                onclick="window.location='<?= $rowUrl ?>'"
                                title="Click to view details">
                                <td class="ps-4">
                                    <span class="badge bg-dark-subtle text-dark border border-dark-subtle px-2 font-monospace small"><?= esc($v['ticket_id'] ?? '#'.$v['id']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= esc($v['driver_name']) ?></div>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace" style="font-size:0.7rem;"><?= esc($v['license_plate']) ?></span>
                                </td>
                                <td class="small text-dark" style="max-width:220px;">
                                    <?php
                                    echo '<ul class="list-unstyled mb-0">';
                                    foreach ($types as $t) {
                                        $parts = explode('::', $t);
                                        echo '<li class="d-flex align-items-center gap-1"><i class="bi bi-dot text-primary fs-6 lh-1"></i><span>' . esc(trim($parts[0])) . '</span></li>';
                                    }
                                    echo '</ul>';
                                    ?>
                                </td>
                                <td>
                                    <span class="fw-bold text-danger font-monospace"
                                          data-bs-toggle="tooltip"
                                          data-bs-placement="top"
                                          title="<?= esc($tooltipText) ?>">
                                        ₱<?= number_format((float) ($v['total_penalty_sum'] ?? $v['penalty_amount'] ?? 0), 2) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($v['status'] == 'Pending'): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 rounded-pill">Pending</span>
                                    <?php elseif ($v['status'] == 'Paid'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 rounded-pill">Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle px-3 rounded-pill">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small pe-4 text-nowrap"><?= date('M d, Y', strtotime($v['violation_date'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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

        // --- Nature of Violation Charts ---
        const natureData = <?= json_encode($nature_summary) ?>;
        const natureLabels = natureData.map(n => n.violation_type);
        const natureCounts = natureData.map(n => n.count);
        const natureRevenue = natureData.map(n => n.total_amount);
        
        // Generate distinct colors based on existing theme
        const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#f97316', '#ec4899'];
        
        // Horizontal Bar Chart
        new Chart(document.getElementById('natureBarChart'), {
            type: 'bar',
            data: {
                labels: natureLabels,
                datasets: [
                    {
                        label: 'Total Incidents',
                        data: natureCounts,
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                indexAxis: 'y', // Makes it horizontal
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { beginAtZero: true, grid: gridOptions },
                    y: { grid: { display: false } }
                }
            }
        });

        // Doughnut Chart (Percentage Share)
        new Chart(document.getElementById('natureDoughnutChart'), {
            type: 'doughnut',
            data: {
                labels: natureLabels,
                datasets: [{
                    data: natureCounts,
                    backgroundColor: colors.slice(0, natureLabels.length),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) label += ': ';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return label + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

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
<?= $this->endSection() ?>
