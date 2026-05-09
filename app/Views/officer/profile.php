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
                        <div class="col-6 col-md-3">
                            <div class="bg-light rounded p-3">
                                <small class="text-muted d-block">Total Records</small>
                                <span class="fs-4 fw-bold"><?= count($records) ?></span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-warning-subtle rounded p-3">
                                <small class="text-muted d-block">Pending</small>
                                <span class="fs-4 fw-bold text-warning-emphasis"><?= $pendingCount ?></span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-success-subtle rounded p-3">
                                <small class="text-muted d-block">Paid</small>
                                <span class="fs-4 fw-bold text-success"><?= $paidCount ?></span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-danger-subtle rounded p-3">
                                <small class="text-muted d-block">Cancelled</small>
                                <span class="fs-4 fw-bold text-danger"><?= $cancelledCount ?></span>
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

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Status Distribution</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="height: 250px; width: 100%;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Monthly Trend (<?= date('Y') ?>)</h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px; width: 100%;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Top 5 Violation Types Issued</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="typeChart"></canvas>
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
    // 1. Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Paid', 'Cancelled'],
            datasets: [{
                data: [<?= $pendingCount ?>, <?= $paidCount ?>, <?= $cancelledCount ?>],
                backgroundColor: ['#ffc107', '#198754', '#dc3545'],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
            },
            cutout: '70%'
        }
    });

    // 2. Monthly Trend Chart
    <?php
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $trendData = array_fill(0, 12, 0);
        foreach ($monthlyTrend as $row) {
            $trendData[(int)$row['month'] - 1] = (int)$row['count'];
        }
    ?>
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($months) ?>,
            datasets: [{
                label: 'Violations Issued',
                data: <?= json_encode($trendData) ?>,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#6366f1',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });

    // 3. Violation Types Chart
    <?php
        $typeLabels = [];
        $typeCounts = [];
        foreach ($violationsByType as $row) {
            $typeLabels[] = $row['violation_type'];
            $typeCounts[] = (int)$row['count'];
        }
    ?>
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($typeLabels) ?>,
            datasets: [{
                label: 'Frequency',
                data: <?= json_encode($typeCounts) ?>,
                backgroundColor: [
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(244, 63, 94, 0.8)',
                    'rgba(249, 115, 22, 0.8)'
                ],
                borderRadius: 8,
                maxBarThickness: 40
            }]
        },
        options: {
            indexAxis: 'y',
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { display: false } },
                y: { grid: { display: false } }
            }
        }
    });
});
</script>
<?= $this->endSection() ?>
