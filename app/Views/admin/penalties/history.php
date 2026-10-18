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
            <a href="<?= base_url('penalties') ?>" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Pending
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-premium-mobile" id="paymentTable">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th>Date Paid</th>
                            <th>Receipt #</th>
                            <th>Driver</th>
                            <th>Violation</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Total Paid</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                            <tr id="noDataRow">
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No payment history found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $currentPage = $pager->getCurrentPage('history');
                            $perPage = 10;
                            $i = ($currentPage - 1) * $perPage + 1;
                            ?>
                            <?php foreach ($payments as $p): ?>
                            <tr class="payment-row">
                                <td class="ps-4 text-muted small" data-label="#"><span><?= $i++ ?></span></td>
                                <td class="text-muted small" data-label="Date Paid" style="white-space:nowrap;">
                                    <i class="bi bi-calendar3 me-1" style="font-size:0.75rem;"></i>
                                    <?= date('M d, Y', strtotime($p['paid_date'])) ?>
                                    <div class="text-muted" style="font-size:0.72rem;"><?= date('H:i', strtotime($p['paid_date'])) ?></div>
                                </td>
                                <td data-label="Receipt #">
                                    <span class="ht-receipt-pill font-monospace"><?= $p['receipt_number'] ?: 'N/A' ?></span>
                                </td>
                                <td data-label="Driver">
                                    <div class="d-flex align-items-center gap-2">
                                        <?php
                                            $dName = $p['driver_name'] ?? 'Unknown';
                                            $colors = ['#6366f1','#3b82f6','#0ea5e9','#10b981','#f59e0b','#8b5cf6','#ef4444'];
                                            $bg = $colors[abs(crc32($dName)) % count($colors)];
                                            $init = strtoupper(substr($dName, 0, 1));
                                        ?>
                                        <div class="ht-avatar" style="background:<?= $bg ?>"><?= $init ?></div>
                                        <div>
                                            <div class="fw-bold" style="font-size:0.88rem;"><?= esc($dName) ?></div>
                                            <span class="ht-plate-pill font-monospace"><?= esc($p['license_plate']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="small fw-semibold text-dark" data-label="Violation">
                                    <?php
                                    $rawTypes = !empty($p['concatenated_violations']) ? $p['concatenated_violations'] : $p['violation_type'];
                                    $types = explode('||', $rawTypes);
                                    echo '<ul class="list-unstyled mb-0 d-flex flex-column" style="gap:3px;">';
                                    foreach ($types as $t) {
                                        $parts = explode('::', $t);
                                        $vName = $parts[0];
                                        echo '<li><i class="bi bi-check-circle-fill me-1" style="color:#10b981;font-size:0.72rem;"></i>' . esc($vName) . '</li>';
                                    }
                                    echo '</ul>';
                                    ?>
                                </td>
                                <td class="small font-monospace text-muted" data-label="Amount">
                                    <?php
                                    $rawTypes2 = !empty($p['concatenated_violations']) ? $p['concatenated_violations'] : $p['violation_type'];
                                    $types2 = explode('||', $rawTypes2);
                                    echo '<ul class="list-unstyled mb-0 d-flex flex-column" style="gap:3px;">';
                                    foreach ($types2 as $t) {
                                        $parts = explode('::', $t);
                                        $vAmt  = isset($parts[1]) && $parts[1] !== '' ? (float)$parts[1] : null;
                                        echo '<li>' . ($vAmt !== null ? '₱' . number_format($vAmt, 2) : '-') . '</li>';
                                    }
                                    echo '</ul>';
                                    ?>
                                </td>
                                <td data-label="Method">
                                    <span class="ht-method-pill">
                                        <i class="bi bi-cash-stack me-1"></i><?= esc($p['payment_method']) ?>
                                    </span>
                                </td>
                                <td data-label="Total Paid">
                                    <span class="ht-total-paid font-monospace">+₱<?= number_format((float) ($p['total_penalty_sum'] ?? $p['penalty_amount'] ?? 0), 2) ?></span>
                                </td>
                                <td class="text-end pe-4" data-label="Actions">
                                    <div class="ht-action-group">
                                        <a href="<?= base_url('user/receipt/' . $p['ticket_id']) ?>" class="ht-action-btn ht-action--view" title="View Receipt" target="_blank">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                        <button type="button" class="ht-action-btn ht-action--reverse btn-reverse"
                                                data-id="<?= $p['id'] ?>" data-receipt="<?= $p['receipt_number'] ?>" title="Reverse Payment">
                                            <i class="bi bi-arrow-counterclockwise"></i>
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
            <?= $pager->links('history', 'bootstrap_pagination') ?>
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
/* ── HISTORY TABLE HEADER ────────────────────────────────────── */
#paymentTable thead th {
    background: #1e293b !important;
    color: #e2e8f0 !important;
    font-size: 0.71rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 0.9rem 1rem;
    border-bottom: 2px solid #334155 !important;
    white-space: nowrap;
}
#paymentTable thead th:first-child { padding-left: 1.5rem; }
#paymentTable thead th:last-child  { padding-right: 1.5rem; }

