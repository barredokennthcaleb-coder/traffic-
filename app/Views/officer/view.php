<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Violation Details - Traffic System<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $isPrintMode  = service('request')->getGet('print') === '1';
    // 58mm mode: used with the "Print 58mm Roll" button when the printer app
    // or print dialog is set to 58mm / Roll paper. The ticket then prints at
    // its true size on one continuous page instead of the A4 spool.
    $paper58      = service('request')->getGet('paper') === '58mm';

    // Where this ticket was opened from, so Back (and the return after
    // printing) goes there instead of dumping the reader in the whole ledger.
    $fromParam         = (string) service('request')->getGet('from');
    $fromViolations    = $fromParam === 'violations';
    $fromViolators     = $fromParam === 'violators';
    $fromProfileId     = (int) service('request')->getGet('violator');
    $fromViolatorPage  = $fromParam === 'violator-profile' && $fromProfileId > 0;
    $isAdmin           = session()->get('role') === 'admin';

    // Administrators work out of the Violators registry, not the enforcer
    // ledger, so that is where Back lands for them. An explicit origin still
    // wins, and enforcers keep returning to their own ledger.
    if ($fromViolators || ($isAdmin && ! $fromViolatorPage)) {
        $backUrl   = base_url('violators');
        $backLabel = 'Back to Violators';
    } elseif ($fromViolatorPage) {
        $backUrl   = base_url('officer/violator-profile/' . $fromProfileId);
        $backLabel = 'Back to Violator Profile';
    } else {
        $backUrl   = base_url('officer/violations');
        $backLabel = 'Back to My Recorded Violations';
    }

    // The print view keeps the same context, so it can hand the reader back to
    // the page they were reading when the dialog closes.
    $printParams = ['print' => 1];
    if ($fromViolators) {
        $printParams['from'] = 'violators';
    } elseif ($fromViolations) {
        $printParams['from'] = 'violations';
    } elseif ($fromViolatorPage) {
        $printParams['from'] = 'violator-profile';
        $printParams['violator'] = $fromProfileId;
    }
    $printUrl = base_url('officer/view/' . (int) ($violation['id'] ?? 0)) . '?' . http_build_query($printParams);

    $totalFine   = 0;
    $vList       = isset($all_violations) ? $all_violations : [$violation];
    $activeViolationNames = [];
    foreach ($vList as $v) {
        $totalFine += (float) ($v['penalty_amount'] ?? 0);
        $vName = $v['violation_type'] ?? $v['violation_name'] ?? '';
        if ($vName) {
            $activeViolationNames[] = strtolower(trim($vName));
        }
    }

    // The printed checklist, its name matching and the "Other Violations"
    // fallback all live in tct_helper.php, so the enforcer, print, admin and
    // driver layouts can never drift apart.
    $standardViolations = tct_standard_violations();

    // The citation lists no longer carry offense/fine columns, so this view
    // states them. Money follows the system rule: cancelled is void.
    $detailStatuses = [];
    $assessedTotal  = 0.0;
    $paidTotal      = 0.0;
    $pendingTotal   = 0.0;

    foreach ($vList as $v) {
        $vStatus        = (string) ($v['status'] ?? 'Pending');
        $detailStatuses[] = $vStatus;
        $amount         = (float) ($v['penalty_amount'] ?? 0);

        if ($vStatus === 'Cancelled') {
            continue;
        }

        $assessedTotal += $amount;

        if ($vStatus === 'Paid') {
            $paidTotal += $amount;
        } else {
            $pendingTotal += $amount;
        }
    }

    $detailStatuses = array_values(array_unique($detailStatuses));

    if ($detailStatuses === ['Cancelled']) {
        $ticketStatus = 'Cancelled';
    } elseif (in_array('Pending', $detailStatuses, true) || $detailStatuses === []) {
        $ticketStatus = 'Pending';
    } else {
        $ticketStatus = 'Paid';
    }

    $detailName = trim(implode(' ', array_filter([
        $violation['first_name'] ?? '',
        $violation['middle_name'] ?? '',
        $violation['last_name'] ?? '',
    ], static fn ($part) => trim((string) $part) !== '')));

    if ($detailName === '') {
        $detailName = trim((string) ($violation['driver_name'] ?? '')) ?: '—';
    }

    // Fallbacks keep every panel of the detail card filled instead of
    // printing empty captions next to a colon.
    $detailOr = static function ($value, string $fallback = '—') {
        $text = trim((string) $value);
        return $text !== '' ? $text : $fallback;
    };

    $detailDate = isset($violation['violation_date']) && trim((string) $violation['violation_date']) !== ''
        ? date('M d, Y h:i A', strtotime($violation['violation_date']))
        : '—';
    $detailPlace      = $detailOr($violation['location'] ?? '', 'Kabankalan City');
    $detailOwner      = $detailOr($violation['owner_name'] ?? '', $detailName);
    $detailEnforcer   = $detailOr($violation['officer_name'] ?? '');
    $detailAckRaw     = strtolower((string) ($violation['acknowledgment'] ?? ''));
    $detailAck        = strpos($detailAckRaw, 'admitted') !== false
        ? 'Admitted'
        : (strpos($detailAckRaw, 'protest') !== false ? 'Under Protest' : '—');

    // The hero banner follows the violator profile so the citation page reads
    // as the same dossier, scoped to this ticket.
    $heroTone       = $ticketStatus === 'Paid' ? 'paid' : ($ticketStatus === 'Cancelled' ? 'cancelled' : 'pending');
    $heroHeadline   = $ticketStatus === 'Paid' ? 'Settled' : ($ticketStatus === 'Cancelled' ? 'Cancelled' : 'Pending settlement');
    $heroMoneyValue = $ticketStatus === 'Paid' ? $paidTotal : ($ticketStatus === 'Cancelled' ? 0.0 : $pendingTotal);
    $heroMoneyLabel = $ticketStatus === 'Paid' ? 'paid' : ($ticketStatus === 'Cancelled' ? 'void' : 'outstanding');

    $detailInitial = mb_strtoupper(mb_substr($detailName !== '—' ? $detailName : '?', 0, 1));
    $detailLicense = $detailOr($violation['license_number'] ?? '');
    $detailPlate   = $detailOr($violation['license_plate'] ?? '');
    $detailVehicle = $detailOr($violation['vehicle_type'] ?? '');
    $detailMtop    = $detailOr($violation['mtop_number'] ?? '');
    $detailAddress = $detailOr($violation['address'] ?? '');
    $detailOwnerId = (int) ($violation['violator_id'] ?? 0);
    $detailCount   = count($vList);
?>

<!-- The page reuses the violator profile's design language, so the same
     record reads the same way whether it is opened from the ledger or from
     the violator directory. -->
