<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Violator Profile - <?= esc($violator['driver_name'] ?? 'Record') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $fullName = trim((string) ($violator['driver_name'] ?? ''));

    if ($fullName === '') {
        $fullName = trim(implode(' ', array_filter([
            $violator['first_name'] ?? '',
            $violator['middle_name'] ?? '',
            $violator['last_name'] ?? '',
        ], static fn($part) => trim((string) $part) !== '')));
    }

    if ($fullName === '') {
        $fullName = 'Unknown Violator';
    }

    $initial = strtoupper(substr($fullName, 0, 1));
    $isPlaceholder = static fn($value) => \App\Libraries\ViolatorIdentity::isPlaceholder((string) $value);
    $license = trim((string) ($violator['license_number'] ?? ''));
    $plate   = trim((string) ($violator['license_plate'] ?? ''));
    $address = trim((string) ($violator['address'] ?? ''));
    $overall = (string) ($summary['overall_status'] ?? 'Pending');
?>
<div class="container-fluid py-4 vp-page">

    <!-- TOOLBAR -->
    <div class="vp-topbar no-print">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= esc($backUrl) ?>" class="vp-back-btn">
                <i class="bi bi-arrow-left"></i>
                <span><?= esc($backLabel) ?></span>
            </a>
            <span class="vp-breadcrumb">/</span>
            <span class="vp-breadcrumb-current"><?= esc($fullName) ?></span>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= esc($recordUrl) ?>" class="vp-record-btn">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Record Violation</span>
            </a>
        </div>
    </div>

    <!-- PROFILE HEADER -->
    <div class="vp-profile-card premium-reveal" style="--reveal-delay:0s;">
        <div class="vp-profile-main">
            <div class="vp-profile-avatar"><?= esc($initial) ?></div>
            <div class="vp-profile-info">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="vp-profile-name mb-0"><?= esc($fullName) ?></h4>
                    <?php if ($overall === 'Paid'): ?>
                        <span class="vp-status vp-status--paid"><i class="bi bi-check-circle-fill"></i>Paid</span>
                    <?php elseif ($overall === 'Cancelled'): ?>
                        <span class="vp-status vp-status--cancelled"><i class="bi bi-x-circle-fill"></i>Cancelled</span>
                    <?php elseif ($overall === 'No Records'): ?>
                        <span class="vp-status vp-status--none">No records</span>
                    <?php else: ?>
                        <span class="vp-status vp-status--pending"><span class="vp-status-dot"></span>Pending</span>
                    <?php endif; ?>
                </div>

                <div class="vp-profile-meta">
                    <?php if ($license !== '' && ! $isPlaceholder($license)): ?>
                        <span class="vp-tag vp-tag--dl"><i class="bi bi-person-vcard"></i>DL: <?= esc($license) ?></span>
                    <?php endif; ?>
                    <?php if ($plate !== '' && ! $isPlaceholder($plate)): ?>
                        <span class="vp-tag vp-tag--plate"><i class="bi bi-car-front-fill"></i><?= esc(strtoupper($plate)) ?></span>
                    <?php endif; ?>
                    <span class="vp-tag vp-tag--plain"><i class="bi bi-geo-alt"></i><?= esc($address !== '' ? $address : 'Address not provided') ?></span>
                    <?php if (! empty($violator['age'])): ?>
                        <span class="vp-tag vp-tag--plain"><i class="bi bi-calendar3"></i><?= (int) $violator['age'] ?> years old</span>
                    <?php endif; ?>
                    <?php if (! empty($violator['owner_name'])): ?>
                        <span class="vp-tag vp-tag--plain"><i class="bi bi-truck"></i>Owner: <?= esc($violator['owner_name']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="vp-profile-note">
                    <i class="bi bi-shield-check me-1"></i>
                    Records grouped by stable identity (licence number first, then name + plate). Editing or printing a ticket never adds an attempt.
                </div>
            </div>
        </div>

        <div class="vp-summary-grid">
            <div class="vp-summary-item">
                <div class="vp-summary-label">Attempts</div>
                <div class="vp-summary-value"><?= number_format((int) ($summary['attempt_count'] ?? 0)) ?></div>
                <div class="vp-summary-sub"><?= (int) ($summary['infraction_count'] ?? 0) ?> infraction<?= (int) ($summary['infraction_count'] ?? 0) === 1 ? '' : 's' ?></div>
            </div>
            <div class="vp-summary-item">
                <div class="vp-summary-label">Total Fines</div>
                <div class="vp-summary-value">₱<?= number_format((float) ($summary['total_fines'] ?? 0), 2) ?></div>
                <div class="vp-summary-sub"><?= (int) ($summary['active_ticket_count'] ?? 0) ?> active ticket<?= (int) ($summary['active_ticket_count'] ?? 0) === 1 ? '' : 's' ?></div>
            </div>
            <div class="vp-summary-item">
                <div class="vp-summary-label">Paid</div>
                <div class="vp-summary-value text-success">₱<?= number_format((float) ($summary['total_paid'] ?? 0), 2) ?></div>
                <div class="vp-summary-sub">Payments received</div>
            </div>
            <div class="vp-summary-item">
                <div class="vp-summary-label">Outstanding</div>
                <div class="vp-summary-value <?= (float) ($summary['balance'] ?? 0) > 0 ? 'text-warning' : 'text-muted' ?>">
                    ₱<?= number_format((float) ($summary['balance'] ?? 0), 2) ?>
                </div>
                <div class="vp-summary-sub">
                    <?= (float) ($summary['balance'] ?? 0) > 0 ? 'Balance remaining' : 'Fully settled' ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- TICKET HISTORY -->
        <div class="col-lg-8">
            <div class="vp-section-card premium-reveal" style="--reveal-delay:.08s;">
                <div class="vp-section-head">
                    <div>
                        <div class="vp-section-title"><i class="bi bi-clock-history me-2"></i>Ticket History</div>
                        <div class="vp-section-sub">Every issued ticket is preserved as an individual record.</div>
                    </div>
                    <span class="vp-count-badge"><?= number_format(count($tickets)) ?> ticket<?= count($tickets) === 1 ? '' : 's' ?></span>
                </div>

                <?php if (empty($tickets)): ?>
                    <div class="vp-empty">
                        <div class="vp-empty-icon"><i class="bi bi-inbox"></i></div>
                        <div class="vp-empty-title">No tickets recorded</div>
                        <div class="vp-empty-sub">This violator has no linked citation yet.</div>
                        <a href="<?= esc($recordUrl) ?>" class="btn btn-dark btn-sm mt-3">
                            <i class="bi bi-plus-circle me-1"></i>Record first violation
                        </a>
                    </div>
                <?php else: ?>
                    <div class="vp-ticket-list">
                        <?php foreach ($tickets as $ticket): ?>
                            <?php
                                $ticketStatus = (string) ($ticket['status'] ?? 'Pending');
                                $ticketClass  = $ticketStatus === 'Paid' ? 'paid' : ($ticketStatus === 'Cancelled' ? 'cancelled' : 'pending');
                                $viewUrl      = $ticketViewBase . (int) $ticket['id'];
                                $printUrl     = $viewUrl . '?print=1';
                            ?>
                            <div class="vp-ticket vp-ticket--<?= esc($ticketClass) ?>">
                                <div class="vp-ticket-head">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <button type="button" class="vp-ticket-id font-monospace"
                                                onclick="copyTicket('<?= esc($ticket['ticket_id'], 'js') ?>', this)"
                                                title="Copy ticket ID">
                                            <i class="bi bi-ticket-perforated"></i><?= esc($ticket['ticket_id']) ?>
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                        <?php if ($ticketStatus === 'Paid'): ?>
                                            <span class="vp-ticket-status vp-ticket-status--paid"><i class="bi bi-check-circle-fill"></i>Paid</span>
                                        <?php elseif ($ticketStatus === 'Cancelled'): ?>
                                            <span class="vp-ticket-status vp-ticket-status--cancelled"><i class="bi bi-x-circle-fill"></i>Cancelled</span>
                                        <?php else: ?>
                                            <span class="vp-ticket-status vp-ticket-status--pending"><span class="vp-status-dot"></span>Pending</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="vp-ticket-date">
                                        <i class="bi bi-calendar3"></i>
                                        <?= ! empty($ticket['violation_date']) ? date('M d, Y · h:i A', strtotime((string) $ticket['violation_date'])) : '—' ?>
                                    </div>
                                </div>

                                <div class="vp-ticket-meta">
                                    <span><i class="bi bi-geo-alt me-1"></i><?= esc($ticket['location'] !== '' ? $ticket['location'] : 'Location not recorded') ?></span>
                                    <span><i class="bi bi-person-badge me-1"></i><?= esc($ticket['officer_name'] !== '' ? $ticket['officer_name'] : 'Officer not recorded') ?></span>
                                    <?php if (! empty($ticket['receipt_number'])): ?>
                                        <span><i class="bi bi-receipt me-1"></i>OR: <?= esc($ticket['receipt_number']) ?></span>
                                    <?php endif; ?>
                                    <?php if (! empty($ticket['paid_date'])): ?>
                                        <span><i class="bi bi-check2 me-1"></i>Paid <?= date('M d, Y', strtotime((string) $ticket['paid_date'])) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="vp-infraction-table">
                                    <?php foreach ($ticket['violations'] as $infraction): ?>
                                        <div class="vp-infraction-row">
                                            <div class="vp-infraction-name">
                                                <span class="vp-infraction-dot"></span>
                                                <?= esc($infraction['type']) ?>
                                                <span class="vp-infraction-state vp-infraction-state--<?= esc(strtolower((string) $infraction['status'])) ?>"><?= esc($infraction['status']) ?></span>
                                            </div>
                                            <div class="vp-infraction-amount">₱<?= number_format((float) $infraction['amount'], 2) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="vp-ticket-foot">
                                    <div class="vp-ticket-totals">
                                        <div>
                                            <span class="vp-total-label">Ticket total</span>
                                            <span class="vp-total-value">₱<?= number_format((float) $ticket['total_amount'], 2) ?></span>
                                        </div>
                                        <div>
                                            <span class="vp-total-label">Paid</span>
                                            <span class="vp-total-value vp-total-value--paid">₱<?= number_format((float) $ticket['paid_amount'], 2) ?></span>
                                        </div>
                                        <div>
                                            <span class="vp-total-label">Balance</span>
                                            <span class="vp-total-value <?= (float) $ticket['balance'] > 0 ? 'vp-total-value--balance' : 'vp-total-value--clear' ?>">₱<?= number_format((float) $ticket['balance'], 2) ?></span>
                                        </div>
                                    </div>

                                    <div class="vp-ticket-actions no-print">
                                        <a href="<?= esc($viewUrl) ?>" class="vp-action vp-action--view" title="View ticket">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= esc($printUrl) ?>" class="vp-action" title="Print ticket" target="_blank" rel="noopener">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <?php if ($ticketStatus === 'Pending' && ! empty($isAdmin)): ?>
                                            <a href="<?= base_url('penalties/pay/' . (int) $ticket['id']) ?>" class="vp-action vp-action--pay" title="Record payment">
                                                <i class="bi bi-credit-card"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($ticketStatus === 'Pending'): ?>
                                            <button type="button" class="vp-action vp-action--cancel btn-open-cancel"
                                                    data-ticket="<?= esc($ticket['ticket_id'], 'attr') ?>"
                                                    data-action="<?= esc($cancelBase . (int) $ticket['id'], 'attr') ?>"
                                                    title="Cancel ticket">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (! empty($ticket['notes'])): ?>
                                    <div class="vp-ticket-notes"><i class="bi bi-sticky me-1"></i><?= esc($ticket['notes']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SIDE PANEL -->
        <div class="col-lg-4">
            <div class="vp-section-card premium-reveal mb-3" style="--reveal-delay:.12s;">
                <div class="vp-section-head">
                    <div>
                        <div class="vp-section-title"><i class="bi bi-car-front me-2"></i>Associated Vehicles</div>
                        <div class="vp-section-sub">Plates seen across this violator's tickets.</div>
                    </div>
                </div>
                <?php if (empty($vehicles)): ?>
                    <div class="vp-side-empty">
                        <i class="bi bi-car-front"></i>
                        <span>No vehicle plate recorded.</span>
                    </div>
                <?php else: ?>
                    <div class="vp-vehicle-list">
                        <?php foreach ($vehicles as $vehicle): ?>
                            <div class="vp-vehicle">
                                <div class="vp-vehicle-plate"><?= esc($vehicle['plate']) ?></div>
                                <div class="vp-vehicle-meta">
                                    <span><?= (int) $vehicle['count'] ?> ticket<?= (int) $vehicle['count'] === 1 ? '' : 's' ?></span>
                                    <?php if (! empty($vehicle['last_seen'])): ?>
                                        <span>Last seen <?= date('M d, Y', strtotime((string) $vehicle['last_seen'])) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="vp-section-card premium-reveal" style="--reveal-delay:.16s;">
                <div class="vp-section-head">
                    <div>
                        <div class="vp-section-title"><i class="bi bi-info-circle me-2"></i>Identity Details</div>
                        <div class="vp-section-sub">Matching uses licence, then name + plate.</div>
                    </div>
                </div>
                <div class="vp-detail-list">
                    <div class="vp-detail-row">
                        <span class="vp-detail-label">Full name</span>
                        <span class="vp-detail-value"><?= esc($fullName) ?></span>
                    </div>
                    <div class="vp-detail-row">
                        <span class="vp-detail-label">Licence no.</span>
                        <span class="vp-detail-value"><?= esc($license !== '' && ! $isPlaceholder($license) ? $license : 'Not provided') ?></span>
                    </div>
                    <div class="vp-detail-row">
                        <span class="vp-detail-label">Primary plate</span>
                        <span class="vp-detail-value"><?= esc($plate !== '' && ! $isPlaceholder($plate) ? strtoupper($plate) : 'Not provided') ?></span>
                    </div>
                    <div class="vp-detail-row">
                        <span class="vp-detail-label">Address</span>
                        <span class="vp-detail-value"><?= esc($address !== '' ? $address : 'Not provided') ?></span>
                    </div>
                    <div class="vp-detail-row">
                        <span class="vp-detail-label">Age</span>
                        <span class="vp-detail-value"><?= ! empty($violator['age']) ? (int) $violator['age'] : 'Not provided' ?></span>
                    </div>
                    <div class="vp-detail-row">
                        <span class="vp-detail-label">Owner</span>
                        <span class="vp-detail-value"><?= esc(! empty($violator['owner_name']) ? $violator['owner_name'] : 'Not provided') ?></span>
                    </div>
                    <div class="vp-detail-row">
                        <span class="vp-detail-label">Cancelled tickets</span>
                        <span class="vp-detail-value"><?= (int) ($summary['cancelled_ticket_count'] ?? 0) ?></span>
                    </div>
                    <div class="vp-detail-row">
                        <span class="vp-detail-label">Record ID</span>
                        <span class="vp-detail-value font-monospace">#<?= (int) ($violator['id'] ?? 0) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CANCEL MODAL -->
<div class="modal fade no-print" id="cancelTicketModal" tabindex="-1" aria-labelledby="cancelTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;overflow:hidden;">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#ef4444,#dc2626);padding:1.1rem 1.35rem;">
                <h5 class="modal-title text-white fw-bold" id="cancelTicketModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Cancel Ticket</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="cancelTicketForm">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="fw-bold mb-1" style="color:var(--text-strong);">Cancel ticket <span class="font-monospace" id="cancelTicketId"></span>?</p>
                    <p class="text-muted small mb-3">All infractions on this ticket will be marked Cancelled. This keeps the historical record intact.</p>
                    <label class="form-label fw-bold small">Reason for cancellation</label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="Provide a reason..." style="border-radius:10px;resize:none;"></textarea>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Keep Ticket</button>
                    <button type="submit" class="btn btn-danger px-4 fw-bold" style="border-radius:9px;">Void Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.vp-page { --vp-ink:#0f172a; --vp-muted:#64748b; --vp-line:#e2e8f0; }

.vp-topbar { display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin-bottom:1rem; }
.vp-back-btn { display:inline-flex; align-items:center; gap:.45rem; padding:.45rem .85rem; border-radius:9px; border:1.5px solid var(--vp-line); background:#fff; color:#334155; font-size:.84rem; font-weight:700; text-decoration:none; }
.vp-back-btn:hover { background:#0f172a; border-color:#0f172a; color:#fff; }
.vp-breadcrumb { color:#cbd5e1; font-weight:700; }
.vp-breadcrumb-current { color:#64748b; font-size:.84rem; font-weight:700; }
.vp-record-btn { display:inline-flex; align-items:center; gap:.45rem; padding:.52rem 1.05rem; border-radius:9px; background:linear-gradient(135deg,#2563eb,#1e40af); color:#fff; font-size:.86rem; font-weight:700; text-decoration:none; box-shadow:0 2px 8px rgba(37,99,235,.3); }
.vp-record-btn:hover { color:#fff; transform:translateY(-1px); }

.vp-profile-card { background:#fff; border:1px solid var(--vp-line); border-radius:16px; padding:1.35rem; box-shadow:0 1px 8px rgba(15,23,42,.05); margin-bottom:1rem; }
.vp-profile-main { display:flex; align-items:flex-start; gap:1rem; }
.vp-profile-avatar { width:64px; height:64px; border-radius:18px; background:linear-gradient(135deg,#eef2ff,#c7d2fe); color:#3730a3; display:inline-flex; align-items:center; justify-content:center; font-size:1.55rem; font-weight:900; flex-shrink:0; border:1px solid #a5b4fc; }
.vp-profile-info { min-width:0; flex:1; }
.vp-profile-name { font-size:1.28rem; font-weight:900; color:var(--vp-ink); letter-spacing:-.02em; }
.vp-profile-meta { display:flex; flex-wrap:wrap; gap:.4rem; margin-top:.55rem; }
.vp-tag { display:inline-flex; align-items:center; gap:.35rem; padding:.24rem .55rem; border-radius:7px; font-size:.75rem; font-weight:700; }
.vp-tag i { font-size:.8rem; }
.vp-tag--dl { background:#eef2ff; color:#3730a3; border:1px solid #c7d2fe; }
.vp-tag--plate { background:#fff; color:#0f172a; border:1.5px solid #94a3b8; letter-spacing:.04em; }
.vp-tag--plain { background:#f8fafc; color:#475569; border:1px solid #e2e8f0; }
.vp-tag--plain i { color:#64748b; }
.vp-profile-note { margin-top:.65rem; font-size:.74rem; color:#94a3b8; }
.vp-profile-note i { color:#10b981; }

.vp-summary-grid { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:.65rem; margin-top:1.15rem; padding-top:1rem; border-top:1px solid #f1f5f9; }
.vp-summary-item { background:#f8fafc; border:1px solid #eef2f7; border-radius:11px; padding:.65rem .75rem; }
.vp-summary-label { font-size:.66rem; font-weight:800; text-transform:uppercase; letter-spacing:.07em; color:#94a3b8; }
.vp-summary-value { font-size:1.02rem; font-weight:900; color:var(--vp-ink); margin-top:.08rem; font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; }
.vp-summary-sub { font-size:.69rem; color:#94a3b8; margin-top:.05rem; }

.vp-section-card { background:#fff; border:1px solid var(--vp-line); border-radius:14px; box-shadow:0 1px 6px rgba(15,23,42,.04); overflow:hidden; }
.vp-section-head { display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; padding:1rem 1.15rem; border-bottom:1px solid #f1f5f9; }
.vp-section-title { font-weight:800; color:var(--vp-ink); font-size:.95rem; }
.vp-section-sub { font-size:.74rem; color:#94a3b8; margin-top:.12rem; }
.vp-count-badge { background:#f1f5f9; border:1px solid #e2e8f0; color:#475569; font-size:.72rem; font-weight:800; border-radius:999px; padding:.22rem .6rem; white-space:nowrap; }

.vp-status { display:inline-flex; align-items:center; gap:5px; padding:.25rem .65rem; border-radius:999px; font-size:.67rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
.vp-status--pending { background:#fffbeb; color:#b45309; border:1px solid #fcd34d; }
.vp-status--paid { background:#ecfdf5; color:#15803d; border:1px solid #86efac; }
.vp-status--cancelled { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
.vp-status--none { background:#f8fafc; color:#64748b; border:1px solid #e2e8f0; }
.vp-status-dot { width:7px; height:7px; border-radius:50%; background:#f59e0b; box-shadow:0 0 6px rgba(245,158,11,.6); animation:vpPulse 2s infinite ease-in-out; }

.vp-ticket-list { padding:.85rem; display:flex; flex-direction:column; gap:.75rem; }
.vp-ticket { border:1px solid #e8edf4; border-radius:12px; padding:.85rem .9rem; background:#fff; position:relative; }
.vp-ticket::before { content:''; position:absolute; left:0; top:12px; bottom:12px; width:3px; border-radius:0 3px 3px 0; background:#f59e0b; }
.vp-ticket--paid::before { background:#10b981; }
.vp-ticket--cancelled::before { background:#ef4444; }
.vp-ticket-head { display:flex; align-items:center; justify-content:space-between; gap:.6rem; flex-wrap:wrap; }
.vp-ticket-id { display:inline-flex; align-items:center; gap:.35rem; background:#f0f7ff; border:1px solid #bfdbfe; color:#1d4ed8; border-radius:7px; padding:.2rem .55rem; font-size:.78rem; font-weight:800; cursor:pointer; }
.vp-ticket-id:hover { background:#dbeafe; }
.vp-ticket-id i:last-child { opacity:.55; font-size:.68rem; }
.vp-ticket-status { display:inline-flex; align-items:center; gap:4px; font-size:.67rem; font-weight:800; border-radius:999px; padding:.2rem .55rem; text-transform:uppercase; letter-spacing:.04em; }
.vp-ticket-status--pending { background:#fffbeb; color:#b45309; border:1px solid #fcd34d; }
.vp-ticket-status--paid { background:#ecfdf5; color:#15803d; border:1px solid #86efac; }
.vp-ticket-status--cancelled { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
.vp-ticket-date { font-size:.73rem; color:#64748b; font-weight:600; white-space:nowrap; }
.vp-ticket-date i { margin-right:.2rem; color:#94a3b8; }
.vp-ticket-meta { display:flex; flex-wrap:wrap; gap:.25rem .9rem; margin-top:.42rem; font-size:.72rem; color:#64748b; }

.vp-infraction-table { margin-top:.6rem; border-top:1px dashed #e2e8f0; }
.vp-infraction-row { display:flex; justify-content:space-between; gap:.75rem; padding:.4rem 0; border-bottom:1px dashed #eef2f7; font-size:.8rem; }
.vp-infraction-row:last-child { border-bottom:none; }
.vp-infraction-name { color:#1e293b; font-weight:600; display:flex; align-items:center; gap:.4rem; flex-wrap:wrap; }
.vp-infraction-dot { width:5px; height:5px; border-radius:50%; background:#ef4444; flex-shrink:0; }
.vp-infraction-state { font-size:.6rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; padding:.1rem .35rem; border-radius:4px; }
.vp-infraction-state--pending { background:#fffbeb; color:#b45309; }
.vp-infraction-state--paid { background:#ecfdf5; color:#15803d; }
.vp-infraction-state--cancelled { background:#fef2f2; color:#b91c1c; }
.vp-infraction-amount { font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-size:.78rem; font-weight:800; color:#334155; white-space:nowrap; }

.vp-ticket-foot { display:flex; align-items:flex-end; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin-top:.65rem; padding-top:.6rem; border-top:1px solid #f1f5f9; }
.vp-ticket-totals { display:flex; gap:1rem; flex-wrap:wrap; }
.vp-total-label { display:block; font-size:.62rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; }
.vp-total-value { font-size:.82rem; font-weight:900; color:#334155; font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; }
.vp-total-value--paid { color:#15803d; }
.vp-total-value--balance { color:#b45309; }
.vp-total-value--clear { color:#94a3b8; }
.vp-ticket-actions { display:flex; gap:.35rem; }
.vp-action { width:32px; height:32px; border-radius:8px; border:1.5px solid #d1d5db; background:#fff; color:#374151; display:inline-flex; align-items:center; justify-content:center; font-size:.82rem; text-decoration:none; transition:all .18s ease; padding:0; }
.vp-action:hover { background:#111827; border-color:#111827; color:#fff; transform:translateY(-1px); }
.vp-action--pay { background:#ecfdf5; border-color:#86efac; color:#15803d; }
.vp-action--pay:hover { background:#15803d; border-color:#15803d; color:#fff; }
.vp-action--cancel { background:#fef2f2; border-color:#fecaca; color:#b91c1c; }
.vp-action--cancel:hover { background:#b91c1c; border-color:#b91c1c; color:#fff; }
.vp-ticket-notes { margin-top:.55rem; padding-top:.5rem; border-top:1px dashed #e2e8f0; font-size:.73rem; color:#64748b; }

.vp-empty { padding:2.5rem 1rem; text-align:center; }
.vp-empty-icon { width:54px; height:54px; border-radius:50%; background:#f1f5f9; border:1.5px solid #e2e8f0; color:#94a3b8; display:inline-flex; align-items:center; justify-content:center; font-size:1.4rem; margin-bottom:.65rem; }
.vp-empty-title { font-weight:800; color:var(--vp-ink); }
.vp-empty-sub { color:#94a3b8; font-size:.82rem; }

.vp-side-empty { padding:1.4rem 1rem; text-align:center; color:#94a3b8; font-size:.8rem; display:flex; flex-direction:column; gap:.4rem; align-items:center; }
.vp-side-empty i { font-size:1.4rem; color:#cbd5e1; }
.vp-vehicle-list { padding:.85rem; display:flex; flex-direction:column; gap:.5rem; }
.vp-vehicle { display:flex; align-items:center; justify-content:space-between; gap:.6rem; padding:.55rem .65rem; border:1px solid #eef2f7; border-radius:9px; background:#f8fafc; }
.vp-vehicle-plate { font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-weight:900; font-size:.85rem; color:#0f172a; letter-spacing:.06em; background:#fff; border:1.5px solid #cbd5e1; border-radius:5px; padding:.12rem .45rem; }
.vp-vehicle-meta { display:flex; flex-direction:column; align-items:flex-end; font-size:.68rem; color:#94a3b8; }

.vp-detail-list { padding:.55rem .95rem .8rem; }
.vp-detail-row { display:flex; justify-content:space-between; gap:.75rem; padding:.5rem 0; border-bottom:1px dashed #eef2f7; font-size:.78rem; }
.vp-detail-row:last-child { border-bottom:none; }
.vp-detail-label { color:#94a3b8; font-weight:700; font-size:.72rem; }
.vp-detail-value { color:#334155; font-weight:600; text-align:right; word-break:break-word; }

@keyframes vpPulse { 0%,100% { opacity:1; transform:scale(1);} 50% { opacity:.35; transform:scale(.7);} }

@media (max-width: 991px) {
    .vp-summary-grid { grid-template-columns:repeat(2, minmax(0,1fr)); }
}
@media (max-width: 768px) {
    .vp-profile-main { flex-direction:column; }
    .vp-profile-avatar { width:52px; height:52px; border-radius:14px; font-size:1.2rem; }
    .vp-ticket-head { align-items:flex-start; flex-direction:column; }
    .vp-ticket-foot { flex-direction:column; align-items:stretch; }
    .vp-ticket-actions { justify-content:flex-end; }
}
@media (max-width: 480px) {
    .vp-summary-grid { grid-template-columns:1fr 1fr; }
    .vp-ticket-totals { gap:.65rem; }
}
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.copyTicket = function (ticketId, el) {
    if (!ticketId) return;
    navigator.clipboard.writeText(ticketId).then(function () {
        const original = el.innerHTML;
        el.innerHTML = '<i class="bi bi-check2"></i>Copied!';
        setTimeout(function () { el.innerHTML = original; }, 1500);
    }).catch(function () {});
};

(function () {
    const modalEl = document.getElementById('cancelTicketModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;

    const modal = new bootstrap.Modal(modalEl);
    const form  = document.getElementById('cancelTicketForm');
    const label = document.getElementById('cancelTicketId');

    document.querySelectorAll('.btn-open-cancel').forEach(function (button) {
        button.addEventListener('click', function () {
            if (form)  form.action = this.dataset.action;
            if (label) label.textContent = this.dataset.ticket || '';
            modal.show();
        });
    });
})();
</script>
<?= $this->endSection() ?>
