<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>My Violation Records - Driver Portal<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>My Violation Records</h4>
            <p class="text-muted small mb-0">List of all your traffic violations, including pending and paid.</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= base_url('user/dashboard') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Ticket ID</th>
                            <th>Violation Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Violation Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($violations)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No violation records found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($violations as $violation): ?>
                            <tr>
                                <td><span class="badge bg-dark"><?= esc($violation['ticket_id'] ?? 'N/A') ?></span></td>
                                <td>
                                    <div class="fw-semibold"><?= esc($violation['violation_type']) ?></div>
                                    <small class="text-muted"><?= esc($violation['location'] ?? 'Location not specified') ?></small>
                                </td>
                                <td><strong class="<?= $violation['status'] == 'Pending' ? 'text-danger' : 'text-success' ?>">$<?= number_format($violation['penalty_amount'], 2) ?></strong></td>
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
                                <td class="text-end">
                                    <?php if ($violation['status'] == 'Pending'): ?>
                                        <a href="/user/pay/<?= esc($violation['ticket_id']) ?>" class="btn btn-sm btn-success px-3">
                                            <i class="bi bi-credit-card me-1"></i> Pay Now
                                        </a>
                                    <?php elseif ($violation['status'] == 'Paid'): ?>
                                        <a href="/user/receipt/<?= esc($violation['ticket_id']) ?>" class="btn btn-sm btn-info text-white px-3">
                                            <i class="bi bi-receipt me-1"></i> Receipt
                                        </a>
                                    <?php endif; ?>
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

<?= $this->endSection() ?>
