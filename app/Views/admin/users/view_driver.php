<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Driver Profile: <?= esc($user['username']) ?> - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php 
    $driverImg = !empty($user['profile_image']) && is_file(FCPATH . 'uploads/profile/' . $user['profile_image'])
        ? base_url('uploads/profile/' . $user['profile_image'])
        : null;
    $driverInitials = strtoupper(substr((string) ($user['username'] ?? 'D'), 0, 1));
    $fullName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?: $user['username'];

    $totalViolations = count($violations);
    $pendingCount = count(array_filter($violations, static fn($v) => ($v['status'] ?? '') === 'Pending'));
    $paidCount = count(array_filter($violations, static fn($v) => ($v['status'] ?? '') === 'Paid'));
    $cancelledCount = count(array_filter($violations, static fn($v) => ($v['status'] ?? '') === 'Cancelled'));

    $totalFines = 0.0;
    $paidFines = 0.0;
    foreach ($violations as $v) {
        if (($v['status'] ?? '') !== 'Cancelled') {
            $totalFines += (float) ($v['penalty_amount'] ?? 0);
            if (($v['status'] ?? '') === 'Paid') {
                $paidFines += (float) ($v['penalty_amount'] ?? 0);
            }
        }
    }
    $collectionRate = $totalFines > 0 ? round(($paidFines / $totalFines) * 100, 1) : 0;
?>

