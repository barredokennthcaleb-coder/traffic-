<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>All Violations - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <h4 class="mb-0"><i class="bi bi-list-ul me-2 text-primary"></i>All Violations</h4>
        </div>
        <div class="col-md-5">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Search by ticket ID, driver, license plate...">
            </div>
        </div>
        <div class="col-md-3 text-end">
            <a href="/officer" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> New Violation
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="violationTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Ticket ID</th>
                            <th>Driver Information</th>
                            <th>Violation Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($violations)): ?>
                            <tr id="noDataRow">
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No violation records found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($violations as $v): ?>
                            <tr class="violation-row">
                                <td class="ps-4"><span class="badge bg-dark-subtle text-dark border border-dark-subtle px-2 font-monospace"><?= esc($v['ticket_id'] ?? 'N/A') ?></span></td>
                                <td>
                                    <div class="fw-bold"><?= esc($v['driver_name']) ?></div>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small font-monospace"><?= esc($v['license_plate']) ?></span>
                                </td>
                                <td class="small fw-semibold text-muted"><?= esc($v['violation_type']) ?></td>
                                <td><span class="fw-bold text-danger">$<?= number_format($v['penalty_amount'], 2) ?></span></td>
                                <td>
                                    <?php if ($v['status'] == 'Pending'): ?>
                                        <span class="badge bg-warning rounded-pill px-3">Pending</span>
                                    <?php elseif ($v['status'] == 'Paid'): ?>
                                        <span class="badge bg-success rounded-pill px-3">Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger rounded-pill px-3">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?= date('M d, Y', strtotime($v['violation_date'])) ?></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm">
                                        <a href="/penalties/view/<?= $v['id'] ?>" class="btn btn-sm btn-white border" title="View Details">
                                            <i class="bi bi-eye text-info"></i>
                                        </a>
                                        <?php if ($v['status'] == 'Pending'): ?>
                                            <a href="/penalties/pay/<?= $v['id'] ?>" class="btn btn-sm btn-white border" title="Record Payment">
                                                <i class="bi bi-credit-card text-success"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-white border" onclick="confirmCancel(<?= $v['id'] ?>)" title="Cancel">
                                                <i class="bi bi-x-circle text-danger"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
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
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Cancel Violation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="fw-bold">Are you sure you want to cancel this violation?</p>
                    <p class="text-muted small">This action will void the ticket and mark it as Cancelled in the system.</p>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Reason for Cancellation</label>
                        <textarea class="form-control shadow-sm" name="reason" rows="3" required placeholder="Provide a reason for the cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Keep Ticket</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm">Void Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .violation-row { transition: all 0.2s ease; }
    .btn-white { background: #fff; }
    .btn-white:hover { background: #f8f9fa; }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Live Search Filtering
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const keyword = this.value.toLowerCase();
        const rows = document.querySelectorAll('.violation-row');
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
                const tbody = document.querySelector('#violationTable tbody');
                const row = tbody.insertRow();
                row.id = 'noDataRow';
                row.innerHTML = `<td colspan="7" class="text-center py-5 text-muted">No matching results found.</td>`;
            }
        } else if (noDataRow) {
            noDataRow.remove();
        }
    });

    function confirmCancel(violationId) {
        const form = document.getElementById('cancelForm');
        form.action = `/penalties/cancel/${violationId}`;
        new bootstrap.Modal(document.getElementById('cancelModal')).show();
    }
</script>
<?= $this->endSection() ?>
