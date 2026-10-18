<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Violators - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- ═══ PAGE HEADER ═══════════════════════════════════════════════ -->
    <div class="av-page-header premium-reveal" style="--reveal-delay: 0s;">
        <div class="av-page-header-inner d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-1 fw-bold d-flex align-items-center gap-2" style="color: var(--text-strong);">
                <span class="av-page-icon"><i class="bi bi-list-ul"></i></span>
                All Violators
            </h4>
            <p class="text-muted mb-0 small">Browse, search and manage all traffic citation records.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form method="GET" action="<?= base_url('penalties/all') ?>" class="d-flex align-items-center gap-2 m-0">
                <!-- Status Filter -->
                <div class="av-filter-wrap">
                    <i class="bi bi-funnel av-filter-icon"></i>
                    <select name="status" class="av-filter-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="Pending"   <?= ($status == 'Pending')   ? 'selected' : '' ?>>Pending</option>
                        <option value="Paid"      <?= ($status == 'Paid')      ? 'selected' : '' ?>>Paid</option>
                        <option value="Cancelled" <?= ($status == 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <!-- Search -->
                <div class="search-wrapper position-relative">
                    <i class="bi bi-search search-icon" id="avSearchIcon"></i>
                    <input type="text" name="search" id="avSearchInput"
                           class="form-control av-search-input"
                           placeholder="Search ticket, driver, plate..."
                           value="<?= esc($search ?? '') ?>"
                           autocomplete="off">
                    <span class="av-search-spinner d-none" id="avSearchSpinner">
                        <span class="spinner-border spinner-border-sm text-primary" style="width:14px;height:14px;"></span>
                    </span>
                </div>
                <?php if(!empty($search) || !empty($status)): ?>
                    <a href="<?= base_url('penalties/all') ?>" class="av-clear-btn" title="Clear Filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </form>
            <a href="<?= base_url('officer') ?>" class="av-new-btn">
                <i class="bi bi-plus-circle-fill"></i>
                <span>New Violation</span>
            </a>
        </div>
        </div><!-- /.av-page-header-inner -->
    </div><!-- /.av-page-header -->

    <!-- ═══ TABLE CARD ════════════════════════════════════════════════ -->
    <div class="av-card premium-reveal" style="--reveal-delay: 0.1s;">
        <div class="table-responsive">
            <table class="table av-table align-middle mb-0" id="violationTable">
                <thead>
                    <tr>
                        <th class="ps-4" style="width:130px;">Ticket ID</th>
                        <th style="min-width:170px;">Driver &amp; Vehicle</th>
                        <th>Violations</th>
                        <th style="width:110px;">Breakdown</th>
                        <th style="width:105px;">Total Fine</th>
                        <th style="width:110px;">Status</th>
                        <th style="width:115px; white-space:nowrap;"><i class="bi bi-calendar3 me-1 opacity-50"></i>Date Issued</th>
                        <th class="text-end pe-4" style="width:110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($violations)): ?>
                        <tr id="noDataRow">
                            <td colspan="8" class="text-center py-5">
                                <div class="av-empty-state">
                                    <div class="av-empty-icon"><i class="bi bi-inbox"></i></div>
                                    <div class="av-empty-title">No records found</div>
                                    <div class="av-empty-sub">Try adjusting your search or filter to find what you're looking for.</div>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($violations as $idx => $v): ?>
                        <?php
                            $status_val = $v['status'] ?? 'Pending';
                            $totalAmt   = (float)($v['total_penalty_sum'] ?? $v['penalty_amount'] ?? 0);
                            $rawTypes   = !empty($v['concatenated_violations']) ? $v['concatenated_violations'] : $v['violation_type'];
                            $types      = explode('||', $rawTypes);
                            $rowClass   = match(strtolower($status_val)) {
                                'paid'      => 'av-row--paid',
                                'cancelled' => 'av-row--cancelled',
                                default     => 'av-row--pending',
                            };
                            $dotColor   = match(strtolower($status_val)) {
                                'paid'      => '#10b981',
                                'cancelled' => '#94a3b8',
                                default     => '#f59e0b',
                            };
                        ?>
                        <tr class="av-row <?= $rowClass ?>"
                            style="animation-delay: <?= ($idx * 0.035) ?>s"
                            onclick="window.location='<?= base_url('penalties/view/' . $v['id']) ?>'"
                            title="Click to view details">

                            <!-- Ticket ID -->
                            <td class="ps-4">
                                <span class="av-ticket-pill font-monospace"><?= esc($v['ticket_id'] ?? 'N/A') ?></span>
                            </td>

                            <!-- Driver & Vehicle -->
                            <td>
                                <?php
                                    $driverName = $v['driver_name'] ?? 'Unknown';
                                    $plate = trim($v['license_plate'] ?? '');
                                    $skipPlate = in_array(strtolower($plate), ['no', 'none', 'n/a', '-', '']);
                                    $initials = strtoupper(implode('', array_map(fn($w) => $w[0] ?? '', array_slice(explode(' ', $driverName), 0, 2))));
                                    $avatarColors = ['#6366f1','#3b82f6','#0ea5e9','#14b8a6','#10b981','#8b5cf6','#f59e0b','#ef4444','#ec4899'];
                                    $avatarBg = $avatarColors[abs(crc32($driverName)) % count($avatarColors)];
                                ?>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="av-avatar" style="background: <?= $avatarBg ?>; flex-shrink:0;"><?= esc($initials) ?></div>
                                    <div>
                                        <div class="fw-semibold" style="color:var(--text-strong); white-space:nowrap;"><?= esc($driverName) ?></div>
                                        <?php if (!$skipPlate): ?>
                                            <span class="av-plate-pill font-monospace"><?= esc($plate) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Violations -->
                            <td class="small" style="max-width:200px;">
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($types as $t): ?>
                                        <?php $vName = trim(explode('::', $t)[0]); ?>
                                        <li class="d-flex align-items-start gap-1">
                                            <span style="color:<?= $dotColor ?>; font-size:1.1rem; line-height:1; flex-shrink:0; margin-top:1px;">•</span>
                                            <span class="fw-medium" style="color:var(--text-body);"><?= esc($vName) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>

                            <!-- Breakdown -->
                            <td class="small font-monospace text-muted">
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($types as $t2): ?>
                                        <?php
                                            $parts2 = explode('::', $t2);
                                            $vAmt   = isset($parts2[1]) && $parts2[1] !== '' ? (float)$parts2[1] : null;
                                        ?>
                                        <li><?= $vAmt !== null ? '₱'.number_format($vAmt, 2) : '—' ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>

                            <!-- Total Fine -->
                            <td>
                                <?php if (strtolower($status_val) === 'paid'): ?>
                                    <span class="av-total av-total--paid">₱<?= number_format($totalAmt, 2) ?></span>
                                <?php else: ?>
                                    <span class="av-total av-total--pending">₱<?= number_format($totalAmt, 2) ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- Status Badge -->
                            <td>
                                <?php if ($status_val === 'Pending'): ?>
                                    <span class="av-status-badge av-status--pending">
                                        <span class="av-pulse-dot"></span>Pending
                                    </span>
                                <?php elseif ($status_val === 'Paid'): ?>
                                    <span class="av-status-badge av-status--paid">
                                        <i class="bi bi-check-circle-fill me-1"></i>Paid
                                    </span>
                                <?php else: ?>
                                    <span class="av-status-badge av-status--cancelled">
                                        <i class="bi bi-x-circle-fill me-1"></i>Cancelled
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Date -->
                            <td style="white-space:nowrap;">
                                <div class="d-flex align-items-center gap-1 text-muted small">
                                    <i class="bi bi-calendar3" style="font-size:0.8rem; flex-shrink:0;"></i>
                                    <span><?= date('M d, Y', strtotime($v['violation_date'])) ?></span>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-4" onclick="event.stopPropagation();">
                                <div class="av-action-group">
                                    <a href="<?= base_url('penalties/view/' . $v['id']) ?>" class="av-action-btn av-action--view" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($status_val === 'Pending'): ?>
                                        <a href="<?= base_url('penalties/pay/' . $v['id']) ?>" class="av-action-btn av-action--pay" title="Record Payment">
                                            <i class="bi bi-credit-card"></i>
                                        </a>
                                        <button type="button" class="av-action-btn av-action--cancel btn-cancel" data-id="<?= $v['id'] ?>" title="Cancel Ticket">
                                            <i class="bi bi-x-lg"></i>
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
        <div class="av-card-footer">
            <?= $pager->links('all', 'bootstrap_pagination') ?>
        </div>
    </div>
</div>

<!-- ═══ CANCEL MODAL ═════════════════════════════════════════════════ -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px; overflow:hidden;">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #ef4444, #dc2626); padding:1.25rem 1.5rem;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Cancel Violation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="fw-bold mb-1" style="color:var(--text-strong);">Are you sure you want to cancel this violation?</p>
                    <p class="text-muted small mb-3">This action will void the ticket and mark it as Cancelled. This cannot be undone.</p>
                    <div class="mb-0">
                        <label class="form-label fw-bold small">Reason for Cancellation</label>
                        <textarea class="form-control" name="reason" rows="3" required
                                  placeholder="Provide a reason for the cancellation..."
                                  style="border-radius:12px; border-color:#e2e8f0; resize:none;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Keep Ticket</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm fw-bold" style="border-radius:10px;">
                        <i class="bi bi-x-circle me-1"></i>Void Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* ══ PAGE HEADER BAND ═══════════════════════════════════════════ */
.av-page-header {
    background: linear-gradient(135deg, #f8faff 0%, #ffffff 60%, #f0f9ff 100%);
    border: 1px solid #e8f0fe;
    border-radius: 18px;
    margin-bottom: 1.25rem;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 2px 12px rgba(59,130,246,0.05);
}
.av-page-header-inner { gap: 1rem; }
.av-page-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
    font-size: 1rem;
    flex-shrink: 0;
    box-shadow: 0 3px 10px rgba(37,99,235,0.22);
}

/* ══ CONTROLS ════════════════════════════════════════════════════ */
.av-filter-wrap {
    position: relative;
}
.av-filter-icon {
    position: absolute;
    left: 11px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.82rem;
    pointer-events: none;
    z-index: 2;
}
.av-filter-select {
    appearance: none;
    padding: 0.42rem 2rem 0.42rem 2rem;
    border-radius: 11px;
    border: 1.5px solid #e2e8f0;
    background: rgba(255,255,255,0.9);
    color: var(--text-strong);
    font-size: 0.86rem;
    font-weight: 600;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.av-filter-select:focus {
    outline: none;
    border-color: #a5b4fc;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}
.search-wrapper { position: relative; }
.search-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.88rem;
    pointer-events: none;
    z-index: 2;
}
.av-search-input {
    padding-left: 2.3rem !important;
    min-width: 265px;
    border-radius: 11px !important;
    border: 1.5px solid #e2e8f0 !important;
    background: rgba(255,255,255,0.9) !important;
    font-size: 0.88rem;
    transition: all 0.25s cubic-bezier(0.16,1,0.3,1);
}
.av-search-input:focus {
    border-color: #a5b4fc !important;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.1) !important;
    background: #fff !important;
    min-width: 310px;
}
.av-clear-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1.5px solid #fca5a5;
    background: #fff5f5;
    color: #ef4444;
    text-decoration: none;
    font-size: 0.8rem;
    transition: all 0.2s ease;
}
.av-clear-btn:hover {
    background: #ef4444;
    color: #fff;
    border-color: #ef4444;
    transform: scale(1.07);
}
.av-new-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.44rem 1.1rem;
    border-radius: 11px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #fff;
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(37,99,235,0.25);
    transition: all 0.22s cubic-bezier(0.16,1,0.3,1);
    white-space: nowrap;
}
.av-new-btn:hover {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37,99,235,0.32);
}

