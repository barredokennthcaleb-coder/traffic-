<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Payment History<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4">Financial Transactions</h2>
    <a href="/penalties" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left me-1"></i> Back to Pending
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Transaction ID</th>
                        <th>Driver</th>
                        <th>Violation</th>
                        <th>Method</th>
                        <th>Amount Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No payment history found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><?= date('M d, Y H:i', strtotime($p['payment_date'])) ?></td>
                            <td><code><?= $p['transaction_id'] ?: 'N/A' ?></code></td>
                            <td><?= $p['driver_name'] ?> (<?= $p['license_plate'] ?>)</td>
                            <td><?= $p['violation_type'] ?></td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= $p['payment_method'] ?>
                                </span>
                            </td>
                            <td><span class="text-success font-monospace">+$<?= number_format($p['amount_paid'], 2) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
