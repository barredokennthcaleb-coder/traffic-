<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Violator Records') ?> - Traffic System<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4" style="font-family: 'Inter', system-ui, sans-serif;">

    <!-- TOP HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 no-print violator-page-head">
        <div class="d-flex align-items-center gap-3 violator-head-main">
            <a href="<?= base_url('officer/violations') ?>" class="btn btn-outline-secondary btn-sm rounded-3 px-3 shadow-sm violator-back-btn">
                <i class="bi bi-arrow-left me-1"></i> Back to Violations
            </a>
            <div>
                <h4 class="mb-0 fw-bold d-flex align-items-center gap-2" style="color: #0f172a;">
                    <i class="bi bi-person-badge-fill text-primary"></i> Violator Record &amp; Apprehension Dossier
                </h4>
                <p class="text-muted small mb-0">Official CTRAMO traffic violation history &amp; citation ledger</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-primary btn-sm rounded-3 px-3 shadow-sm">
                <i class="bi bi-printer me-1"></i> Print Official Dossier
            </button>
        </div>
    </div>

    <!-- PRINT ONLY HEADER -->
    <div class="print-header d-none text-center mb-4">
        <h3 class="fw-bold mb-0">CITY TRAFFIC MANAGEMENT OFFICE (CTRAMO)</h3>
        <h5 class="text-secondary mb-1">Kabankalan City, Negros Occidental</h5>
        <h4 class="fw-bold mt-2">OFFICIAL VIOLATOR APPREHENSION &amp; OFFENSE RECORD</h4>
        <p class="small text-muted">Generated on <?= date('F d, Y h:i A') ?></p>
        <hr style="border-top: 2px solid #000;">
    </div>

    <!-- PROFILE HERO CARD -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-4 violator-hero-body">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7 d-flex align-items-center gap-3">
                    <div class="violator-avatar shadow-sm" style="width: 72px; height: 72px; border-radius: 20px; background: linear-gradient(135deg, #2563eb, #1e40af); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; flex-shrink: 0;">
                        <?= strtoupper(substr($violator['name'] ?? 'V', 0, 1)) ?>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h3 class="fw-bolder mb-0 text-dark" style="letter-spacing: -0.02em;"><?= esc($violator['name']) ?></h3>
                            <span class="badge <?= ($violator['total_tickets'] ?? 0) > 1 ? 'bg-danger' : 'bg-primary' ?> rounded-pill px-3 py-1 fw-bold" style="font-size: 0.78rem;">
                                <?= esc($violator['offense_degree'] ?? '1st Offense') ?>
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                            <span class="plate-pill font-monospace" style="background:#fff !important; color:#0f172a !important; border:1.5px solid #cbd5e1 !important; border-radius:6px; padding:0.2rem 0.6rem; font-size:0.8rem; font-weight:700; box-shadow:0 1px 2px rgba(15,23,42,0.06);">
                                <i class="bi bi-car-front-fill text-primary me-1"></i><?= esc($violator['license_plate'] ?: 'NO PLATE') ?>
                            </span>
                            <?php if (!empty($violator['license_number']) && !in_array(strtolower(trim($violator['license_number'])), ['no', 'none', 'n/a', '-'])): ?>
                                <span class="dl-pill font-monospace" style="background:#f8fafc; color:#0f172a; border:1.5px solid #cbd5e1; border-radius:6px; padding:0.2rem 0.6rem; font-size:0.8rem; font-weight:700;">
                                    <i class="bi bi-person-badge text-secondary me-1"></i>DL: <?= esc($violator['license_number']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($violator['address']) && $violator['address'] !== 'Kabankalan City'): ?>
                                <span class="badge bg-light text-secondary border px-2 py-1.5" style="font-size: 0.76rem;">
                                    <i class="bi bi-geo-alt me-1 text-danger"></i><?= esc($violator['address']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="row g-2 text-center violator-stat-row">
                        <div class="col-4">
                            <div class="p-3 bg-white rounded-3 border shadow-2xs violator-stat-tile">
                                <div class="text-muted small fw-semibold text-uppercase violator-stat-label" style="font-size: 0.68rem;">Attempts</div>
                                <div class="fs-4 fw-bolder text-dark mt-1 violator-stat-value"><?= (int)($violator['attempts'] ?? $violator['total_tickets'] ?? 0) ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-white rounded-3 border shadow-2xs violator-stat-tile">
                                <div class="text-muted small fw-semibold text-uppercase violator-stat-label" style="font-size: 0.68rem;">Open Tickets</div>
                                <div class="fs-4 fw-bolder text-primary mt-1 violator-stat-value"><?= (int)($violator['total_tickets'] ?? 0) ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 <?= ($violator['pending_fines'] ?? 0) > 0 ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-success-subtle text-success border-success-subtle' ?> rounded-3 border shadow-2xs violator-stat-tile">
                                <div class="small fw-semibold text-uppercase violator-stat-label" style="font-size: 0.68rem;">Unpaid</div>
                                <div class="fs-4 fw-bolder mt-1 violator-stat-value">&#8369;<?= number_format((float)($violator['pending_fines'] ?? 0), 2) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CITATION HISTORY LIST -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" id="citationRecordsCard" style="border: 1px solid #e2e8f0 !important;">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0 fw-bold d-flex align-items-center gap-2" style="color: #0f172a;">
                <i class="bi bi-clock-history text-primary"></i> Complete Citation Records
            </h5>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 0.78rem;">
                <?= count($tickets ?? []) ?> Citation(s) Found
            </span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($tickets)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-folder-x fs-1 text-secondary opacity-50 mb-2 d-block"></i>
                    <h6>No recorded violations found for this individual.</h6>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="citationRecordsTable">
                        <thead class="table-light">
                            <tr style="font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;">
                                <th class="ps-4">Ticket ID</th>
                                <th>Location</th>
                                <th>Violations Infraction</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Total Amount</th>
                                <th>Status</th>
                                <th>Date &amp; Time</th>
                                <th class="text-end pe-4 no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $t): ?>
                                <?php
                                    $isPaid = strtolower($t['status'] ?? '') === 'paid';
                                    $isCancelled = strtolower($t['status'] ?? '') === 'cancelled';
                                ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td class="ps-4">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1.5 rounded-2 fw-bold" style="font-size: 0.82rem;">
                                            <i class="bi bi-ticket-perforated me-1"></i><?= esc($t['ticket_id']) ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 0.85rem; color: #475569;">
                                        <i class="bi bi-geo-alt text-secondary me-1"></i><?= esc($t['location'] ?: 'Kabankalan Proper') ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column py-1">
                                            <?php foreach ($t['violations'] as $vItem): ?>
                                                <div class="d-flex align-items-center gap-1.5 vp-violation-row" style="font-size: 0.82rem; color: #1e293b; min-height: 26px; line-height: 26px;">
                                                    <span style="color: #ef4444; font-size: 1.1rem; line-height: 0.7;">&bull;</span>
                                                    <span class="fw-medium"><?= esc($vItem['type']) ?></span>
                                                    <!-- Same figure as the Amount column, shown on phones where the
                                                         column is collapsed so each name keeps its own fine. -->
                                                    <span class="vp-amount-inline font-monospace fw-semibold ms-auto">&#8369;<?= number_format($vItem['amount'], 2) ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <!-- Each amount lines up with its violation on the left -->
                                    <td class="text-end">
                                        <div class="d-flex flex-column align-items-end py-1">
                                            <?php foreach ($t['violations'] as $vItem): ?>
                                                <div class="font-monospace fw-semibold" style="font-size: 0.8rem; color: #334155; min-height: 26px; line-height: 26px;">
                                                    &#8369;<?= number_format($vItem['amount'], 2) ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="font-monospace fw-bold <?= $isPaid ? 'text-success' : 'text-danger' ?>" style="font-size: 0.95rem;">
                                            &#8369;<?= number_format($t['total_amount'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($isPaid): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i>PAID
                                            </span>
                                            <?php if (!empty($t['receipt_number'])): ?>
                                                <div class="small text-muted font-monospace mt-1" style="font-size: 0.72rem;">OR# <?= esc($t['receipt_number']) ?></div>
                                            <?php endif; ?>
                                        <?php elseif ($isCancelled): ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                                CANCELLED
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                                <i class="bi bi-hourglass-split me-1"></i>PENDING
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark" style="font-size: 0.88rem;">
                                            <?= !empty($t['violation_date']) ? date('M d, Y', strtotime($t['violation_date'])) : '-' ?>
                                        </div>
                                        <div class="small text-muted" style="font-size: 0.75rem;">
                                            <?= !empty($t['violation_date']) ? date('h:i A', strtotime($t['violation_date'])) : '' ?>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4 no-print">
                                        <button type="button" class="btn btn-sm btn-white border citation-menu-btn"
                                                data-menu-id="citationMenu<?= (int) $t['id'] ?>"
                                                aria-haspopup="true" aria-expanded="false" title="Citation Actions">
                                            <i class="bi bi-three-dots-vertical text-secondary"></i>
                                        </button>
                                        <ul class="dropdown-menu citation-menu-pop shadow border-0 rounded-3 py-1" id="citationMenu<?= (int) $t['id'] ?>">
                                            <li>
                                                <a class="dropdown-item py-2 text-dark fw-semibold" href="<?= base_url('officer/view/' . $t['id']) ?>">
                                                    <i class="bi bi-eye me-2 text-primary"></i> View Citation
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-2 text-dark fw-semibold" href="<?= base_url('officer/view/' . $t['id'] . '?print=1') ?>" target="_blank">
                                                    <i class="bi bi-printer me-2 text-success"></i> Print Ticket
                                                </a>
                                            </li>
                                            <?php if (! $isPaid && ! $isCancelled): ?>
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <a class="dropdown-item py-2 text-dark fw-semibold" href="<?= base_url('officer/violations?edit_ticket=' . (int) $t['id']) ?>">
                                                        <i class="bi bi-pencil me-2 text-warning"></i> Edit Citation
                                                    </a>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item py-2 btn-cancel-ticket text-danger fw-semibold" data-id="<?= (int) $t['id'] ?>">
                                                        <i class="bi bi-x-circle me-2 text-danger"></i> Cancel Ticket
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item py-2 btn-delete-ticket text-danger fw-semibold" data-id="<?= (int) $t['id'] ?>">
                                                        <i class="bi bi-trash3 me-2 text-danger"></i> Delete Permanently
                                                    </button>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Cancel Ticket Modal -->
<div class="modal fade" id="cancelTicketModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Cancel Ticket</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelTicketForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="fw-bold mb-2">Cancel this citation ticket?</p>
                    <p class="text-muted small mb-3"><i class="bi bi-info-circle me-1"></i>All infractions printed on the ticket are cancelled together.</p>
                    <label class="form-label fw-bold">Reason</label>
                    <textarea class="form-control shadow-sm" name="reason" rows="3" required placeholder="Provide a reason for cancellation..."></textarea>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Keep Ticket</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm">Confirm Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Ticket Modal -->
<div class="modal fade" id="deleteTicketModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash3 me-2"></i>Delete Ticket</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteTicketForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="fw-bold mb-2">Delete this citation permanently?</p>
                    <p class="text-muted small mb-0"><i class="bi bi-exclamation-triangle me-1 text-danger"></i>This removes every infraction under the ticket and cannot be undone.</p>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Keep Ticket</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm">Delete Permanently</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* The citation actions float as a menu anchored to the page itself, so the
       rounded card, the table's scroll box and the blurred backdrop can never
       clip it or shove it off-screen. */
    .citation-menu-pop {
        position: absolute;
        display: block;
        min-width: 235px;
        z-index: 1080;
        opacity: 0;
        transform: translateY(-6px) scale(.97);
        transform-origin: top right;
        pointer-events: none;
        transition: opacity .16s ease, transform .16s ease;
    }
    .citation-menu-pop.show {
        opacity: 1;
        transform: none;
        pointer-events: auto;
    }
    .citation-menu-btn i {
        display: inline-block;
        transition: transform .25s ease;
    }
    .citation-menu-btn[aria-expanded="true"] i {
        transform: rotate(90deg);
    }

/* ── HERO STAT TILES ──────────────────────────────────────────────
   Three equal columns leave the fine ~80px of usable width below a wide
   desktop, so "₱3,500.00" broke over three lines. The two counters stay
   side by side and the amount gets a full row of its own; at 1400px and
   up there is room for the original three-across row again. */
@media (max-width: 1399.98px) {
    .violator-stat-row > [class*="col-"] {
        flex: 0 0 50%;
        max-width: 50%;
    }
    .violator-stat-row > [class*="col-"]:last-child {
        flex: 0 0 100%;
        max-width: 100%;
    }
    .violator-stat-row > [class*="col-"]:last-child .violator-stat-tile {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.6rem 0.85rem !important;
        text-align: left;
    }
    .violator-stat-row > [class*="col-"]:last-child .violator-stat-value {
        margin-top: 0 !important;
    }
    .violator-stat-row .violator-stat-value {
        white-space: nowrap;
    }
}

@media (max-width: 575.98px) {
    .violator-hero-body {
        padding: 1rem 0.85rem !important;
    }
    .violator-stat-row {
        --bs-gutter-x: 0.65rem;
        --bs-gutter-y: 0.65rem;
    }
    .violator-stat-tile {
        padding: 0.6rem 0.4rem !important;
    }
    .violator-stat-label {
        font-size: 0.6rem !important;
        letter-spacing: 0 !important;
        line-height: 1.2;
    }
    .violator-stat-value {
        font-size: 1.3rem !important;
        line-height: 1.2;
        white-space: nowrap;
        letter-spacing: -0.02em;
    }
    /* Header + records title: one item per row instead of three-line wraps. */
    .violator-page-head {
        gap: 0.75rem !important;
    }
    .violator-head-main {
        flex-wrap: wrap;
        width: 100%;
    }
    .violator-back-btn {
        white-space: nowrap;
    }
    .violator-page-head > .d-flex.gap-2,
    .violator-page-head > .d-flex.gap-2 .btn {
        width: 100%;
    }
    #citationRecordsCard > .card-header {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.5rem;
    }
}

