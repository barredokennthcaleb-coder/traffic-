<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Admin Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Quick Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white premium-kpi premium-reveal premium-pulse" style="--reveal-delay:.03s; --shine-delay:.3s; --float-delay:0s;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small">Total Revenue</h6>
                            <h3 class="mb-0 fw-bold">$<?= number_format($summary['total_collected'], 2) ?></h3>
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
            <div class="card border-0 shadow-sm bg-success text-white premium-kpi premium-reveal premium-pulse" style="--reveal-delay:.09s; --shine-delay:1.1s; --float-delay:.8s;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small">Collection Rate</h6>
                            <h3 class="mb-0 fw-bold"><?= $summary['collection_rate'] ?>%</h3>
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
            <div class="card border-0 shadow-sm bg-info text-white premium-kpi premium-reveal premium-pulse" style="--reveal-delay:.15s; --shine-delay:1.9s; --float-delay:1.6s;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small">Total Violations</h6>
                            <h3 class="mb-0 fw-bold"><?= number_format($summary['total_violations']) ?></h3>
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
            <div class="card border-0 shadow-sm bg-warning text-dark premium-kpi premium-reveal premium-pulse" style="--reveal-delay:.21s; --shine-delay:2.7s; --float-delay:2.4s;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small">Top Violation</h6>
                            <h4 class="mb-0 fw-bold text-truncate" style="max-width: 150px;"><?= esc($summary['top_violation']) ?></h4>
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
        <div class="card border-0 shadow-sm h-100 premium-reveal analytics-tilt" style="--reveal-delay:.28s;">
                <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-calendar3 me-2 text-primary"></i>Monthly Violation Trend</h5>
                <small class="text-muted">Track issuance and revenue movement month by month.</small>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <!-- Status Pie Chart -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 premium-reveal analytics-tilt" style="--reveal-delay:.35s;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-pie-chart me-2 text-success"></i>Violation Status</h5>
                    <small class="text-muted">Current split of pending, paid, and cancelled tickets.</small>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="width: 100%; max-width: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Violation Types Bar Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100 premium-reveal analytics-tilt" style="--reveal-delay:.42s;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2 text-info"></i>Violations by Type</h5>
                    <small class="text-muted">Most frequent violations and their revenue contribution.</small>
                </div>
                <div class="card-body">
                    <canvas id="typeChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Users by Role -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 premium-reveal analytics-tilt" style="--reveal-delay:.49s;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-people me-2 text-secondary"></i>Users by Role</h5>
                    <small class="text-muted">Distribution of accounts in the system.</small>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="width: 100%; max-width: 380px;">
                        <canvas id="roleChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card border-0 shadow-sm premium-reveal" style="--reveal-delay:.56s;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="bi bi-activity me-2 text-primary"></i>Recent Violations</h5>
            <a href="<?= base_url('penalties/all') ?>" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-premium-mobile">
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
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <div class="empty-state-title">No Recent Violations</div>
                                        <div>New records will appear here once violations are issued.</div>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_violations as $v): ?>
                            <tr>
                                <td class="ps-4" data-label="Ticket ID">
                                    <span class="badge bg-dark-subtle text-dark border border-dark-subtle px-2 font-monospace"><?= esc($v['ticket_id'] ?? '#'.$v['id']) ?></span>
                                </td>
                                <td data-label="Driver Information">
                                    <div class="fw-bold"><?= esc($v['driver_name']) ?></div>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small font-monospace"><?= esc($v['license_plate']) ?></span>
                                </td>
                                <td class="small fw-semibold text-muted" data-label="Violation Type"><?= esc($v['violation_type']) ?></td>
                                <td data-label="Amount"><span class="fw-bold text-danger">$<?= number_format($v['penalty_amount'], 2) ?></span></td>
                                <td data-label="Status">
                                    <?php if ($v['status'] == 'Pending'): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 rounded-pill">Pending</span>
                                    <?php elseif ($v['status'] == 'Paid'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 rounded-pill">Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle px-3 rounded-pill">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small pe-4" data-label="Date"><?= date('M d, Y', strtotime($v['violation_date'])) ?></td>
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
    const isDarkThemeActive = () => {
        const explicitTheme = document.documentElement.getAttribute('data-theme');
        if (explicitTheme === 'dark') return true;
        if (explicitTheme === 'light') return false;
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    };

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

    let trendChartInstance = null;
    let statusChartInstance = null;
    let typeChartInstance = null;
    let roleChartInstance = null;

    const renderDashboardCharts = () => {
        const prefersDark = isDarkThemeActive();
        const chartTextColor = prefersDark ? '#c6d4f7' : '#4d5678';
        const chartGridColor = prefersDark ? 'rgba(125, 153, 215, 0.20)' : 'rgba(101, 119, 170, 0.12)';
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const typeCtx = document.getElementById('typeChart').getContext('2d');

        const trendGradient = trendCtx.createLinearGradient(0, 0, 0, 320);
        if (prefersDark) {
            trendGradient.addColorStop(0, 'rgba(124, 163, 255, 0.48)');
            trendGradient.addColorStop(1, 'rgba(124, 163, 255, 0.04)');
        } else {
            trendGradient.addColorStop(0, 'rgba(13, 110, 253, 0.36)');
            trendGradient.addColorStop(1, 'rgba(13, 110, 253, 0.03)');
        }

        const typeCountGradient = typeCtx.createLinearGradient(0, 0, 0, 300);
        const typeRevenueGradient = typeCtx.createLinearGradient(0, 0, 0, 300);
        if (prefersDark) {
            typeCountGradient.addColorStop(0, 'rgba(111, 223, 247, 0.95)');
            typeCountGradient.addColorStop(1, 'rgba(79, 210, 245, 0.30)');
            typeRevenueGradient.addColorStop(0, 'rgba(124, 163, 255, 0.95)');
            typeRevenueGradient.addColorStop(1, 'rgba(124, 163, 255, 0.30)');
        } else {
            typeCountGradient.addColorStop(0, 'rgba(13, 202, 240, 0.95)');
            typeCountGradient.addColorStop(1, 'rgba(13, 202, 240, 0.30)');
            typeRevenueGradient.addColorStop(0, 'rgba(13, 110, 253, 0.95)');
            typeRevenueGradient.addColorStop(1, 'rgba(13, 110, 253, 0.30)');
        }

        trendChartInstance?.destroy();
        statusChartInstance?.destroy();
        typeChartInstance?.destroy();
        roleChartInstance?.destroy();

        trendChartInstance = new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Violations Issued',
                        data: trendCounts,
                        borderColor: prefersDark ? '#7ca3ff' : '#0d6efd',
                        backgroundColor: trendGradient,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Revenue Collected ($)',
                        data: trendRevenue,
                        borderColor: prefersDark ? '#4ad3a0' : '#198754',
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
                animation: { duration: 1450, easing: 'easeOutQuart' },
                plugins: { legend: { labels: { color: chartTextColor } } },
                scales: {
                    x: { ticks: { color: chartTextColor }, grid: { color: chartGridColor } },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Count', color: chartTextColor },
                        ticks: { color: chartTextColor },
                        grid: { color: chartGridColor }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        title: { display: true, text: 'Revenue ($)', color: chartTextColor },
                        ticks: { color: chartTextColor },
                        grid: { drawOnChartArea: false, color: chartGridColor }
                    }
                }
            }
        });

        statusChartInstance = new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: statusLabels.map(label => {
                        if (label === 'Pending') return prefersDark ? '#ffd56a' : '#ffc107';
                        if (label === 'Paid') return prefersDark ? '#4ad3a0' : '#198754';
                        return prefersDark ? '#ff8a9b' : '#dc3545';
                    })
                }]
            },
            options: {
                responsive: true,
                animation: { animateRotate: true, duration: 1300, easing: 'easeOutQuart' },
                plugins: { legend: { position: 'bottom', labels: { color: chartTextColor } } }
            }
        });

        typeChartInstance = new Chart(document.getElementById('typeChart'), {
            type: 'bar',
            data: {
                labels: typeLabels,
                datasets: [
                    { label: 'Count', data: typeCounts, backgroundColor: typeCountGradient, borderRadius: 8 },
                    { label: 'Revenue ($)', data: typeRevenue, backgroundColor: typeRevenueGradient, borderRadius: 8 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 1200, easing: 'easeOutQuart' },
                plugins: { legend: { labels: { color: chartTextColor } } },
                scales: {
                    x: { ticks: { color: chartTextColor }, grid: { color: chartGridColor } },
                    y: { beginAtZero: true, ticks: { color: chartTextColor }, grid: { color: chartGridColor } }
                }
            }
        });

        roleChartInstance = new Chart(document.getElementById('roleChart'), {
            type: 'doughnut',
            data: {
                labels: roleLabels,
                datasets: [{
                    data: roleCounts,
                    backgroundColor: (prefersDark
                        ? ['#7ca3ff', '#4ad3a0', '#ffd56a', '#9aa6c8', '#ff8a9b']
                        : ['#0d6efd', '#198754', '#ffc107', '#6c757d', '#dc3545']
                    ).slice(0, roleLabels.length),
                }]
            },
            options: {
                responsive: true,
                animation: { animateRotate: true, duration: 1200, easing: 'easeOutQuart' },
                plugins: { legend: { position: 'bottom', labels: { color: chartTextColor } } }
            }
        });
    };

    renderDashboardCharts();
    window.addEventListener('themechange', renderDashboardCharts);

    // Subtle 3D tilt interaction for analytics cards.
    const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const tiltCards = document.querySelectorAll('.analytics-tilt');
    tiltCards.forEach((card) => {
        card.addEventListener('mousemove', (e) => {
            if (reducedMotion) return;
            const rect = card.getBoundingClientRect();
            const px = (e.clientX - rect.left) / rect.width;
            const py = (e.clientY - rect.top) / rect.height;
            const rotateY = (px - 0.5) * 6;
            const rotateX = (0.5 - py) * 6;
            card.style.transform = `perspective(900px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateY(-4px) scale(1.01)`;
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });
</script>
<?= $this->endSection() ?>
