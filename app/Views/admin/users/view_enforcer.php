<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Traffic Enforcer Profile - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row mb-4 no-print">
        <div class="col-md-6">
            <h4 class="mb-0"><i class="bi bi-person-vcard me-2 text-primary"></i>Enforcer: <?= esc($user['username']) ?></h4>
            <small class="text-muted">Profile and issued violation records</small>
        </div>
        <div class="col-md-6 text-end">
            <button onclick="window.print()" class="btn btn-outline-primary me-2">
                <i class="bi bi-printer me-1"></i> Print Profile
            </button>
            <a href="<?= base_url('users?role=enforcer') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Enforcers
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Enforcer Profile</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Username</small>
                        <span class="fw-semibold"><?= esc($user['username']) ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Email</small>
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
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Role</small>
                        <span class="badge bg-info-subtle text-info border border-info-subtle text-uppercase px-2">Traffic Enforcer</span>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted text-uppercase d-block">Status</small>
                        <?php if (($user['status'] ?? '') === 'active'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 rounded-pill">Active</span>
                        <?php elseif (($user['status'] ?? '') === 'inactive'): ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 rounded-pill">Inactive</span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 rounded-pill"><?= ucfirst(esc($user['status'] ?? 'unknown')) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Record Overview</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded bg-light">
                                <div class="small text-muted">Total Records</div>
                                <div class="fs-4 fw-bold"><?= count($records) ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded bg-warning-subtle">
                                <div class="small text-muted">Pending</div>
                                <div class="fs-4 fw-bold text-warning-emphasis"><?= $pendingCount ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded bg-success-subtle">
                                <div class="small text-muted">Paid</div>
                                <div class="fs-4 fw-bold text-success"><?= $paidCount ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded bg-danger-subtle">
                                <div class="small text-muted">Cancelled</div>
                                <div class="fs-4 fw-bold text-danger"><?= $cancelledCount ?></div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <small class="text-muted text-uppercase d-block">Total Issued Amount</small>
                        <h5 class="mb-0 text-danger"><?= number_format((float) $totalIssuedAmount, 2) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Issued Violation Records</h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= count($records) ?> total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-premium-mobile">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Ticket ID</th>
                            <th>Violator</th>
                            <th>Violation Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-4 no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No issued records yet for this enforcer.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td class="ps-4" data-label="Ticket ID"><span class="badge bg-dark-subtle text-dark border border-dark-subtle"><?= esc($record['ticket_id'] ?? 'N/A') ?></span></td>
                                    <td data-label="Violator"><?= esc(trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? '')) ?: ($record['driver_name'] ?? '-')) ?></td>
                                    <td data-label="Violation Type"><?= esc($record['violation_type'] ?? '-') ?></td>
                                    <td class="fw-semibold text-danger" data-label="Amount"><?= number_format((float) ($record['penalty_amount'] ?? 0), 2) ?></td>
                                    <td data-label="Status">
                                        <?php if (($record['status'] ?? '') === 'Pending'): ?>
                                            <span class="badge bg-warning rounded-pill px-3">Pending</span>
                                        <?php elseif (($record['status'] ?? '') === 'Paid'): ?>
                                            <span class="badge bg-success rounded-pill px-3">Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger rounded-pill px-3">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Date"><?= isset($record['violation_date']) ? date('M d, Y h:i A', strtotime($record['violation_date'])) : '-' ?></td>
                                    <td class="text-end pe-4 no-print" data-label="Action">
                                        <a href="<?= base_url('officer/view/' . $record['id']) ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i> View
                                        </a>
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
    @media print {
        .sidebar, .sidebar-toggle, .btn, .alert, .main-content h1, .breadcrumb, .no-print {
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
