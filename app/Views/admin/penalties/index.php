<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Penalty Management - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <h4 class="mb-0"><i class="bi bi-hourglass-split me-2" style="color: #2563eb;"></i>Payment Penalties</h4>
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
            <a href="<?= base_url('penalties/history') ?>" class="btn shadow-sm" style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: white; border: 2px solid #2563eb; font-weight: 600;">
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
                            <?php
                                $fullName = trim(($v['first_name'] ?? '') . ' ' . ($v['last_name'] ?? ''));
                                $dName    = !empty($fullName) ? $fullName : ($v['driver_name'] ?? 'Unknown');
                                $plate    = trim($v['license_plate'] ?? '');
                                $skipPlate = in_array(strtolower($plate), ['no', 'none', 'n/a', '-', '']);
                                $dl       = trim($v['license_number'] ?? '');
                                $skipDl   = in_array(strtolower($dl), ['no', 'none', 'n/a', '-', '']);
                                $totalAmt = (float)($v['total_penalty_sum'] ?? $v['penalty_amount'] ?? 0);
                                $rawTypes = !empty($v['concatenated_violations']) ? $v['concatenated_violations'] : ($v['violation_type'] ?? '-');
                                $types    = explode('||', $rawTypes);
                            ?>
                            <tr class="pending-row">
                                <td class="ps-4 text-muted small col-index" data-label="#"><?= $i++ ?></td>
                                <td data-label="Ticket ID">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <span class="ticket-id-pill font-monospace" title="Click to copy" onclick="copyTicketId('<?= esc($v['ticket_id'] ?? '') ?>', this)">
                                            <i class="bi bi-ticket-perforated me-1"></i><?= esc($v['ticket_id'] ?? 'N/A') ?>
                                            <i class="bi bi-clipboard ms-1 opacity-75 copy-icon" style="font-size: 0.75rem;"></i>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="Driver & Vehicle">
                                    <div>
                                        <div class="driver-name-text lh-sm"><?= esc($dName) ?></div>
                                        <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                            <?php if (!$skipPlate): ?>
                                                <span class="plate-pill font-monospace" style="font-size: 0.74rem;">
                                                    <i class="bi bi-car-front-fill me-1"></i><?= esc($plate) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!$skipDl): ?>
                                                <span class="dl-pill font-monospace" title="Driver License / Permit">
                                                    DL: <?= esc($dl) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="small fw-semibold" data-label="Violations">
                                    <div class="d-flex flex-column gap-1">
                                        <?php foreach ($types as $t): ?>
                                            <?php 
                                                $parts = explode('::', $t);
                                                $vName = $parts[0];
                                            ?>
                                            <div class="violation-chip"><span class="violation-dot">&bull;</span><?= esc($vName) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="small font-monospace" data-label="Amount">
                                    <div class="d-flex flex-column gap-1">
                                        <?php foreach ($types as $t): ?>
                                            <?php 
                                                $parts = explode('::', $t);
                                                $vAmt  = isset($parts[1]) && $parts[1] !== '' ? (float)$parts[1] : null;
                                            ?>
                                            <div class="amount-text"><?= $vAmt !== null ? '₱' . number_format($vAmt, 2) : '-' ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td data-label="Total Amount">
                                    <span class="total-fine-badge total-fine-pending">₱<?= number_format($totalAmt, 2) ?></span>
                                </td>
                                <td class="small" data-label="Date Issued" style="white-space:nowrap;">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <i class="bi bi-calendar3 text-primary" style="font-size:0.8rem; flex-shrink: 0;"></i>
                                        <span><?= date('M d, Y', strtotime($v['violation_date'])) ?></span>
                                    </div>
                                </td>
                                <td class="text-end pe-4 col-actions" data-label="Actions">
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
/* ── PENALTIES TABLE – MODERN & CLEAN ──────────────────────────── */
#pendingTable thead th {
    background: #f8fafc;
    color: #475569;
    font-size: 0.74rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.85rem 1rem;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}
#pendingTable thead th:first-child { padding-left: 1.5rem; }
#pendingTable thead th:last-child  { padding-right: 1.5rem; }
#pendingTable tbody td {
    padding: 0.85rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    font-size: 0.88rem;
}
/* Pending rows: smooth hover lift */
.pending-row {
    background: #ffffff;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
.pending-row:hover {
    background: #f8fafc;
    transform: translateY(-1.5px);
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.07);
}

