<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>My Recorded Violations<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0"><i class="bi bi-list-ul me-2 text-primary"></i>Recorded Violations</h4>
        </div>
        <div class="col-md-6 text-end">
            <a href="/officer" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Record New Violation
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Ticket ID</th>
                            <th>Driver Name</th>
                            <th>License Plate</th>
                            <th>Violation Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($violations)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No violations recorded yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($violations as $violation): ?>
                            <tr>
                                <td><span class="badge bg-dark"><?= esc($violation['ticket_id'] ?? 'N/A') ?></span></td>
                                <td><?= esc($violation['driver_name']) ?></td>
                                <td><span class="badge bg-secondary"><?= esc($violation['license_plate']) ?></span></td>
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