/* ══ CARD ════════════════════════════════════════════════════════ */
.av-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid #e8ecf4;
    box-shadow: 0 4px 24px rgba(15,23,42,0.06);
    overflow: hidden;
}
.av-card-footer {
    padding: 0.9rem 1.5rem;
    border-top: 1px solid #f1f5f9;
    background: #fafbff;
}

/* ══ TABLE ═══════════════════════════════════════════════════════ */
.av-table thead th {
    background: #1e293b;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 700;
    color: #e2e8f0;
    border-bottom: 2px solid #334155;
    padding: 14px 12px;
    white-space: nowrap;
}
.av-table thead th:first-child { padding-left: 1.5rem; }
.av-table thead th:last-child  { padding-right: 1.5rem; }

/* ── STATUS BARS AT REST (inset shadow — works regardless of border-collapse) ── */
.av-table tbody tr.av-row--pending {
    background: rgba(254,252,232,0.5);
    box-shadow: inset 5px 0 0 #f59e0b, inset 8px 0 12px rgba(245,158,11,0.08);
}
.av-table tbody tr.av-row--paid {
    background: rgba(240,253,248,0.6);
    box-shadow: inset 5px 0 0 #10b981, inset 8px 0 12px rgba(16,185,129,0.08);
}
.av-table tbody tr.av-row--cancelled {
    background: rgba(248,250,252,0.7);
    box-shadow: inset 5px 0 0 #94a3b8, inset 8px 0 12px rgba(148,163,184,0.08);
}

