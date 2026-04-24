<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Admin Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Quick Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Total Violations</h6>
                            <h2 class="mb-0 fw-bold"><?= number_format($total_violations) ?></h2>
                        </div>
                        <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Pending Penalties</h6>
                            <h2 class="mb-0 fw-bold"><?= number_format($pending) ?></h2>
                        </div>
                        <i class="bi bi-clock-history fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Paid Penalties</h6>
                            <h2 class="mb-0 fw-bold"><?= number_format($paid) ?></h2>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Total Revenue</h6>
                            <h2 class="mb-0 fw-bold">$<?= number_format($total_revenue, 2) ?></h2>
                        </div>
                        <i class="bi bi-currency-dollar fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="bi bi-activity me-2 text-primary"></i>Recent Violations</h5>
            <a href="/penalties/all" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Ticket ID</th>
                            <th>Driver Information</th>
                            <th>Violation Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="pe-4">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_violations)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No recent violations found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_violations as $v): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-dark-subtle text-dark border border-dark-subtle px-2 font-monospace"><?= esc($v['ticket_id'] ?? '#'.$v['id']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= esc($v['driver_name']) ?></div>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small font-monospace"><?= esc($v['license_plate']) ?></span>
                                </td>
                                <td class="small fw-semibold text-muted"><?= esc($v['violation_type']) ?></td>
                                <td><span class="fw-bold text-danger">$<?= number_format($v['penalty_amount'], 2) ?></span></td>
                                <td>
                                    <?php if ($v['status'] == 'Pending'): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 rounded-pill">Pending</span>
                                    <?php elseif ($v['status'] == 'Paid'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 rounded-pill">Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle px-3 rounded-pill">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small pe-4"><?= date('M d, Y', strtotime($v['violation_date'])) ?></td>
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
