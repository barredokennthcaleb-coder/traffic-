<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>My Profile<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-7">
            <h4 class="mb-0"><i class="bi bi-person-vcard me-2 text-primary"></i>Traffic Enforcer Profile</h4>
            <small class="text-muted">View your account details and recorded violator history.</small>
        </div>
        <div class="col-md-5 text-end">
            <a href="<?= base_url('officer/violations') ?>" class="btn btn-outline-primary me-2">
                <i class="bi bi-plus-circle me-1"></i> Violation
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Enforcer Details</h6>
                </div>
                <div class="card-body">
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
                    <h5 class="mb-0 text-danger">$<?= number_format((float) $totalAmount, 2) ?></h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Violator Records You Issued</h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= count($records) ?> entries</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Ticket ID</th>
                            <th>Violator</th>
                            <th>License Plate</th>
                            <th>Violation Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    You have no violator records yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td class="ps-4"><span class="badge bg-dark-subtle text-dark border border-dark-subtle"><?= esc($record['ticket_id'] ?? 'N/A') ?></span></td>
                                    <td><?= esc(trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? '')) ?: ($record['driver_name'] ?? '-')) ?></td>
                                    <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><?= esc($record['license_plate'] ?? '-') ?></span></td>
                                    <td><?= esc($record['violation_type'] ?? '-') ?></td>
                                    <td class="fw-semibold text-danger">$<?= number_format((float) ($record['penalty_amount'] ?? 0), 2) ?></td>
                                    <td>
                                        <?php if (($record['status'] ?? '') === 'Pending'): ?>
                                            <span class="badge bg-warning rounded-pill px-3">Pending</span>
                                        <?php elseif (($record['status'] ?? '') === 'Paid'): ?>
                                            <span class="badge bg-success rounded-pill px-3">Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger rounded-pill px-3">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= isset($record['violation_date']) ? date('M d, Y h:i A', strtotime($record['violation_date'])) : '-' ?></td>
                                    <td class="text-end pe-4">
                                        <a href="<?= base_url('officer/view/' . $record['id']) ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>View
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