<div class="container-fluid py-4 driver-profile-page">
    <!-- Header / Actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="<?= base_url('users?role=driver') ?>" class="text-decoration-none text-muted small fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i>Drivers
                </a>
                <span class="text-muted small">/</span>
                <span class="text-primary small fw-semibold">Profile</span>
            </div>
            <h4 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-circle p-2 d-inline-flex">
                    <i class="bi bi-person-badge fs-5"></i>
                </span>
                <span>Driver: <?= esc($user['username']) ?></span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button onclick="window.print()" class="btn btn-outline-primary shadow-sm px-3">
                <i class="bi bi-printer me-1.5"></i> Print Records
            </button>
            <a href="<?= base_url('users?role=driver') ?>" class="btn btn-secondary shadow-sm px-3">
                <i class="bi bi-arrow-left me-1.5"></i> Back to Drivers
            </a>
        </div>
    </div>

    <!-- Main Grid: Driver Information (Left) + Violation Summary (Right) -->
    <div class="row g-4 mb-4">
        <!-- Left: Driver Identity Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden driver-card">
                <div class="card-header bg-white border-bottom border-light-subtle py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-person-vcard text-primary"></i>
                        <span>Driver Information</span>
                    </h6>
                    <?php if (($user['status'] ?? '') === 'active'): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill small">Active</span>
                    <?php elseif (($user['status'] ?? '') === 'inactive'): ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill small">Inactive</span>
                    <?php else: ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded-pill small"><?= ucfirst(esc($user['status'] ?? 'unknown')) ?></span>
                    <?php endif; ?>
                </div>

                <div class="card-body p-4">
                    <!-- Avatar & Name -->
                    <div class="text-center mb-4 pb-3 border-bottom border-light-subtle">
                        <div class="position-relative mx-auto mb-3" style="width: 100px; height: 100px;">
                            <div class="rounded-circle overflow-hidden border border-3 border-primary-subtle shadow-sm" style="width: 100px; height: 100px; background: #e2e8f0; display: flex; align-items: center; justify-content: center;">
                                <?php if ($driverImg): ?>
                                    <img src="<?= esc($driverImg) ?>" alt="<?= esc($user['username']) ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="w-100 h-100 rounded-circle bg-primary text-white align-items-center justify-content-center fw-bold fs-2 text-uppercase" style="display: none;">
                                        <?= esc($driverInitials) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="w-100 h-100 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-2 text-uppercase">
                                        <?= esc($driverInitials) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <h5 class="mb-1 fw-bold text-dark"><?= esc($fullName) ?></h5>
                        <div class="text-muted small font-monospace mb-2">@<?= esc($user['username']) ?></div>

                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                            Registered Driver
                        </span>
                    </div>

                    <!-- Details List -->
                    <div class="driver-info-list d-flex flex-column gap-3">
                        <div class="info-row d-flex justify-content-between align-items-start py-1 border-bottom border-light-subtle">
                            <span class="text-muted small d-flex align-items-center gap-1.5">
                                <i class="bi bi-envelope text-primary"></i>Email
                            </span>
                            <span class="fw-semibold text-dark small text-end text-break"><?= esc($user['email'] ?? '-') ?></span>
                        </div>

                        <div class="info-row d-flex justify-content-between align-items-start py-1 border-bottom border-light-subtle">
                            <span class="text-muted small d-flex align-items-center gap-1.5">
                                <i class="bi bi-person text-primary"></i>Full Name
                            </span>
                            <span class="fw-semibold text-dark small text-end"><?= esc($fullName) ?></span>
                        </div>

                        <div class="info-row d-flex justify-content-between align-items-start py-1 border-bottom border-light-subtle">
                            <span class="text-muted small d-flex align-items-center gap-1.5">
                                <i class="bi bi-calendar-event text-primary"></i>Age
                            </span>
                            <span class="fw-semibold text-dark small"><?= esc((string) ($user['age'] ?? '-')) ?> years old</span>
                        </div>

                        <div class="info-row d-flex justify-content-between align-items-start py-1 border-bottom border-light-subtle">
                            <span class="text-muted small d-flex align-items-center gap-1.5">
                                <i class="bi bi-geo-alt text-primary"></i>Address
                            </span>
                            <span class="fw-semibold text-dark small text-end"><?= esc($user['address'] ?? '-') ?></span>
                        </div>

                        <?php if (!empty($user['license_number'])): ?>
                        <div class="info-row d-flex justify-content-between align-items-start py-1 border-bottom border-light-subtle">
                            <span class="text-muted small d-flex align-items-center gap-1.5">
                                <i class="bi bi-card-heading text-primary"></i>Driver's License
                            </span>
                            <span class="fw-bold font-monospace text-primary small"><?= esc($user['license_number']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Violation Record Summary -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden driver-card">
                <div class="card-header bg-white border-bottom border-light-subtle py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-graph-up text-primary"></i>
                        <span>Violation & Penalty Summary</span>
                    </h6>
                    <span class="badge bg-light text-muted border border-light-subtle px-2.5 py-1 small">
                        <?= number_format($totalViolations) ?> Total Citations
                    </span>
                </div>

                <div class="card-body p-4">
                    <!-- KPI Cards Grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="kpi-card p-3 rounded-3 bg-light border border-light-subtle text-center h-100">
                                <div class="text-muted small text-uppercase fw-semibold mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Total Infractions</div>
                                <div class="fs-3 fw-bold text-dark mb-0"><?= number_format($totalViolations) ?></div>
                                <div class="text-muted" style="font-size: 0.72rem;">Recorded</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="kpi-card p-3 rounded-3 bg-danger-subtle border border-danger-subtle text-center h-100">
                                <div class="text-danger small text-uppercase fw-semibold mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Pending</div>
                                <div class="fs-3 fw-bold text-danger mb-0"><?= number_format($pendingCount) ?></div>
                                <div class="text-muted" style="font-size: 0.72rem;">Unpaid</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="kpi-card p-3 rounded-3 bg-success-subtle border border-success-subtle text-center h-100">
                                <div class="text-success small text-uppercase fw-semibold mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Settled</div>
                                <div class="fs-3 fw-bold text-success mb-0"><?= number_format($paidCount) ?></div>
                                <div class="text-muted" style="font-size: 0.72rem;">Paid</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="kpi-card p-3 rounded-3 bg-secondary-subtle border border-secondary-subtle text-center h-100">
                                <div class="text-secondary small text-uppercase fw-semibold mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Cancelled</div>
                                <div class="fs-3 fw-bold text-secondary mb-0"><?= number_format($cancelledCount) ?></div>
                                <div class="text-muted" style="font-size: 0.72rem;">Void</div>
                            </div>
                        </div>
                    </div>

                    <!-- Fines & Settlement Rate Box -->
                    <div class="p-3.5 p-md-4 rounded-4 bg-light border border-light-subtle">
                        <div class="row align-items-center g-3">
                            <div class="col-md-6 border-end-md">
                                <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                    Total Assessed Penalties
                                </small>
                                <h3 class="fw-extrabold text-danger mb-1 font-monospace">
                                    ₱<?= number_format((float) $totalFines, 2) ?>
                                </h3>
                                <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                                    Settled: ₱<?= number_format($paidFines, 2) ?> &bull; Pending: ₱<?= number_format(max(0, $totalFines - $paidFines), 2) ?>
                                </p>
                            </div>

                            <div class="col-md-6 ps-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <span class="small fw-semibold text-dark">Payment Compliance</span>
                                    <span class="small fw-bold text-success font-monospace"><?= $collectionRate ?>%</span>
                                </div>
                                <div class="progress" style="height: 10px; border-radius: 999px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= min(100, max(0, $collectionRate)) ?>%;" aria-valuenow="<?= $collectionRate ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 text-muted" style="font-size: 0.72rem;">
                                    <span>Paid: <strong><?= number_format($paidCount) ?></strong></span>
                                    <span>Unpaid: <strong><?= number_format($pendingCount) ?></strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom: Violation History Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden driver-card">
        <div class="card-header bg-white border-bottom border-light-subtle py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary"></i>
                    <span>Violation History</span>
                </h6>
                <small class="text-muted">Recorded citations for driver <?= esc($user['username']) ?></small>
            </div>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1.5 rounded-pill fw-semibold">
                <?= number_format($totalViolations) ?> total records
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Ticket ID</th>
                            <th>Violation Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Citation Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($violations)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <div class="py-4">
                                        <i class="bi bi-check-circle fs-1 text-success opacity-50 d-block mb-2"></i>
                                        <h6 class="fw-semibold text-dark mb-1">Clean Driving Record</h6>
                                        <p class="small text-muted mb-0">No recorded traffic violations for this driver account.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($violations as $violation): ?>
                                <?php
                                    $vStatus = strtolower($violation['status'] ?? 'pending');
                                    $isPaid = $vStatus === 'paid';
                                    $isCancelled = $vStatus === 'cancelled';
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-dark-subtle text-dark border border-dark-subtle px-2.5 py-1 font-monospace">
                                            <?= esc($violation['ticket_id'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark"><?= esc($violation['violation_type']) ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-bold font-monospace <?= $isPaid ? 'text-success' : 'text-danger' ?>">
                                            ₱<?= number_format((float) ($violation['penalty_amount'] ?? 0), 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($vStatus === 'pending'): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">Pending</span>
                                        <?php elseif ($isPaid): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?= isset($violation['violation_date']) ? date('M d, Y h:i A', strtotime($violation['violation_date'])) : '-' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .driver-card {
        border-radius: 16px;
    }
    .kpi-card {
        transition: transform 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
    }
    @media print {
        .sidebar, .sidebar-toggle, .btn, .alert, .main-content h1, .breadcrumb, .no-print {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .container-fluid {
            padding: 0 !important;
        }
    }
</style>

<?= $this->endSection() ?>
