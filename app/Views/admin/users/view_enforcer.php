<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Traffic Enforcer Profile - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row mb-4 no-print">
        <div class="col-md-6">
            <h4 class="mb-0"><i class="bi bi-person-vcard me-2 text-primary"></i>Enforcer: <?= esc($user['username']) ?></h4>
            <small class="text-muted">Profile and issued violation records</small>
        </div>
        <div class="col-md-6 text-end">
            <button onclick="window.print()" class="btn btn-outline-primary me-2">
                <i class="bi bi-printer me-1"></i> Print Profile
            </button>
            <a href="<?= base_url('users?role=enforcer') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Enforcers
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Enforcer Profile</h6>
                </div>
                <div class="card-body">
                    <?php 
                        $enfImg = !empty($user['profile_image']) && is_file(FCPATH . 'uploads/profile/' . $user['profile_image'])
                            ? base_url('uploads/profile/' . $user['profile_image'])
                            : null;
                        $enfInitials = strtoupper(substr((string) ($user['username'] ?? 'E'), 0, 1));
                    ?>
                    <div class="text-center mb-4 pb-3 border-bottom">
                        <div class="mx-auto position-relative" style="width: 88px; height: 88px;">
                            <?php if ($enfImg): ?>
                                <img src="<?= esc($enfImg) ?>" alt="Enforcer photo" class="w-100 h-100 rounded-circle border shadow-sm" style="object-fit: cover;">
                            <?php else: ?>
                                <div class="w-100 h-100 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-3 shadow-sm">
                                    <?= esc($enfInitials) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h6 class="mt-2 mb-0 fw-bold"><?= esc(trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?: $user['username']) ?></h6>
                        <small class="text-muted">@<?= esc($user['username']) ?></small>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Username</small>
                        <span class="fw-semibold"><?= esc($user['username']) ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Email</small>
                        <span><?= esc($user['email']) ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Full Name</small>
                        <span><?= esc(trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?: '-') ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Age</small>
                        <span><?= esc((string) ($user['age'] ?? '-')) ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Address</small>
                        <span><?= esc($user['address'] ?? '-') ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Duty Station</small>
                        <span class="fw-semibold text-primary"><?= esc($user['assigned_station'] ?? 'Not Assigned') ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block">Role</small>
                        <span class="badge bg-info-subtle text-info border border-info-subtle text-uppercase px-2">Traffic Enforcer</span>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted text-uppercase d-block">Status</small>
                        <?php if (($user['status'] ?? '') === 'active'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 rounded-pill">Active</span>
                        <?php elseif (($user['status'] ?? '') === 'inactive'): ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 rounded-pill">Inactive</span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 rounded-pill"><?= ucfirst(esc($user['status'] ?? 'unknown')) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Record Overview</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded bg-light">
                                <div class="small text-muted">Total Records</div>
                                <div class="fs-4 fw-bold"><?= count($records) ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded bg-warning-subtle">
                                <div class="small text-muted">Pending</div>
                                <div class="fs-4 fw-bold text-warning-emphasis"><?= $pendingCount ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded bg-success-subtle">
                                <div class="small text-muted">Paid</div>
                                <div class="fs-4 fw-bold text-success"><?= $paidCount ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded bg-danger-subtle">
                                <div class="small text-muted">Cancelled</div>
                                <div class="fs-4 fw-bold text-danger"><?= $cancelledCount ?></div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <small class="text-muted text-uppercase d-block">Total Issued Amount</small>
                        <h5 class="mb-0 text-danger"><?= number_format((float) $totalIssuedAmount, 2) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Issued Violation Records</h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= count($records) ?> total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 enforcer-violations-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Ticket ID</th>
                            <th>Violator</th>
                            <th>Violation Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-4 no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No issued records yet for this enforcer.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $record): ?>
                                <?php
                                    $vStatus = strtolower($record['status'] ?? 'pending');
                                    $isPaid  = $vStatus === 'paid';
                                    $isCancelled = $vStatus === 'cancelled';
                                    $vName   = $record['violation_type'] ?? '-';
                                ?>
                                <tr class="enforcer-record-row">
                                    <td class="ps-4" data-label="Ticket ID">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <span class="ticket-id-pill font-monospace" title="Click to copy" onclick="copyTicketId('<?= esc($record['ticket_id'] ?? '') ?>', this)">
                                                <i class="bi bi-ticket-perforated me-1"></i><?= esc($record['ticket_id'] ?? 'N/A') ?>
                                                <i class="bi bi-clipboard ms-1 opacity-75 copy-icon" style="font-size: 0.75rem;"></i>
                                            </span>
                                        </div>
                                    </td>
                                    <td data-label="Violator">
                                        <div class="driver-name-text lh-sm">
                                            <?= esc(trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? '')) ?: ($record['driver_name'] ?? '-')) ?>
                                        </div>
                                        <?php if (!empty($record['license_plate'])): ?>
                                            <div class="mt-1">
                                                <span class="plate-pill font-monospace" style="font-size: 0.72rem;">
                                                    <i class="bi bi-car-front-fill me-1"></i><?= esc($record['license_plate']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Violation Type">
                                        <div class="violation-chip"><span class="violation-dot">&bull;</span><?= esc($vName) ?></div>
                                    </td>
                                    <td data-label="Amount">
                                        <span class="total-fine-badge <?= $isPaid ? 'total-fine-paid' : 'total-fine-pending' ?>">
                                            ₱<?= number_format((float) ($record['penalty_amount'] ?? 0), 2) ?>
                                        </span>
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
                                    <td data-label="Date" class="small text-muted" style="white-space: nowrap;">
                                        <div class="d-flex align-items-center gap-1.5">
                                            <i class="bi bi-calendar3 text-primary" style="font-size: 0.8rem; flex-shrink: 0;"></i>
                                            <span><?= isset($record['violation_date']) ? date('M d, Y h:i A', strtotime($record['violation_date'])) : '-' ?></span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4 no-print col-actions" data-label="Action">
                                        <a href="<?= base_url('officer/view/' . $record['id']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-2xs">
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
    /* Table & Row Hover */
    .enforcer-violations-table thead th {
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
    .enforcer-record-row {
        background: #ffffff;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .enforcer-record-row:hover {
        background: #f8fafc;
        transform: translateY(-1.5px);
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.07);
    }

    /* Driver & Badges */
    .driver-name-text {
        color: #0f172a;
        font-weight: 700;
        font-size: 0.92rem;
        letter-spacing: -0.01em;
    }
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
    .plate-pill i {
        color: #2563eb !important;
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
        color: #b45309;
        background: #fffbeb;
        border: 1px solid #fde68a;
    }
    .total-fine-paid {
        color: #15803d;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
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
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fcd34d;
    }
    .status-pending .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #f59e0b;
        margin-right: 6px;
        box-shadow: 0 0 6px rgba(245, 158, 11, 0.6);
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

    /* Mobile Responsive Card Transformation */
    @media (max-width: 768px) {
        .enforcer-violations-table thead { display: none !important; }
        .enforcer-violations-table tbody tr {
            display: block !important;
            margin-bottom: 0.85rem !important;
            border-radius: 12px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 2px 8px rgba(15,23,42,0.06) !important;
            transform: none !important;
            padding: 0.4rem 0 !important;
            background: #ffffff !important;
        }
        .enforcer-violations-table tbody td {
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            padding: 0.55rem 1rem !important;
            border-bottom: 1px solid #f1f5f9 !important;
            font-size: 0.84rem !important;
            color: #1e293b !important;
        }
        .enforcer-violations-table tbody td:last-child { border-bottom: none !important; }
        .enforcer-violations-table tbody td::before {
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
        .enforcer-violations-table tbody td.col-actions {
            justify-content: flex-end !important;
        }
    }

    @media print {
        .sidebar, .sidebar-toggle, .btn, .alert, .main-content h1, .breadcrumb, .no-print {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
            padding: 0 !important;
        }
        .container-fluid {
            padding: 0 !important;
        }
    }
</style>

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
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