.vp-amount-inline {
    display: none;
}

/* ── TABLE LEGIBILITY ───────────────────────────────────────────────
   Code, status and date columns hold short fixed-shape values; letting
   them wrap turned "Oct 23, 2026" into three lines and split the
   "Date & Time" header. They stay on one line and the variable-length
   violation text absorbs the difference. */
#citationRecordsTable th:nth-child(1),
#citationRecordsTable td:nth-child(1),
#citationRecordsTable th:nth-child(6),
#citationRecordsTable td:nth-child(6),
#citationRecordsTable th:nth-child(7),
#citationRecordsTable td:nth-child(7) {
    white-space: nowrap;
}
#citationRecordsTable th:nth-child(4),
#citationRecordsTable td:nth-child(4),
#citationRecordsTable th:nth-child(5),
#citationRecordsTable td:nth-child(5) {
    white-space: nowrap;
}
/* Reclaim the outer gutters the cells do not need. */
#citationRecordsTable th:first-child,
#citationRecordsTable td:first-child {
    padding-left: 1rem;
}
#citationRecordsTable th:last-child,
#citationRecordsTable td:last-child {
    padding-right: 1rem;
}

/* Give the amount column a gutter so it never touches the infraction text. */
#citationRecordsTable td:nth-child(4),
#citationRecordsTable th:nth-child(4) {
    padding-left: 1rem;
}