<div class="container-fluid py-4 vp-page">
    <div class="no-print">

        <div class="vp-topbar">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="<?= $backUrl ?>" class="vp-back-btn">
                    <i class="bi bi-arrow-left"></i><span><?= esc($backLabel) ?></span>
                </a>
                <span class="vp-breadcrumb">/</span>
                <span class="vp-breadcrumb-current font-monospace"><?= esc($violation['ticket_id'] ?? 'N/A') ?></span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <?php if ($detailOwnerId > 0): ?>
                    <a href="<?= base_url('officer/violator-profile/' . $detailOwnerId) ?>" class="vp-report-btn">
                        <i class="bi bi-person-vcard"></i> Violator Profile
                    </a>
                <?php endif; ?>
                <a href="<?= $printUrl ?>" class="vp-record-btn">
                    <i class="bi bi-printer"></i> Print Ticket
                </a>
                <a href="<?= $printUrl ?>&paper=58mm" class="vp-report-btn"
                   title="One-page printout for the 58mm roll — set the printer app or the print dialog to 58mm / Roll paper first.">
                    <i class="bi bi-receipt"></i> 58mm
                </a>
            </div>
        </div>

        <div class="vp-hero premium-reveal" style="--reveal-delay:0s;">
            <div class="vp-hero-orb vp-hero-orb-1"></div>
            <div class="vp-hero-orb vp-hero-orb-2"></div>
            <div class="vp-hero-orb vp-hero-orb-3"></div>
            <div class="vp-hero-content">

                <div class="vp-hero-avatar-wrap">
                    <div class="vp-avatar-display">
                        <span class="vp-avatar-initials"><?= esc($detailInitial) ?></span>
                    </div>
                    <span class="vp-outcome-dot vp-outcome-dot--<?= esc($heroTone) ?>" title="<?= esc($ticketStatus) ?>"></span>
                </div>

                <div class="vp-hero-info">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h2 class="vp-hero-name mb-0"><?= esc($detailName) ?></h2>
                        <?php if ($detailPlate !== '—'): ?>
                            <span class="vp-hero-plate"><?= tricycle_icon('me-1') ?><?= esc(strtoupper($detailPlate)) ?></span>
                        <?php else: ?>
                            <span class="vp-hero-plate"><i class="bi bi-person-vcard me-1"></i>Record #<?= $detailOwnerId > 0 ? $detailOwnerId : (int) ($violation['id'] ?? 0) ?></span>
                        <?php endif; ?>
                        <span class="vp-hero-outcome vp-hero-outcome--<?= esc($heroTone) ?>">
                            <?php if ($heroTone === 'paid'): ?><i class="bi bi-check-circle-fill me-1"></i>
                            <?php elseif ($heroTone === 'cancelled'): ?><i class="bi bi-x-circle-fill me-1"></i>
                            <?php else: ?><i class="bi bi-hourglass-split me-1"></i><?php endif; ?>
                            <?= esc($ticketStatus) ?>
                        </span>
                    </div>
                    <div class="vp-hero-meta">
                        <?php if ($detailLicense !== '—'): ?>
                            <span><i class="bi bi-person-badge me-1"></i>DL: <?= esc($detailLicense) ?></span>
                        <?php endif; ?>
                        <?php if ($detailVehicle !== '—'): ?>
                            <span><i class="bi bi-truck me-1"></i><?= esc($detailVehicle) ?></span>
                        <?php endif; ?>
                        <?php if ($detailMtop !== '—'): ?>
                            <span><i class="bi bi-upc-scan me-1"></i>MTOP <?= esc($detailMtop) ?></span>
                        <?php endif; ?>
                        <?php if ($detailAddress !== '—'): ?>
                            <span><i class="bi bi-geo-alt me-1"></i><?= esc($detailAddress) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="vp-hero-hud ms-lg-auto me-lg-3">
                    <div class="vp-hud-status vp-hud-status--<?= esc($heroTone) ?>">
                        <span class="vp-radar-dot vp-radar-dot--<?= esc($heroTone) ?>"></span>
                        <span class="fw-bold"><?= esc($heroHeadline) ?></span>
                    </div>
                    <div class="vp-hud-money">
                        <i class="bi bi-cash-coin me-1"></i>
                        <span class="font-monospace fw-bold">&#8369;<?= number_format($heroMoneyValue, 2) ?></span>
                        <small class="ms-1"><?= esc($heroMoneyLabel) ?></small>
                    </div>
                    <div class="vp-hud-last">
                        <i class="bi bi-clock-history me-1"></i>
                        Apprehended <?= esc($detailDate) ?><?= $detailPlace !== '' ? ' &middot; ' . esc($detailPlace) : '' ?>
                    </div>
                </div>

                <div class="vp-hero-upload">
                    <button type="button" class="vp-btn-upload citation-ticket-toggle"
                            id="ticketToggleBtn" aria-expanded="false" aria-controls="ticketCollapse"
                            onclick="toggleCitationTicket()">
                        <i class="bi bi-receipt-cutoff me-1"></i><span>View Citation Ticket</span>
                    </button>
                    <small class="vp-hero-hint">Official TCT &middot; opens below</small>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="vp-section-card premium-reveal" style="--reveal-delay:.08s;">
                    <div class="vp-section-head">
                        <div>
                            <div class="vp-section-title"><i class="bi bi-receipt me-2"></i>Citation</div>
                            <div class="vp-section-sub">Offenses recorded on this ticket, with the fine for each.</div>
                        </div>
                        <span class="vp-count-badge">
                            <?= number_format($detailCount) ?> infraction<?= $detailCount === 1 ? '' : 's' ?>
                        </span>
                    </div>

                    <div class="vp-ticket-list">
                        <div class="vp-ticket vp-ticket--<?= esc($heroTone) ?>">
                            <div class="vp-ticket-head">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="vp-ticket-id font-monospace">
                                        <i class="bi bi-ticket-perforated"></i><?= esc($violation['ticket_id'] ?? 'N/A') ?>
                                    </span>
                                    <?php if ($ticketStatus === 'Paid'): ?>
                                        <span class="vp-ticket-status vp-ticket-status--paid"><i class="bi bi-check-circle-fill"></i>Paid</span>
                                    <?php elseif ($ticketStatus === 'Cancelled'): ?>
                                        <span class="vp-ticket-status vp-ticket-status--cancelled"><i class="bi bi-x-circle-fill"></i>Cancelled</span>
                                    <?php else: ?>
                                        <span class="vp-ticket-status vp-ticket-status--pending"><span class="vp-status-dot"></span>Pending</span>
                                    <?php endif; ?>
                                </div>
                                <div class="vp-ticket-date">
                                    <i class="bi bi-calendar3"></i><?= esc($detailDate) ?>
                                </div>
                            </div>

                            <div class="vp-ticket-meta">
                                <span><i class="bi bi-geo-alt me-1"></i><?= esc($detailPlace) ?></span>
                                <span class="vp-ticket-enforcer" title="Enforcer who recorded this ticket">
                                    <i class="bi bi-shield-check"></i>
                                    <span class="vp-ticket-enforcer-label">Enforcer:</span>
                                    <?= esc($detailEnforcer) ?>
                                </span>
                                <?php if ($detailAck !== '—'): ?>
                                    <span><i class="bi bi-check2-circle me-1"></i><?= esc($detailAck) ?></span>
                                <?php endif; ?>
                                <?php if ($detailOwner !== $detailName): ?>
                                    <span><i class="bi bi-person me-1"></i>Owner: <?= esc($detailOwner) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="vp-infraction-table">
                                <?php foreach ($vList as $v): ?>
                                    <?php
                                        $rowStatus = (string) ($v['status'] ?? 'Pending');
                                        $rowTone   = strtolower($rowStatus);
                                    ?>
                                    <div class="vp-infraction-row">
                                        <div class="vp-infraction-name">
                                            <span class="vp-infraction-dot"></span>
                                            <?= esc($v['violation_type'] ?? $v['violation_name'] ?? 'Traffic Infraction') ?>
                                            <span class="vp-infraction-state vp-infraction-state--<?= esc($rowTone) ?>"><?= esc($rowStatus) ?></span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="vp-infraction-amount">₱<?= number_format((float) ($v['penalty_amount'] ?? 0), 2) ?></div>
                                            <button type="button" class="vp-infraction-view"
                                                    onclick="showCitationTicket()"
                                                    title="Open the citation ticket (TCT) covering this violation">
                                                <i class="bi bi-eye"></i><span>View Ticket</span>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="vp-ticket-foot">
                                <div class="vp-ticket-totals">
                                    <div>
                                        <span class="vp-total-label">Ticket total</span>
                                        <span class="vp-total-value">₱<?= number_format($assessedTotal, 2) ?></span>
                                    </div>
                                    <div>
                                        <span class="vp-total-label">Paid</span>
                                        <span class="vp-total-value vp-total-value--paid">₱<?= number_format($paidTotal, 2) ?></span>
                                    </div>
                                    <div>
                                        <span class="vp-total-label">Balance</span>
                                        <span class="vp-total-value <?= $pendingTotal > 0 ? 'vp-total-value--balance' : 'vp-total-value--clear' ?>">₱<?= number_format($pendingTotal, 2) ?></span>
                                    </div>
                                </div>

                                <div class="vp-ticket-actions no-print">
                                    <a href="<?= $printUrl ?>" class="vp-action vp-action--print vp-action--labeled"
                                       title="Print this ticket">
                                        <i class="bi bi-printer"></i><span>Print</span>
                                    </a>
                                    <a href="<?= $printUrl ?>&paper=58mm" class="vp-action vp-action--print vp-action--labeled"
                                       title="One-page printout for the 58mm roll">
                                        <i class="bi bi-receipt"></i><span>58mm</span>
                                    </a>
                                </div>
                            </div>

                            <div class="vp-ticket-notes">
                                <i class="bi bi-info-circle me-1"></i>
                                58mm roll: use “58mm” &middot; Paper — 58mm / Roll paper if listed, otherwise <b>Save as PDF</b>
                                &middot; Scale — Default (100%); if the right side comes out cut, set Custom 22 &middot; Margins — None
                                &middot; Headers and footers — off &middot; Background graphics — on
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="vp-section-card premium-reveal mb-3" style="--reveal-delay:.12s;">
                    <div class="vp-section-head">
                        <div>
                            <div class="vp-section-title"><?= tricycle_icon('me-2') ?>Associated Vehicle</div>
                            <div class="vp-section-sub">Recorded on this citation.</div>
                        </div>
                    </div>
                    <?php if ($detailPlate === '—'): ?>
                        <div class="vp-side-empty">
                            <?= tricycle_icon() ?>
                            <span>No vehicle plate recorded.</span>
                        </div>
                    <?php else: ?>
                        <div class="vp-vehicle-list">
                            <div class="vp-vehicle">
                                <div class="vp-vehicle-id">
                                    <span class="vp-vehicle-label">Plate no.</span>
                                    <div class="vp-vehicle-plate"><?= esc(strtoupper($detailPlate)) ?></div>
                                </div>
                                <div class="vp-vehicle-meta">
                                    <span><?= esc($detailVehicle !== '—' ? $detailVehicle : 'Vehicle not recorded') ?></span>
                                    <?php if ($detailMtop !== '—'): ?>
                                        <span>MTOP <?= esc($detailMtop) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="vp-section-card premium-reveal" style="--reveal-delay:.16s;">
                    <div class="vp-section-head">
                        <div>
                            <div class="vp-section-title"><i class="bi bi-person-vcard me-2"></i>Violator Record</div>
                            <div class="vp-section-sub">Photo, other tickets, and the full dossier.</div>
                        </div>
                    </div>
                    <?php if ($detailOwnerId > 0): ?>
                        <div class="vp-profile-link">
                            <span class="vp-profile-link-name"><?= esc($detailName) ?></span>
                            <span class="vp-profile-link-id font-monospace">Record #<?= $detailOwnerId ?></span>
                            <a href="<?= base_url('officer/violator-profile/' . $detailOwnerId) ?>" class="vp-profile-link-btn">
                                <i class="bi bi-arrow-up-right"></i><span>View Violator Profile</span>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="vp-side-empty">
                            <i class="bi bi-person-x"></i>
                            <span>No violator record is linked to this citation.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- TCT Official Document Wrapper — collapsed until "View Citation Ticket"
         is used; print mode always renders it. -->
    <div class="citation-ticket-shell<?= $isPrintMode ? ' is-open' : '' ?>" id="ticketCollapse">
        <div class="citation-ticket-shell-bar no-print">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleCitationTicket()">
                <i class="bi bi-chevron-up me-1"></i> Hide Citation Ticket
            </button>
        </div>
        <div class="tct-official-card" id="tctCard">

        <!-- Header -->
        <div class="tct-header-area">
            <div class="tct-seal-box">
                <img src="<?= base_url('img/pic-1.png') ?>" alt="Kabankalan City Logo" class="tct-seal-img">
            </div>
            <div class="tct-header-text">
                <div class="tct-rep">Republic of the Philippines</div>
                <div class="tct-office">OFFICE OF THE CITY MAYOR</div>
                <div class="tct-city">Kabankalan City</div>
                <h1 class="tct-doc-title">TRAFFIC CITATION TICKET (TCT)</h1>
                <div class="tct-ord-no">C.O. # 2023-006</div>
            </div>
        </div>

        <!-- Section 1: Driver & Vehicle Info -->
        <div class="tct-doc-section">
            <!-- TO: and Driver's Name share one line, the way the printed ticket
                 stock has them, with the name parts lined up to their right. -->
            
            <div class="tct-row mb-2" style="display: flex; align-items: flex-start;">
                <span class="tct-bold-label me-3" style="white-space: nowrap; padding-top: 3px;">TO:</span>
                <span class="tct-bold-label me-2" style="white-space: nowrap; padding-top: 3px;">Driver's Name:</span>
                <div class="flex-1 d-flex flex-column" style="min-width: 0;">
                    <?php $tctName = tct_split_name($violation['first_name'] ?? null, $violation['middle_name'] ?? null, $violation['last_name'] ?? null, $violation['driver_name'] ?? null); ?>
                    <div class="d-flex w-100" style="min-width: 0; gap: 4px; align-items: flex-start;">
                        <div class="d-flex flex-column text-center" style="min-width: 0; flex: 1 1 auto;">
                            <span class="tct-field-underline d-block" style="min-width: 0;"><?= esc($tctName['first']) ?></span>
                            <span class="d-block" style="font-size: 0.72rem; font-style: italic; font-family: Arial, sans-serif; color: #64748b; margin-top: 2px;">(First)</span>
                        </div>
                        <div class="d-flex flex-column text-center" style="min-width: 0; flex: 1 1 auto;">
                            <span class="tct-field-underline d-block" style="min-width: 0;"><?= esc($tctName['middle']) ?></span>
                            <span class="d-block" style="font-size: 0.72rem; font-style: italic; font-family: Arial, sans-serif; color: #64748b; margin-top: 2px;">(Middle)</span>
                        </div>
                        <div class="d-flex flex-column text-center" style="min-width: 0; flex: 1 1 auto;">
                            <span class="tct-field-underline d-block" style="min-width: 0;"><?= esc($tctName['last']) ?></span>
                            <span class="d-block" style="font-size: 0.72rem; font-style: italic; font-family: Arial, sans-serif; color: #64748b; margin-top: 2px;">(Last)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tct-row mb-2 align-items-end">
                <span class="tct-bold-label me-2">Address:</span>
                <span class="tct-field-underline flex-1"><?= esc($violation['address'] ?? '—') ?></span>
            </div>

            <div class="tct-row mb-2 align-items-end">
                <div class="d-flex flex-1 align-items-end me-3">
                    <span class="tct-bold-label me-1">DL/Permit No.</span>
                    <span class="tct-field-underline flex-1"><?= esc($violation['license_number'] ?? '—') ?></span>
                </div>
                <div class="d-flex flex-1 align-items-end me-3">
                    <span class="tct-bold-label me-1">Plate#</span>
                    <span class="tct-field-underline flex-1"><?= esc($violation['license_plate'] ?? '—') ?></span>
                </div>
                <div class="d-flex flex-1 align-items-end me-3">
                    <span class="tct-bold-label me-1">MTOP#</span>
                    <span class="tct-field-underline flex-1"><?= esc($violation['mtop_number'] ?? '—') ?></span>
                </div>
                <div class="d-flex flex-1 align-items-end">
                    <span class="tct-bold-label me-1">Vehicle</span>
                    <span class="tct-field-underline flex-1"><?= esc($violation['vehicle_type'] ?? '—') ?></span>
                </div>
            </div>

            <div class="tct-row align-items-end">
                <div class="d-flex flex-1 align-items-end me-3">
                    <span class="tct-bold-label me-1">Owner:</span>
                    <span class="tct-field-underline flex-1"><?= esc($violation['owner_name'] ?? $violation['driver_name'] ?? '—') ?></span>
                </div>
                <div class="d-flex flex-1 align-items-end">
                    <span class="tct-bold-label me-1">Address</span>
                    <span class="tct-field-underline flex-1"><?= esc(trim((string) ($violation['owner_address'] ?? '')) !== '' ? $violation['owner_address'] : ($violation['address'] ?? '—')) ?></span>
                </div>
            </div>
        </div>

        <!-- Section 2: Violations Checklist -->
        <div class="tct-doc-section">
            <div class="tct-sec-heading text-center">VIOLATIONS</div>
            <div class="tct-sec-notice text-center mb-3">
                You are hereby charged/cited for comitting the violations marked "x" hereunder:
            </div>

            <div class="tct-checklist-container">
                <?php foreach ($standardViolations as $std): 
                    $checked = tct_is_checked($std, $activeViolationNames);
                ?>
                    <div class="tct-check-item">
                        <div class="tct-box <?= $checked ? 'is-checked' : '' ?>">
                            <?= $checked ? 'X' : '' ?>
                        </div>
                        <span class="tct-item-text"><?= esc($std) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Section 3: Place, Date & Legal Notice -->
        <div class="tct-doc-section">
            <div class="tct-row align-items-end mb-3">
                <div class="d-flex flex-1 align-items-end me-4">
                    <span class="tct-bold-label me-1">Place:</span>
                    <span class="tct-field-underline flex-1"><?= esc(trim((string) ($violation['location'] ?? '')) !== '' ? $violation['location'] : 'Kabankalan City') ?></span>
                </div>
                <div class="d-flex flex-1 align-items-end">
                    <span class="tct-bold-label me-1">Date &amp; Time</span>
                    <span class="tct-field-underline flex-1">
                        <?= isset($violation['violation_date']) ? date('M d, Y h:i A', strtotime($violation['violation_date'])) : date('M d, Y') ?>
                    </span>
                </div>
            </div>

            <div class="tct-legal-text">
                You are likewise directed to pay before the Office of the City Treasurer of Kabankalan City within three (3) days from the date of this citation and to secure clearance from the CTRAMO for disposition, Failure on your part to comply will constrain the Office to file the appropriate criminal action against you with the proper court.
            </div>
        </div>

        <!-- Section 4: Acknowledgment & Signatures -->
        <div class="tct-doc-section border-bottom-0 pb-0">
            <div class="tct-ack-heading mb-2">I HEREBY ACKNOWLEDGE RECEIPT OF THIS TCT.</div>

            <?php 
                $ackStr = strtolower($violation['acknowledgment'] ?? '');
                $isAdmitted = (strpos($ackStr, 'admitted') !== false);
                $isProtest  = (strpos($ackStr, 'protest') !== false);
            ?>
            <div class="d-flex gap-5 mb-4">
                <div class="d-flex align-items-center">
                    <div class="tct-box me-2 <?= $isAdmitted ? 'is-checked' : '' ?>">
                        <?= $isAdmitted ? 'X' : '' ?>
                    </div>
                    <span class="fw-semibold">Admitted</span>
                </div>
                <div class="d-flex align-items-center">
                    <div class="tct-box me-2 <?= $isProtest ? 'is-checked' : '' ?>">
                        <?= $isProtest ? 'X' : '' ?>
                    </div>
                    <span class="fw-semibold">Under Protest</span>
                </div>
            </div>

            <div class="tct-driver-signature-area text-center mb-4">
                <div class="tct-sig-wrapper mx-auto position-relative" style="width: 260px;">
                    <?php if (!empty($violation['driver_signature'])): ?>
                        <img src="<?= esc($violation['driver_signature']) ?>" alt="Driver Signature" style="max-height: 45px; position: absolute; bottom: 4px; left: 50%; transform: translateX(-50%);">
                    <?php endif; ?>
                    <div class="tct-sig-underline w-100"></div>
                </div>
                <div class="tct-sub-label mt-1">Signature of the Driver</div>
            </div>

            <div class="tct-officer-signature-area mb-3">
                <div class="tct-sub-label mb-1">Printed Name and Signature of Apprehending Officer.</div>
                <div class="tct-officer-box position-relative">
                    <?php if (!empty($violation['officer_signature'])): ?>
                        <img src="<?= esc($violation['officer_signature']) ?>" alt="Officer Signature" style="max-height: 48px; position: absolute; z-index: 1;">
                    <?php endif; ?>
                    <span class="officer-printed-name"><?= esc($violation['officer_name'] ?? 'OFFICER') ?></span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="tct-footer-area d-flex justify-content-between align-items-center pt-2">
            <div class="tct-driver-copy-tag">Driver's Copy</div>
            <div class="tct-number-tag">
                TCT No. <?= format_tct_number_display($violation['ticket_id'] ?? null, (int) ($violation['id'] ?? 0), $violation['violation_date'] ?? null) ?>
            </div>
        </div>

    </div>
