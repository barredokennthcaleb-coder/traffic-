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
                <table class="table table-hover align-middle mb-0" id="pendingTable">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th>Ticket ID</th>
                            <th>Driver Information</th>
                            <th>Violation Type</th>
                            <th>Amount</th>
                            <th>Total</th>
                            <th>Violation Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pending_violations)): ?>
                            <tr id="noDataRow">
                                <td colspan="8" class="text-center py-5 text-muted">
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
                                <td class="ps-4 text-muted small"><?= $i++ ?></td>
                                <td><span class="pn-ticket-pill font-monospace"><?= esc($v['ticket_id'] ?? 'N/A') ?></span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php
                                            $dName = $v['driver_name'] ?? 'Unknown';
                                            $colors = ['#6366f1','#3b82f6','#0ea5e9','#10b981','#f59e0b','#8b5cf6','#ef4444'];
                                            $bg = $colors[abs(crc32($dName)) % count($colors)];
                                            $init = strtoupper(substr($dName, 0, 1));
                                        ?>
                                        <div class="pn-avatar" style="background:<?= $bg ?>"><?= $init ?></div>
                                        <div>
                                            <div class="fw-bold"><?= esc($dName) ?></div>
                                            <span class="pn-plate-pill font-monospace"><?= esc($v['license_plate']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="small fw-semibold text-dark">
                                    <?php 
                                    $rawTypes = !empty($v['concatenated_violations']) ? $v['concatenated_violations'] : $v['violation_type'];
                                    $types = explode('||', $rawTypes);
                                    echo '<ul class="list-unstyled mb-0 gap-1 d-flex flex-column">';
                                    foreach ($types as $t) {
                                        $parts = explode('::', $t);
                                        $vName = $parts[0];
                                        echo '<li><i class="bi bi-exclamation-circle-fill text-warning me-1" style="font-size:0.75rem;"></i>' . esc($vName) . '</li>';
                                    }
                                    echo '</ul>';
                                    ?>
                                </td>
                                <td class="small font-monospace text-muted">
                                    <?php 
                                    $rawTypes = !empty($v['concatenated_violations']) ? $v['concatenated_violations'] : $v['violation_type'];
                                    $types = explode('||', $rawTypes);
                                    echo '<ul class="list-unstyled mb-0 gap-1 d-flex flex-column">';
                                    foreach ($types as $t) {
                                        $parts = explode('::', $t);
                                        $vAmt  = isset($parts[1]) && $parts[1] !== '' ? (float)$parts[1] : null;
                                        echo '<li>';
                                        if ($vAmt !== null) {
                                            echo '₱' . number_format($vAmt, 2);
                                        } else {
                                            echo '-';
                                        }
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                    ?>
                                </td>
                                <td>
                                    <span class="pn-total-fine font-monospace">₱<?= number_format((float) ($v['total_penalty_sum'] ?? $v['penalty_amount'] ?? 0), 2) ?></span>
                                </td>
                                <td class="text-muted small" style="white-space:nowrap;">
                                    <i class="bi bi-calendar3 me-1" style="font-size:0.8rem;"></i><?= date('M d, Y', strtotime($v['violation_date'])) ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="pn-action-group">
                                        <a href="<?= base_url('penalties/pay/' . $v['id']) ?>" class="pn-action-btn pn-action--pay" title="Record Payment">
                                            <i class="bi bi-cash-coin"></i>
                                        </a>
                                        <a href="<?= base_url('penalties/view/' . $v['id']) ?>" class="pn-action-btn pn-action--view" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" class="pn-action-btn pn-action--cancel btn-cancel" data-id="<?= $v['id'] ?>" title="Cancel Violation">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <button type="button" class="pn-action-btn pn-action--delete btn-delete" data-id="<?= $v['id'] ?>" title="Delete Record">
                                            <i class="bi bi-trash3"></i>
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

<style>
/* ── PENALTIES TABLE – FULL UPGRADE ──────────────────────────── */
#pendingTable thead th {
    background: #1e293b;
    color: #e2e8f0;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 0.9rem 1rem;
    border-bottom: 2px solid #334155;
    white-space: nowrap;
}
#pendingTable thead th:first-child { padding-left: 1.5rem; }
#pendingTable thead th:last-child  { padding-right: 1.5rem; }
#pendingTable tbody td {
    padding: 0.9rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    font-size: 0.88rem;
    transition: background 0.18s ease;
}
/* Pending rows: vivid amber inset bar AT REST (inset works on collapsed borders) */
.pending-row {
    background: rgba(254,252,232,0.5);
    box-shadow: inset 5px 0 0 #f59e0b, inset 8px 0 14px rgba(245,158,11,0.09);
    transition: background  0.28s cubic-bezier(0.34,1.56,0.64,1),
                box-shadow  0.28s cubic-bezier(0.34,1.56,0.64,1),
                transform   0.28s cubic-bezier(0.34,1.56,0.64,1);
}
.pending-row:hover {
    background: #fffbeb !important;
    box-shadow: inset 6px 0 0 #f59e0b,
                inset 12px 0 22px rgba(245,158,11,0.15),
                0 4px 24px rgba(245,158,11,0.13);
    transform: translateX(4px);
}
/* Ticket pill */
.pn-ticket-pill {
    display: inline-block;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border: 1.5px solid #fcd34d;
    color: #92400e;
    border-radius: 8px;
    padding: 3px 10px;
    font-size: 0.79rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    white-space: nowrap;
}
/* Avatar */
.pn-avatar {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    color: #fff;
    font-size: 0.84rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    transition: transform 0.2s ease;
}
.pending-row:hover .pn-avatar { transform: scale(1.08); }
/* Plate pill */
.pn-plate-pill {
    display: inline-block;
    background: #0f172a;
    color: #f8fafc;
    border-radius: 5px;
    padding: 1px 7px;
    font-size: 0.72rem;
    font-weight: 700;
    margin-top: 3px;
    letter-spacing: 0.04em;
}
/* Total fine – amber since all are pending */
.pn-total-fine {
    font-weight: 800;
    font-size: 0.95rem;
    color: #92400e;
    background: rgba(245,158,11,0.10);
    border: 1.5px solid rgba(245,158,11,0.30);
    border-radius: 8px;
    padding: 0.2rem 0.55rem;
    display: inline-block;
    transition: transform 0.18s ease;
}
.pending-row:hover .pn-total-fine { transform: scale(1.03); }
/* Action buttons */
.pn-action-group {
    display: flex;
    gap: 5px;
    justify-content: flex-end;
    align-items: center;
}
.pn-action-btn {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    border: 1.5px solid;
    cursor: pointer;
    background: #fff;
    transition: all 0.2s cubic-bezier(0.16,1,0.3,1);
    text-decoration: none;
}
.pn-action-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 12px rgba(15,23,42,0.10); }
.pn-action--pay   { color: #047857; border-color: #86efac; }
.pn-action--pay:hover   { background: #ecfdf5; border-color: #34d399; color: #065f46; }
.pn-action--view  { color: #2563eb; border-color: #bfdbfe; }
.pn-action--view:hover  { background: #eff6ff; border-color: #93c5fd; }
.pn-action--cancel { color: #f59e0b; border-color: #fcd34d; }
.pn-action--cancel:hover { background: #fffbeb; border-color: #f59e0b; color: #92400e; }
.pn-action--delete { color: #ef4444; border-color: #fca5a5; }
.pn-action--delete:hover { background: #fef2f2; border-color: #f87171; color: #b91c1c; }
/* Responsive mobile cards */
@media (max-width: 767px) {
    #pendingTable thead { display: none; }
    #pendingTable tbody tr {
        display: block;
        margin-bottom: 0.75rem;
        border-radius: 12px;
        border: 1px solid #fcd34d;
        border-left: 4px solid #f59e0b !important;
        box-shadow: 0 2px 8px rgba(245,158,11,0.08);
        transform: none !important;
    }
    #pendingTable tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.82rem;
    }
    #pendingTable tbody td:last-child { border-bottom: none; }
    #pendingTable tbody td:first-child { display: none; }
}
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

    const cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
    const cancelForm = document.getElementById('cancelForm');

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
            
            Swal.fire({
                title: 'Confirm Delete',
                text: 'Are you sure you want to delete this record permanently? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `<?= base_url('penalties/delete') ?>/${id}`;
                    
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '<?= csrf_token() ?>';
                    csrfInput.value = '<?= csrf_hash() ?>';
                    
                    form.appendChild(csrfInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            deleteForm.action = `<?= base_url('penalties/delete') ?>/${id}`;
            deleteModal.show();
        });
    });

    <?php if (session()->getFlashdata('payment_settled')): ?>
    Swal.fire({
        title: 'Payment Settled!',
        text: '<?= session()->getFlashdata('payment_settled') ?>',
        icon: 'success',
        confirmButtonColor: '#3b82f6',
        confirmButtonText: '<i class="bi bi-check2 me-1"></i> Great'
    });
    <?php endif; ?>
</script>
<?= $this->endSection() ?>
