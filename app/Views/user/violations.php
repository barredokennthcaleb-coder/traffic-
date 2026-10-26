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
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Ticket ID</th>
                            <th>Violation Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Violation Date</th>
                            <th class="text-end pe-4">Actions</th>
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
                                <td class="ps-4"><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 font-monospace"><?= esc($violation['ticket_id'] ?? 'N/A') ?></span></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= esc($violation['violation_type']) ?></div>
                                    <small class="text-muted"><?= esc($violation['location'] ?? 'Location not specified') ?></small>
                                </td>
                                <td><span class="badge bg-success-subtle text-success border border-success-subtle px-3 font-monospace">₱<?= number_format($violation['penalty_amount'], 2) ?></span></td>
                                <td>
                                    <?php if ($violation['status'] == 'Pending'): ?>
                                        <span class="badge bg-warning text-dark rounded-pill px-3">Pending</span>
                                    <?php elseif ($violation['status'] == 'Paid'): ?>
                                        <span class="badge bg-success rounded-pill px-3">Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill px-3">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($violation['violation_date'])) ?></td>
                                <td class="text-end pe-4">
                                    <a href="<?= base_url('user/view/' . esc($violation['ticket_id'])) ?>" class="btn btn-sm btn-outline-primary me-1" title="View Ticket">
                                        <i class="bi bi-eye me-1"></i> View Ticket
                                    </a>
                                    <?php if ($violation['status'] == 'Pending'): ?>
                                        <a href="<?= base_url('user/pay/' . esc($violation['ticket_id'])) ?>" class="btn btn-sm btn-success px-3">
                                            <i class="bi bi-credit-card me-1"></i> Pay Now
                                        </a>
                                    <?php elseif ($violation['status'] == 'Paid'): ?>
                                        <a href="<?= base_url('user/receipt/' . esc($violation['ticket_id'])) ?>" class="btn btn-sm btn-info text-white px-3">
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