/* ── ROWS ────────────────────────────────────────────────────── */
#paymentTable tbody td {
    padding: 0.9rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    font-size: 0.87rem;
}
.payment-row {
    background: rgba(240,253,248,0.4);
    box-shadow: inset 5px 0 0 #10b981, inset 8px 0 14px rgba(16,185,129,0.07);
    transition: background  0.26s cubic-bezier(0.34,1.56,0.64,1),
                box-shadow  0.26s cubic-bezier(0.34,1.56,0.64,1),
                transform   0.26s cubic-bezier(0.34,1.56,0.64,1);
}
.payment-row:hover {
    background: #ecfdf5 !important;
    box-shadow: inset 6px 0 0 #10b981,
                inset 12px 0 22px rgba(16,185,129,0.12),
                0 4px 20px rgba(16,185,129,0.10) !important;
    transform: translateX(4px);
}
.payment-row:hover .ht-avatar { transform: scale(1.08); }
.payment-row:hover .ht-total-paid { transform: scale(1.03); }

/* ── RECEIPT PILL ────────────────────────────────────────────── */
.ht-receipt-pill {
    display: inline-block;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border: 1.5px solid #86efac;
    color: #15803d;
    border-radius: 8px;
    padding: 3px 10px;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    white-space: nowrap;
}

/* ── AVATAR ──────────────────────────────────────────────────── */
.ht-avatar {
    width: 32px; height: 32px;
    border-radius: 9px;
    color: #fff; font-size: 0.84rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(0,0,0,0.12);
    transition: transform 0.2s ease;
}

/* ── PLATE PILL ──────────────────────────────────────────────── */
.ht-plate-pill {
    display: inline-block;
    background: #0f172a; color: #f8fafc;
    border-radius: 5px; padding: 1px 7px;
    font-size: 0.71rem; font-weight: 700;
    margin-top: 3px; letter-spacing: 0.04em;
}

/* ── PAYMENT METHOD ──────────────────────────────────────────── */
.ht-method-pill {
    display: inline-flex; align-items: center;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    color: #475569;
    border-radius: 8px;
    padding: 3px 10px;
    font-size: 0.78rem;
    font-weight: 600;
}

/* ── TOTAL PAID ──────────────────────────────────────────────── */
.ht-total-paid {
    font-weight: 800; font-size: 0.95rem;
    color: #047857;
    background: rgba(16,185,129,0.09);
    border: 1.5px solid rgba(16,185,129,0.28);
    border-radius: 8px;
    padding: 0.2rem 0.55rem;
    display: inline-block;
    transition: transform 0.18s ease;
}

/* ── ACTION BUTTONS ──────────────────────────────────────────── */
.ht-action-group { display: flex; gap: 5px; justify-content: flex-end; }
.ht-action-btn {
    width: 32px; height: 32px;
    border-radius: 9px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.85rem;
    border: 1.5px solid;
    cursor: pointer;
    background: #fff;
    transition: all 0.2s cubic-bezier(0.16,1,0.3,1);
    text-decoration: none;
}
.ht-action-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 12px rgba(15,23,42,0.10); }
.ht-action--view  { color: #0ea5e9; border-color: #bae6fd; }
.ht-action--view:hover  { background: #f0f9ff; border-color: #38bdf8; }
.ht-action--reverse { color: #ef4444; border-color: #fca5a5; }
.ht-action--reverse:hover { background: #fef2f2; border-color: #f87171; color: #b91c1c; }

/* ── RESPONSIVE ──────────────────────────────────────────────── */
@media (max-width: 767px) {
    #paymentTable thead { display: none; }
    #paymentTable tbody tr {
        display: block;
        margin-bottom: 0.75rem;
        border-radius: 12px;
        border: 1px solid #bbf7d0;
        border-left: 4px solid #10b981 !important;
        box-shadow: 0 2px 8px rgba(16,185,129,0.08);
        transform: none !important;
    }
    #paymentTable tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.82rem;
    }
    #paymentTable tbody td:last-child { border-bottom: none; }
}
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Live Search Filtering
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
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
    }

    // Reverse Payment Modal Logic
    const reverseModal = new bootstrap.Modal(document.getElementById('reverseModal'));
    const receiptIdSpan = document.getElementById('receiptId');
    const confirmReverseBtn = document.getElementById('confirmReverseBtn');

    document.querySelectorAll('.btn-reverse').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const receipt = this.dataset.receipt;
            receiptIdSpan.textContent = receipt;
            confirmReverseBtn.href = `<?= base_url('penalties/reverse') ?>/${id}`;
            reverseModal.show();
        });
    });
</script>
<?= $this->endSection() ?>