</div>

</div>

<!-- Styles -->
<style>
    /* ── Citation page — the violator profile's design language ────────── */
    .vp-page { --vp-ink:#0b1730; --vp-line:#dbe5f1; --vp-violet:#7c3aed; --vp-green-dark:#047857; }

    .vp-topbar { display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin-bottom:1rem; }
    .vp-back-btn { display:inline-flex; align-items:center; gap:.45rem; padding:.56rem 1rem; border-radius:10px; border:1px solid #6d28d9; background:linear-gradient(135deg,#8b5cf6,#6d28d9); color:#fff; font-size:.92rem; font-weight:750; text-decoration:none; box-shadow:0 3px 9px rgba(124, 58, 237,.24); transition:all .18s ease; }
    .vp-back-btn:hover { background:linear-gradient(135deg,#7c3aed,#5b21b6); border-color:#5b21b6; color:#fff; box-shadow:0 5px 13px rgba(109, 40, 217,.3); transform:translateY(-1px); }
    .vp-breadcrumb { color:#64748b; font-weight:800; }
    .vp-breadcrumb-current { color:#24344d; font-size:.92rem; font-weight:750; }
    .vp-record-btn { display:inline-flex; align-items:center; gap:.5rem; padding:.6rem 1.1rem; border-radius:10px; background:linear-gradient(135deg,#8b5cf6,#6d28d9); color:#fff; font-size:.9rem; font-weight:750; text-decoration:none; box-shadow:0 3px 10px rgba(124, 58, 237,.24); transition:all .18s ease; }
    .vp-record-btn:hover { color:#fff; background:linear-gradient(135deg,#7c3aed,#5b21b6); box-shadow:0 5px 14px rgba(109, 40, 217,.3); transform:translateY(-1px); }
    .vp-report-btn { display:inline-flex; align-items:center; gap:.5rem; padding:.6rem 1.1rem; border-radius:10px; border:1px solid #ddd6fe; background:#f5f3ff; color:#6d28d9; font-size:.9rem; font-weight:750; text-decoration:none; transition:all .18s ease; }
    .vp-report-btn:hover { background:#ede9fe; border-color:#c4b5fd; color:#4c1d95; transform:translateY(-1px); }
    .vp-page a:focus-visible, .vp-page button:focus-visible { outline:3px solid rgba(124, 58, 237,.42); outline-offset:3px; }

    /* ── Hero banner ──────────────────────────────────────────────────── */
    .vp-hero { position:relative; overflow:hidden; border-radius:20px; padding:1.9rem 2rem; margin-bottom:1rem; background:linear-gradient(135deg,#4c1d95 0%,#8b5cf6 50%,#7c3aed 100%); box-shadow:0 12px 32px rgba(76, 29, 149,.25); border:1px solid rgba(255,255,255,.15); isolation:isolate; }
    .vp-hero-content { position:relative; z-index:2; display:flex; flex-wrap:wrap; align-items:center; gap:1.35rem; }

    .vp-hero-avatar-wrap { position:relative; width:104px; height:104px; flex-shrink:0; border-radius:50%; }
    .vp-avatar-display { width:100%; height:100%; border-radius:50%; overflow:hidden; position:relative; background:rgba(255,255,255,.12); }
    .vp-avatar-initials { width:100%; height:100%; border-radius:50%; border:4px solid rgba(255,255,255,.4); box-shadow:0 8px 28px rgba(0,0,0,.3); background:rgba(255,255,255,.18); backdrop-filter:blur(8px); display:flex; align-items:center; justify-content:center; font-size:2.4rem; font-weight:800; color:#fff; letter-spacing:-1px; }
    .vp-outcome-dot { position:absolute; bottom:4px; right:4px; width:18px; height:18px; border-radius:50%; border:3px solid #fff; z-index:5; }
    .vp-outcome-dot--paid { background:#4ade80; box-shadow:0 0 10px rgba(74,222,128,.85); }
    .vp-outcome-dot--pending { background:#ef4444; box-shadow:0 0 10px rgba(239,68,68,.85); }
    .vp-outcome-dot--cancelled { background:#94a3b8; }

    .vp-hero-info { min-width:0; flex:1 1 320px; }
    .vp-hero-name { font-size:1.7rem; font-weight:800; color:#fff; letter-spacing:-.03em; }
    .vp-hero-plate { display:inline-flex; align-items:center; background:#fff; color:#6d28d9; font-weight:800; font-size:.78rem; letter-spacing:.04em; padding:.32rem .7rem; border-radius:999px; box-shadow:0 2px 8px rgba(0,0,0,.18); font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; }
    .vp-hero-outcome { display:inline-flex; align-items:center; font-size:.73rem; font-weight:700; padding:.3rem .75rem; border-radius:20px; letter-spacing:.03em; text-transform:uppercase; }
    .vp-hero-outcome--paid { background:rgba(4,47,34,.55); border:1px solid rgba(167,243,208,.45); color:#a7f3d0; }
    .vp-hero-outcome--pending { background:rgba(127,29,29,.55); border:1px solid rgba(254,202,202,.55); color:#fecaca; }
    .vp-hero-outcome--cancelled { background:rgba(15,23,42,.5); border:1px solid rgba(203,213,225,.4); color:#e2e8f0; }
    .vp-hero-meta { display:flex; flex-wrap:wrap; gap:.35rem 1.05rem; color:rgba(255,255,255,.82); font-size:.85rem; margin-top:.4rem; }
    .vp-hero-meta span { display:inline-flex; align-items:center; min-width:0; }
    .vp-hero-meta i { opacity:.85; }

    .vp-hero-hud { background:rgba(15,23,42,.45); backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,.18); border-radius:16px; padding:.75rem 1.25rem; display:flex; flex-direction:column; gap:.35rem; box-shadow:0 8px 24px rgba(0,0,0,.2); min-width:0; }
    .vp-hud-status { display:inline-flex; align-items:center; gap:.45rem; font-size:.72rem; letter-spacing:.06em; text-transform:uppercase; }
    .vp-hud-status--paid { color:#4ade80; }
    .vp-hud-status--pending { color:#fca5a5; }
    .vp-hud-status--cancelled { color:#cbd5e1; }
    .vp-radar-dot { width:9px; height:9px; border-radius:50%; flex-shrink:0; }
    .vp-radar-dot--paid { background:#4ade80; box-shadow:0 0 0 0 rgba(74,222,128,.7); animation:vpRadar 1.8s infinite; }
    .vp-radar-dot--pending { background:#ef4444; box-shadow:0 0 0 0 rgba(239,68,68,.7); animation:vpRadarAmber 1.8s infinite; }
    .vp-radar-dot--cancelled { background:#94a3b8; }
    .vp-hud-money { color:#fff; font-size:.95rem; display:flex; align-items:baseline; }
    .vp-hud-money small { color:rgba(255,255,255,.75); font-size:.72rem; }
    .vp-hud-last { color:rgba(255,255,255,.78); font-size:.72rem; display:flex; align-items:center; flex-wrap:wrap; }

    .vp-hero-upload { display:flex; flex-direction:column; align-items:flex-start; }
    .vp-btn-upload { display:inline-flex; align-items:center; background:rgba(255,255,255,.2); backdrop-filter:blur(6px); border:1px solid rgba(255,255,255,.38); color:#fff; font-size:.85rem; font-weight:600; border-radius:12px; padding:.55rem 1.15rem; cursor:pointer; transition:all .22s ease; white-space:nowrap; box-shadow:0 4px 14px rgba(0,0,0,.15); margin:0; }
    .vp-btn-upload:hover { background:rgba(255,255,255,.32); transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,.22); }
    .vp-hero-hint { display:block; text-align:left; margin-top:.35rem; color:rgba(255,255,255,.55); font-size:.72rem; }

    .vp-hero-orb { position:absolute; border-radius:50%; pointer-events:none; z-index:1; filter:blur(55px); opacity:.38; }
    .vp-hero-orb-1 { width:240px; height:240px; background:#a78bfa; top:-70px; right:8%; animation:vpOrbFloat 14s ease-in-out infinite alternate; }
    .vp-hero-orb-2 { width:160px; height:160px; background:#c084fc; bottom:-55px; right:28%; animation:vpOrbFloat 18s ease-in-out infinite alternate-reverse; }
    .vp-hero-orb-3 { width:120px; height:120px; background:#c4b5fd; top:20%; right:4%; animation:vpOrbFloat 11s ease-in-out infinite alternate; }

    .vp-section-card { background:#fff; border:1px solid var(--vp-line); border-radius:14px; box-shadow:0 3px 12px rgba(15,23,42,.045); overflow:hidden; }
    .vp-section-head { display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; padding:1rem 1.15rem; border-bottom:1px solid #eaf0f7; background:linear-gradient(180deg,#fff,#fbfdff); }
    .vp-section-title { font-weight:800; color:var(--vp-ink); font-size:1rem; }
    .vp-section-title i { color:#6d28d9; }
    .vp-section-sub { font-size:.82rem; color:#52627a; margin-top:.16rem; }
    .vp-count-badge { background:#f5f3ff; border:1px solid #ddd6fe; color:#5b21b6; font-size:.78rem; font-weight:800; border-radius:999px; padding:.28rem .7rem; white-space:nowrap; }

    .vp-status-dot { width:7px; height:7px; border-radius:50%; background:#ef4444; box-shadow:0 0 6px rgba(239,68,68,.6); animation:vpPulse 2s infinite ease-in-out; }

    .vp-ticket-list { padding:.85rem; display:flex; flex-direction:column; gap:.75rem; }
    .vp-ticket { border:1px solid #e1e9f2; border-radius:12px; padding:.9rem 1rem; background:#fff; position:relative; transition:border-color .18s ease, box-shadow .18s ease; }
    .vp-ticket:hover { border-color:#ddd6fe; box-shadow:0 4px 14px rgba(15,23,42,.06); }
    .vp-ticket::before { content:''; position:absolute; left:0; top:12px; bottom:12px; width:3px; border-radius:0 3px 3px 0; background:#ef4444; }
    .vp-ticket--paid::before { background:#10b981; }
    .vp-ticket--cancelled::before { background:#ef4444; }
    .vp-ticket-head { display:flex; align-items:center; justify-content:space-between; gap:.6rem; flex-wrap:wrap; }
    .vp-ticket-id { display:inline-flex; align-items:center; gap:.4rem; background:#f5f3ff; border:1px solid #ddd6fe; color:#6d28d9; border-radius:8px; padding:.3rem .6rem; font-size:.84rem; font-weight:800; font-variant-numeric:tabular-nums; }
    .vp-ticket-status { display:inline-flex; align-items:center; gap:4px; font-size:.73rem; font-weight:800; border-radius:999px; padding:.28rem .65rem; text-transform:uppercase; letter-spacing:.04em; }
    .vp-ticket-status--pending { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }
    .vp-ticket-status--paid { background:#ecfccb; color:#3f6212; border:1px solid #84cc16; }
    .vp-ticket-status--cancelled { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
    .vp-ticket-date { font-size:.8rem; color:#475569; font-weight:650; white-space:nowrap; }
    .vp-ticket-date i { margin-right:.2rem; color:#6d28d9; }
    .vp-ticket-meta { display:flex; flex-wrap:wrap; gap:.3rem .9rem; margin-top:.48rem; font-size:.8rem; color:#475569; }
    .vp-ticket-meta i { color:#64748b; }
    .vp-ticket-enforcer { display:inline-flex; align-items:center; gap:.35rem; background:#f5f3ff; border:1px solid #c4b5fd; color:#6d28d9; border-radius:999px; padding:.2rem .62rem; font-weight:700; }
    .vp-ticket-meta .vp-ticket-enforcer i { color:#7c3aed; }
    .vp-ticket-enforcer-label { font-size:.68rem; font-weight:900; text-transform:uppercase; letter-spacing:.05em; opacity:.8; }

    .vp-infraction-table { margin-top:.6rem; border-top:1px dashed #e2e8f0; }
    .vp-infraction-row { display:flex; justify-content:space-between; align-items:center; gap:.75rem; padding:.48rem 0; border-bottom:1px dashed #e7edf5; font-size:.86rem; flex-wrap:wrap; }
    .vp-infraction-row:last-child { border-bottom:none; }
    .vp-infraction-name { color:#17243a; font-weight:650; display:flex; align-items:center; gap:.45rem; flex-wrap:wrap; }
    .vp-infraction-dot { width:5px; height:5px; border-radius:50%; background:#ef4444; flex-shrink:0; }
    .vp-infraction-state { font-size:.68rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; padding:.16rem .4rem; border-radius:5px; }
    .vp-infraction-state--pending { background:#fee2e2; color:#991b1b; }
    .vp-infraction-state--paid { background:#ecfccb; color:#3f6212; }
    .vp-infraction-state--cancelled { background:#fef2f2; color:#b91c1c; }
    .vp-infraction-amount { min-width:92px; text-align:right; font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-size:.84rem; font-weight:800; color:#1e293b; white-space:nowrap; font-variant-numeric:tabular-nums; }
    /* Every offense gets its own way into the official document. */
    .vp-infraction-view { display:inline-flex; align-items:center; gap:.3rem; background:#f5f3ff; border:1px solid #ddd6fe; color:#6d28d9; font-size:.72rem; font-weight:800; border-radius:7px; padding:.24rem .55rem; white-space:nowrap; transition:all .18s ease; }
    .vp-infraction-view:hover { background:#6d28d9; border-color:#6d28d9; color:#fff; transform:translateY(-1px); box-shadow:0 3px 8px rgba(109, 40, 217,.28); }

    .vp-ticket-foot { display:flex; align-items:flex-end; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin-top:.65rem; padding-top:.6rem; border-top:1px solid #f1f5f9; }
    .vp-ticket-totals { display:flex; gap:1rem; flex-wrap:wrap; }
    .vp-ticket-totals > div + div { padding-left:1rem; border-left:1px solid #eef2f7; }
    .vp-total-label { display:block; font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#52627a; }
    .vp-total-value { font-size:.86rem; font-weight:900; color:#1e293b; font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-variant-numeric:tabular-nums; }
    .vp-total-value--paid { color:var(--vp-green-dark); }
    .vp-total-value--balance { color:#b91c1c; }
    .vp-total-value--clear { color:var(--text-subtle, #5c6b80); }
    .vp-ticket-actions { display:flex; flex-wrap:wrap; align-items:center; gap:.35rem; }
    .vp-ticket-foot .vp-ticket-actions { padding-left:.8rem; border-left:1px solid #eef2f7; }
    .vp-action { height:36px; min-width:36px; border-radius:9px; border:1.5px solid #d1dbe8; background:#fff; color:#334155; display:inline-flex; align-items:center; justify-content:center; gap:.35rem; font-size:.9rem; text-decoration:none; transition:all .18s ease; padding:0 .55rem; }
    .vp-action:hover { transform:translateY(-1px); box-shadow:0 3px 8px rgba(15,23,42,.12); }
    .vp-action--labeled { width:auto; min-width:0; padding:0 .75rem; font-size:.78rem; font-weight:700; white-space:nowrap; }
    .vp-action--print { background:#f8fafc; border-color:#cbd5e1; color:#334155; }
    .vp-action--print:hover { background:#334155; border-color:#334155; color:#fff; }
    .vp-ticket-notes { margin-top:.55rem; padding-top:.5rem; border-top:1px dashed #e2e8f0; font-size:.8rem; color:#475569; }

    .vp-side-empty { padding:1.4rem 1rem; text-align:center; color:#52627a; font-size:.86rem; display:flex; flex-direction:column; gap:.4rem; align-items:center; }
    .vp-side-empty i { font-size:1.4rem; color:#cbd5e1; }
    .vp-vehicle-list { padding:.85rem; display:flex; flex-direction:column; gap:.5rem; }
    .vp-vehicle { display:flex; align-items:center; justify-content:space-between; gap:.6rem; padding:.55rem .65rem; border:1px solid #eef2f7; border-radius:9px; background:#f8fafc; }
    .vp-vehicle-id { display:flex; flex-direction:column; gap:.25rem; min-width:0; }
    .vp-vehicle-label { font-size:.66rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#52627a; }
    .vp-vehicle-plate { font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-weight:900; font-size:.85rem; color:#0f172a; letter-spacing:.06em; background:#fff; border:1.5px solid #cbd5e1; border-radius:5px; padding:.12rem .45rem; }
    .vp-vehicle-meta { display:flex; flex-direction:column; align-items:flex-end; font-size:.76rem; color:#52627a; }

    /* Person-level details stay in the dossier; this rail just points there. */
    .vp-profile-link { display:flex; flex-direction:column; gap:.5rem; padding:.85rem 1rem 1rem; }
    .vp-profile-link-name { color:#0f172a; font-weight:800; font-size:.9rem; }
    .vp-profile-link-id { color:#52627a; font-size:.75rem; font-weight:700; letter-spacing:.04em; }
    .vp-profile-link-btn { margin-top:.15rem; display:inline-flex; align-items:center; justify-content:center; gap:.35rem; padding:.5rem .8rem; border:1.5px solid #ddd6fe; border-radius:9px; background:#f5f3ff; color:#6d28d9; font-size:.8rem; font-weight:800; text-decoration:none; transition:all .18s ease; }
    .vp-profile-link-btn:hover { background:#6d28d9; border-color:#6d28d9; color:#fff; transform:translateY(-1px); box-shadow:0 3px 8px rgba(109, 40, 217,.28); }

    /* Dark theme, matching the profile cards. */
    :root[data-bs-theme="dark"] .vp-page { --vp-ink:#f8fafc; --vp-line:#334155; --vp-green-dark:#a3e635; }
    :root[data-bs-theme="dark"] .vp-breadcrumb-current,
    :root[data-bs-theme="dark"] .vp-ticket-date,
    :root[data-bs-theme="dark"] .vp-ticket-meta,
    :root[data-bs-theme="dark"] .vp-infraction-name,
    :root[data-bs-theme="dark"] .vp-infraction-amount,
    :root[data-bs-theme="dark"] .vp-total-value,
    :root[data-bs-theme="dark"] .vp-profile-link-name,
    :root[data-bs-theme="dark"] .vp-vehicle-meta { color:#e2e8f0; }
    :root[data-bs-theme="dark"] .vp-section-card,
    :root[data-bs-theme="dark"] .vp-ticket { background:#111b2f; border-color:#334155; }
    :root[data-bs-theme="dark"] .vp-section-head { background:linear-gradient(180deg,#172033,#111b2f); border-color:#334155; }
    :root[data-bs-theme="dark"] .vp-section-sub,
    :root[data-bs-theme="dark"] .vp-total-label,
    :root[data-bs-theme="dark"] .vp-profile-link-id,
    :root[data-bs-theme="dark"] .vp-vehicle-label,
    :root[data-bs-theme="dark"] .vp-side-empty,
    :root[data-bs-theme="dark"] .vp-breadcrumb,
    :root[data-bs-theme="dark"] .vp-ticket-notes { color:#94a3b8; }
    :root[data-bs-theme="dark"] .vp-section-title { color:#f8fafc; }
    :root[data-bs-theme="dark"] .vp-ticket-meta i { color:#94a3b8; }
    :root[data-bs-theme="dark"] .vp-ticket-enforcer { background:#2e1065; border-color:#5b21b6; color:#c4b5fd; }
    :root[data-bs-theme="dark"] .vp-ticket-meta .vp-ticket-enforcer i { color:#a78bfa; }
    :root[data-bs-theme="dark"] .vp-ticket-id,
    :root[data-bs-theme="dark"] .vp-count-badge { background:#2e1065; border-color:#6d28d9; color:#ddd6fe; }
    :root[data-bs-theme="dark"] .vp-ticket-status--paid,
    :root[data-bs-theme="dark"] .vp-infraction-state--paid { background:rgba(132,204,22,.16); border-color:rgba(163,230,53,.42); color:#bef264; }
    :root[data-bs-theme="dark"] .vp-infraction-table,
    :root[data-bs-theme="dark"] .vp-infraction-row,
    :root[data-bs-theme="dark"] .vp-ticket-foot,
    :root[data-bs-theme="dark"] .vp-ticket-foot .vp-ticket-actions,
    :root[data-bs-theme="dark"] .vp-ticket-notes { border-color:#334155; }
    :root[data-bs-theme="dark"] .vp-total-value--balance { color:#fca5a5; }
    :root[data-bs-theme="dark"] .vp-action { background:#1e293b; border-color:#475569; color:#e2e8f0; }
    :root[data-bs-theme="dark"] .vp-action--print { background:#1e293b; border-color:#475569; color:#cbd5e1; }
    :root[data-bs-theme="dark"] .vp-infraction-view,
    :root[data-bs-theme="dark"] .vp-profile-link-btn { background:rgba(124, 58, 237,.18); border-color:rgba(167, 139, 250,.4); color:#c4b5fd; }
    :root[data-bs-theme="dark"] .vp-vehicle { background:#172033; border-color:#334155; }
    :root[data-bs-theme="dark"] .vp-vehicle-plate { background:#0f172a; border-color:#475569; color:#f8fafc; }

    @keyframes vpPulse { 0%,100% { opacity:1; transform:scale(1);} 50% { opacity:.35; transform:scale(.7);} }
    @keyframes vpOrbFloat { from { transform:translate(0,0); } to { transform:translate(18px,14px); } }
    @keyframes vpRadar { 0%{transform:scale(.95);box-shadow:0 0 0 0 rgba(74,222,128,.7)} 70%{transform:scale(1.1);box-shadow:0 0 0 8px rgba(74,222,128,0)} 100%{transform:scale(.95);box-shadow:0 0 0 0 rgba(74,222,128,0)} }
    @keyframes vpRadarAmber { 0%{transform:scale(.95);box-shadow:0 0 0 0 rgba(239,68,68,.7)} 70%{transform:scale(1.1);box-shadow:0 0 0 8px rgba(239,68,68,0)} 100%{transform:scale(.95);box-shadow:0 0 0 0 rgba(239,68,68,0)} }

    /* The official TCT stays folded away until the reader asks for it. */
    .citation-ticket-shell { display: none; }
    .citation-ticket-shell.is-open {
        display: block;
        animation: citation-ticket-reveal .22s ease-out;
    }
    @keyframes citation-ticket-reveal {
        from { opacity: 0; transform: translateY(-6px); }
        to   { opacity: 1; transform: none; }
    }
    .citation-ticket-toggle i { pointer-events: none; }
    .citation-ticket-shell-bar {
        max-width: 720px;
        margin: 0 auto .6rem;
        display: flex;
        justify-content: flex-end;
    }

    @media (max-width: 991px) {
        .vp-hero-content { align-items:stretch; }
        .vp-hero-hud { width:100%; margin:.75rem 0 0 0 !important; flex-direction:row; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:.75rem; }
        .vp-hero-upload { width:100%; margin-top:.5rem; }
    }
    @media (max-width: 768px) {
        .vp-hero { padding:1.5rem 1.2rem; border-radius:18px; }
        .vp-hero-content { flex-direction:column; align-items:flex-start; gap:1.1rem; }
        .vp-hero-info { flex:0 0 auto; width:100%; }
        .vp-hero-avatar-wrap { width:90px; height:90px; }
        .vp-avatar-initials { font-size:2rem; }
        .vp-hero-name { font-size:1.35rem; }
        .vp-hero-meta { font-size:.8rem; gap:.3rem .75rem; }
        .vp-hero-upload { width:100%; }
        .vp-ticket-head { align-items:flex-start; flex-direction:column; }
        .vp-ticket-foot { flex-direction:column; align-items:stretch; }
        .vp-ticket-actions { justify-content:flex-end; }
        .vp-ticket-foot .vp-ticket-actions { padding-left:0; border-left:0; padding-top:.55rem; border-top:1px solid #eef2f7; }
        .vp-action { height:40px; min-width:40px; }
    }
    @media (max-width: 576px) {
        .vp-hero-hud { flex-direction:column; align-items:flex-start; gap:.4rem; padding:.75rem 1rem; }
        .vp-infraction-row > .d-flex { width:100%; justify-content:space-between; }
    }
    @media (max-width: 480px) {
        .vp-section-head { padding:.9rem; }
        .vp-section-sub { font-size:.78rem; }
        .vp-ticket { padding:.8rem .85rem; }
        .vp-ticket-totals { gap:.65rem; }
        .vp-ticket-totals > div + div { padding-left:.65rem; }
        .vp-infraction-amount { min-width:0; }
    }

    @media print {
        /* Printing always includes the official document, toggle or not. */
        .citation-ticket-shell { display: block !important; }
    }

    .tct-official-card {
        max-width: 720px;
        margin: 0 auto;
        background: #fff;
        border: 2px solid #000;
        padding: 24px 32px;
        font-family: 'Times New Roman', Times, serif, Arial, sans-serif;
        color: #000;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    /* Header */
    .tct-header-area {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 20px;
        min-height: 90px;
    }
    .tct-seal-box {
        position: absolute;
        left: 0;
        top: 0;
        width: 75px;
        height: 75px;
    }
    .tct-seal-img {
        width: 75px !important;
        height: 75px !important;
        max-width: 75px !important;
        max-height: 75px !important;
        object-fit: contain;
        display: block;
    }
    .tct-header-text {
        text-align: center;
        line-height: 1.25;
    }
    .tct-rep {
        font-size: 1rem;
    }
    .tct-office {
        font-size: 1.15rem;
        font-weight: bold;
    }
    .tct-city {
        font-size: 1rem;
    }
    .tct-doc-title {
        font-size: 1.35rem;
        font-weight: 900;
        margin-top: 8px;
        margin-bottom: 2px;
        letter-spacing: 0.02em;
        font-family: Arial, sans-serif;
    }
    .tct-ord-no {
        font-size: 1.05rem;
        font-weight: bold;
    }

    /* Sections */
    .tct-doc-section {
        border-bottom: 1.5px solid #000;
        padding-bottom: 14px;
        margin-bottom: 14px;
    }
    .tct-row {
        display: flex;
        width: 100%;
    }
    .tct-bold-label {
        font-weight: bold;
        font-size: 0.95rem;
        white-space: nowrap;
        font-family: Arial, sans-serif;
    }
    .tct-sub-label {
        font-size: 0.82rem;
        font-style: italic;
        color: #333;
    }

    /* Inline fields */
    .tct-name-group {
        display: flex;
        justify-content: space-between;
        gap: 15px;
    }
    .tct-name-col {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        border-bottom: 1px solid #000;
    }
    .tct-field-val {
        font-size: 1rem;
        font-weight: bold;
        text-transform: uppercase;
        font-family: Arial, sans-serif;
        min-height: 1.2rem;
    }
    .tct-field-underline {
        border-bottom: 1px solid #000;
        padding-left: 8px;
        padding-right: 8px;
        font-size: 0.98rem;
        font-weight: bold;
        font-family: Arial, sans-serif;
        min-height: 1.25rem;
    }

    /* Violations Checklist */
    .tct-sec-heading {
        font-size: 1.1rem;
        font-weight: bold;
        letter-spacing: 0.05em;
        font-family: Arial, sans-serif;
    }
    .tct-sec-notice {
        font-size: 0.88rem;
        font-style: italic;
    }
    .tct-checklist-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 6px 12px;
    }
    .tct-check-item {
        display: flex;
        align-items: center;
        font-size: 0.92rem;
    }
    .tct-box {
        width: 18px;
        height: 18px;
        border: 1.5px solid #000;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.85rem;
        font-family: monospace;
        line-height: 1;
        margin-right: 10px;
        flex-shrink: 0;
        background: #fff;
    }
    .tct-box.is-checked {
        background: #000;
        color: #fff;
    }
    .tct-item-text {
        font-family: Arial, sans-serif;
        font-size: 0.92rem;
    }

    /* Legal Notice */
    .tct-legal-text {
        font-size: 0.85rem;
        line-height: 1.35;
        text-align: justify;
        font-family: Times, 'Times New Roman', serif;
    }

    /* Signatures */
    .tct-ack-heading {
        font-weight: bold;
        font-size: 0.9rem;
        font-family: Arial, sans-serif;
    }
    .tct-sig-underline {
        width: 260px;
        border-bottom: 1.5px solid #000;
        height: 35px;
    }
    .tct-officer-box {
        border: 1.5px solid #000;
        height: 55px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fafafa;
    }
    .officer-printed-name {
        font-weight: bold;
        font-size: 1.05rem;
        text-transform: uppercase;
        font-family: Arial, sans-serif;
        letter-spacing: 0.05em;
    }

    /* Footer */
    .tct-driver-copy-tag {
        font-weight: bold;
        font-size: 1rem;
        font-family: Arial, sans-serif;
    }
    .tct-number-tag {
        font-weight: bold;
        font-size: 1.15rem;
        font-family: Arial, sans-serif;
    }
    .tct-year-prefix {
        color: #000;
        font-weight: 700;
    }
    .tct-red-no {
        color: #dc3545;
        font-family: 'Courier New', monospace;
        font-size: 1.3rem;
        font-weight: 900;
        letter-spacing: 0.05em;
    }

    /* Print styling — tuned for a 58mm thermal receipt printer.
       The printer's app/driver spools the page on an A4 canvas and then
       squeezes that canvas down to the 58mm roll (210mm ÷ 58mm ≈ 3.62×).
       The ticket is therefore still drawn as a 58mm column — readable
       sizes, one field per line — and pre-scaled up by exactly that factor,
       so the squeeze lands it back at true 58mm size on paper, with the
       length following the ticket's own height. */
    @media print {
        /* Keep the ticket shading on paper: the black "X" boxes that mark each
           charged violation are background paints, which browsers drop by
           default in print. */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .no-print,
        .sidebar, .sidebar-toggle, .mobile-topbar,
        .desktop-header, .breadcrumb, .btn,
        .alert, nav, header, footer {
            display: none !important;
        }
        .main-content,
        .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            transform: none !important;
        }
        /* Plain A4 canvas: the print app owns the roll mapping, we only make
           sure nothing limits the canvas. The root font-size rescales every
           rem-based size of the ticket at once. */
        html, body {
            width: 100% !important;
            font-size: 9px !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }
        .tct-official-card {
            width: 58mm !important;
            max-width: 58mm !important;
            margin: 0 auto !important;
            border: none !important;
            box-shadow: none !important;
            /* ~5mm side margins keep every line inside the printable band. */
            padding: 6px 20px 8px !important;
            /* Pre-scaled for the A4 canvas the app squeezes onto the roll
               (210 ÷ 58 ≈ 3.6207): the squeeze lands it back at 58mm, 1:1. */
            zoom: 3.6207 !important;
            transform: none !important;
        }

        /* Letterhead, centred on the paper: the seal sits above the text
           instead of beside it, so every line is centred on the full width
           and nothing is pushed to one side on the roll. */
        .tct-header-area {
            display: block !important;
            position: static !important;
            min-height: 0 !important;
            margin-bottom: 4px !important;
            text-align: center;
        }
        .tct-seal-box {
            position: static !important;
            width: 34px !important;
            height: 34px !important;
            margin: 0 auto 3px !important;
        }
        .tct-seal-img {
            width: 34px !important;
            height: 34px !important;
            max-width: 34px !important;
            max-height: 34px !important;
            margin: 0 auto !important;
            display: block !important;
        }
        /* The seal no longer shares their row; the lines centre on the full
           width like every other heading. */
        .tct-rep, .tct-office, .tct-city { margin-left: 0 !important; }
        .tct-doc-title { font-size: 9.2px; margin: 3px 0 1px; }
        .tct-office { font-size: 9.5px; }

        /* 58mm of paper cannot hold side-by-side columns: give every field
           its own line so nothing is squeezed into a two-letter column. */
        .tct-row { display: block !important; }
        .tct-row > .d-flex {
            margin-right: 0 !important;
            margin-bottom: 3px !important;
        }

        /* Driver's name: stack First / Middle / Last, left-aligned. */
        .tct-official-card .d-flex.w-100 {
            flex-direction: column !important;
            align-items: stretch !important;
        }
        .tct-official-card .d-flex.w-100 .d-flex.flex-column {
            text-align: left !important;
        }
        .tct-official-card .d-flex.w-100 .d-block:not(.tct-field-underline) {
            font-size: 7px !important;
            color: #000 !important;
        }

        /* Sections: tighten the air around each block. */
        .tct-doc-section {
            padding-bottom: 3px;
            margin-bottom: 3px;
        }
        .tct-doc-section .mb-2 { margin-bottom: 3px !important; }
        .tct-doc-section .mb-3 { margin-bottom: 5px !important; }
        .tct-doc-section .mb-4 { margin-bottom: 6px !important; }
        .tct-sub-label { font-size: 7.5px; color: #000; }
        .tct-field-underline {
            font-size: 8.5px;
            min-height: 10px;
            padding-left: 3px;
            padding-right: 3px;
        }

        /* Violations checklist: box and text sized for the narrow paper. */
        .tct-sec-notice {
            font-size: 8px;
            margin-bottom: 4px !important;
        }
        .tct-checklist-container { gap: 2px 0; line-height: 1.2; }
        .tct-check-item,
        .tct-item-text { font-size: 8.5px; }
        .tct-check-item { align-items: flex-start; }
        .tct-box {
            width: 12px;
            height: 12px;
            font-size: 7px;
            margin-right: 4px;
            border-width: 1px;
        }

        /* Legal notice: justified text leaves rivers this narrow; left-align. */
        .tct-legal-text {
            font-size: 8.5px;
            line-height: 1.25;
            text-align: left;
        }
        .tct-ack-heading { font-size: 9px; }

        /* Acknowledgment + signatures. */
        .tct-official-card .d-flex.gap-5 {
            gap: 14px !important;
            margin-bottom: 10px !important;
        }
        .tct-official-card .tct-sig-wrapper {
            width: 100% !important;
            max-width: 100% !important;
        }
        .tct-sig-underline {
            width: 100% !important;
            height: 24px;
        }
        .tct-officer-box { height: 36px; }
        .officer-printed-name { font-size: 9px; letter-spacing: 0.02em; }
        .tct-driver-signature-area { margin-bottom: 6px !important; }
        .tct-officer-signature-area { margin-bottom: 0 !important; }

        /* Footer: the two tags may wrap onto their own lines. */
        .tct-footer-area {
            flex-wrap: wrap;
            gap: 2px 8px;
            padding-top: 0 !important;
        }
        .tct-number-tag { font-size: 10px; }

        /* The spool canvas the app squeezes onto the roll is A4-shaped; keep
           it flat (no margins) so the geometry above stays predictable. */
        @page {
            size: A4 portrait;
            margin: 0;
        }
    }


    /* ── MOBILE: stack TCT rows so the citation fits the phone screen ── */
    @media screen and (max-width: 767px) {
        .tct-official-card {
            padding: 14px 12px;
        }

        /* Letterhead: the seal sits above the text instead of on top of it. */
        .tct-header-area {
            flex-direction: column;
            gap: 8px;
            min-height: 0;
            margin-bottom: 14px;
        }
        .tct-seal-box {
            position: static;
            width: 54px;
            height: 54px;
        }
        .tct-seal-img {
            width: 54px !important;
            height: 54px !important;
        }
        .tct-rep,
        .tct-city { font-size: 0.92rem; }
        .tct-office { font-size: 1.02rem; }
        .tct-doc-title {
            font-size: 1.16rem;
            line-height: 1.15;
            margin: 5px 0 2px;
        }

        .tct-row,
        .tct-row > * {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.3rem;
        }
        .tct-row > *,
        .tct-row > * > * {
            flex: none !important;
            width: 100% !important;
            min-width: 0 !important;
            margin-right: 0 !important;
        }

        /* The filled-in value starts on the same left edge as its label, so the
           typed name / address / plate lines up with the caption above it. */
        .tct-field-underline {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .tct-bold-label {
            margin-right: 0 !important;
            margin-bottom: 0 !important;
        }

        .tct-official-card .tct-sig-wrapper {
            width: 100% !important;
            max-width: 260px;
        }
        .tct-official-card .d-flex.gap-5 {
            gap: 1rem !important;
        }
    }
</style>

<?php if ($isPrintMode && $paper58): ?>
<style>
    /* 58mm roll mode ("Print 58mm Roll" button): use this when the printer
       app or the print dialog is set to 58mm / Roll paper. The ticket then
       prints exactly as drawn, at true size, on one continuous page. The
       card keeps its symmetric side paddings and auto margins, so the
       content stays centred on the roll for drivers that print from the
       paper origin. */
    @page { size: 58mm 297mm; margin: 0; }
    html, body { width: 58mm !important; margin: 0 auto !important; }
    .tct-official-card { zoom: 1 !important; margin: 0 auto !important; }
</style>
<?php endif; ?>

<?php if ($isPrintMode): ?>
<script>
    window.addEventListener('load', function () {
        // On touch devices (phones and tablets, including iPad desktop mode)
        // the dialog only opens from a user tap; the helper shows a
        // "Tap to Print" button there instead of failing silently. On desktop
        // it prints immediately. Either way the helper owns the return trip:
        // it comes back to the list only after the print sheet has closed.
        if (window.TrafficPrint) {
            window.TrafficPrint.autoOrPrompt({
                backUrl: <?= json_encode($backUrl) ?>
            });
        } else {
            window.print();
        }
    });
</script>
<?php endif; ?>

<script>
    (function () {
        // One ticket can cover several offenses, so every row's "View Ticket"
        // and the header toggle drive this single document panel.
        function setCitationTicket(open, shouldScroll) {
            var shell = document.getElementById('ticketCollapse');
            if (!shell) return;

            shell.classList.toggle('is-open', open);

            var btn = document.getElementById('ticketToggleBtn');
            if (btn) {
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                var label = btn.querySelector('span');
                if (label) {
                    label.textContent = open ? 'Hide Citation Ticket' : 'View Citation Ticket';
                }
            }

            if (open && shouldScroll) {
                shell.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        window.toggleCitationTicket = function () {
            var shell = document.getElementById('ticketCollapse');
            if (!shell) return;
            setCitationTicket(!shell.classList.contains('is-open'), true);
        };

        window.showCitationTicket = function () {
            setCitationTicket(true, true);
        };
    })();
</script>

<?= $this->endSection() ?>
