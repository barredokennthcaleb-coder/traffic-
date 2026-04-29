<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Penalty Management - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <h4 class="mb-0"><i class="bi bi-hourglass-split me-2 text-warning"></i>Payment Penalties</h4>
        </div>
        <div class="col-md-5">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Filter pending records...">
            </div>
        </div>
        <div class="col-md-3 text-end">
            <a href="<?= base_url('penalties/history') ?>" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-clock-history me-1"></i> Payment History
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-premium-mobile" id="pendingTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th>Ticket ID</th>
                            <th>Driver Information</th>
                            <th>Violation Type</th>
                            <th>Amount Due</th>
                            <th>Violation Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pending_violations)): ?>
                            <tr id="noDataRow">
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                                    No pending penalties found. All clear!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $currentPage = $pager->getCurrentPage('penalties');
                            $perPage = 10; // As set in controller
                            $i = ($currentPage - 1) * $perPage + 1;
                            ?>
                            <?php foreach ($pending_violations as $v): ?>
                            <tr class="pending-row">
                                <td class="ps-4 text-muted small" data-label="#"><span><?= $i++ ?></span></td>
                                <td data-label="Ticket ID"><span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 font-monospace"><?= esc($v['ticket_id'] ?? 'N/A') ?></span></td>
                                <td data-label="Driver Information">
                                    <div class="fw-bold"><?= esc($v['driver_name']) ?></div>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small font-monospace"><?= esc($v['license_plate']) ?></span>
                                </td>
                                <td class="small fw-semibold text-muted" data-label="Violation Type"><?= esc($v['violation_type']) ?></td>
                                <td data-label="Amount Due"><span class="fw-bold text-danger">$<?= number_format($v['penalty_amount'], 2) ?></span></td>
                                <td class="text-muted small" data-label="Violation Date"><?= date('M d, Y', strtotime($v['violation_date'])) ?></td>
                                <td class="text-end pe-4" data-label="Actions">
                                    <div class="btn-group shadow-sm">
                                        <a href="<?= base_url('penalties/pay/' . $v['id']) ?>" class="btn btn-sm btn-success px-3" title="Record Payment">
                                            <i class="bi bi-cash-coin me-1"></i> Record Payment
                                        </a>
                                        <a href="<?= base_url('penalties/view/' . $v['id']) ?>" class="btn btn-sm btn-white border" title="View Details">
                                            <i class="bi bi-eye text-info"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-white border btn-cancel" data-id="<?= $v['id'] ?>" title="Cancel Violation">
                                            <i class="bi bi-x-circle text-danger"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-white border btn-delete" data-id="<?= $v['id'] ?>" title="Delete Record">
                                            <i class="bi bi-trash text-danger"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            <?= $pager->links('penalties', 'bootstrap_pagination') ?>
        </div>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Cancel Violation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="fw-bold">Are you sure you want to cancel this violation ticket?</p>
                    <div class="mb-3">
                        <label class="form-label">Reason for Cancellation</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Enter the reason for cancelling this ticket..."></textarea>
                    </div>
                    <div class="alert alert-warning border-0 shadow-sm mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This action will set the status to <strong>Cancelled</strong>.
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
                </div>
                <h5 class="mb-2">Confirm Delete</h5>
                <p class="text-muted small">Are you sure you want to delete this record permanently? This action cannot be undone.</p>
                <form id="deleteForm" method="POST">
                    <?= csrf_field() ?>
                    <div class="d-flex justify-content-center gap-2 mt-4">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4 shadow-sm">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .pending-row { transition: all 0.2s ease; }
    .btn-white { background: #fff; }
    .btn-white:hover { background: #f8f9fa; }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Live Search Filtering
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const keyword = this.value.toLowerCase();
        const rows = document.querySelectorAll('.pending-row');
        let hasResults = false;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(keyword)) {
                row.style.display = '';
                hasResults = true;
            } else {
                row.style.display = 'none';
            }
        });

        const noDataRow = document.getElementById('noDataRow');
        if (!hasResults) {
            if (!noDataRow) {
                const tbody = document.querySelector('#pendingTable tbody');
                const row = tbody.insertRow();
                row.id = 'noDataRow';
                row.innerHTML = `<td colspan="6" class="text-center py-5 text-muted">No matching pending records found.</td>`;
            }
        } else if (noDataRow) {
            noDataRow.remove();
        }
    });

    // Cancel and Delete Modal Logic
    const cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
    const cancelForm = document.getElementById('cancelForm');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteForm = document.getElementById('deleteForm');

    document.querySelectorAll('.btn-cancel').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            cancelForm.action = `<?= base_url('penalties/cancel') ?>/${id}`;
            cancelModal.show();
        });
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            deleteForm.action = `<?= base_url('penalties/delete') ?>/${id}`;
            deleteModal.show();
        });
    });
</script>
<?= $this->endSection() ?>