/* ── MOBILE: the eight-column table becomes one card per ticket ────────
   Eight columns inside a phone-width card only worked by swiping the
   whole row sideways. Each ticket is now a card: ticket + status on the
   first line, location + date on the second, and the amounts sit beside
   their violations so the pairing survives. No record detail changes. */
@media (max-width: 767.98px) {
    #citationRecordsTable {
        min-width: 0 !important;
    }
    #citationRecordsTable thead {
        display: none;
    }
    #citationRecordsTable tbody tr {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 0.4rem 0.75rem;
        align-items: start;
        margin: 0 0 0.85rem;
        padding: 0.75rem 0.9rem 0.85rem;
        border: 1px solid var(--border-soft, #e2e8f0);
        border-radius: 14px;
        background: var(--surface-card, #fff);
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
    }
    #citationRecordsTable tbody tr:last-child {
        margin-bottom: 0.35rem;
    }
    #citationRecordsTable tbody td {
        display: block;
        width: auto;
        padding: 0 !important;
        border: 0 !important;
    }
    /* Bootstrap paints the hover state per cell; in the card layout that
       shows as grey patches, so the card itself carries the highlight. */
    #citationRecordsTable.table-hover tbody tr:hover > td {
        background-color: transparent !important;
    }
    #citationRecordsTable tbody td:nth-child(1) { grid-area: 1 / 1; }
    #citationRecordsTable tbody td:nth-child(2) { grid-area: 2 / 1; }
    #citationRecordsTable tbody td:nth-child(3) { grid-area: 3 / 1 / 3 / 3; }
    /* The fine now travels inside each violation row, so the separate
       amount column is not repeated in the card. */
    #citationRecordsTable tbody td:nth-child(4) { display: none !important; }
    #citationRecordsTable .vp-violation-row {
        align-items: flex-start !important;
        min-height: 0 !important;
        line-height: 1.35 !important;
        padding: 2px 0;
    }
    #citationRecordsTable .vp-violation-row > span:first-child {
        line-height: 1.2 !important;
        margin-top: 1px;
    }
    #citationRecordsTable .vp-amount-inline {
        display: inline-block;
        margin-left: 0.6rem;
        white-space: nowrap;
        font-size: 0.78rem;
        opacity: 0.85;
    }
    #citationRecordsTable tbody td:nth-child(6) { grid-area: 1 / 2; justify-self: end; }
    #citationRecordsTable tbody td:nth-child(7) { grid-area: 2 / 2; justify-self: end; text-align: right; }
    #citationRecordsTable tbody td:nth-child(5) {
        grid-area: 4 / 1;
        display: flex;
        align-items: baseline;
        gap: 0.4rem;
        margin-top: 0.15rem;
        padding-top: 0.5rem !important;
        border-top: 1px dashed var(--border-soft, #e2e8f0) !important;
    }
    #citationRecordsTable tbody td:nth-child(5)::before {
        content: "TOTAL";
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        color: #64748b;
    }
    #citationRecordsTable tbody td:nth-child(8) {
        grid-area: 4 / 2;
        justify-self: end;
        margin-top: 0.15rem;
        padding-top: 0.4rem !important;
    }
    #citationRecordsTable tbody td:nth-child(6) .badge {
        font-size: 0.72rem !important;
    }
}

