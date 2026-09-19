<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>My Profile<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <?php
        $fullName = trim((string) (($officer['firstname'] ?? '') . ' ' . ($officer['lastname'] ?? '')));
        $displayName = $fullName !== '' ? $fullName : (string) ($officer['username'] ?? 'Officer');
        $initials = strtoupper(substr((string) $displayName, 0, 1));
        $profileImage = !empty($officer['profile_image'])
            ? base_url('uploads/profile/' . $officer['profile_image'])
            : null;
    ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-7">
            <h4 class="mb-0"><i class="bi bi-person-vcard me-2 text-primary"></i>Traffic Enforcer Profile</h4>
            <small class="text-muted">View your account details and recorded violator history.</small>
        </div>
        <!-- <div class="col-md-5 text-end">
            <a href="<?= base_url('officer/violations') ?>" class="btn btn-outline-primary me-2">
                <i class="bi bi-plus-circle me-1"></i> Violation
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div> -->
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Enforcer Details</h6>
                </div>
                <div class="card-body">
                    <div class="profile-avatar-wrap mb-4 text-center">
                        <div class="profile-avatar">
                            <?php if ($profileImage): ?>
                                <img src="<?= esc($profileImage) ?>" alt="Profile photo" class="profile-avatar-img">
                            <?php else: ?>
                                <span class="profile-avatar-fallback"><?= esc($initials) ?></span>
                            <?php endif; ?>
                        </div>
                        <form action="<?= base_url('officer/profile/photo') ?>" method="POST" enctype="multipart/form-data" class="mt-3">
                            <?= csrf_field() ?>
                            <div class="input-group input-group-sm">
                                <input type="file" name="profile_photo" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp" required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-camera me-1"></i>Update
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">Upload JPG, PNG, or WEBP (max 2MB).</small>
                        </form>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Username</small>
                        <span class="fw-semibold"><?= esc($officer['username'] ?? '-') ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Email</small>
                        <span><?= esc($officer['email'] ?? '-') ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Role</small>
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2">Traffic Enforcer</span>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase d-block">Status</small>
                        <?php if (($officer['status'] ?? '') === 'active'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Active</span>
                        <?php elseif (($officer['status'] ?? '') === 'inactive'): ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3">Inactive</span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3"><?= ucfirst(esc($officer['status'] ?? 'unknown')) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Record Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-6 col-md-4">
                            <div class="bg-light rounded p-3">
                                <small class="text-muted d-block">Total Records</small>
                                <span class="fs-4 fw-bold"><?= count($records) ?></span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="bg-warning-subtle rounded p-3">
                                <small class="text-muted d-block">Pending</small>
                                <span class="fs-4 fw-bold text-warning-emphasis"><?= $pendingCount ?></span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="bg-success-subtle rounded p-3">
                                <small class="text-muted d-block">Paid</small>
                                <span class="fs-4 fw-bold text-success"><?= $paidCount ?></span>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <small class="text-muted text-uppercase d-block">Total Issued Fines</small>
                    <h5 class="mb-0 text-danger"><?= number_format((float) $totalAmount, 2) ?></h5>
                </div>
            </div>
        </div>
    </div>

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
                    <form method="GET" class="m-0">
                        <select name="year" class="form-select form-select-sm border-0 bg-light" style="width: 80px;" onchange="this.form.submit()">
                            <?php $currentYear = date('Y'); ?>
                            <?php for($y = $currentYear; $y >= $currentYear - 4; $y--): ?>
                                <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Row 2: Nature of Violation -->
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm premium-reveal" style="--reveal-delay: 0.7s;">
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
    </div>
</div>

<style>
    .profile-avatar-wrap {
        border: 1px solid #e9edf7;
        border-radius: 14px;
        background: #f9fbff;
        padding: 1rem;
    }
    .profile-avatar {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        margin: 0 auto;
        border: 4px solid #fff;
        box-shadow: 0 4px 14px rgba(22, 34, 66, 0.14);
        background: linear-gradient(135deg, #5f78ff, #7c4dff);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .profile-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .profile-avatar-fallback {
        font-size: 2.1rem;
        font-weight: 700;
        color: #fff;
        text-transform: uppercase;
    }
    @media print {
        .sidebar, .sidebar-toggle, .btn, .alert, .main-content h1, .breadcrumb {
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
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>
<?= $this->endSection() ?>