.av-table tbody tr.av-row {
    cursor: pointer;
    border-color: #f1f5f9;
    transition: background 0.28s cubic-bezier(0.34,1.56,0.64,1),
                box-shadow  0.28s cubic-bezier(0.34,1.56,0.64,1),
                transform   0.28s cubic-bezier(0.34,1.56,0.64,1);
    animation: avRowIn 0.42s cubic-bezier(0.16,1,0.3,1) both;
}
/* ── HOVER: vivid glow + slide ── */
.av-table tbody tr.av-row--pending:hover {
    background: #fffbeb !important;
    box-shadow: inset 6px 0 0 #f59e0b,
                inset 10px 0 20px rgba(245,158,11,0.14),
                0 4px 24px rgba(245,158,11,0.12);
    transform: translateX(4px);
}
.av-table tbody tr.av-row--paid:hover {
    background: #ecfdf5 !important;
    box-shadow: inset 6px 0 0 #10b981,
                inset 10px 0 20px rgba(16,185,129,0.14),
                0 4px 24px rgba(16,185,129,0.12);
    transform: translateX(4px);
}
.av-table tbody tr.av-row--cancelled:hover {
    background: #f8fafc !important;
    box-shadow: inset 6px 0 0 #94a3b8,
                inset 10px 0 20px rgba(148,163,184,0.14),
                0 4px 24px rgba(148,163,184,0.10);
    transform: translateX(4px);
}
/* fallback for any unclassed av-row */
.av-table tbody tr.av-row:not([class*="av-row--"]):hover {
    background: #f0f4ff !important;
    box-shadow: inset 6px 0 0 #3b82f6, 0 4px 24px rgba(59,130,246,0.10);
    transform: translateX(4px);
}

