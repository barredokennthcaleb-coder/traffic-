<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>My Recorded Violations - Traffic Officer<?= $this->endSection() ?>

<?= $this->section('content') ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <ul class="mb-0 ps-3">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?= $this->include('officer/partials/record_ticket_modal') ?>


<div class="container-fluid pb-4">

    <!-- ═══════════════════════════════════════════════════════════
         TICKET LEDGER
         ═══════════════════════════════════════════════════════════ -->
    <div id="ledgerPane">

    <!-- ═══ CITATION TABLE CARD ═══════════════════════════════════ -->
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm main-table-glass-card premium-reveal" style="--reveal-delay:.3s;">
                
                <!-- Card Header with Filters & Actions -->
                <div class="card-header py-3 px-4 main-table-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    
                    <!-- Left: Title & Filter Chips -->
                    <div class="d-flex align-items-center gap-2 flex-wrap ledger-filter">
                        <span class="fw-bold text-dark me-2 ledger-filter-title" style="font-size: 0.95rem;">
                            <i class="bi bi-list-check me-1 text-primary"></i>My Recorded Violations
                        </span>
                        <div class="ledger-filter-chips d-flex align-items-center gap-2">
                            <div class="vr mx-1 d-none d-md-block" style="height: 20px;"></div>
                            <button type="button" class="btn btn-sm btn-filter-chip btn-filter-all <?= empty($statusFilter) ? 'active' : '' ?>" data-status="" aria-pressed="<?= empty($statusFilter) ? 'true' : 'false' ?>">
                                All <span class="badge ms-1 rounded-pill"><?= $totalViolators ?? 0 ?></span>
                            </button>
                            <button type="button" class="btn btn-sm btn-filter-chip btn-filter-pending <?= ($statusFilter ?? '') === 'pending' ? 'active' : '' ?>" data-status="pending" aria-pressed="<?= ($statusFilter ?? '') === 'pending' ? 'true' : 'false' ?>">
                                Pending <span class="badge filter-badge-pending ms-1 rounded-pill"><?= $pendingViolators ?? 0 ?></span>
                            </button>
                            <button type="button" class="btn btn-sm btn-filter-chip btn-filter-paid <?= ($statusFilter ?? '') === 'paid' ? 'active' : '' ?>" data-status="paid" aria-pressed="<?= ($statusFilter ?? '') === 'paid' ? 'true' : 'false' ?>">
                                Paid <span class="badge filter-badge-paid ms-1 rounded-pill"><?= $paidViolators ?? 0 ?></span>
                            </button>
                        </div>
                    </div>

                    <!-- Right: Search & Record Violation Button -->
                    <div class="d-flex gap-2 flex-wrap align-items-center ms-auto">
                        <div class="modern-search-box position-relative">
                            <i class="bi bi-search search-icon" aria-hidden="true"></i>
                            <input type="text" id="tableSearchInput" class="form-control form-control-sm modern-search-input" placeholder="Search name, plate or license..." aria-label="Search violators by name, plate or license number" value="<?= esc($searchTerm ?? '') ?>" autocomplete="off">
                            <button type="button" class="btn btn-sm btn-clear-search d-none" id="btnClearSearch" aria-label="Clear search">
                                <i class="bi bi-x-circle-fill" aria-hidden="true"></i>
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-new-violation rounded-pill px-3 shadow-sm d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#recordViolationModal">
                            <span class="d-inline-flex align-items-center justify-content-center bg-white text-primary rounded-circle me-1" style="width: 18px; height: 18px; font-size: 11px; flex-shrink: 0;">
                                <i class="bi bi-plus-lg fw-bold" style="-webkit-text-stroke: 0.5px currentColor;"></i>
                            </span>
                            <span>New Violation</span>
                        </button>
                    </div>
                </div>

                <!-- Everything the status chips change lives in here, so it can
                     be swapped in place instead of reloading the page. -->
                <div id="ledgerResults">
                <!-- Table Content -->
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 officer-violations-table" id="officerViolationTable">
                            <colgroup>
                                <col class="col-violator">
                                <col class="col-vehicle">
                                <col class="col-status">
                                <col class="col-actions">
                            </colgroup>
                            <thead class="table-light">
                                <tr>
                                    <!-- Headings sit on the same edge as the data below them:
                                         names, plate/vehicle and status all start at their
                                         cell's left padding, so the labels line up with
                                         the records instead of floating in the middle. -->
                                    <th scope="col" class="text-start">Violator</th>
                                    <th scope="col" class="text-start">Plate &amp; Vehicle</th>
                                    <th scope="col" class="text-start">Status</th>
                                    <th scope="col" class="text-end col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($violations)): ?>
                                    <tr id="noTableDataRow">
                                        <td colspan="4">
                                            <div class="empty-state py-5 text-center">
                                                <div class="empty-state-icon-wrap mb-3 mx-auto">
                                                    <i class="bi bi-people fs-1 text-primary"></i>
                                                </div>
                                                <?php if (! empty($searchTerm) || ! empty($statusFilter)): ?>
                                                    <h6 class="fw-bold text-dark">No Matching Violators</h6>
                                                    <p class="text-muted small mb-3">
                                                        <?= ! empty($searchTerm)
                                                            ? 'No records match "' . esc($searchTerm) . '". Try resetting your search or filters.'
                                                            : 'No violators match the selected status. Try resetting your filters.' ?>
                                                    </p>
                                                <?php else: ?>
                                                    <h6 class="fw-bold text-dark">No Recorded Violators</h6>
                                                    <p class="text-muted small mb-3">You have not recorded a violator yet. Every citation you issue stays on this ledger, including the ones already paid.</p>
                                                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#recordViolationModal">
                                                        <i class="bi bi-plus-circle me-1"></i> Record First Violation
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($violations as $v): ?>
                                    <?php
                                        // Ledger names show the middle name as one
                                        // initial; the full name stays on the record.
                                        $vName = \App\Libraries\ViolatorIdentity::shortName(
                                            $v['first_name'] ?? '',
                                            $v['middle_name'] ?? '',
                                            $v['last_name'] ?? '',
                                            $v['driver_name'] ?? ''
                                        );
                                        if ($vName === '') {
                                            $vName = 'Unknown Violator';
                                        }
                                        $vHistoryUrl = base_url('officer/violator-profile/' . (int) ($v['violator_id'] ?? 0));
                                        // "+" records another citation for this same person: the TCT
                                        // record form opens already filled with their details.
                                        $vAddUrl     = base_url('officer/violations?violator_id=' . (int) ($v['violator_id'] ?? 0));

                                        // A row can carry both: an older ticket that was paid and
                                        // a fresh one still unpaid. The badges state exactly that
                                        // instead of hiding the settled part of the record.
                                        $vPendingAmount = (float) ($v['pending_amount'] ?? 0);
                                        $vPaidAmount    = (float) ($v['paid_amount'] ?? 0);
                                        $vHasPending    = (int) ($v['pending_citation_count'] ?? 0) > 0;
                                        $vHasPaid       = (int) ($v['paid_citation_count'] ?? 0) > 0;
                                        $vStatuses      = trim(($vHasPending ? 'pending ' : '') . ($vHasPaid ? 'paid' : ''));
                                        $vRowClass      = $vHasPending ? 'row-pending' : 'row-paid';

                                        // Sanctions: one per apprehension on the person's whole
                                        // record, settled ones included (the row's ticket line
                                        // stays scoped to this ledger and to the active tab).
                                        // The license count must not shrink just because a
                                        // section or this officer's own citations are on show,
                                        // so the query carries the record-wide count. The third
                                        // sanction is the three-strike mark: the row wears the
                                        // warning sign and its action buttons turn red, visible
                                        // at rest, with the sign answering the pointer on hover.
                                        $vSanctions      = (int) ($v['sanction_ticket_count'] ?? $v['citation_count'] ?? 0);
                                        $vThirdSanction  = $vSanctions >= 3;

                                        $vLicense    = trim((string) ($v['license_number'] ?? ''));
                                        $vPlateNo    = trim((string) ($v['license_plate'] ?? ''));
                                        $vVehicle    = trim((string) ($v['vehicle_type'] ?? ''));
                                        $vMtop       = trim((string) ($v['mtop_number'] ?? ''));
                                        $vBlankValue = static fn (string $value): bool => in_array(strtolower($value), ['', '-', 'n/a', 'none', 'no'], true);
                                        $vHasLicense = ! $vBlankValue($vLicense);
                                        $vHasPlate   = ! $vBlankValue($vPlateNo);
                                        $vHasVehicle = ! $vBlankValue($vVehicle);
                                        $vHasMtop    = ! $vBlankValue($vMtop);

                                    ?>
                                    <tr class="officer-violation-row <?= esc($vRowClass) ?><?= $vThirdSanction ? ' row-third-sanction' : '' ?>" data-status="<?= esc($vStatuses !== '' ? $vStatuses : 'pending') ?>">
                                        <td class="ps-4" data-label="Violator">
                                            <div class="ledger-identity-text">
                                                <div class="driver-name-text">
                                                    <span><?= esc($vName) ?></span>
                                                    <?php if ($vThirdSanction): ?>
                                                        <span class="ledger-sanction-warn" tabindex="0"
                                                              title="<?= esc(sanction_label($vSanctions), 'attr') ?> on this ledger — one sanction per apprehension; the three-strike rule can revoke the driver's license.">
                                                            <span class="ledger-sanction-icon" aria-hidden="true">!</span><?= esc(sanction_label($vSanctions)) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($vHasLicense): ?>
                                                    <div class="ledger-license">License <?= esc($vLicense) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <td data-label="Plate &amp; Vehicle">
                                            <div class="ledger-vehicle">
                                                <span class="ledger-plate<?= $vHasPlate ? '' : ' ledger-plate--none' ?>" <?= $vHasPlate ? '' : 'title="No license plate recorded for this violator"' ?>>
                                                    <?= tricycle_icon() ?><?= esc($vHasPlate ? strtoupper($vPlateNo) : 'No plate') ?>
                                                </span>
                                                <?php if ($vHasVehicle || $vHasMtop): ?>
                                                    <div class="ledger-vehicle-meta">
                                                        <?php if ($vHasVehicle): ?>
                                                            <span class="ledger-vehicle-name"><span class="ledger-field-label">Vehicle: </span><?= esc($vVehicle) ?></span>
                                                        <?php endif; ?>
                                                        <?php if ($vHasMtop): ?>
                                                            <span class="ledger-mtop">MTOP <?= esc($vMtop) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <td data-label="Status &amp; Offense">
                                            <div class="ledger-state">
                                                <div class="ledger-state-chips" role="group" aria-label="Ticket status: <?= (int) ($v['pending_citation_count'] ?? 0) ?> pending, <?= (int) ($v['paid_citation_count'] ?? 0) ?> paid">
                                                    <?php if ($vHasPending): ?>
                                                        <span class="ledger-status-chip ledger-status-chip--pending" title="Still owed on this violator's citations">
                                                            <i class="bi bi-hourglass-split" aria-hidden="true"></i>Pending <span class="ledger-status-count"><?= (int) ($v['pending_citation_count'] ?? 0) ?></span>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($vHasPaid): ?>
                                                        <span class="ledger-status-chip ledger-status-chip--paid" title="Already settled at the cashier">
                                                            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>Paid <span class="ledger-status-count"><?= (int) ($v['paid_citation_count'] ?? 0) ?></span>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- View this violator's records, or add another violation to them -->
                                        <td class="text-end pe-4 col-actions" data-label="Actions">
                                            <div class="d-inline-flex align-items-center gap-2 justify-content-end">
                                                <a href="<?= esc($vHistoryUrl) ?>"
                                                   class="btn btn-sm btn-white border shadow-sm <?= $vThirdSanction ? 'btn-ledger-view--danger' : '' ?>"
                                                   title="<?= $vThirdSanction
                                                       ? 'View the records of ' . esc($vName, 'attr') . ' — ' . esc(sanction_label($vSanctions), 'attr') . ' on this ledger'
                                                       : 'View the records of ' . esc($vName, 'attr') ?>" aria-label="View the records of <?= esc($vName, 'attr') ?>">
                                                    <i class="bi bi-eye text-primary me-1"></i> View
                                                    <?php if ($vThirdSanction): ?>
                                                        <i class="bi bi-exclamation-triangle-fill btn-ledger-warn-sign" aria-hidden="true"></i>
                                                    <?php endif; ?>
                                                </a>
                                                <a href="<?= esc($vAddUrl) ?>"
                                                   class="btn btn-sm btn-primary btn-ledger-add rounded-circle shadow-sm <?= $vThirdSanction ? 'btn-ledger-add--danger' : '' ?>"
                                                   style="width: 32px; height: 32px;"
                                                   title="<?= $vThirdSanction
                                                       ? esc($vName, 'attr') . ' carries ' . esc(sanction_label($vSanctions), 'attr') . ' — record the next apprehension carefully'
                                                       : 'Record another violation for ' . esc($vName, 'attr') ?>" aria-label="Record another violation for <?= esc($vName, 'attr') ?>">
                                                    <i class="bi bi-plus-lg fw-bold"></i>
                                                    <?php if ($vThirdSanction): ?>
                                                        <span class="ledger-add-warn" aria-hidden="true"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                                    <?php endif; ?>
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

                <?php if (isset($pager)): ?>
                    <!-- Same pager placement as the Violators directory: the page
                         count sits at the left edge and the buttons at the right,
                         instead of both huddling in the middle of the footer. -->
                    <div class="ledger-pager">
                        <?= $pager->links('violations', 'bootstrap_pagination') ?>
                    </div>
                <?php endif; ?>
                </div><!-- /#ledgerResults -->
            </div>
        </div>
    </div>
    </div><!-- /#ledgerPane -->

