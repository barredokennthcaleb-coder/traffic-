<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Admin Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Quick Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card premium-kpi premium-reveal bg-primary text-white" style="--reveal-delay: 0.1s; --float-delay: 0s;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 fw-bold opacity-75 small">Total Revenue</h6>
                            <h3 class="mb-0 fw-bold">$<?= number_format($summary['total_collected'], 2) ?></h3>
                        </div>
                        <i class="bi bi-cash-stack fs-1 opacity-25"></i>
                    </div>
                    <div class="mt-3 small">
                        <span class="opacity-75">Outstanding: $<?= number_format($summary['total_pending'], 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card premium-kpi premium-reveal bg-success text-white" style="--reveal-delay: 0.2s; --float-delay: 0.4s;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 fw-bold opacity-75 small">Collection Rate</h6>
                            <h3 class="mb-0 fw-bold"><?= $summary['collection_rate'] ?>%</h3>
                        </div>
                        <i class="bi bi-graph-up-arrow fs-1 opacity-25"></i>
                    </div>
                    <div class="mt-3 small">
                        <span class="opacity-75">Paid vs Total Violations</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card premium-kpi premium-reveal bg-info text-white" style="--reveal-delay: 0.3s; --float-delay: 0.8s;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 fw-bold opacity-75 small">Total Violations</h6>
                            <h3 class="mb-0 fw-bold"><?= number_format($summary['total_violations']) ?></h3>
                        </div>
                        <i class="bi bi-file-earmark-text fs-1 opacity-25"></i>
                    </div>
                    <div class="mt-3 small">
                        <span class="opacity-75">Registered records</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card premium-kpi premium-reveal bg-warning text-dark" style="--reveal-delay: 0.4s; --float-delay: 1.2s;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 fw-bold opacity-75 small">Top Violation</h6>
                            <h4 class="mb-0 fw-bold text-truncate" style="max-width: 150px;"><?= esc($summary['top_violation']) ?></h4>
                        </div>
                        <i class="bi bi-exclamation-triangle fs-1 opacity-25"></i>
                    </div>
                    <div class="mt-3 small">
                        <span class="opacity-75">Most frequent type</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Combined Analytics Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold" id="chartTitle">
                        <i class="bi bi-calendar3 me-2 text-primary"></i>Monthly Violation Trend
                    </h5>
                    <div class="btn-group btn-group-sm shadow-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="btnTrend" title="Show Monthly Trend">
                            <i class="bi bi-graph-up"></i> Trend
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="btnType" title="Show Violations by Type">
                            <i class="bi bi-bar-chart"></i> Types
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="mainAnalyticsChart" height="350"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Status and Role Combined Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold" id="rightChartTitle">
                        <i class="bi bi-pie-chart me-2 text-success"></i>Violation Status
                    </h5>
                    <div class="btn-group btn-group-sm shadow-sm" role="group">
                        <button type="button" class="btn btn-outline-success active" id="btnStatus" title="Show Violation Status">
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
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="bi bi-activity me-2 text-primary"></i>Recent Violations</h5>
            <a href="<?= base_url('penalties/all') ?>" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Ticket ID</th>
                            <th>Driver Information</th>
                            <th>Violation Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="pe-4">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_violations)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No recent violations found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_violations as $v): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-dark-subtle text-dark border border-dark-subtle px-2 font-monospace"><?= esc($v['ticket_id'] ?? '#'.$v['id']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= esc($v['driver_name']) ?></div>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small font-monospace"><?= esc($v['license_plate']) ?></span>
                                </td>
                                <td class="small fw-semibold text-muted"><?= esc($v['violation_type']) ?></td>
                                <td><span class="fw-bold text-danger">$<?= number_format($v['penalty_amount'], 2) ?></span></td>
                                <td>
                                    <?php if ($v['status'] == 'Pending'): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 rounded-pill">Pending</span>
                                    <?php elseif ($v['status'] == 'Paid'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 rounded-pill">Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle px-3 rounded-pill">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small pe-4"><?= date('M d, Y', strtotime($v['violation_date'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Prepare Data
    const monthlyData = <?= json_encode($monthly_trend) ?>;
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const trendCounts = Object.values(monthlyData).map(m => m.count);
    const trendRevenue = Object.values(monthlyData).map(m => m.revenue);

    const statusData = <?= json_encode($status_distribution) ?>;
    const statusLabels = statusData.map(s => s.status);
    const statusValues = statusData.map(s => s.count);

    const typeData = <?= json_encode($violation_types) ?>;
    const typeLabels = typeData.map(t => t.violation_type);
    const typeCounts = typeData.map(t => t.count);
    const typeRevenue = typeData.map(t => t.total_amount);

    const roleData = <?= json_encode($user_role_distribution ?? []) ?>;
    const roleLabels = roleData.map(r => r.role);
    const roleCounts = roleData.map(r => r.count);

    // Main Chart Controller
    let mainChart;
    const chartCtx = document.getElementById('mainAnalyticsChart').getContext('2d');
    const chartTitle = document.getElementById('chartTitle');

    function showTrendChart() {
        if (mainChart) mainChart.destroy();
        
        chartTitle.innerHTML = '<i class="bi bi-calendar3 me-2 text-primary"></i>Monthly Violation Trend';
        document.getElementById('btnTrend').classList.add('active');
        document.getElementById('btnType').classList.remove('active');

        mainChart = new Chart(chartCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Violations Issued',
                        data: trendCounts,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Revenue Collected ($)',
                        data: trendRevenue,
                        borderColor: '#198754',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Count' } },
                    y1: { beginAtZero: true, position: 'right', title: { display: true, text: 'Revenue ($)' }, grid: { drawOnChartArea: false } }
                }
            }
        });
    }

    function showTypeChart() {
        if (mainChart) mainChart.destroy();

        chartTitle.innerHTML = '<i class="bi bi-bar-chart me-2 text-info"></i>Violations by Type';
        document.getElementById('btnType').classList.add('active');
        document.getElementById('btnTrend').classList.remove('active');

        mainChart = new Chart(chartCtx, {
            type: 'bar',
            data: {
                labels: typeLabels,
                datasets: [
                    {
                        label: 'Count',
                        data: typeCounts,
                        backgroundColor: '#0dcaf0'
                    },
                    {
                        label: 'Revenue ($)',
                        data: typeRevenue,
                        backgroundColor: '#0d6efd'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Initialize with Trend
    showTrendChart();

    // Event Listeners
    document.getElementById('btnTrend').addEventListener('click', showTrendChart);
    document.getElementById('btnType').addEventListener('click', showTypeChart);

    // Secondary Chart Controller (Right Side)
    let secondaryChart;
    const secondaryCtx = document.getElementById('secondaryAnalyticsChart').getContext('2d');
    const rightChartTitle = document.getElementById('rightChartTitle');

    function showStatusChart() {
        if (secondaryChart) secondaryChart.destroy();

        rightChartTitle.innerHTML = '<i class="bi bi-pie-chart me-2 text-success"></i>Violation Status';
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
</script>
<?= $this->endSection() ?>
