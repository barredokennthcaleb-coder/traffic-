<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>All Violations - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0"><i class="bi bi-list-ul me-2 text-primary"></i>All Violations</h4>
        </div>
        <div class="col-md-6 text-end">
            <form action="/penalties/search" method="GET" class="d-inline-block me-2">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Search by ticket ID, driver, plate..." value="<?= esc($keyword ?? '') ?>">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
            <a href="/officer" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Record New Violation
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
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
                            <th>Officer</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($violations)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No violations found.
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
                                <td><?= esc($violation['officer_name'] ?? 'N/A') ?></td>
                                <td>
                                    <a href="/penalties/view/<?= $violation['id'] ?>" class="btn btn-sm btn-info text-white" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($violation['status'] == 'Pending'): ?>
                                        <a href="/penalties/pay/<?= $violation['id'] ?>" class="btn btn-sm btn-success" title="Record Payment">
                                            <i class="bi bi-credit-card"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" title="Cancel Violation" onclick="confirmCancel(<?= $violation['id'] ?>)">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
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

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Cancel Violation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancelForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this violation?</p>
                    <div class="mb-3">
                        <label for="cancelReason" class="form-label">Reason for Cancellation (Optional)</label>
                        <textarea class="form-control" id="cancelReason" name="reason" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function confirmCancel(violationId) {
        const form = document.getElementById('cancelForm');
        form.action = `/penalties/cancel/${violationId}`;
        var cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
        cancelModal.show();
    }
</script>
<?= $this->endSection() ?>