.av-table td { padding: 13px 12px; vertical-align: middle; }
@keyframes avRowIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ══ TICKET PILL ═════════════════════════════════════════════════ */
.av-ticket-pill {
    display: inline-block;
    background: linear-gradient(135deg, #eff6ff 0%, #e0eaff 100%);
    border: 1.5px solid #bfdbfe;
    color: #1e40af;
    border-radius: 9px;
    padding: 3px 10px;
    font-size: 0.79rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    white-space: nowrap;
    box-shadow: 0 1px 4px rgba(37,99,235,0.08);
    transition: background 0.2s, border-color 0.2s, box-shadow 0.2s;
}
tr:hover .av-ticket-pill {
    background: linear-gradient(135deg, #dbeafe 0%, #c7d9ff 100%);
    border-color: #93c5fd;
    box-shadow: 0 2px 8px rgba(37,99,235,0.14);
}

/* ══ PLATE PILL ══════════════════════════════════════════════════ */
.av-plate-pill {
    display: inline-block;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #475569;
    border-radius: 6px;
    padding: 2px 8px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 3px;
    letter-spacing: 0.03em;
}

/* ══ TOTAL FINE ══════════════════════════════════════════════════ */
.av-total {
    font-weight: 800;
    font-size: 0.93rem;
    font-family: 'ui-monospace', 'Fira Code', monospace;
    display: inline-block;
    padding: 2px 6px;
    border-radius: 7px;
}
.av-total--paid {
    color: #047857;
    background: rgba(16,185,129,0.08);
}
.av-total--pending {
    color: #92400e;
    background: rgba(245,158,11,0.09);
    border: 1.5px solid rgba(245,158,11,0.28);
}

/* ══ STATUS BADGES ═══════════════════════════════════════════════ */
.av-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 11px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    white-space: nowrap;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.av-status-badge:hover { transform: scale(1.04); }
.av-status--pending {
    background: rgba(245,158,11,0.1);
    color: #78350f;
    border: 1.5px solid rgba(245,158,11,0.35);
    box-shadow: 0 1px 5px rgba(245,158,11,0.1);
}
.av-status--paid {
    background: rgba(16,185,129,0.09);
    color: #065f46;
    border: 1.5px solid rgba(16,185,129,0.28);
    box-shadow: 0 1px 5px rgba(16,185,129,0.09);
}
.av-status--cancelled {
    background: rgba(148,163,184,0.09);
    color: #475569;
    border: 1.5px solid rgba(148,163,184,0.28);
}
/* Pulsing dot for Pending */
.av-pulse-dot {
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #f59e0b;
    animation: avPulse 1.8s ease-in-out infinite;
    flex-shrink: 0;
    box-shadow: 0 0 0 2px rgba(245,158,11,0.2);
}
@keyframes avPulse {
    0%, 100% { opacity: 1; transform: scale(1); box-shadow: 0 0 0 2px rgba(245,158,11,0.2); }
    50%       { opacity: 0.5; transform: scale(0.72); box-shadow: 0 0 0 4px rgba(245,158,11,0.08); }
}

/* ══ ACTION BUTTONS ══════════════════════════════════════════════ */
.av-action-group {
    display: flex;
    gap: 5px;
    justify-content: flex-end;
    align-items: center;
}
.av-action-btn {
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
    box-shadow: 0 1px 4px rgba(15,23,42,0.05);
}
.av-action-btn:hover {
    transform: translateY(-2px) scale(1.1);
}
.av-action--view   { color: #0284c7; border-color: #bae6fd; background: #f0f9ff; }
.av-action--view:hover  { background: #0ea5e9; color: #fff; border-color: #0ea5e9; box-shadow: 0 4px 14px rgba(14,165,233,0.28); }
.av-action--pay    { color: #059669; border-color: #a7f3d0; background: #f0fdf4; }
.av-action--pay:hover   { background: #10b981; color: #fff; border-color: #10b981; box-shadow: 0 4px 14px rgba(16,185,129,0.28); }
.av-action--cancel { color: #dc2626; border-color: #fca5a5; background: #fff5f5; }
.av-action--cancel:hover{ background: #ef4444; color: #fff; border-color: #ef4444; box-shadow: 0 4px 14px rgba(239,68,68,0.28); }

/* ══ DRIVER AVATAR ═══════════════════════════════════════════════ */
.av-avatar {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    transition: transform 0.2s ease;
}
tr:hover .av-avatar { transform: scale(1.08); }

/* ══ EMPTY STATE ═════════════════════════════════════════════════ */
.av-empty-state { padding: 2rem 0; }
.av-empty-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    color: #94a3b8;
    font-size: 1.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
}
.av-empty-title {
    font-weight: 700;
    color: var(--text-strong);
    font-size: 1rem;
    margin-bottom: 0.3rem;
}
.av-empty-sub {
    color: #94a3b8;
    font-size: 0.85rem;
}

/* ══ PAGINATION ══════════════════════════════════════════════════ */
.av-card-footer .pagination {
    gap: 4px;
    margin: 0;
}
.av-card-footer .page-link {
    border-radius: 9px;
    border: 1.5px solid #e2e8f0;
    color: #475569;
    font-size: 0.82rem;
    font-weight: 600;
    padding: 0.32rem 0.7rem;
    transition: all 0.18s ease;
}
.av-card-footer .page-link:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #2563eb;
}
.av-card-footer .page-item.active .page-link {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border-color: #2563eb;
    color: #fff;
    box-shadow: 0 2px 8px rgba(37,99,235,0.25);
}

/* ══ RESPONSIVE ══════════════════════════════════════════════════ */
@media (max-width: 768px) {
    .av-page-header { border-radius: 14px; padding: 1rem 1rem; }
    .av-card { border-radius: 14px; }
    .av-search-input { min-width: 200px !important; }
    .av-search-input:focus { min-width: 240px !important; }
    .av-table thead th { font-size: 0.65rem; padding: 10px 8px; }
    .av-table td { padding: 10px 8px; }
    .av-action-group { gap: 3px; }
    .av-action-btn { width: 28px; height: 28px; font-size: 0.78rem; }
    .av-avatar { width: 28px; height: 28px; font-size: 0.62rem; border-radius: 8px; }
}
@media (max-width: 576px) {
    .av-new-btn span { display: none; }
    .av-new-btn { padding: 0.44rem 0.7rem; }
}
/* Live search spinner positioning */
.av-search-spinner {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    z-index: 2;
}
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // ── CANCEL MODAL ─────────────────────────────────────────────
    const cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
    const cancelForm  = document.getElementById('cancelForm');
    function attachCancelBtns(scope) {
        scope.querySelectorAll('.btn-cancel').forEach(btn => {
            btn.addEventListener('click', function () {
                cancelForm.action = `<?= base_url('penalties/cancel') ?>/${this.dataset.id}`;
                cancelModal.show();
            });
        });
    }
    attachCancelBtns(document);

    // ── LIVE SEARCH (debounced AJAX, 300ms) ───────────────────────
    const searchInput   = document.getElementById('avSearchInput');
    const searchSpinner = document.getElementById('avSearchSpinner');
    const searchIcon    = document.getElementById('avSearchIcon');
    const statusSelect  = document.querySelector('select[name="status"]');
    const tableBody     = document.querySelector('#violationTable tbody');
    const cardFooter    = document.querySelector('.av-card-footer');
    const baseUrl       = '<?= base_url("penalties/all") ?>';
    let   debounceTimer = null;

    const showSpinner = () => { searchSpinner.classList.remove('d-none'); searchIcon.classList.add('d-none'); };
    const hideSpinner = () => { searchSpinner.classList.add('d-none'); searchIcon.classList.remove('d-none'); };

    async function doSearch(query, status) {
        showSpinner();
        const params = new URLSearchParams();
        if (query)  params.set('search', query);
        if (status) params.set('status', status);
        try {
            const res  = await fetch(`${baseUrl}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await res.text();
            const doc  = new DOMParser().parseFromString(html, 'text/html');
            const newBody   = doc.querySelector('#violationTable tbody');
            const newFooter = doc.querySelector('.av-card-footer');

            if (newBody) {
                tableBody.style.opacity = '0';
                tableBody.style.transition = 'opacity 0.18s ease';
                setTimeout(() => {
                    tableBody.innerHTML = newBody.innerHTML;
                    tableBody.style.opacity = '1';
                    attachCancelBtns(tableBody);
                }, 180);
            }
            if (newFooter && cardFooter) {
                cardFooter.innerHTML = newFooter.innerHTML;
            }
        } catch(e) {
            console.error('Search failed:', e);
        } finally {
            hideSpinner();
        }
    }

    // Trigger on every keystroke with 300ms debounce
    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            doSearch(this.value.trim(), statusSelect ? statusSelect.value : '');
        }, 300);
    });

    // Status dropdown also does live filter (no page reload)
    if (statusSelect) {
        statusSelect.removeAttribute('onchange');
        statusSelect.addEventListener('change', function () {
            clearTimeout(debounceTimer);
            doSearch(searchInput.value.trim(), this.value);
        });
    }
</script>

<?= $this->endSection() ?>