/* Driver & Vehicle */
.driver-name-text {
    color: #0f172a;
    font-weight: 700;
    font-size: 0.93rem;
    letter-spacing: -0.01em;
}

/* Ticket ID Glass Pill */
.ticket-id-pill {
    background: #f0f7ff;
    border: 1px solid #bfdbfe;
    color: #1d4ed8;
    padding: 0.25rem 0.65rem;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    letter-spacing: 0.02em;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    user-select: none;
}
.ticket-id-pill:hover {
    background: #dbeafe;
    border-color: #93c5fd;
    color: #1e40af;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(37, 99, 235, 0.15);
}
.ticket-id-pill.copied {
    background: #dcfce7 !important;
    border-color: #86efac !important;
    color: #15803d !important;
    box-shadow: 0 0 10px rgba(22, 163, 74, 0.2) !important;
}

/* Vehicle Plate & DL Pills */
.plate-pill {
    background: #ffffff !important;
    color: #0f172a !important;
    border: 1.5px solid #cbd5e1 !important;
    border-radius: 6px;
    padding: 0.18rem 0.55rem;
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
    transition: all 0.2s ease;
}
.plate-pill i {
    color: #2563eb !important;
    margin-right: 0.25rem;
}
.plate-pill:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #000000 !important;
}
.dl-pill {
    background: #f8fafc;
    color: #0f172a;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    padding: 0.18rem 0.55rem;
    font-size: 0.74rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    display: inline-flex;
    align-items: center;
}

/* Violation Infraction Chip */
.violation-chip {
    font-size: 0.81rem;
    color: #1e293b;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 0.22rem 0.55rem;
    display: inline-flex;
    align-items: center;
    line-height: 1.35;
    font-weight: 500;
    word-break: break-word;
    transition: all 0.18s ease;
}
.violation-chip .violation-dot {
    color: #ef4444;
    font-size: 1.15rem;
    line-height: 0.9;
    margin-right: 5px;
    font-weight: bold;
}
.pending-row:hover .violation-chip {
    background: #ffffff;
    border-color: #cbd5e1;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

/* Amount text */
.amount-text {
    color: #475569;
    font-family: 'ui-monospace', 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
    font-size: 0.84rem;
    font-weight: 600;
}

/* Total fine – warm amber for pending */
.total-fine-badge {
    font-size: 0.88rem;
    font-weight: 800;
    font-family: 'ui-monospace', 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
    padding: 0.25rem 0.7rem;
    border-radius: 999px;
    display: inline-block;
    transition: transform 0.2s ease;
}
.total-fine-pending {
    color: #b45309;
    background: #fffbeb;
    border: 1px solid #fde68a;
}
.pending-row:hover .total-fine-badge {
    transform: scale(1.03);
}

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
@media (max-width: 768px) {
    #pendingTable thead { display: none !important; }
    #pendingTable tbody tr {
        display: block !important;
        margin-bottom: 0.85rem !important;
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06) !important;
        transform: none !important;
        padding: 0.4rem 0 !important;
        background: #ffffff !important;
    }
    #pendingTable tbody td {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        padding: 0.55rem 1rem !important;
        border-bottom: 1px solid #f1f5f9 !important;
        font-size: 0.84rem !important;
        color: #1e293b !important;
    }
    #pendingTable tbody td:last-child { border-bottom: none !important; }
    #pendingTable tbody td.col-index { display: none !important; }
    #pendingTable tbody td::before {
        content: attr(data-label);
        font-weight: 700;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        flex-shrink: 0;
        margin-right: 0.75rem;
        margin-top: 2px;
    }
    #pendingTable tbody td.col-actions {
        justify-content: flex-end !important;
    }
}
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // ── 1-CLICK COPY TICKET ID ──────────────────────────────────
    window.copyTicketId = function(ticketId, el) {
        if (!ticketId || ticketId === 'N/A') return;
        navigator.clipboard.writeText(ticketId).then(() => {
            el.classList.add('copied');
            const originalHTML = el.innerHTML;
            el.innerHTML = `<i class="bi bi-check2 text-success me-1"></i>Copied!`;
            setTimeout(() => {
                el.innerHTML = originalHTML;
                el.classList.remove('copied');
            }, 1800);
        }).catch(() => {});
    };

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
