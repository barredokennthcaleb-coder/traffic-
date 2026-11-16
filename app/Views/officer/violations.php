<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>My Recorded Violations<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <h4 class="mb-0"><i class="bi bi-list-ul me-2 text-primary"></i>Recorded Violations</h4>
            <small class="text-muted">Track and filter your recently issued tickets.</small>
        </div>
        <div class="col-md-5">
            <div class="input-group shadow-sm no-print">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Filter recorded violations...">
            </div>
        </div>
        <div class="col-md-3 text-end">
            <button onclick="window.print()" class="btn btn-outline-primary me-2 no-print shadow-sm">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="<?= base_url('officer') ?>" class="btn btn-primary no-print shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> New
            </a>
        </div>
    </div>

    <div class="print-header d-none text-center mb-4">
        <h2 class="fw-bold">Traffic Violation Management System</h2>
        <h4 class="text-muted">Official Officer Record Report</h4>
        <p class="small">Generated on: <?= date('M d, Y H:i') ?></p>
        <hr>
    </div>

    <div class="card border-0 shadow-sm violations-card">
        <div class="card-body p-0">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm m-3 no-print" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="violationTable">
                    <thead class="table-light violations-head">
                        <tr>
                            <th class="ps-4">Ticket ID</th>
                            <th>Violator</th>
                            <th>Age</th>
                            <th>Address</th>
                            <th>License Plate</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-4 no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($violations)): ?>
                            <tr id="noDataRow">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No violations recorded yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($violations as $violation): ?>
                            <?php
                                $vStatus = strtolower($violation['status'] ?? 'pending');
                                $isPaid  = $vStatus === 'paid';
                                $isCancelled = $vStatus === 'cancelled';
                            ?>
                            <tr class="violation-row">
                                <td class="ps-4" data-label="Ticket ID">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <span class="ticket-id-pill font-monospace" title="Click to copy" onclick="copyTicketId('<?= esc($violation['ticket_id'] ?? '') ?>', this)">
                                            <i class="bi bi-ticket-perforated me-1"></i><?= esc($violation['ticket_id'] ?? 'N/A') ?>
                                            <i class="bi bi-clipboard ms-1 opacity-75 copy-icon" style="font-size: 0.75rem;"></i>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="Violator">
                                    <div class="driver-name-text lh-sm">
                                        <?= esc(trim(($violation['first_name'] ?? '') . ' ' . ($violation['last_name'] ?? '')) ?: ($violation['driver_name'] ?? '-')) ?>
                                    </div>
                                </td>
                                <td data-label="Age"><?= esc((string) ($violation['age'] ?? '-')) ?></td>
                                <td class="small text-muted" data-label="Address"><?= esc($violation['address'] ?? '-') ?></td>
                                <td data-label="License Plate">
                                    <?php if (!empty($violation['license_plate'])): ?>
                                        <span class="plate-pill font-monospace" style="font-size: 0.74rem;">
                                            <?= tricycle_icon('me-1') ?><?= esc($violation['license_plate']) ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td data-label="Status">
                                    <?php if ($vStatus === 'pending'): ?>
                                        <span class="status-glow-badge status-pending"><span class="status-dot"></span>Pending</span>
                                    <?php elseif ($isPaid): ?>
                                        <span class="status-glow-badge status-paid"><i class="bi bi-check-circle-fill me-1"></i>Paid</span>
                                    <?php else: ?>
                                        <span class="status-glow-badge status-cancelled"><i class="bi bi-x-circle-fill me-1"></i>Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted" data-label="Date" style="white-space: nowrap;">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <i class="bi bi-calendar3 text-primary" style="font-size: 0.8rem; flex-shrink: 0;"></i>
                                        <span><?= date('M d, Y', strtotime($violation['violation_date'])) ?></span>
                                    </div>
                                </td>
                                <td class="text-end pe-4 no-print" data-label="Action">
                                    <a href="<?= base_url('officer/view/' . (int) $violation['id']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-2xs">
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
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
    .violations-card {
        border: 1px solid #e2e8f0 !important;
        border-radius: 14px;
        overflow: hidden;
    }
    .violations-head th {
        background: #f8fafc !important;
        color: #475569 !important;
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e2e8f0;
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }
    .violation-row {
        background: #ffffff;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .violation-row:hover {
        background: #f8fafc;
        transform: translateY(-1.5px);
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.07);
    }

    /* Badges & Pills */
    .driver-name-text {
        color: #0f172a;
        font-weight: 700;
        font-size: 0.92rem;
        letter-spacing: -0.01em;
    }
    .ticket-id-pill {
        background: #f5f3ff;
        border: 1px solid #ddd6fe;
        color: #6d28d9;
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
        background: #ede9fe;
        border-color: #c4b5fd;
        color: #5b21b6;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(124, 58, 237, 0.15);
    }
    .ticket-id-pill.copied {
        background: #dcfce7 !important;
        border-color: #86efac !important;
        color: #15803d !important;
        box-shadow: 0 0 10px rgba(22, 163, 74, 0.2) !important;
    }
    .plate-pill {
        background: #ffffff !important;
        color: #0f172a !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 6px;
        padding: 0.15rem 0.5rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
    }
    .plate-pill i,
    .plate-pill .tricycle-icon {
        color: #7c3aed !important;
    }
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
    }
    .violation-chip .violation-dot {
        color: #ef4444;
        font-size: 1.15rem;
        line-height: 0.9;
        margin-right: 5px;
        font-weight: bold;
    }
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
        color: #854d0e;
        background: #fef9c3;
        border: 1px solid #fde047;
    }
    .total-fine-paid {
        color: #065f46;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
    }
    .status-glow-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.32rem 0.8rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .status-pending {
        background: #fef9c3;
        color: #854d0e;
        border: 1px solid #eab308;
    }
    .status-pending .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #ca8a04;
        margin-right: 6px;
        box-shadow: 0 0 6px rgba(202, 138, 4, 0.6);
        animation: pulseAmber 2s infinite ease-in-out;
        flex-shrink: 0;
    }
    .status-paid {
        background: #ecfdf5;
        color: #15803d;
        border: 1px solid #86efac;
    }
    .status-cancelled {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    @keyframes pulseAmber {
        0%, 100% { transform: scale(1); opacity: 1; }
        50%      { transform: scale(0.85); opacity: 0.55; }
    }

    #searchInput {
        border-color: #e9d5ff;
    }

    /* Mobile Responsive Card Transformation */
    @media (max-width: 768px) {
        .container-fluid.py-4 {
            padding-top: 0.5rem !important;
        }
        .row.mb-4.align-items-center > [class*='col-'] {
            margin-bottom: 0.75rem;
        }
        .row.mb-4.align-items-center .text-end {
            text-align: left !important;
        }
        .row.mb-4.align-items-center .text-end .btn {
            width: 100%;
            margin-right: 0 !important;
        }
        .row.mb-4.align-items-center .text-end .btn + .btn {
            margin-top: 0.5rem;
        }

        #violationTable thead { display: none !important; }
        #violationTable tbody tr {
            display: block !important;
            margin-bottom: 0.85rem !important;
            border-radius: 12px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 2px 8px rgba(15,23,42,0.06) !important;
            transform: none !important;
            padding: 0.4rem 0 !important;
            background: #ffffff !important;
        }
        #violationTable tbody td {
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            padding: 0.55rem 1rem !important;
            border-bottom: 1px solid #f1f5f9 !important;
            font-size: 0.84rem !important;
            color: #1e293b !important;
        }
        #violationTable tbody td:last-child { border-bottom: none !important; }
        #violationTable tbody td::before {
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
        /* A table box keeps its column widths even after its rows become
           cards; making the table and its body blocks lets each card take
           the whole width instead of one column's share. */
        #violationTable,
        #violationTable tbody {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
        }
        #violationTable tbody tr,
        #violationTable tbody td {
            width: 100% !important;
            box-sizing: border-box !important;
        }
        #violationTable tbody td {
            row-gap: 0.35rem;
            flex-wrap: wrap;
            /* Values pack to the trailing edge; a long value that wraps to
               its own line still lands on the right instead of snapping
               back to the label. */
            justify-content: flex-end !important;
            text-align: right;
        }
        #violationTable tbody td::before {
            max-width: 45%;
            text-align: left;
            margin-right: auto !important;
        }
        #violationTable tbody td > * {
            min-width: 0;
            max-width: 100%;
        }
        /* A person's name reads from the left edge, under its own small
           label, the way the enforcer ledger reads it. */
        #violationTable tbody td[data-label="Violator"] {
            display: block !important;
            text-align: left;
        }
        #violationTable tbody td[data-label="Violator"]::before {
            display: block;
            margin: 0 0 0.15rem;
        }
    }
    @media print {
        .no-print, .sidebar, .sidebar-toggle, .btn, .alert, .main-content h1, .breadcrumb {
            display: none !important;
        }
        .print-header {
            display: block !important;
        }
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
            padding: 0 !important;
        }
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
        .container-fluid {
            padding: 0 !important;
        }
        body {
            background-color: white !important;
        }
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
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
                row.innerHTML = `<td colspan="8" class="text-center py-5 text-muted">No matching results found.</td>`;
            }
        } else if (noDataRow) {
            noDataRow.remove();
        }
    });
</script>
<?= $this->endSection() ?>
