<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Penalty Management<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4">Pending Penalties</h2>
    <a href="/penalties/history" class="btn btn-outline-primary">
        <i class="bi bi-clock-history me-1"></i> View Payment History
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Ticket ID</th>
                        <th>Driver Name</th>
                        <th>License Plate</th>
                        <th>Violation Type</th>
                        <th>Amount Due</th>
                        <th>Violation Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($violations)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No pending penalties found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($violations as $v): ?>
                        <tr>
                            <td>#<?= $v['id'] ?></td>
                            <td><?= $v['driver_name'] ?></td>
                            <td><span class="badge bg-secondary"><?= $v['license_plate'] ?></span></td>
                            <td><?= $v['violation_type'] ?></td>
                            <td><strong>$<?= number_format($v['penalty_amount'], 2) ?></strong></td>
                            <td><?= date('M d, Y', strtotime($v['violation_date'])) ?></td>
                            <td>
                                <a href="/penalties/pay/<?= $v['id'] ?>" class="btn btn-sm btn-success">
                                    <i class="bi bi-cash me-1"></i> Record Payment
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

<?= $this->endSection() ?>
