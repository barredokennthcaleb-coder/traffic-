<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Penalty Management - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <h4 class="mb-0"><i class="bi bi-hourglass-split me-2 text-warning"></i>Pending Penalties</h4>
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
            <a href="/penalties/history" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-clock-history me-1"></i> Payment History
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="pendingTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Ticket ID</th>
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
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                                    No pending penalties found. All clear!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pending_violations as $v): ?>
                            <tr class="pending-row">
                                <td class="ps-4"><span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 font-monospace"><?= esc($v['ticket_id'] ?? 'N/A') ?></span></td>
                                <td>
                                    <div class="fw-bold"><?= esc($v['driver_name']) ?></div>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small font-monospace"><?= esc($v['license_plate']) ?></span>
                                </td>
                                <td class="small fw-semibold text-muted"><?= esc($v['violation_type']) ?></td>
                                <td><span class="fw-bold text-danger">$<?= number_format($v['penalty_amount'], 2) ?></span></td>
                                <td class="text-muted small"><?= date('M d, Y', strtotime($v['violation_date'])) ?></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm">
                                        <a href="/penalties/pay/<?= $v['id'] ?>" class="btn btn-sm btn-success px-3" title="Record Payment">
                                            <i class="bi bi-cash-coin me-1"></i> Record Payment
                                        </a>
                                        <a href="/penalties/view/<?= $v['id'] ?>" class="btn btn-sm btn-white border" title="View Details">
                                            <i class="bi bi-eye text-info"></i>
                                        </a>
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
</script>
<?= $this->endSection() ?>
