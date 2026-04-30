<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Driver Violation Records - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    @media print {
        .sidebar, .sidebar-toggle, .btn, .alert, .main-content h1, .breadcrumb, .no-print {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
        }
        .container-fluid {
            padding: 0 !important;
        }
        body {
            background-color: white !important;
        }
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 30px;
        }
    }
    .print-header {
        display: none;
    }
</style>

<div class="container-fluid py-4">
    <div class="print-header">
        <h2>Traffic Violation Management System</h2>
        <h4>Official Driver Violation Report</h4>
        <hr>
    </div>

    <div class="row mb-4 no-print">
        <div class="col-md-6">
            <h4 class="mb-0"><i class="bi bi-person-badge me-2 text-primary"></i>Driver: <?= esc($user['username']) ?></h4>
        </div>
        <div class="col-md-6 text-end">
            <button onclick="window.print()" class="btn btn-outline-primary me-2">
                <i class="bi bi-printer me-1"></i> Print Records
            </button>
            <a href="<?= base_url('users?role=driver') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Users
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Driver Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Username</small>
                        <span class="fw-bold"><?= esc($user['username']) ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Email Address</small>
                        <span><?= esc($user['email']) ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Full Name</small>
                        <span><?= esc(trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?: '-') ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Age</small>
                        <span><?= esc((string) ($user['age'] ?? '-')) ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Address</small>
                        <span><?= esc($user['address'] ?? '-') ?></span>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted text-uppercase d-block">Status</small>
                        <?php if ($user['status'] == 'active'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 rounded-pill">Active</span>
                        <?php elseif ($user['status'] == 'inactive'): ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 rounded-pill">Inactive</span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 rounded-pill"><?= ucfirst(esc($user['status'])) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Violation Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4 border-end">
                            <h3 class="mb-0"><?= count($violations) ?></h3>
                            <small class="text-muted">Total Violations</small>
                        </div>
                        <div class="col-4 border-end">
                            <h3 class="mb-0 text-warning">
                                <?php 
                                    echo count(array_filter($violations, function($v) {
                                        return $v['status'] === 'Pending';
                                    }));
                                ?>
                            </h3>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col-4">
                            <h3 class="mb-0 text-success">
                                <?php 
                                    echo count(array_filter($violations, function($v) {
                                        return $v['status'] === 'Paid';
                                    }));
                                ?>
                            </h3>
                            <small class="text-muted">Paid</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Violation History</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Ticket ID</th>
                            <th>Violation Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($violations)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    No violations found for this driver.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($violations as $violation): ?>
                            <tr>
                                <td><span class="badge bg-dark"><?= esc($violation['ticket_id'] ?? 'N/A') ?></span></td>
                                <td><?= esc($violation['violation_type']) ?></td>
                                <td>$<?= number_format($violation['penalty_amount'], 2) ?></td>
                                <td>
                                    <?php if ($violation['status'] == 'Pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php elseif ($violation['status'] == 'Paid'): ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($violation['violation_date'])) ?></td>
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