@media print {
    .no-print, .sidebar, .navbar, .ht-header, .btn, .breadcrumb, #sidebar-wrapper {
        display: none !important;
    }
    .print-header {
        display: block !important;
    }
    body, .main-content, .container-fluid {
        background: #fff !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    .card {
        border: 1px solid #ccc !important;
        box-shadow: none !important;
    }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    // Citation actions float over the page: the menu is moved to <body> and
    // positioned with document coordinates, so nothing inside the card can
    // clip it and the blurred backdrop cannot displace it.
    const citationMenus = Array.from(document.querySelectorAll('.citation-menu-pop'));

    const closeCitationMenus = () => {
        citationMenus.forEach(menu => menu.classList.remove('show'));
        document.querySelectorAll('.citation-menu-btn').forEach(btn => btn.setAttribute('aria-expanded', 'false'));
    };

    document.querySelectorAll('.citation-menu-btn').forEach(btn => {
        btn.addEventListener('click', function (event) {
            event.stopPropagation();
            // Look the menu up by id: it lives on <body> once it has been opened,
            // so it is no longer the button's next sibling.
            const menu = document.getElementById(this.dataset.menuId || '');
            if (!menu || !menu.classList.contains('citation-menu-pop')) return;

            const wasOpen = menu.classList.contains('show');
            closeCitationMenus();
            if (wasOpen) return;

            if (menu.parentElement !== document.body) {
                document.body.appendChild(menu);
            }
            menu.classList.add('show');
            this.setAttribute('aria-expanded', 'true');

            // Anchor it under its own button, flipping upward and shifting
            // sideways whenever it would leave the screen.
            const buttonRect = this.getBoundingClientRect();
            const menuWidth  = menu.offsetWidth  || 235;
            const menuHeight = menu.offsetHeight || 210;

            let top = buttonRect.bottom + window.scrollY + 6;
            if (buttonRect.bottom + menuHeight + 12 > window.innerHeight) {
                top = Math.max(window.scrollY + 8, buttonRect.top + window.scrollY - menuHeight - 6);
            }

            let left = buttonRect.right + window.scrollX - menuWidth;
            left = Math.min(Math.max(left, window.scrollX + 8), window.scrollX + document.documentElement.clientWidth - menuWidth - 8);

            menu.style.top  = `${Math.round(top)}px`;
            menu.style.left = `${Math.round(left)}px`;
        });
    });

    document.addEventListener('click', closeCitationMenus);
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeCitationMenus();
    });
    window.addEventListener('scroll', closeCitationMenus, true);

    const cancelModalEl = document.getElementById('cancelTicketModal');
    const cancelForm    = document.getElementById('cancelTicketForm');
    if (cancelModalEl && cancelForm) {
        const cancelModal = new bootstrap.Modal(cancelModalEl);
        document.querySelectorAll('.btn-cancel-ticket').forEach(btn => {
            btn.addEventListener('click', function () {
                cancelForm.action = `<?= base_url('officer/cancel') ?>/${this.dataset.id}`;
                cancelModal.show();
            });
        });
        cancelForm.addEventListener('submit', function () {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Cancelling...';
            }
        });
    }

    const deleteModalEl = document.getElementById('deleteTicketModal');
    const deleteForm    = document.getElementById('deleteTicketForm');
    if (deleteModalEl && deleteForm) {
        const deleteModal = new bootstrap.Modal(deleteModalEl);
        document.querySelectorAll('.btn-delete-ticket').forEach(btn => {
            btn.addEventListener('click', function () {
                deleteForm.action = `<?= base_url('officer/delete') ?>/${this.dataset.id}`;
                deleteModal.show();
            });
        });
        deleteForm.addEventListener('submit', function () {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Deleting...';
            }
        });
    }
})();
</script>
<?= $this->endSection() ?>