</div><!-- /.container-fluid -->

<div class="modal fade" id="cancelViolationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Cancel Violation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelViolationForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="fw-bold">Are you sure you want to cancel this ticket?</p>
                    <p class="text-muted small mb-3"><i class="bi bi-info-circle me-1"></i>All violations under this ticket will be cancelled.</p>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Reason</label>
                        <textarea class="form-control shadow-sm" name="reason" rows="3" required placeholder="Provide a reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Keep Ticket</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm">Confirm Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->include('officer/partials/edit_ticket_modal') ?>

<div class="modal fade" id="deleteViolationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Delete Violation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteViolationForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="fw-bold mb-1">Delete this violation record permanently?</p>
                    <p class="text-muted small mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Floating Quick-Add Button -->
<button type="button" class="fab-new-violation no-print" data-bs-toggle="modal" data-bs-target="#recordViolationModal" title="Add New Violation" aria-label="Add New Violation">
    <i class="bi bi-plus-lg"></i>
</button>

<style>
    /* ── DASHBOARD HERO BANNER ─────────────────────────────────── */
    .officer-hero-banner {
        position: relative;
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #0066ff 100%);
        border-radius: 20px;
        padding: 1.75rem 2rem;
        overflow: hidden;
        box-shadow: 0 12px 32px rgba(76, 29, 149, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .hero-bg-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(50px);
        pointer-events: none;
        opacity: 0.35;
    }
    .orb-1 { width: 200px; height: 200px; background: #60a5fa; top: -60px; right: 10%; }
    .orb-2 { width: 150px; height: 150px; background: #93c5fd; bottom: -50px; right: 25%; }
    .hero-icon-badge {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    }
    .live-duty-badge {
        display: inline-flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: #ffffff;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .btn-record-hero {
        background: #fff;
        color: #1e3a8a;
        font-weight: 700;
        font-size: 0.9rem;
        border-radius: 12px;
        padding: 0.65rem 1.3rem;
        border: none;
        transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
        display: inline-flex;
        align-items: center;
    }
    .btn-record-hero:hover {
        background: #eff6ff;
        color: #0066ff;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.25) !important;
    }

    /* ── STAT KPI GLASS CARDS ──────────────────────────────────── */
    .stat-glass-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        transition: all 0.25s ease;
        overflow: hidden;
    }
    .stat-glass-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
    }
    .stat-card-body {
        padding: 1.25rem 1.25rem 1rem;
    }
    .stat-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
    }
    .stat-icon-wrap {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }
    .stat-counter-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.03em;
        color: #1e293b;
        margin-bottom: 0.35rem;
    }
    .stat-footer-text {
        font-size: 0.75rem;
    }
    .pulse-dot-amber {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #ca8a04;
        box-shadow: 0 0 6px rgba(202, 138, 4, 0.8);
        animation: pulseAmber 2s infinite;
    }
    @keyframes pulseAmber {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.85); }
    }

    /* ── MAIN TABLE GLASS CARD ─────────────────────────────────── */
    .main-table-glass-card {
        background: #fff;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }
    .main-table-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
    }
    .btn-filter-chip {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-weight: 600;
        font-size: 0.82rem;
        border-radius: 20px;
        padding: 0.35rem 0.85rem;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .btn-filter-chip:hover {
        background: #eff6ff;
        border-color: #93c5fd;
        color: #0066ff;
        transform: translateY(-1px);
    }
    .btn-filter-chip.active,
    .btn-filter-all.active,
    .btn-filter-chip.active:hover,
    .btn-filter-all.active:hover,
    .btn-filter-chip.active:focus-visible,
    .btn-filter-all.active:focus-visible {
        background: #0066ff;
        border-color: #0066ff;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 102, 255, 0.32);
    }
    .btn-filter-all .badge {
        background: #e2e8f0;
        color: #475569;
    }
    .btn-filter-chip .badge {
        font-size: 0.76rem;
        font-weight: 700;
        min-width: 22px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 6px;
        border-radius: 10px;
    }
    .btn-filter-chip.active .badge {
        background: rgba(255, 255, 255, 0.28) !important;
        color: #ffffff !important;
        border: none !important;
    }
    /* One palette for the three filters: blue = all, red = pending,
       emerald = paid. The chip and the rows it filters share a colour. */
    .btn-filter-all:not(.active) {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
    }
    .filter-badge-pending {
        background: #fecaca;
        color: #991b1b;
        border: 1px solid #f87171;
    }
    .btn-filter-pending:not(.active) {
        background: #fef2f2;
        border-color: #fca5a5;
        color: #b91c1c;
    }
    .btn-filter-pending:hover {
        background: #fee2e2;
        border-color: #f87171;
        color: #991b1b;
    }
    .btn-filter-pending.active,
    .btn-filter-pending.active:hover,
    .btn-filter-pending.active:focus-visible {
        background: #dc2626;
        border-color: #dc2626;
        color: #ffffff;
        box-shadow: 0 3px 10px rgba(220, 38, 38, 0.25);
    }
    .btn-filter-pending.active .badge {
        background: rgba(255, 255, 255, 0.28) !important;
        color: #ffffff !important;
        border: none !important;
    }
    .filter-badge-paid {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .btn-filter-paid:not(.active) {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #047857;
    }
    .btn-filter-paid:hover {
        background: #d1fae5;
        border-color: #6ee7b7;
        color: #065f46;
    }
    .btn-filter-paid.active,
    .btn-filter-paid.active:hover,
    .btn-filter-paid.active:focus-visible {
        background: linear-gradient(135deg, #10b981, #059669);
        border-color: #059669;
        color: #ffffff;
        box-shadow: 0 3px 10px rgba(5, 150, 105, 0.32);
    }
    .btn-filter-paid.active .badge {
        background: rgba(255, 255, 255, 0.28) !important;
        color: #ffffff !important;
        border: none !important;
    }

    /* ── LEDGER STATUS CHIPS ──
       One row can hold a settled ticket and a fresh one at the same time, so
       the chips report both amounts instead of collapsing the record to one
       state. */
    .ledger-status-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.72rem;
        font-weight: 700;
        line-height: 1;
        padding: 0.32rem 0.6rem;
        border-radius: 999px;
        white-space: nowrap;
        border: 1px solid transparent;
    }
    .ledger-status-chip i {
        font-size: 0.72rem;
    }
    .ledger-status-chip--pending {
        background: #fee2e2;
        color: #991b1b;
        border-color: #f87171;
        box-shadow: 0 1px 5px rgba(220, 38, 38, 0.14);
    }
    .ledger-status-chip--pending .ledger-status-count {
        background: rgba(220, 38, 38, 0.14);
        color: #991b1b;
    }
    .ledger-status-chip--paid {
        background: #ecfdf5;
        color: #065f46;
        border-color: #10b981;
    }
    /* A row can be pending and paid at once. The number makes the two states
       unambiguous without changing what either chip means. */
    .ledger-status-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 4px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.08);
        font-size: 0.66rem;
        font-weight: 800;
        line-height: 1;
    }
    [data-bs-theme="dark"] .ledger-status-count { background: rgba(255, 255, 255, 0.16); }

    /* ── VIOLATOR LEDGER DETAILS ── */
    .ledger-identity-text { min-width: 0; }
    .ledger-license {
        font-size: 0.76rem;
        color: #64748b;
        margin-top: 2px;
        word-break: break-word;
    }
    .ledger-plate {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        border-radius: 8px;
        padding: 0.24rem 0.6rem;
        font-family: ui-monospace, 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        white-space: nowrap;
    }
    .ledger-plate i, .ledger-plate .tricycle-icon { font-size: 0.72rem; }
    .ledger-plate--none {
        background: #f1f5f9;
        color: #64748b;
        border-color: #e2e8f0;
        font-family: inherit;
        letter-spacing: 0;
    }
    .ledger-vehicle {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.3rem;
        min-width: 0;
    }
    .ledger-vehicle-meta {
        display: flex;
        flex-direction: column;
        gap: 0.12rem;
        font-size: 0.74rem;
        line-height: 1.35;
        min-width: 0;
    }
    .ledger-vehicle-name {
        color: #334155;
        font-weight: 600;
        word-break: break-word;
    }
    /* Labels the line beneath "No plate" as the vehicle, so the two never
       read as one broken license plate. */
    .ledger-field-label {
        color: #64748b;
        font-weight: 500;
    }
    [data-bs-theme="dark"] .ledger-field-label { color: #94a3b8; }
    .ledger-mtop {
        color: #64748b;
        font-family: ui-monospace, 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
        letter-spacing: 0.03em;
    }
    .ledger-state {
        display: flex;
        flex-direction: column;
        /* The STATUS heading starts at the column's left edge on desktop, so
           the pills and the offense line under it start on that same edge.
           The phone card layout re-aligns them to the trailing edge further
           down. */
        align-items: flex-start;
        gap: 0.3rem;
        min-width: 0;
    }
    /* One status per line, all on the same left edge: a row with a single
       state and a row with both states read as the same column instead of two
       pills of different widths sitting side by side. */
    .ledger-state-chips {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
        min-width: 0;
    }
    [data-bs-theme="dark"] .ledger-license {
        color: #94a3b8;
    }
    [data-bs-theme="dark"] .ledger-plate {
        background: #422006;
        color: #fcd34d;
        border-color: #78350f;
    }
    [data-bs-theme="dark"] .ledger-plate--none {
        background: #1e293b;
        color: #94a3b8;
        border-color: #334155;
    }
    [data-bs-theme="dark"] .ledger-vehicle-name {
        color: #cbd5e1;
    }
    [data-bs-theme="dark"] .ledger-mtop {
        color: #94a3b8;
    }

    /* ── NEW VIOLATION BUTTON ──
       Electric blue stays #0066ff at rest and on hover/focus; only the
       shadow and lift change, so the colour never shifts mid-action. */
    .btn-new-violation {
        background: #0066ff;
        border: none;
        color: #ffffff;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.45rem 1rem;
        border-radius: 999px;
        box-shadow: 0 2px 10px rgba(0, 102, 255, 0.3);
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
        white-space: nowrap;
    }
    .btn-new-violation:hover,
    .btn-new-violation:focus-visible {
        background: #0066ff;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 5px 16px rgba(0, 102, 255, 0.45);
    }
    .btn-new-violation:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(0, 102, 255, 0.2);
    }

    #addNewInfractionBtn {
        background: #0066ff;
        border-color: #0066ff;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 102, 255, 0.28);
    }
    #addNewInfractionBtn:hover,
    #addNewInfractionBtn:focus-visible {
        background: #0066ff;
        border-color: #0066ff;
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(0, 102, 255, 0.4);
    }

    #recordViolationModal #submitPrintBtn {
        background: #0066ff;
        border-color: #0066ff;
        color: #ffffff;
        box-shadow: 0 2px 10px rgba(0, 102, 255, 0.3);
    }
    #recordViolationModal #submitPrintBtn:hover,
    #recordViolationModal #submitPrintBtn:focus-visible {
        background: #0066ff;
        border-color: #0066ff;
        color: #ffffff;
        box-shadow: 0 5px 16px rgba(0, 102, 255, 0.45);
    }
    /* Disabled "Processing Ticket..." state keeps the same blue, just dimmed. */
    #recordViolationModal #submitPrintBtn:disabled {
        background: #0066ff;
        border-color: #0066ff;
        color: #ffffff;
        opacity: 0.75;
    }
    #recordViolationModal [data-open-signature] {
        color: #0066ff;
        border-color: #0066ff;
    }
    #recordViolationModal [data-open-signature]:hover,
    #recordViolationModal [data-open-signature]:focus-visible {
        background: #eff6ff;
        color: #0066ff;
        border-color: #0066ff;
    }

    .officer-violations-table .btn-white {
        color: #0052cc;
        border-color: #bfdbfe;
    }
    .officer-violations-table .btn-white:hover {
        background: #eff6ff;
        color: #1e3a8a;
        border-color: #60a5fa;
    }
    .officer-violations-table .btn-ledger-add,
    .officer-violations-table .btn-ledger-add:hover,
    .officer-violations-table .btn-ledger-add:focus-visible {
        background: #0066ff;
        border-color: #0066ff;
        color: #ffffff;
    }

    /* ── THIRD SANCTION — VISIBLE WARNING, RED ACTIONS ──
       Three sanctions on this ledger is the three-strike mark: the warning
       sign stays visible beside the name, the row wears a red spine and both
       action buttons turn red at rest instead of only on hover. Hovering or
       focusing a sign nudges its triangle, and the badge on the + button
       pops, so the warning answers the pointer on the way to the click. */
    .officer-violations-table .row-third-sanction td:first-child {
        box-shadow: inset 3px 0 0 #dc2626;
    }
    .ledger-sanction-warn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 0.14rem 0.55rem 0.14rem 0.2rem;
        border-radius: 999px;
        background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
        font-size: 0.68rem; font-weight: 800; letter-spacing: 0.03em;
        line-height: 1.35; white-space: nowrap; cursor: help;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.08);
        transition: background-color 0.16s ease, border-color 0.16s ease, transform 0.16s ease, box-shadow 0.16s ease;
    }
    .ledger-sanction-icon {
        display: inline-flex; align-items: center; justify-content: center;
        width: 18px; height: 18px; border-radius: 50%;
        background: #dc2626; color: #ffffff;
        font-size: 0.68rem; font-weight: 900; line-height: 1;
        box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.16);
    }
    .ledger-sanction-warn:hover,
    .ledger-sanction-warn:focus-visible {
        background: #fee2e2; border-color: #fca5a5; color: #7f1d1d;
        transform: translateY(-1px) scale(1.03);
        box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.12), 0 4px 12px rgba(220, 38, 38, 0.22);
        outline: 2px solid rgba(220, 38, 38, 0.35); outline-offset: 1px;
    }
    @keyframes ledgerWarnNudge {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-14deg); }
        60% { transform: rotate(12deg); }
    }
    .officer-violations-table .btn-ledger-view--danger {
        background: #fef2f2;
        border-color: #fca5a5;
        color: #b91c1c;
    }
    .officer-violations-table .btn-ledger-view--danger:hover {
        background: #fee2e2;
        border-color: #ef4444;
        color: #991b1b;
    }
    .officer-violations-table .btn-ledger-view--danger .bi-eye { color: #dc2626 !important; }
    .btn-ledger-warn-sign { font-size: 0.68rem; color: #dc2626; margin-left: 2px; }
    .officer-violations-table .btn-ledger-view--danger:hover .btn-ledger-warn-sign,
    .officer-violations-table .btn-ledger-view--danger:focus-visible .btn-ledger-warn-sign {
        animation: ledgerWarnNudge 0.5s ease;
    }
    .officer-violations-table .btn-ledger-add--danger {
        position: relative;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        border-color: #dc2626;
        color: #ffffff;
    }
    .officer-violations-table .btn-ledger-add--danger:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        border-color: #b91c1c;
        color: #ffffff;
    }
    .ledger-add-warn {
        position: absolute; top: -4px; right: -4px;
        width: 15px; height: 15px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        background: #ffffff; color: #dc2626; font-size: 0.58rem; line-height: 1;
        border: 1px solid #fca5a5;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.18);
        transition: transform 0.16s ease, color 0.16s ease, border-color 0.16s ease;
    }
    .officer-violations-table .btn-ledger-add--danger:hover .ledger-add-warn,
    .officer-violations-table .btn-ledger-add--danger:focus-visible .ledger-add-warn {
        color: #991b1b; border-color: #ef4444;
        transform: scale(1.18) rotate(-8deg);
    }

    /* ── FLOATING QUICK-ADD BUTTON ── */
    .fab-new-violation {
        position: fixed;
        right: 1.75rem;
        bottom: 1.75rem;
        width: 58px;
        height: 58px;
        border: none;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #0066ff;
        color: #ffffff;
        box-shadow: 0 8px 24px rgba(0, 102, 255, 0.4);
        z-index: 1030;
        transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s ease;
    }
    .fab-new-violation i {
        font-size: 1.55rem;
        line-height: 1;
    }
    .fab-new-violation:hover,
    .fab-new-violation:focus-visible {
        background: #0066ff;
        color: #ffffff;
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 12px 30px rgba(0, 102, 255, 0.5);
    }
    .fab-new-violation:active {
        background: #0066ff;
        transform: translateY(0) scale(0.97);
        box-shadow: 0 4px 14px rgba(0, 102, 255, 0.35);
    }
    .fab-new-violation:focus-visible {
        outline: 3px solid rgba(0, 102, 255, 0.35);
        outline-offset: 3px;
    }
    @media (max-width: 768px) {
        /* Phones: the quick-add button grows to a comfortable 60px tap target
           and lifts off the bottom edge (safe-area aware) so browser chrome
           does not clip it. */
        .fab-new-violation {
            right: max(1rem, env(safe-area-inset-right, 0px));
            bottom: calc(1.15rem + env(safe-area-inset-bottom, 0px));
            width: 60px;
            height: 60px;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
            box-shadow: 0 8px 24px rgba(0, 102, 255, 0.45), 0 0 0 5px rgba(0, 102, 255, 0.1);
        }
        .fab-new-violation i {
            font-size: 1.7rem;
        }
    }
    @media (max-width: 576px) {
        /* The row's round + button follows the same rule: 32px is below a
           comfortable thumb target on a phone. */
        .officer-violations-table .btn-ledger-add {
            width: 38px !important;
            height: 38px !important;
        }
        .officer-violations-table .btn-ledger-add i {
            font-size: 1rem;
        }
    }
    /* Wide screens already have the labelled "New Violation" button in the
       header. A second floating + only duplicates it and sits over the last
       row's View / + actions, so keep the FAB for narrow screens only. */
    @media (min-width: 768px) {
        .fab-new-violation { display: none !important; }
    }
    @media (max-width: 767.98px) {
        /* The quick-add appears while scrolling on phones; reserve its space
           so it never covers the final row's buttons. */
        #ledgerPane { padding-bottom: 5.5rem; }
    }
    @media print {
        .fab-new-violation { display: none !important; }
        #ledgerPane { padding-bottom: 0 !important; }
    }

    /* ── SEARCH BOX ────────────────────────────────────────────── */
    .modern-search-box {
        min-width: 260px;
        /* Keep the clear button anchored: the box never grows with the typed
           text, so the X stays at the same spot in the search bar. */
        flex: 0 0 260px;
        width: 260px;
    }
    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-subtle, #5c6b80);
        font-size: 0.85rem;
        pointer-events: none;
    }
    .modern-search-input {
        padding-left: 34px !important;
        padding-right: 30px !important;
        border-radius: 20px !important;
        border: 2px solid #0066ff !important;
        background: #fff !important;
        font-size: 0.85rem !important;
        height: 36px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .modern-search-input:focus {
        border-color: #0066ff !important;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.16) !important;
    }
    .btn-clear-search {
        position: absolute;
        right: 8px;
        /* Pin to the input's full height and center inside a fixed-size box:
           no vertical drift when the field height changes (desktop/mobile). */
        top: 0;
        bottom: 0;
        margin: auto 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        flex: 0 0 24px;
        color: var(--text-subtle, #5c6b80);
        padding: 0;
        border: none;
        background: transparent;
        font-size: 0.85rem;
        line-height: 1;
    }
    .btn-clear-search.d-none { display: none !important; }
    .btn-clear-search:hover { color: #334155; }

    /* ── PAGER ──
       Matches the Violators directory: a full-width strip under the last row,
       so the page count lines up at the left edge and the buttons at the
       right. The footer it used to sit in centred the whole strip. */
    #ledgerResults { transition: opacity 0.15s ease; }
    #ledgerResults.is-updating { opacity: 0.55; }
    .ledger-pager {
        padding: 12px 1.5rem;
        border-top: 1px solid #eef2f7;
        background: #ffffff;
    }

    /* ── TABLE ROW & CELL BADGES (CLEAN, SMOOTH & RESPONSIVE) ── */
    .officer-violations-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    .officer-violations-table thead th {
        background: #f8f9fa;
        color: #475569;
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.85rem 1rem;
        border-bottom: 1.5px solid #e2e8f0;
        white-space: nowrap;
    }
    .officer-violations-table thead th:first-child { padding-left: 1.5rem; }
    .officer-violations-table thead th:last-child  { padding-right: 1.5rem; }
    .officer-violations-table tbody td {
        padding: 0.95rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.88rem;
        vertical-align: middle;
        background: #ffffff;
        transition: background-color 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .driver-name-link {
        cursor: pointer;
        color: inherit;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .driver-name-link:hover .driver-name-text {
        color: #0066ff !important;
        text-decoration: underline;
    }
    .driver-link-icon {
        transition: transform 0.18s ease, opacity 0.18s ease;
    }
    .driver-name-text {
        display: inline-flex; flex-wrap: wrap; align-items: center; gap: 6px;
        color: #0f172a;
        font-weight: 700;
        font-size: 0.93rem;
        letter-spacing: -0.01em;
        transition: color 0.15s ease;
    }
    .amount-text {
        color: #475569;
        font-family: 'ui-monospace', 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
        font-size: 0.84rem;
        font-weight: 600;
    }
    .date-text {
        color: #475569;
        font-size: 0.84rem;
        font-weight: 500;
    }
    .btn-white {
        background: #ffffff;
        color: #475569;
        border-color: #dee2e6;
        transition: all 0.2s ease;
    }
    .btn-white:hover {
        background: #f8f9fa;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    /* ── SMOOTH ROWS & HOVER ── */
    .officer-violations-table tbody tr {
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
        position: relative;
        z-index: 1;
        background: #ffffff;
    }
    .officer-violations-table tbody tr:hover td {
        background-color: #eff6ff !important;
    }
    .officer-violations-table tbody tr:hover {
        z-index: 2;
        transform: translateY(-1.5px);
        box-shadow: 0 4px 14px rgba(124, 58, 237, 0.06);
    }
    /* Elevate row when its action dropdown is opened or focused */
    .officer-violations-table tbody tr:focus-within,
    .officer-violations-table tbody tr:has(.dropdown-menu.show),
    .officer-violations-table tbody tr:has(.show),
    .officer-violations-table tbody tr.has-open-dropdown {
        z-index: 1065 !important;
        position: relative;
    }
    .officer-violations-table tbody tr.row-pending,
    .officer-violations-table tbody tr.row-paid,
    .officer-violations-table tbody tr.row-cancelled {
        background: #ffffff;
    }
    /* Responsive: mobile card layout */
    @media (max-width: 767px) {
        .officer-violations-table {
            min-width: 0;
            width: 100%;
        }
        .officer-violations-table .col-actions {
            width: auto;
            white-space: normal;
        }
        .officer-violations-table thead { display: none; }
        .officer-violations-table tbody tr {
            display: block;
            margin-bottom: 0.85rem;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(15,23,42,0.06);
            transform: none !important;
            padding: 0.4rem 0;
            background: #ffffff !important;
        }
        .officer-violations-table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.55rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.83rem;
            color: #1e293b;
        }
        .officer-violations-table tbody td:last-child { border-bottom: none; }
        /* Keep the violator name and license together in the mobile card. */
        .officer-violations-table tbody td[data-label="Violator"] {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.4rem;
        }
        .officer-violations-table tbody td::before {
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
        /* The name and action buttons are self-explanatory on a card.
           Repeating those two headers only makes each card taller. */
        .officer-violations-table tbody td[data-label="Violator"]::before,
        .officer-violations-table tbody td[data-label="Actions"]::before {
            display: none;
        }
        .officer-violations-table tbody td {
            padding: 0.45rem 0.9rem;
        }
        .officer-violations-table tbody tr {
            margin-bottom: 0.6rem;
        }
        .col-actions { justify-content: flex-end; }

        /* The card layout was hard-coded white, so a dark screen showed a
           white ledger on a navy page. */
        [data-bs-theme="dark"] .officer-violations-table tbody tr,
        [data-bs-theme="dark"] .officer-violations-table tbody td {
            background: #111b2f !important;
            border-color: #1e293b !important;
            color: #e2e8f0;
        }
        [data-bs-theme="dark"] .officer-violations-table tbody td::before {
            color: #94a3b8;
        }

    }

    /* Ticket ID Glass Pill */
    .ticket-id-pill {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #0052cc;
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
        color: #0066ff !important;
        margin-right: 0.25rem;
    }
    .plate-pill:hover {
        background: #f1f5f9 !important;
        border-color: var(--text-subtle, #5c6b80) !important;
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
    .officer-violation-row:hover .violation-chip {
        background: #ffffff;
        border-color: #cbd5e1;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    /* Total Fine Badge (Subtle Soft Colors, High Readability) */
    .total-fine-badge {
        font-size: 0.94rem;
        font-weight: 800;
        letter-spacing: -0.01em;
        padding: 0.25rem 0.65rem;
        border-radius: 7px;
        display: inline-block;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .total-fine-pending {
        color: #854d0e;
        background: #fef9c3;
        border: 1px solid #facc15;
    }
    .total-fine-paid {
        color: #15803d;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
    }
    .officer-violation-row:hover .total-fine-badge {
        transform: scale(1.02);
    }

    /* Status Badges (Alive, Subtle Glow & Dot Animation) */
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
        background: #d97706;
        margin-right: 6px;
        box-shadow: 0 0 6px rgba(217, 119, 6, 0.6);
        animation: pulseMustard 2s infinite ease-in-out;
        flex-shrink: 0;
    }
    @keyframes pulseMustard {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.35; transform: scale(0.75); }
    }
    .status-paid {
        background: #ecfdf5;
        color: #15803d;
        border: 1px solid #86efac;
    }
    .status-paid i {
        color: #16a34a;
    }
    .status-cancelled {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    .status-cancelled i {
        color: #dc2626;
    }
    .officer-violation-row:hover .status-glow-badge {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    }

    /* Action Icon Buttons */
    .btn-action-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #64748b;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        padding: 0;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        text-decoration: none;
    }
    .btn-action-icon:hover {
        transform: translateY(-2px);
    }
    .btn-action-print:hover {
        background: #ecfdf5;
        border-color: #86efac;
        color: #059669;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
    }
    .btn-action-view:hover {
        background: #eff6ff;
        border-color: #93c5fd;
        color: #0066ff;
        box-shadow: 0 4px 10px rgba(124, 58, 237, 0.2);
    }
    .btn-action-more:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #334155;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
    }

    /* Dropdown Menu Polish */
    .officer-violations-table .col-actions {
        position: relative;
    }
    .officer-violations-table .col-actions .dropdown {
        position: relative;
    }
    .officer-violations-table .dropdown-menu,
    .dropdown-menu {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.16) !important;
        padding: 0.4rem !important;
        background-color: #ffffff !important;
        z-index: 1070 !important;
    }
    .dropdown-item {
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 500;
        padding: 0.45rem 0.85rem;
        transition: all 0.15s ease;
        color: #1e293b;
    }
    .dropdown-item:hover {
        background-color: #f1f5f9;
        color: #0f172a;
    }
    .dropdown-item.btn-cancel-violation,
    .dropdown-item.btn-delete-violation {
        color: #dc2626 !important;
    }
    .dropdown-item.btn-cancel-violation:hover,
    .dropdown-item.btn-delete-violation:hover {
        background-color: #fef2f2 !important;
        color: #b91c1c !important;
    }

    /* Modern Pagination */
    .card-footer .pagination {
        margin-bottom: 0;
        gap: 4px;
    }
    .card-footer .page-link {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.4rem 0.75rem;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .card-footer .page-link:hover {
        background: #f1f5f9;
        color: #1e293b;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }
    .card-footer .page-item.active .page-link {
        background: linear-gradient(135deg, #0066ff 0%, #0052cc 100%);
        border-color: #0052cc;
        color: #fff;
        box-shadow: 0 3px 8px rgba(124, 58, 237, 0.25);
    }

    /* Smooth Scrollbar for Table */
    .table-responsive::-webkit-scrollbar {
        height: 7px;
    }
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
        transition: background 0.2s ease;
    }
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: var(--text-subtle, #5c6b80);
    }

    /* ── DARK THEME ADAPTATIONS ────────────────────────────────── */
    [data-bs-theme="dark"] .stat-glass-card,
    [data-bs-theme="dark"] .main-table-glass-card {
        background: #111b2f;
        border-color: #1e293b;
    }
    [data-bs-theme="dark"] .stat-counter-value { color: #f8fafc; }
    /* Names and the ledger title were hard-coded near-black, which left them
       invisible once the dark palette is active. */
    [data-bs-theme="dark"] .driver-name-text { color: #f8fafc; }
    [data-bs-theme="dark"] .main-table-header .text-dark { color: #e2e8f0 !important; }
    [data-bs-theme="dark"] .main-table-header { background: #0f172a; border-color: #1e293b; }
    [data-bs-theme="dark"] .officer-violations-table thead th { background: #0f172a; border-color: #1e293b; color: #94a3b8; }
    [data-bs-theme="dark"] .officer-violations-table tbody td { border-color: #1e293b; }
    [data-bs-theme="dark"] .officer-violations-table tbody tr:hover { background-color: #172554 !important; border-left-color: #60a5fa !important; }
    [data-bs-theme="dark"] .ticket-id-pill { background: #1e293b; border-color: #3b82f6; color: #93c5fd; }
    [data-bs-theme="dark"] .ticket-id-pill:hover { background: #1e3a8a; border-color: #60a5fa; color: #bfdbfe; }
    [data-bs-theme="dark"] .dl-pill { background: #1e293b; color: #94a3b8; border-color: #334155; }
    [data-bs-theme="dark"] .violation-chip { background: #1e293b; border-color: #334155; color: #e2e8f0; }
    [data-bs-theme="dark"] .total-fine-pending { color: #f8fafc; background: #1e293b; border-color: #334155; }
    [data-bs-theme="dark"] .total-fine-paid { color: #34d399; background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.3); }
    [data-bs-theme="dark"] .ledger-status-chip--pending { background: rgba(239, 68, 68, 0.15); border-color: rgba(248, 113, 113, 0.35); color: #fca5a5; }
    [data-bs-theme="dark"] .ledger-status-chip--paid { background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.3); color: #34d399; }
    /* Third sanction in dark mode: solid red warning (white letters) and a
       lifted red View button, so neither sinks into the dark card. */
    [data-bs-theme="dark"] .ledger-sanction-warn { background: #dc2626; border-color: #991b1b; color: #ffffff; }
    [data-bs-theme="dark"] .officer-violations-table .btn-ledger-view--danger {
        background: rgba(239, 68, 68, 0.14); border-color: rgba(248, 113, 113, 0.45); color: #fca5a5;
    }
    [data-bs-theme="dark"] .officer-violations-table .btn-ledger-view--danger:hover {
        background: rgba(239, 68, 68, 0.24); border-color: #ef4444; color: #fecaca;
    }
    [data-bs-theme="dark"] .officer-violations-table .btn-ledger-view--danger .bi-eye { color: #fca5a5 !important; }
    [data-bs-theme="dark"] .btn-ledger-warn-sign { color: #fca5a5; }
    [data-bs-theme="dark"] .ledger-add-warn { background: #0f172a; border-color: #7f1d1d; }
    [data-bs-theme="dark"] .filter-badge-paid { background: rgba(16, 185, 129, 0.18); border-color: rgba(16, 185, 129, 0.35); color: #34d399; }
    [data-bs-theme="dark"] .btn-action-icon { background: #1e293b; border-color: #334155; color: #94a3b8; }
    [data-bs-theme="dark"] .btn-action-more:hover { background: #334155; color: #f8fafc; }
    [data-bs-theme="dark"] .modern-search-input { background: #1e293b !important; border-color: #334155 !important; color: #f8fafc !important; }
    [data-bs-theme="dark"] .dropdown-menu { background: #111b2f !important; border-color: #334155 !important; }
    [data-bs-theme="dark"] .dropdown-item { color: #e2e8f0 !important; }
    [data-bs-theme="dark"] .dropdown-item:hover { background: #1e293b !important; }
    [data-bs-theme="dark"] .table-responsive::-webkit-scrollbar-track { background: #0f172a; }
    [data-bs-theme="dark"] .table-responsive::-webkit-scrollbar-thumb { background: #334155; }
    [data-bs-theme="dark"] .table-responsive { scrollbar-color: #334155 #0f172a; }

    /* The ledger's rows, pager and neutral buttons were only darkened inside
       the <=767px card layout, so at desktop width the dark theme painted a
       white table under the near-white names it had already switched over.
       Scoped to the table rather than the whole page so the modals keep their
       light form styling. */
    [data-bs-theme="dark"] .officer-violations-table tbody tr,
    [data-bs-theme="dark"] .officer-violations-table tbody td {
        background-color: #111b2f !important;
        border-color: #1e293b !important;
    }
    [data-bs-theme="dark"] .officer-violations-table tbody tr:hover td {
        background-color: #172554 !important;
    }
    /* Chips and amount badges repaint themselves white on row hover, which
       undid their dark colours at the moment the row was highlighted. */
    [data-bs-theme="dark"] .officer-violation-row:hover .violation-chip {
        background: #1e3a8a;
        border-color: #3b82f6;
        color: #dbeafe;
    }
    [data-bs-theme="dark"] .ledger-pager {
        background: #111b2f;
        border-color: #1e293b;
    }
    [data-bs-theme="dark"] .officer-violations-table .btn-white {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #cbd5e1 !important;
    }
    [data-bs-theme="dark"] .officer-violations-table .btn-white:hover {
        background: #334155 !important;
        color: #f8fafc !important;
    }
    /* The status chips keep their light pill styling, and the count badges
       inside them are tinted rather than solid, so on a dark screen they sat
       on white and washed out. */
    [data-bs-theme="dark"] .btn-filter-chip:not(.active),
    [data-bs-theme="dark"] .btn-filter-all:not(.active) {
        background: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
    }
    [data-bs-theme="dark"] .btn-filter-chip:not(.active):hover,
    [data-bs-theme="dark"] .btn-filter-all:not(.active):hover {
        background: #334155;
        border-color: #60a5fa;
        color: #bfdbfe;
    }
    [data-bs-theme="dark"] .btn-filter-all .badge {
        background: #334155;
        color: #cbd5e1;
    }
    [data-bs-theme="dark"] .btn-filter-all:not(.active) {
        background: rgba(59, 130, 246, 0.12);
        border-color: rgba(96, 165, 250, 0.35);
        color: #93c5fd;
    }
    [data-bs-theme="dark"] .filter-badge-pending {
        background: rgba(239, 68, 68, 0.18);
        border-color: rgba(248, 113, 113, 0.4);
        color: #fca5a5;
    }
    [data-bs-theme="dark"] .btn-filter-pending:not(.active) {
        background: rgba(239, 68, 68, 0.12);
        border-color: rgba(248, 113, 113, 0.35);
        color: #fca5a5;
    }
    [data-bs-theme="dark"] .btn-filter-pending.active,
    [data-bs-theme="dark"] .btn-filter-pending.active:hover {
        background: #b91c1c;
        border-color: #b91c1c;
        color: #ffffff;
    }
    [data-bs-theme="dark"] .btn-filter-pending.active .badge {
        background: rgba(255, 255, 255, 0.28) !important;
        border-color: transparent !important;
        color: #ffffff !important;
    }
    [data-bs-theme="dark"] .btn-filter-paid:not(.active) {
        background: rgba(16, 185, 129, 0.12);
        border-color: rgba(16, 185, 129, 0.35);
        color: #6ee7b7;
    }
    [data-bs-theme="dark"] .btn-filter-paid.active {
        background: rgba(16, 185, 129, 0.18);
        border-color: rgba(16, 185, 129, 0.4);
        color: #6ee7b7;
    }
    [data-bs-theme="dark"] .btn-filter-paid.active .badge {
        background: rgba(16, 185, 129, 0.2) !important;
        border-color: rgba(16, 185, 129, 0.35) !important;
        color: #6ee7b7 !important;
    }
    [data-bs-theme="dark"] .btn-filter-pending:not(.active):hover {
        border-color: rgba(248, 113, 113, 0.55);
        color: #fecaca;
    }
    [data-bs-theme="dark"] .btn-filter-paid:not(.active):hover {
        border-color: rgba(16, 185, 129, 0.5);
        color: #6ee7b7;
    }

    .no-caret::after {
        display: none !important;
    }
    .tct-layout .form-label {
        font-size: 0.84rem;
    }
    .tct-checklist {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.45rem 1rem;
        padding: 0.75rem;
        border: 1px solid #e9d5ff;
        border-radius: 10px;
        background: #fbfaff;
        max-height: 240px;
        overflow: auto;
    }
    .tct-check-item {
        display: flex;
        align-items: flex-start;
        gap: 0.45rem;
        font-size: 0.88rem;
        line-height: 1.25rem;
    }
    .tct-check-item input {
        margin-top: 0.2rem;
    }
    .tct-checklist-single {
        grid-template-columns: 1fr;
    }
    
    .pill-select-wrapper {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.25rem;
        padding: 0.375rem 0.75rem;
        min-height: 42px;
        background-color: #fff;
        border: 1px solid #e9d5ff;
        border-radius: 10px;
        cursor: text;
    }
    .pill-select-wrapper:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.2rem rgba(139, 92, 246, 0.25);
    }
    .pill-selected-items {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    .pill-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
        font-weight: 500;
        background: #eff6ff;
        color: #3b82f6;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
    }
    .pill-badge .remove-pill {
        cursor: pointer;
        opacity: 0.6;
        transition: opacity 0.2s;
    }
    .pill-badge .remove-pill:hover {
        opacity: 1;
        color: #dc3545;
    }
    .pill-search-input {
        border: none;
        outline: none;
        flex-grow: 1;
        min-width: 120px;
        background: transparent;
        font-size: 0.9rem;
    }
    .pill-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 200px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #e9d5ff;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        z-index: 1050;
        display: none;
        margin-top: 4px;
    }
    .pill-dropdown.show {
        display: block;
    }
    .pill-option {
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        font-size: 0.9rem;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pill-option:hover, .pill-option.focused {
        background: #eff6ff;
        color: #3b82f6;
    }
    .pill-option.selected {
        background: #eff6ff;
        color: #3b82f6;
        font-weight: 500;
    }
    .pill-option.selected::after {
        content: '\F26A'; /* Bootstrap icon check */
        font-family: 'bootstrap-icons';
        font-size: 0.9rem;
    }
    [data-bs-theme="dark"] .pill-select-wrapper {
        background-color: #121d35;
        border-color: #2a3b60;
    }
    [data-bs-theme="dark"] .pill-search-input {
        color: #bfdbfe;
    }
    [data-bs-theme="dark"] .pill-dropdown {
        background: #111b2f;
        border-color: #2a3b60;
        box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    }
    [data-bs-theme="dark"] .pill-option:hover, [data-bs-theme="dark"] .pill-option.focused {
        background: #16233f;
        color: #bfdbfe;
    }
    [data-bs-theme="dark"] .pill-option.selected {
        background: #1e293b;
        color: #bfdbfe;
    }
    [data-bs-theme="dark"] .pill-badge {
        background: #1e293b;
        color: #bfdbfe;
        border-color: #2e3a59;
    }
    .enforcer-card {
        overflow: hidden;
        border: 1px solid #e9d5ff !important;
        border-radius: 16px;
        background: linear-gradient(165deg, rgba(255,255,255,0.95) 0%, rgba(247,250,255,0.88) 100%);
        transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.3s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .enforcer-header {
        background: linear-gradient(120deg, rgba(124, 58, 237,0.14) 0%, rgba(255,255,255,0.95) 65%);
        border-bottom: 1px solid #e9d5ff;
    }
    .enforcer-header h4 {
        color: #1e3a8a;
        font-weight: 700;
    }
    .enforcer-card .form-label {
        color: #5b21b6;
        font-weight: 600;
    }
    .section-label {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #0052cc;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 999px;
        padding: 0.35rem 0.75rem;
    }
    .enforcer-card .form-text {
        font-size: 0.76rem;
        color: #3b82f6;
        margin-top: 0.25rem;
    }
    .enforcer-card .input-group-text {
        background: #eff6ff;
        border-color: #e9d5ff;
        color: #0052cc;
    }
    .enforcer-card .form-control,
    .enforcer-card .form-select {
        border-color: #e9d5ff;
        border-radius: 10px;
    }
    .enforcer-card .form-control,
    .enforcer-card .form-select,
    .enforcer-card .input-group-text {
        min-height: 42px;
    }
    .enforcer-card .form-control.bg-light {
        background: #eff6ff !important;
    }
    .info-box {
        background: linear-gradient(120deg, #dbeafe 0%, #eff6ff 100%);
        color: #1e3a8a;
        border: 1px solid #bfdbfe;
    }
    .btn-white { background: #fff; }
    .btn-white:hover { background: #f8f9fa; }
    .enforcer-card .table thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #5b21b6;
        border-bottom: 1px solid #dbeafe;
    }
    .enforcer-card .table tbody tr:hover {
        background-color: #eff6ff;
    }
    .enforcer-card .btn-group .btn {
        border-radius: 8px !important;
    }
    .officer-violations-table {
        table-layout: fixed;
    }
    @media (min-width: 768px) {
        .officer-violations-table {
            min-width: 1040px;
        }
        .officer-violations-table .col-violator { width: 30%; }
        .officer-violations-table .col-vehicle { width: 19%; }
        .officer-violations-table .col-status { width: 35%; }
        .officer-violations-table .col-actions { width: 16%; }
    }
    .officer-violations-table th,
    .officer-violations-table td {
        vertical-align: middle;
    }
    @media (min-width: 768px) {
        .officer-violations-table .col-actions {
            width: 16%;
            white-space: nowrap;
        }
    }
    .officer-violations-table .actions-group {
        flex-wrap: nowrap;
    }
    .table-responsive {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
        border-radius: 0 0 16px 16px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }
    .table-responsive::-webkit-scrollbar-track {
        background: #f8fafc;
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 99px;
    }
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: var(--text-subtle, #5c6b80);
    }
    .enforcer-card .card-header h4,
    .enforcer-card .card-header h5 {
        letter-spacing: -0.01em;
    }
    .enforcer-card .card-header small {
        display: block;
        margin-top: 0.25rem;
        color: #3b82f6 !important;
    }
    #violationForm .row.g-4 {
        --bs-gutter-y: 1rem;
    }
    #violationForm hr {
        opacity: 0.12;
    }
    .form-footer {
        background: #eff6ff;
        border: 1px solid #dbeafe;
        border-radius: 12px;
        padding: 0.75rem 0.9rem;
    }
    #violationForm .btn-outline-primary {
        border-width: 1px;
    }
    #tableSearchInput,
    #statusFilter {
        min-height: 40px;
    }
    .analytics-tilt {
        transform-style: preserve-3d;
        will-change: transform;
    }
    #tableSearchInput {
        border-radius: 0 10px 10px 0;
    }
    /* Keep the ledger search aligned with the Enforcer electric-blue accent. */
    #tableSearchInput.modern-search-input {
        border: 2px solid #0066ff !important;
        outline: none !important;
    }
    #tableSearchInput.modern-search-input:focus,
    #tableSearchInput.modern-search-input:focus-visible {
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.16) !important;
    }
    @media (max-width: 768px) {
        .tct-checklist {
            grid-template-columns: 1fr;
        }
        .enforcer-card .card-body {
            padding: 1rem !important;
        }
        .section-label {
            width: 100%;
            justify-content: center;
        }
        .enforcer-header h4 {
            font-size: 1.1rem;
        }
        .enforcer-card .input-group {
            flex-wrap: nowrap;
        }
        .enforcer-card .input-group .btn {
            white-space: nowrap;
            font-size: 0.85rem;
            padding-left: 0.65rem;
            padding-right: 0.65rem;
        }
        .enforcer-card .d-flex.justify-content-between.align-items-center {
            flex-direction: column;
            align-items: stretch !important;
            gap: 0.75rem;
        }
        .form-footer {
            padding: 0.7rem;
        }
        .enforcer-card .d-flex.gap-2 {
            width: 100%;
            display: grid !important;
            grid-template-columns: 1fr;
        }
        .enforcer-card .d-flex.gap-2 .btn {
            width: 100%;
        }
        #statusFilter {
            max-width: 100% !important;
        }
    }

<?= $this->include('officer/partials/tct_ticket_styles') ?>

    /* ── MOBILE: enforcer streamlining ───────────────────────────── */
    @media (max-width: 767.98px) {
        /* The header stops squeezing: the title and chips keep their own
           lines while search and the record button share one full-width row
           instead of dropping into an awkward half-width stack. */
        .main-table-header {
            padding: 0.7rem 0.9rem !important;
            gap: 0.55rem !important;
            align-items: stretch !important;
        }
        .main-table-header > div { width: 100%; }
        .main-table-header .modern-search-box {
            min-width: 0;
            width: auto;
            flex: 1 1 0;
        }
        .main-table-header .modern-search-input { height: 40px; }
        .main-table-header .btn-new-violation { flex: 0 0 auto; white-space: nowrap; }
        .main-table-header .btn-filter-chip { flex: 0 0 auto; }

        /* Thumbs, not mice: the row's actions reach a 44px target without
           making the cards any taller than they already are. */
        .officer-violations-table tbody td.col-actions .btn {
            min-height: 44px;
            min-width: 44px;
        }
        .officer-violations-table tbody td.col-actions a.btn.rounded-circle {
            width: 44px !important;
            height: 44px !important;
        }
        .officer-violations-table tbody td {
            padding-left: 0.9rem;
            padding-right: 0.9rem;
        }
        /* Record / edit citation: the header and footer stay within reach
           while the long form scrolls between them, so Save is never a
           2,800px scroll away from the field being filled. */
        #recordViolationModal .modal-content,
        #editViolationModal .modal-content {
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 2rem);
            max-height: calc(100dvh - 2rem);
        }
        #recordViolationModal .modal-body,
        #editViolationModal .modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* The form's own action bar rides along at the bottom of that scroll,
           so Save & Print Ticket stays under the thumb the whole way down a
           2,700px ticket instead of hiding at the end of it. */
        #recordViolationModal .tct-modal-card .d-flex.justify-content-end.gap-2.border-top,
        #editViolationModal .tct-modal-card .d-flex.justify-content-between.border-top {
            position: sticky;
            bottom: 0;
            z-index: 3;
            background: #ffffff;
            margin-top: 0.75rem;
            padding-bottom: 0.35rem;
            box-shadow: 0 -10px 14px -10px rgba(15, 23, 42, 0.25);
        }
        #editViolationModal .tct-modal-card .d-flex.justify-content-between.border-top {
            flex-wrap: wrap;
            gap: 0.35rem 0.75rem;
        }
        #editViolationModal .tct-modal-card .d-flex.justify-content-between.border-top .d-flex.gap-2 {
            flex: 1 1 auto;
            justify-content: flex-end;
            flex-wrap: wrap;
        }
        #editViolationModal .tct-modal-card .d-flex.justify-content-between.border-top .d-flex.gap-2 .btn {
            min-height: 44px;
        }

        /* A table box keeps its own column widths even after its rows become
           cards, so every card stayed a quarter of the width and its content
           spilled out sideways (the vehicle name broke letter by letter).
           Making the table and its body blocks lets each card take the whole
           column - the same fix the payment list already uses. */
        .officer-violations-table,
        .officer-violations-table tbody {
            display: block;
            width: 100%;
            min-width: 0;
            table-layout: auto;
        }
        .officer-violations-table tbody tr,
        .officer-violations-table tbody td {
            width: 100%;
            box-sizing: border-box;
        }
        .officer-violations-table tbody td {
            row-gap: 0.35rem;
            flex-wrap: wrap;
        }
        .officer-violations-table tbody td::before {
            max-width: 45%;
        }
        .officer-violations-table tbody td > * {
            min-width: 0;
            max-width: 100%;
        }
        /* Label left, value right: the value side takes the rest of the row
           and parks its pills at the trailing edge instead of hugging the
           label with dead space on the far side. */
        .officer-violations-table tbody td:not([data-label="Violator"]) > div {
            flex: 1 1 auto;
            text-align: right;
        }
        .officer-violations-table tbody td .ledger-vehicle,
        .officer-violations-table tbody td .ledger-state {
            align-items: flex-end;
        }
        .officer-violations-table tbody td .ledger-vehicle-meta {
            align-items: flex-end;
            text-align: right;
        }
        .officer-violations-table tbody td .ledger-state-chips {
            justify-content: flex-end;
        }
    }

    @media (max-width: 575.98px) {
        /* The title owns its line and the three chips scroll sideways as one
           row, instead of a lonely chip dropping to a second line. */
        .main-table-header .ledger-filter-title { flex: 0 0 100%; }
        .ledger-filter-chips {
            width: 100%;
            min-width: 0;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 2px;
            scrollbar-width: none;
            -ms-overflow-style: none;
            -webkit-overflow-scrolling: touch;
        }
        .ledger-filter-chips::-webkit-scrollbar { display: none; }
        .ledger-filter-chips .btn-filter-chip { flex: 0 0 auto; }
    }

    /* The floating button hides while the header's New Violation button is
       on screen, so it never sits over a row's View / + actions. */
    .fab-new-violation {
        transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.16, 1, 0.3, 1),
                    box-shadow 0.22s ease;
    }
    .fab-new-violation.is-hidden {
        opacity: 0;
        visibility: hidden;
        transform: translateY(14px) scale(0.85);
        pointer-events: none;
        /* Visibility waits for the fade, so the button is never focusable or
           announced while it sits over nothing; dropping the class reveals it
           immediately with the base transition. */
        transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.16, 1, 0.3, 1),
                    visibility 0s linear 0.25s;
    }

    /* A lifted row is a hover state, not a pressed one: touch screens would
       otherwise keep it stuck after a tap. The warning signs keep their full
       colour on those screens, they just stop moving. */
    @media (hover: none), (pointer: coarse) {
        .officer-violations-table tbody tr:hover { transform: none !important; }
        .ledger-sanction-warn:hover { transform: none; }
        .officer-violations-table .btn-ledger-add--danger:hover .ledger-add-warn,
        .officer-violations-table .btn-ledger-add--danger:focus-visible .ledger-add-warn { transform: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .ledger-sanction-warn,
        .ledger-sanction-warn i,
        .ledger-add-warn,
        .btn-ledger-warn-sign { transition: none !important; }
        .ledger-sanction-warn:hover,
        .ledger-sanction-warn:focus-visible { transform: none !important; }
        .ledger-sanction-warn:hover i,
        .ledger-sanction-warn:focus-visible i,
        .officer-violations-table .btn-ledger-view--danger:hover .btn-ledger-warn-sign,
        .officer-violations-table .btn-ledger-view--danger:focus-visible .btn-ledger-warn-sign,
        .officer-violations-table .btn-ledger-add--danger:hover .ledger-add-warn,
        .officer-violations-table .btn-ledger-add--danger:focus-visible .ledger-add-warn { animation: none !important; }
    }

    /* ── MOBILE: compact enforcer ledger cards ─────────────────────
       The phone card was three stacked rows with the caption on the left
       and its value pinned to the far right, so a 400px card had a dead
       gap in the middle and the View / + buttons on a row of their own.
       Grid reorders the same four cells at phone widths only: the name
       and its actions share the first line, and each detail's caption
       sits on the same line as its left-aligned value. Desktop uses the
       normal table. */
    @media (max-width: 767.98px) {
        .officer-violations-table tbody tr {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            grid-template-areas:
                "identity actions"
                "vehicle  vehicle"
                "status   status";
            column-gap: 0.5rem;
            padding: 0.75rem 0.9rem;
        }
        .officer-violations-table tbody td:nth-child(1) { grid-area: identity; align-self: center; }
        .officer-violations-table tbody td:nth-child(2) { grid-area: vehicle; }
        .officer-violations-table tbody td:nth-child(3) { grid-area: status; }
        .officer-violations-table tbody td:nth-child(4) { grid-area: actions; align-self: center; }

        /* Caption on the card's left edge with the value beside it. The
           caption must stay natural-width: forcing it to 100% collapsed
           the value div to zero width and wrapped text letter by letter. */
        .officer-violations-table tbody td::before {
            display: block;
            flex: 0 0 auto;
            width: auto;
            max-width: none;
            margin: 0 0.45rem 0 0;
        }
        .officer-violations-table tbody td[data-label="Violator"]::before,
        .officer-violations-table tbody td[data-label="Actions"]::before { display: none; }
        .officer-violations-table tbody td:not([data-label="Violator"]):not([data-label="Actions"]) > div {
            flex: 1 1 100%;
            text-align: left;
            max-width: 100%;
        }
        .officer-violations-table tbody td .ledger-vehicle,
        .officer-violations-table tbody td .ledger-state,
        .officer-violations-table tbody td .ledger-vehicle-meta,
        .officer-violations-table tbody td .ledger-state-chips {
            align-items: flex-start;
            text-align: left;
        }
        /* Pending and Paid sit side by side and wrap only when they must. */
        .officer-violations-table tbody td .ledger-state-chips {
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: flex-start;
        }
        .officer-violations-table tbody td:nth-child(2),
        .officer-violations-table tbody td:nth-child(3) {
            padding: 0.55rem 0 0;
            border-top: 1px solid #f1f5f9;
            border-bottom: 0;
        }
        [data-bs-theme="dark"] .officer-violations-table tbody td:nth-child(2),
        [data-bs-theme="dark"] .officer-violations-table tbody td:nth-child(3) { border-top-color: #1e293b; }
        .officer-violations-table tbody td.col-actions {
            padding: 0 !important;
            border: 0 !important;
            justify-content: flex-end;
        }
        .officer-violations-table tbody td.col-actions .d-inline-flex {
            gap: 0.35rem;
            flex-wrap: nowrap;
        }
        .officer-violations-table tbody td.col-actions a.btn-white {
            padding-left: 0.6rem;
            padding-right: 0.6rem;
        }
        /* The empty-state row is a single colspan cell, not a record card. */
        .officer-violations-table tbody tr#noTableDataRow { display: block; }
        .officer-violations-table tbody #noTableDataRow td {
            display: block;
            width: 100%;
            padding: 1rem !important;
        }
        .officer-violations-table tbody #noTableDataRow td::before { display: none !important; }
    }
</style>


<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function printTicket(url) {
        // iOS Safari cannot run window.print() inside a hidden iframe, so on
        // phones the ticket opens in this tab instead; it triggers the print
        // dialog itself and the Back button returns here.
        if (/Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent || '')) {
            window.location.href = url;
            return;
        }

        // Remove existing print iframe if it exists
        const existing = document.getElementById('print-iframe');
        if (existing) existing.remove();

        const iframe = document.createElement('iframe');
        iframe.id = 'print-iframe';
        iframe.style.display = 'none';
        iframe.src = url;
        document.body.appendChild(iframe);
        
        // Cleanup after a delay (ensuring print dialog has been handled)
        setTimeout(() => {
            if (document.getElementById('print-iframe')) {
                document.getElementById('print-iframe').remove();
            }
        }, 10000);
    }
    <?= $this->include('officer/partials/record_ticket_modal_script') ?>

    // ═══ 1-CLICK COPY TICKET ID ═════════════════════════════════
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

    // ═══ INTERACTIVE SEARCH & FILTER CHIPS ══════════════════════
    const tableSearchInput = document.getElementById('tableSearchInput');
    const btnClearSearch = document.getElementById('btnClearSearch');
    let currentStatusFilter = <?= json_encode($statusFilter ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    // Search and status changes load server-filtered results in place. The
    // page does not reload: the table and pager are swapped in, and the query
    // string is updated for refreshes and pagination.
    let ledgerInFlight = null;

    const ledgerSwap = function(status, search) {
        const table   = document.getElementById('officerViolationTable');
        const results = document.getElementById('ledgerResults');
        const url     = new URL(window.location.href);
        const searchValue = (typeof search === 'string' ? search : (tableSearchInput?.value || '')).trim();

        if (status) {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }

        if (searchValue) {
            url.searchParams.set('search', searchValue);
        } else {
            url.searchParams.delete('search');
        }

        for (const key of Array.from(url.searchParams.keys())) {
            if (key === 'page' || key === 'violations' || /^page_/i.test(key)) {
                url.searchParams.delete(key);
            }
        }

        if (!table || !results) {
            window.location.assign(url.toString());
            return;
        }

        if (ledgerInFlight) ledgerInFlight.abort();

        const controller = new AbortController();
        ledgerInFlight   = controller;
        results.classList.add('is-updating');

        fetch(url.toString(), { signal: controller.signal })
            .then(function(response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.text();
            })
            .then(function(html) {
                const doc        = new DOMParser().parseFromString(html, 'text/html');
                const nextTable  = doc.getElementById('officerViolationTable');
                const nextChips  = doc.querySelector('.ledger-filter-chips');
                const nextResult = doc.getElementById('ledgerResults');

                if (!nextTable || !nextChips || !nextResult) throw new Error('no swap target');

                const chipsNow = document.querySelector('.ledger-filter-chips');
                if (chipsNow) chipsNow.replaceWith(nextChips);
                results.replaceWith(nextResult);

                currentStatusFilter = status;

                window.history.pushState({ ledgerStatus: status }, '', url.toString());
            })
            .catch(function(error) {
                if (error && error.name === 'AbortError') return;
                window.location.assign(url.toString());
            })
            .finally(function() {
                if (ledgerInFlight === controller) ledgerInFlight = null;
                const fresh = document.getElementById('ledgerResults');
                if (fresh) fresh.classList.remove('is-updating');
            });
    };

    document.addEventListener('click', function(event) {
        const chip = event.target.closest && event.target.closest('.ledger-filter-chips .btn-filter-chip');
        if (!chip) return;

        event.preventDefault();
        ledgerSwap((chip.dataset.status || '').toLowerCase(), tableSearchInput?.value || '');
    });

    const updateSearchControls = () => {
        const keyword = (tableSearchInput?.value || '').toLowerCase().trim();

        if (btnClearSearch) {
            if (keyword.length > 0) {
                btnClearSearch.classList.remove('d-none');
            } else {
                btnClearSearch.classList.add('d-none');
            }
        }
    };

    let searchTimer = null;
    tableSearchInput?.addEventListener('input', function() {
        updateSearchControls();
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(function() {
            ledgerSwap(currentStatusFilter, tableSearchInput.value);
        }, 300);
    });

    if (btnClearSearch) {
        btnClearSearch.addEventListener('click', function() {
            if (tableSearchInput) {
                tableSearchInput.value = '';
                updateSearchControls();
                window.clearTimeout(searchTimer);
                ledgerSwap(currentStatusFilter, '');
                tableSearchInput.focus();
            }
        });
    }

    updateSearchControls();

    // ═══ ANIMATED COUNTERS FOR STAT CARDS ═══════════════════════
    document.querySelectorAll('.stat-counter-value').forEach(el => {
        const val = parseInt(el.textContent.replace(/[^0-9]/g, ''), 10);
        if (isNaN(val) || el.textContent.includes('₱')) return;
        let start = null;
        const target = val;
        const step = ts => {
            if (!start) start = ts;
            const p = Math.min((ts - start) / 800, 1);
            el.textContent = Math.round((1 - Math.pow(1 - p, 3)) * target);
            if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    });

    const cancelViolationModalEl = document.getElementById('cancelViolationModal');
    const cancelViolationForm = document.getElementById('cancelViolationForm');
    if (cancelViolationModalEl && cancelViolationForm) {
        const cancelViolationModal = new bootstrap.Modal(cancelViolationModalEl);
        // Delegated: the ledger rows are replaced in place when a status chip
        // is chosen, so a handler bound to each button would go stale.
        document.addEventListener('click', function(event) {
            const btn = event.target.closest && event.target.closest('.btn-cancel-violation');
            if (!btn) return;

            const id = btn.dataset.id;
            cancelViolationForm.action = `<?= base_url('officer/cancel') ?>/${id}`;
            cancelViolationModal.show();
        });
        cancelViolationForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Cancelling...';
            }
        });
    }

<?= $this->include('officer/partials/edit_ticket_modal_script') ?>

    const deleteModalEl = document.getElementById('deleteViolationModal');
    const deleteForm = document.getElementById('deleteViolationForm');
    if (deleteModalEl && deleteForm) {
        const deleteModal = new bootstrap.Modal(deleteModalEl);
        document.addEventListener('click', function(event) {
            const btn = event.target.closest && event.target.closest('.btn-delete-violation');
            if (!btn) return;

            const id = btn.dataset.id;
            deleteForm.action = `<?= base_url('officer/delete') ?>/${id}`;
            deleteModal.show();
        });
        deleteForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Deleting...';
            }
        });
    }

    // Elevate row stacking context dynamically whenever action dropdown opens
    // Delegated so the rows surviving a chip switch keep their stacking fix.
    document.addEventListener('show.bs.dropdown', function (event) {
        const dropdownEl = event.target.closest && event.target.closest('.officer-violations-table .dropdown');
        if (!dropdownEl) return;
        const tr = dropdownEl.closest('tr');
        if (tr) {
            tr.classList.add('has-open-dropdown');
            tr.style.zIndex = '1065';
        }
    });
    document.addEventListener('hidden.bs.dropdown', function (event) {
        const dropdownEl = event.target.closest && event.target.closest('.officer-violations-table .dropdown');
        if (!dropdownEl) return;
        const tr = dropdownEl.closest('tr');
        if (tr) {
            tr.classList.remove('has-open-dropdown');
            tr.style.zIndex = '';
        }
    });

    // Auto-close open action dropdowns upon scrolling page or table container so menus never float or follow screen
    const closeOpenActionDropdowns = () => {
        document.querySelectorAll('.officer-violations-table .dropdown-menu.show').forEach(menu => {
            const toggle = menu.closest('.dropdown')?.querySelector('[data-bs-toggle="dropdown"]');
            if (toggle && typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                const instance = bootstrap.Dropdown.getInstance(toggle);
                if (instance) {
                    instance.hide();
                }
            }
        });
    };
    window.addEventListener('scroll', closeOpenActionDropdowns, { passive: true });
    document.querySelectorAll('.table-responsive').forEach(el => {
        el.addEventListener('scroll', closeOpenActionDropdowns, { passive: true });
    });

    // Subtle premium tilt interaction, consistent with admin.
    const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.querySelectorAll('.analytics-tilt').forEach(card => {
        card.addEventListener('mousemove', (e) => {
            if (reducedMotion) return;
            const rect = card.getBoundingClientRect();
            const px = (e.clientX - rect.left) / rect.width;
            const py = (e.clientY - rect.top) / rect.height;
            const rotateY = (px - 0.5) * 4;
            const rotateX = (0.5 - py) * 4;
            card.style.transform = `perspective(900px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateY(-2px)`;
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });

    // The floating New Violation button steps aside whenever the header's
    // New Violation button is already visible, so it never covers a row's
    // View / + actions while the officer scrolls the ledger. Phones only: on
    // a wide screen the header and the FAB never compete for the same space.
    const fabBtn = document.querySelector('.fab-new-violation');
    const headerNewBtn = document.querySelector('.btn-new-violation');
    const fabMedia = window.matchMedia('(max-width: 767.98px)');
    let fabObserver = null;

    if (fabBtn && headerNewBtn && 'IntersectionObserver' in window) {
        fabObserver = new IntersectionObserver(([entry]) => {
            fabBtn.classList.toggle('is-hidden', entry.isIntersecting);
        }, { threshold: 0.6 });

        const syncFabMode = () => {
            if (fabMedia.matches) {
                fabObserver.observe(headerNewBtn);
            } else {
                fabObserver.unobserve(headerNewBtn);
                fabBtn.classList.remove('is-hidden');
            }
        };

        syncFabMode();
        fabMedia.addEventListener('change', syncFabMode);
    }

    // Auto-open the form for validation errors or when recording another
    // violation for a prefilled violator.
    <?php if (session()->getFlashdata('error') || session()->getFlashdata('errors') || ! empty($prefillViolator)): ?>
        bootstrap.Modal.getOrCreateInstance(document.getElementById('recordViolationModal')).show();
    <?php endif; ?>


</script>
<?= $this->endSection() ?>
