<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Payment History - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <h4 class="mb-0"><i class="bi bi-receipt me-2 text-success"></i>Financial Transactions</h4>
        </div>
        <div class="col-md-5">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Search by receipt #, driver, license plate...">
            </div>
        </div>
        <div class="col-md-3 text-end">
            <a href="/penalties" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Pending
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="paymentTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date Paid</th>
                            <th>Receipt #</th>
                            <th>Driver</th>
                            <th>Violation</th>
                            <th>Method</th>
                            <th>Amount Paid</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                            <tr id="noDataRow">
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No payment history found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($payments as $p): ?>
                            <tr class="payment-row">
                                <td class="ps-4 text-muted small"><?= date('M d, Y H:i', strtotime($p['paid_date'])) ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 font-monospace"><?= $p['receipt_number'] ?: 'N/A' ?></span></td>
                                <td>
                                    <div class="fw-bold"><?= esc($p['driver_name']) ?></div>
                                    <span class="badge bg-light text-dark border small font-monospace"><?= esc($p['license_plate']) ?></span>
                                </td>
                                <td class="small fw-semibold text-muted"><?= esc($p['violation_type']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= esc($p['payment_method']) ?>
                                    </span>
                                </td>
                                <td><span class="text-success fw-bold">+$<?= number_format($p['penalty_amount'], 2) ?></span></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm">
                                        <a href="/user/receipt/<?= $p['ticket_id'] ?>" class="btn btn-sm btn-white border" title="View Receipt" target="_blank">
                                            <i class="bi bi-receipt text-info"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-white border" title="Reverse Payment" 
                                                onclick="confirmReverse(<?= $p['id'] ?>, '<?= $p['receipt_number'] ?>')">
                                            <i class="bi bi-arrow-counterclockwise text-danger"></i>
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
    </div>
</div>

<!-- Reverse Payment Modal -->
<div class="modal fade" id="reverseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise me-2"></i>Reverse Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="fw-bold">Are you sure you want to reverse payment for receipt <span id="receiptId" class="text-primary"></span>?</p>
                <div class="alert alert-danger border-0 shadow-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    This will set the violation status back to <strong>Pending</strong> and clear all payment data.
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Keep Payment</button>
                <a href="#" id="confirmReverseBtn" class="btn btn-danger px-4 shadow-sm">Confirm Reverse</a>
            </div>
        </div>
    </div>
</div>

<style>
    .payment-row { transition: all 0.2s ease; }
    .btn-white { background: #fff; }
    .btn-white:hover { background: #f8f9fa; }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Live Search Filtering
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const keyword = this.value.toLowerCase();
        const rows = document.querySelectorAll('.payment-row');
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
                const tbody = document.querySelector('#paymentTable tbody');
                const row = tbody.insertRow();
                row.id = 'noDataRow';
                row.innerHTML = `<td colspan="7" class="text-center py-5 text-muted">No matching transactions found.</td>`;
            }
        } else if (noDataRow) {
            noDataRow.remove();
        }
    });

    function confirmReverse(id, receipt) {
        document.getElementById('receiptId').textContent = receipt;
        document.getElementById('confirmReverseBtn').href = '/penalties/reverse/' + id;
        var modal = new bootstrap.Modal(document.getElementById('reverseModal'));
        modal.show();
    }
</script>
<?= $this->endSection() ?>
