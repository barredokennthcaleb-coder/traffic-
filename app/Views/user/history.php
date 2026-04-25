<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Payment History - Driver Portal<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0"><i class="bi bi-receipt me-2 text-primary"></i>Payment History</h4>
            <p class="text-muted small mb-0">Records of all your settled traffic violation payments.</p>
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
                            <th>Amount Paid</th>
                            <th>Payment Method</th>
                            <th>Date Paid</th>
                            <th class="text-end">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($violations)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No payment history found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($violations as $violation): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= esc($violation['ticket_id'] ?? 'N/A') ?></span></td>
                                <td><?= esc($violation['violation_type']) ?></td>
                                <td><strong class="text-success">$<?= number_format($violation['penalty_amount'], 2) ?></strong></td>
                                <td><?= esc($violation['payment_method']) ?></td>
                                <td><?= date('M d, Y', strtotime($violation['paid_date'])) ?></td>
                                <td class="text-end">
                                    <a href="<?= base_url('user/receipt/' . esc($violation['ticket_id'])) ?>" class="btn btn-sm btn-outline-primary px-3">
                                        <i class="bi bi-printer me-1"></i> View & Print
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

<?= $this->endSection() ?>
