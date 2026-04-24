<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small">Total Revenue</h6>
                            <h3 class="mb-0">$<?= number_format($summary['total_collected'], 2) ?></h3>
                        </div>
                        <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                    </div>
                    <div class="mt-3 small">
                        <span class="opacity-75">Outstanding: $<?= number_format($summary['total_pending'], 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small">Collection Rate</h6>
                            <h3 class="mb-0"><?= $summary['collection_rate'] ?>%</h3>
                        </div>
                        <i class="bi bi-graph-up-arrow fs-1 opacity-50"></i>
                    </div>
                    <div class="mt-3 small">
                        <span class="opacity-75">Paid vs Total Violations</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small">Total Violations</h6>
                            <h3 class="mb-0"><?= number_format($summary['total_violations']) ?></h3>
                        </div>
                        <i class="bi bi-file-earmark-text fs-1 opacity-50"></i>
                    </div>
                    <div class="mt-3 small">
                        <span class="opacity-75">Registered records</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small">Top Violation</h6>
                            <h4 class="mb-0 text-truncate" style="max-width: 150px;"><?= esc($summary['top_violation']) ?></h4>
                        </div>
                        <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                    </div>
                    <div class="mt-3 small">
                        <span class="opacity-75">Most frequent type</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Monthly Trend Line Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-calendar3 me-2 text-primary"></i>Monthly Violation Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <!-- Status Pie Chart -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-pie-chart me-2 text-success"></i>Violation Status</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="width: 100%; max-width: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Violation Types Bar Chart -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-bar-chart me-2 text-info"></i>Violations by Type</h5>
                </div>
                <div class="card-body">
                    <canvas id="typeChart" height="100"></canvas>
                </div>
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

    // Trend Chart
    new Chart(document.getElementById('trendChart'), {
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

    // Status Chart
    new Chart(document.getElementById('statusChart'), {
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

    // Type Chart
    new Chart(document.getElementById('typeChart'), {
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
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
<?= $this->endSection() ?>
