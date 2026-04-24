<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Violations</h6>
                        <h2 class="mb-0"><?= $total_violations ?></h2>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Pending Penalties</h6>
                        <h2 class="mb-0"><?= $pending_penalties ?></h2>
                    </div>
                    <i class="bi bi-clock-history fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Paid Penalties</h6>
                        <h2 class="mb-0"><?= $paid_penalties ?></h2>
                    </div>
                    <i class="bi bi-check-circle fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Revenue</h6>
                        <h2 class="mb-0">$<?= number_format($total_revenue, 2) ?></h2>
                    </div>
                    <i class="bi bi-currency-dollar fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Recent Violations</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Driver Name</th>
                        <th>License Plate</th>
                        <th>Violation Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_violations)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No violations found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_violations as $violation): ?>
                        <tr>
                            <td>#<?= $violation['id'] ?></td>
                            <td><?= $violation['driver_name'] ?></td>
                            <td><span class="badge bg-secondary"><?= $violation['license_plate'] ?></span></td>
                            <td><?= $violation['violation_type'] ?></td>
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

<?= $this->endSection() ?>
