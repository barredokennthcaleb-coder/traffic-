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
    $vehicle = trim((string) ($violator['vehicle_type'] ?? ''));
    $mtop    = trim((string) ($violator['mtop_number'] ?? ''));
    $address = trim((string) ($violator['address'] ?? ''));
    $overall = (string) ($summary['overall_status'] ?? 'Pending');

    // Violator photo: shown when the stored file exists on disk, the single
    // initial otherwise. Upload controls appear only for a registered row.
    $photoViolatorId = (int) ($violator['id'] ?? 0);
    $photoFile       = trim((string) ($violator['profile_image'] ?? ''));
    $hasPhoto        = $photoFile !== '' && is_file(FCPATH . 'uploads/violators/' . $photoFile);
    $photoUrl        = $hasPhoto ? base_url('uploads/violators/' . $photoFile) : '';

    // ── Outcome summary ─────────────────────────────────────────────────
    // The hero banner and the three outcome cards are organized around this
    // person's standing on record: Pending, Settled or Cancelled. The dot,
    // the headline and the money line all follow it instead of the officer
    // profile's live duty status.
    $outcomeTone  = $overall === 'Paid' ? 'paid' : ($overall === 'Cancelled' ? 'cancelled' : 'pending');
    $outcomeLabel = $outcomeTone === 'paid' ? 'Settled' : ($outcomeTone === 'cancelled' ? 'Cancelled' : 'Pending');
    $outcomeHeadline = match ($outcomeTone) {
        'paid'      => 'ALL CITATIONS SETTLED',
        'cancelled' => 'CITATIONS CANCELLED',
        default     => 'PENDING SETTLEMENT',
    };

    $balance         = (float) ($summary['balance'] ?? 0);
    $assessed        = (float) ($summary['total_fines'] ?? 0);
    $paidTotal       = (float) ($summary['total_paid'] ?? 0);
    $sanctionCount   = (int) ($summary['attempt_count'] ?? 0);
    $ticketCount     = count($tickets);
    $infractionCount = (int) ($summary['infraction_count'] ?? 0);

    // Newest citation (tickets are sorted newest first) feeds the "last
    // apprehension" line with a real date and place.
    $lastTicket   = $tickets[0] ?? null;
    $lastDate     = ! empty($summary['last_violation_at'])
        ? date('M d, Y', strtotime((string) $summary['last_violation_at']))
        : '—';
    $lastLocation = trim((string) ($lastTicket['location'] ?? ''));

    // Money line wording follows the outcome: what is still owed while
    // pending, what was settled when paid, and nothing when cancelled.
    $moneyValue = $outcomeTone === 'paid' ? $paidTotal : ($outcomeTone === 'cancelled' ? 0.0 : $balance);
    $moneyLabel = $outcomeTone === 'paid' ? 'settled in full' : ($outcomeTone === 'cancelled' ? 'nothing due' : 'outstanding');
?>
<div class="container-fluid py-4 vp-page">

    <!-- TOOLBAR -->
    <div class="vp-topbar no-print">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= esc($backUrl) ?>" class="vp-back-btn btn-back-electric">
                <i class="bi bi-arrow-left"></i>
                <span><?= esc($backLabel) ?></span>
            </a>
            <span class="vp-breadcrumb">/</span>
            <span class="vp-breadcrumb-current"><?= esc($fullName) ?></span>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?php if (! empty($isAdmin)): ?>
                <a href="<?= base_url('violators/report/' . (int) $photoViolatorId) ?>" class="vp-report-btn"
                   title="Apprehension and sanction report for <?= esc($fullName, 'attr') ?>">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>Apprehension Report</span>
                </a>
            <?php endif; ?>
            <a href="<?= esc($recordUrl) ?>" class="vp-record-btn">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Record Violation</span>
            </a>
        </div>
    </div>

    <!-- ═══ OUTCOME HERO — the same banner language as the enforcer profile,
         re-aimed at this violator's standing on record ═══ -->
    <div class="vp-hero premium-reveal<?= $sanctionCount >= 3 ? ' is-third-sanction' : '' ?>" style="--reveal-delay:0s;">
        <div class="vp-hero-orb vp-hero-orb-1"></div>
        <div class="vp-hero-orb vp-hero-orb-2"></div>
        <div class="vp-hero-orb vp-hero-orb-3"></div>
        <div class="vp-hero-content">

            <!-- Photo: the uploaded picture, or the initials wordmark -->
            <div class="vp-hero-avatar-wrap" title="<?= $photoViolatorId > 0 ? 'Click or tap to change the photo' : 'Violator photo' ?>">
                <div class="vp-avatar-display" id="violatorAvatarDisplay">
                    <?php if ($hasPhoto): ?>
                        <img src="<?= esc($photoUrl) ?>" alt="Violator photo" class="vp-avatar-img" id="violatorAvatarImg">
                    <?php else: ?>
                        <span class="vp-avatar-initials" id="violatorAvatarInitials"><?= esc($initial) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($photoViolatorId > 0): ?>
                    <label for="violatorPhotoInput" class="vp-avatar-overlay" title="Upload photo">
                        <i class="bi bi-camera-fill"></i><span>Change</span>
                    </label>
                    <div class="vp-avatar-spinner d-none" id="violatorPhotoSpinner">
                        <div class="spinner-border text-light" style="width:1.6rem;height:1.6rem;" role="status"><span class="visually-hidden">Uploading...</span></div>
                    </div>
                <?php endif; ?>
                <!-- Outcome dot: green settled, amber balance, grey cancelled. -->
                <span class="vp-outcome-dot vp-outcome-dot--<?= esc($outcomeTone) ?>" title="<?= esc($outcomeLabel) ?>"></span>
            </div>

            <!-- Identity -->
            <div class="vp-hero-info">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h2 class="vp-hero-name mb-0"><?= esc($fullName) ?></h2>
                    <?php if ($plate !== '' && ! $isPlaceholder($plate)): ?>
                        <span class="vp-hero-plate"><?= tricycle_icon('me-1') ?><?= esc(strtoupper($plate)) ?></span>
                    <?php else: ?>
                        <span class="vp-hero-plate"><i class="bi bi-person-vcard me-1"></i>Record #<?= (int) ($violator['id'] ?? 0) ?></span>
                    <?php endif; ?>
                    <span class="vp-hero-outcome vp-hero-outcome--<?= esc($outcomeTone) ?>">
                        <?php if ($outcomeTone === 'paid'): ?><i class="bi bi-check-circle-fill me-1"></i>
                        <?php elseif ($outcomeTone === 'cancelled'): ?><i class="bi bi-x-circle-fill me-1"></i>
                        <?php else: ?><i class="bi bi-hourglass-split me-1"></i><?php endif; ?>
                        <?= esc($outcomeLabel) ?>
                    </span>
                </div>
                <div class="vp-hero-meta">
                                            <span class="badge sanction-level <?= $sanctionCount >= 3 ? 'sanction-level--third' : ($sanctionCount === 2 ? 'sanction-level--second' : 'sanction-level--first') ?> rounded-pill px-3 py-1 fw-bold" style="font-size: 0.78rem;" title="One sanction per apprehension on record"><?php if ($sanctionCount >= 3): ?><span class="sanction-badge-icon" aria-hidden="true">!</span><?php endif; ?><i class="bi bi-patch-check me-1"></i><?= esc(sanction_label($sanctionCount)) ?></span>
                    <?php if ($sanctionCount >= 2): ?>
                        <?php
                            $warningText = $sanctionCount >= 3
                                ? 'License at risk'
                                : 'Last sanction';
                            $warningTitle = $sanctionCount >= 3
                                ? sprintf('%s on record — one sanction per apprehension; the three-strike rule can revoke the driver\'s license.', esc(sanction_label($sanctionCount)))
                                : 'Second sanction on record — the next violation can put the driver\'s license in danger.';
                        ?>
                        <span class="sanction-warning" tabindex="0"
                              title="<?= esc($warningTitle, 'attr') ?>">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i><?= esc($warningText) ?><span class="sanction-exclaim" aria-hidden="true">!</span>
                        </span>
                    <?php endif; ?>
                    <?php if ($license !== '' && ! $isPlaceholder($license)): ?>
                        <span><i class="bi bi-person-badge me-1"></i>DL: <?= esc($license) ?></span>
                    <?php endif; ?>
                    <?php if ($vehicle !== '' && ! $isPlaceholder($vehicle)): ?>
                        <span><i class="bi bi-truck me-1"></i><?= esc($vehicle) ?></span>
                    <?php endif; ?>
                    <?php if ($address !== ''): ?>
                        <span><i class="bi bi-geo-alt me-1"></i><?= esc($address) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Outcome HUD: what the record stands at right now -->
            <div class="vp-hero-hud ms-lg-auto me-lg-3">
                <div class="vp-hud-status vp-hud-status--<?= esc($outcomeTone) ?>">
                    <span class="vp-radar-dot vp-radar-dot--<?= esc($outcomeTone) ?>"></span>
                    <span class="fw-bold"><?= esc($outcomeHeadline) ?></span>
                </div>
                <div class="vp-hud-money">
                    <i class="bi bi-cash-coin me-1"></i>
                    <span class="font-monospace fw-bold">&#8369;<?= number_format($moneyValue, 2) ?></span>
                    <small class="ms-1"><?= esc($moneyLabel) ?></small>
                </div>
                <div class="vp-hud-last">
                    <i class="bi bi-clock-history me-1"></i>
                    Last apprehension &middot; <?= esc($lastDate) ?><?= $lastLocation !== '' ? ' &middot; ' . esc($lastLocation) : '' ?>
                </div>
            </div>

            <!-- Photo actions -->
            <?php if ($photoViolatorId > 0): ?>
                <div class="vp-hero-upload">
                    <form id="violatorPhotoForm" action="<?= base_url('officer/violator/' . $photoViolatorId . '/photo') ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="file" id="violatorPhotoInput" name="profile_photo" class="d-none" accept="image/png,image/jpeg,image/jpg,image/webp">
                    </form>
                    <div class="d-flex flex-wrap align-items-center gap-2 justify-content-start justify-content-md-end">
                        <label class="vp-btn-upload" for="violatorPhotoInput">
                            <i class="bi bi-camera-fill me-1"></i><span id="violatorUploadText"><?= $hasPhoto ? 'Change Photo' : 'Add Photo' ?></span>
                        </label>
                        <button type="button" class="vp-btn-remove <?= $hasPhoto ? '' : 'd-none' ?>" id="btnRemoveViolatorPhoto" title="Remove violator photo">
                            <i class="bi bi-trash3 me-1"></i>Remove
                        </button>
                    </div>
                    <small class="vp-hero-hint">JPG, PNG, WEBP &middot; Max 10MB</small>
                </div>
            <?php endif; ?>
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
                    <?php $infractions = (int) ($summary['infraction_count'] ?? 0); ?>
                    <span class="vp-count-badge" title="Tickets issued, and the offenses listed on them">
                        <?= number_format(count($tickets)) ?> ticket<?= count($tickets) === 1 ? '' : 's' ?>
                        &middot; <?= number_format($infractions) ?> infraction<?= $infractions === 1 ? '' : 's' ?>
                    </span>
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
                                // The ticket page carries where it was opened from, the
                                // same way this profile does, so the sidebar keeps the
                                // Violators section lit instead of jumping to Payment.
                                $viewUrl      = $ticketViewBase . (int) $ticket['id'] . '?from=violators';
                                $printUrl     = $ticketViewBase . (int) $ticket['id'] . '?print=1&from=violators';
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
                                    <span class="vp-ticket-enforcer" title="Enforcer who recorded this ticket">
                                        <i class="bi bi-shield-check"></i>
                                        <span class="vp-ticket-enforcer-label">Enforcer:</span>
                                        <?= esc($ticket['officer_name'] !== '' ? $ticket['officer_name'] : 'Not recorded') ?>
                                    </span>
                                    <?php if (! empty($ticket['receipt_number'])): ?>
                                        <span title="Official Receipt (OR) number"><i class="bi bi-receipt me-1"></i>OR (Official Receipt): <?= esc($ticket['receipt_number']) ?></span>
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
                                        <a href="<?= esc($viewUrl) ?>" class="vp-action vp-action--view"
                                           title="View ticket" aria-label="View ticket <?= esc($ticket['ticket_id'], 'attr') ?>">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= esc($printUrl) ?>" class="vp-action vp-action--print"
                                           title="Print ticket in this tab" aria-label="Print ticket <?= esc($ticket['ticket_id'], 'attr') ?>">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <?php if ($ticketStatus === 'Pending' && ! empty($isAdmin)): ?>
                                            <a href="<?= base_url('penalties/pay/' . (int) $ticket['id']) ?>" class="vp-action vp-action--pay"
                                               title="Record payment" aria-label="Record payment for ticket <?= esc($ticket['ticket_id'], 'attr') ?>">
                                                <i class="bi bi-credit-card"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($ticketStatus === 'Pending'): ?>
                                            <button type="button" class="vp-action vp-action--cancel btn-open-cancel"
                                                    data-ticket="<?= esc($ticket['ticket_id'], 'attr') ?>"
                                                    data-action="<?= esc($cancelBase . (int) $ticket['id'], 'attr') ?>"
                                                    title="Cancel ticket"
                                                    aria-label="Cancel ticket <?= esc($ticket['ticket_id'], 'attr') ?>">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php
                                            // Delete stays available for every status, because the
                                            // reason to remove a ticket (a mistake, a duplicate, a
                                            // test entry) is not tied to whether it was paid.
                                            // Only an admin sees it, and it always asks first.
                                        ?>
                                        <?php if (! empty($isAdmin)): ?>
                                            <form method="post" class="vp-delete-form"
                                                  action="<?= base_url('penalties/delete/' . (int) $ticket['id']) ?>"
                                                  data-ticket-id="<?= esc($ticket['ticket_id'], 'attr') ?>">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="vp-action vp-action--delete"
                                                        title="Delete this ticket"
                                                        aria-label="Delete ticket <?= esc($ticket['ticket_id'], 'attr') ?>">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
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
                        <div class="vp-section-title"><?= tricycle_icon('me-2') ?>Associated Vehicles</div>
                        <div class="vp-section-sub">Plates seen across this violator's tickets.</div>
                    </div>
                </div>
                <?php if (empty($vehicles)): ?>
                    <div class="vp-side-empty">
                        <?= tricycle_icon() ?>
                        <span>No vehicle plate recorded.</span>
                    </div>
                <?php else: ?>
                    <div class="vp-vehicle-list">
                        <?php
                            // Iterate as $vehicleRow: $vehicle holds the person's
                            // snapshot (used down in Identity Details) and must
                            // not be clobbered by this loop.
                            foreach ($vehicles as $vehicleRow): ?>
                            <div class="vp-vehicle">
                                <div class="vp-vehicle-id">
                                    <span class="vp-vehicle-label">Plate no.</span>
                                    <div class="vp-vehicle-plate"><?= esc($vehicleRow['plate']) ?></div>
                                </div>
                                <div class="vp-vehicle-meta">
                                    <span><?= (int) $vehicleRow['count'] ?> ticket<?= (int) $vehicleRow['count'] === 1 ? '' : 's' ?></span>
                                    <?php if (! empty($vehicleRow['last_seen'])): ?>
                                        <span>Last seen <?= date('M d, Y', strtotime((string) $vehicleRow['last_seen'])) ?></span>
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
                        <div class="vp-section-sub">Matching uses license, then name + plate.</div>
                    </div>
                </div>
                <div class="vp-detail-list">
                    <div class="vp-detail-row">
                        <span class="vp-detail-label">Full name</span>
                        <span class="vp-detail-value"><?= esc($fullName) ?></span>
                    </div>
                    <div class="vp-detail-row">
                        <span class="vp-detail-label">License no.</span>
                        <span class="vp-detail-value"><?= esc($license !== '' && ! $isPlaceholder($license) ? $license : 'Not provided') ?></span>
                    </div>
                    <?php if ($vehicle !== '' && ! $isPlaceholder($vehicle)): ?>
                    <div class="vp-detail-row">
                        <span class="vp-detail-label">Vehicle</span>
                        <span class="vp-detail-value"><?= esc($vehicle) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($mtop !== '' && ! $isPlaceholder($mtop)): ?>
                    <div class="vp-detail-row">
                        <span class="vp-detail-label">MTOP no.</span>
                        <span class="vp-detail-value font-monospace"><?= esc(strtoupper($mtop)) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="vp-detail-row">
                        <span class="vp-detail-label">Address</span>
                        <span class="vp-detail-value"><?= esc($address !== '' ? $address : 'Not provided') ?></span>
                    </div>
                    <div class="vp-detail-row">
                        <span class="vp-detail-label">Owner</span>
                        <span class="vp-detail-value"><?= esc(! empty($violator['owner_name']) ? $violator['owner_name'] : 'Not provided') ?></span>
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
.vp-page { --vp-ink:#0b1730; --vp-line:#dbe5f1; --vp-blue:#0066ff; --vp-green-dark:#047857; }

.vp-topbar { display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin-bottom:1rem; }
.vp-back-btn { display:inline-flex; align-items:center; gap:.45rem; padding:.56rem 1rem; border-radius:10px; border:1px solid #0052cc; background:linear-gradient(135deg,#0875ff,#0052cc); color:#fff; font-size:.92rem; font-weight:750; text-decoration:none; box-shadow:0 3px 9px rgba(0,102,255,.24); transition:all .18s ease; }
.vp-back-btn:hover { background:linear-gradient(135deg,#0066ff,#0047b3); border-color:#0047b3; color:#fff; box-shadow:0 5px 13px rgba(0,82,204,.3); transform:translateY(-1px); }
.vp-breadcrumb { color:#64748b; font-weight:800; }
.vp-breadcrumb-current { color:#24344d; font-size:.92rem; font-weight:750; }
.vp-record-btn { display:inline-flex; align-items:center; gap:.5rem; padding:.6rem 1.1rem; border-radius:10px; background:linear-gradient(135deg,#0875ff,#0052cc); color:#fff; font-size:.9rem; font-weight:750; text-decoration:none; box-shadow:0 3px 10px rgba(0,102,255,.24); transition:all .18s ease; }
.vp-record-btn:hover { color:#fff; background:linear-gradient(135deg,#0066ff,#0047b3); box-shadow:0 5px 14px rgba(0,82,204,.3); transform:translateY(-1px); }
.vp-report-btn { display:inline-flex; align-items:center; gap:.5rem; padding:.6rem 1.1rem; border-radius:10px; border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; font-size:.9rem; font-weight:750; text-decoration:none; transition:all .18s ease; }
.vp-report-btn:hover { background:#dbeafe; border-color:#93c5fd; color:#1e3a8a; transform:translateY(-1px); }
.vp-page a:focus-visible, .vp-page button:focus-visible { outline:3px solid rgba(0,102,255,.42); outline-offset:3px; }

/* ── OUTCOME HERO ────────────────────────────────────────────────────────
   The same banner language as the traffic enforcer profile, re-aimed at the
   violator's standing on record: Pending, Settled or Cancelled. The dot,
   the headline and the money line all follow the outcome. */
.vp-hero { position:relative; overflow:hidden; border-radius:20px; padding:1.9rem 2rem; margin-bottom:1rem; background:linear-gradient(135deg,#1e3a8a 0%,#3b82f6 50%,#2563eb 100%); box-shadow:0 12px 32px rgba(30,58,138,.25); border:1px solid rgba(255,255,255,.15); isolation:isolate; }
.vp-hero-content { position:relative; z-index:2; display:flex; flex-wrap:wrap; align-items:center; gap:1.35rem; }

.vp-hero-avatar-wrap { position:relative; width:104px; height:104px; flex-shrink:0; border-radius:50%; }
.vp-avatar-display { width:100%; height:100%; border-radius:50%; overflow:hidden; position:relative; background:rgba(255,255,255,.12); }
.vp-avatar-img { width:100%; height:100%; border-radius:50%; object-fit:cover; border:4px solid rgba(255,255,255,.4); box-shadow:0 8px 28px rgba(0,0,0,.3); display:block; }
.vp-avatar-initials { width:100%; height:100%; border-radius:50%; border:4px solid rgba(255,255,255,.4); box-shadow:0 8px 28px rgba(0,0,0,.3); background:rgba(255,255,255,.18); backdrop-filter:blur(8px); display:flex; align-items:center; justify-content:center; font-size:2.4rem; font-weight:800; color:#fff; letter-spacing:-1px; }
.vp-avatar-overlay { position:absolute; inset:0; border-radius:50%; background:rgba(15,23,42,.68); backdrop-filter:blur(3px); display:flex; flex-direction:column; align-items:center; justify-content:center; color:#fff; font-size:.72rem; font-weight:700; opacity:0; pointer-events:none; transition:opacity .22s ease; cursor:pointer; z-index:3; text-transform:uppercase; letter-spacing:.05em; }
.vp-hero-avatar-wrap:hover .vp-avatar-overlay { opacity:1; pointer-events:auto; }
.vp-avatar-spinner { position:absolute; inset:0; border-radius:50%; background:rgba(15,23,42,.78); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; z-index:4; }
.vp-outcome-dot { position:absolute; bottom:4px; right:4px; width:18px; height:18px; border-radius:50%; border:3px solid #fff; z-index:5; }
.vp-outcome-dot--paid { background:#4ade80; box-shadow:0 0 10px rgba(74,222,128,.85); }
.vp-outcome-dot--pending { background:#ef4444; box-shadow:0 0 10px rgba(239,68,68,.85); }
.vp-outcome-dot--cancelled { background:#94a3b8; }

.vp-hero-info { min-width:0; flex:1 1 320px; }
.vp-hero-name { font-size:1.7rem; font-weight:800; color:#fff; letter-spacing:-.03em; }
.vp-hero-plate { display:inline-flex; align-items:center; background:#fff; color:#1d4ed8; font-weight:800; font-size:.78rem; letter-spacing:.04em; padding:.32rem .7rem; border-radius:999px; box-shadow:0 2px 8px rgba(0,0,0,.18); font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; }
.vp-hero-outcome { display:inline-flex; align-items:center; font-size:.73rem; font-weight:700; padding:.3rem .75rem; border-radius:20px; letter-spacing:.03em; text-transform:uppercase; }
.vp-hero-outcome--paid { background:rgba(4,47,34,.55); border:1px solid rgba(167,243,208,.45); color:#a7f3d0; }
.vp-hero-outcome--pending { background:rgba(127,29,29,.55); border:1px solid rgba(254,202,202,.55); color:#fecaca; }
.vp-hero-outcome--cancelled { background:rgba(15,23,42,.5); border:1px solid rgba(203,213,225,.4); color:#e2e8f0; }
.vp-hero-meta { display:flex; flex-wrap:wrap; gap:.35rem 1.05rem; color:rgba(255,255,255,.82); font-size:.85rem; margin-top:.4rem; }
.vp-hero-meta span { display:inline-flex; align-items:center; min-width:0; }
.vp-hero-meta i { opacity:.85; }
/* Sanction level: one sanction per apprehension, highlighted by attempt
   count — green first, amber second, red from the third on. */
.sanction-level {
    border: 1px solid transparent;
    transition: color .18s ease, background-color .18s ease, border-color .18s ease, box-shadow .18s ease;
}
.sanction-level--first {
    background: #d1fae5;
    border-color: #a7f3d0;
    color: #047857;
}
.sanction-level--second {
    background: #fef3c7;
    border-color: #fde68a;
    color: #a16207;
}
.sanction-level--third {
    background: #fef2f2;
    border-color: #fecaca;
    color: #991b1b;
    box-shadow: 0 0 0 4px rgba(220, 38, 38, .10);
}
/* The 3rd-sanction badge leads with a solid red exclamation mark, so the
   escalation is read before the words are. */
.sanction-badge-icon { display: none; }
.sanction-level--third .sanction-badge-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    margin-right: .4rem;
    border-radius: 50%;
    background: #dc2626;
    color: #ffffff;
    font-size: .78rem;
    font-weight: 900;
    line-height: 1;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, .16);
    transform-origin: center;
    animation: sanctionBadgeBeat 1.35s ease-in-out infinite;
}
/* Heartbeat on the mark itself: two soft beats per cycle, so the 3rd-sanction
   badge reads as alive without the whole card moving. */
@keyframes sanctionBadgeBeat {
    0%, 26%, 58%, 100% { scale: 1;    box-shadow: 0 0 0 3px rgba(220, 38, 38, .16); }
    12%                 { scale: 1.18; box-shadow: 0 0 0 6px rgba(220, 38, 38, .10); }
    40%                 { scale: 1.12; box-shadow: 0 0 0 5px rgba(220, 38, 38, .12); }
}
.sanction-level--third .bi-patch-check { display: none; }
/* 3rd sanction or worse: the record is red on screen at rest, so the
   escalation is visible without hovering. Hover adds a heartbeat pulse. */
.vp-hero.is-third-sanction {
    background: linear-gradient(135deg, #7f1d1d 0%, #dc2626 50%, #b91c1c 100%);
    border-color: rgba(254, 202, 202, .5);
    box-shadow: 0 12px 32px rgba(185, 28, 28, .32);
    transition: border-color .28s ease, box-shadow .28s ease;
}
.vp-hero.is-third-sanction .sanction-level--third {
    background: #fef2f2;
    border-color: #fecaca;
    color: #991b1b;
    box-shadow: 0 0 0 4px rgba(220, 38, 38, .12), 0 4px 14px rgba(69, 10, 10, .28);
}
/* The detail line carries the license, plate and address: on the red hero it
   goes full white and a step heavier so the labels stay readable. */
.vp-hero.is-third-sanction .vp-hero-meta {
    color: #ffffff;
    font-weight: 600;
    font-size: .9rem;
}
.vp-hero.is-third-sanction .vp-hero-meta i { opacity: 1; }
.vp-hero.is-third-sanction .vp-hero-plate { color: #b91c1c; }
.vp-hero.is-third-sanction .vp-hero-outcome {
    background: rgba(69, 10, 10, .55);
    border-color: rgba(254, 202, 202, .6);
    color: #fecaca;
}
.vp-hero.is-third-sanction .vp-hero-orb-2 { background: rgba(248, 113, 113, .45); }

/* The heartbeat belongs to the warning sign only: the license-at-risk chip
   beats gently, and nothing else on the hero moves. */
.sanction-warning {
    transform-origin: center;
    animation: sanctionChipBeat 1.35s ease-in-out infinite;
}
@keyframes sanctionChipBeat {
    0%, 26%, 58%, 100% { scale: 1; }
    12%                { scale: 1.07; }
    40%                { scale: 1.04; }
}

/* Exclamation closes the wording, so it reads as part of the label. */
.sanction-exclaim {
    display: none;
    margin-left: .22rem;
    font-weight: 900;
    font-size: 1.15em;
    line-height: 1;
}
.sanction-warning .sanction-exclaim {
    display: inline-block;
    color: #ffffff;
}

/* No hover state for the escalated hero. The card is already red and its
   glow already beats, so pointing at it or dragging across it must not
   change anything (an earlier hover animation collided with .premium-reveal
   and collapsed the card). */
/* Reduced motion: keep the red state, drop the beats and the easing. */
@media (prefers-reduced-motion: reduce) {
    .vp-hero.is-third-sanction { transition: none; }
    .vp-hero.is-third-sanction .sanction-level { transition: none; }
    .sanction-warning { animation: none; }
    .sanction-level--third .sanction-badge-icon { animation: none; }
}
:root[data-bs-theme="dark"] .sanction-level--first {
    background: rgba(52, 211, 153, .14);
    border-color: rgba(52, 211, 153, .32);
    color: #6ee7b7;
}
:root[data-bs-theme="dark"] .sanction-level--second {
    background: rgba(245, 158, 11, .13);
    border-color: rgba(251, 191, 36, .28);
    color: #fcd34d;
}
:root[data-bs-theme="dark"] .sanction-level--third {
    background: rgba(248, 113, 113, .13);
    border-color: rgba(248, 113, 113, .3);
    color: #fca5a5;
}
/* Third sanction: a red warning sign beside the badge, visible at rest and
   hover responsive — it lifts, and its triangle nudges, on hover or focus. */
.sanction-warning {
    display: inline-flex;
    align-items: center;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .02em;
    padding: .28rem .65rem;
    border-radius: 999px;
    background: #dc2626;
    border: 1px solid #991b1b;
    color: #ffffff;
    white-space: nowrap;
    cursor: help;
    box-shadow: 0 2px 10px rgba(220, 38, 38, .45);
    transition: background-color .16s ease, border-color .16s ease, transform .16s ease, box-shadow .16s ease;
}
.sanction-warning:hover,
.sanction-warning:focus-visible {
    background: #b91c1c;
    border-color: #7f1d1d;
    color: #ffffff;
    transform: translateY(-1px) scale(1.05);
    box-shadow: 0 6px 18px rgba(220, 38, 38, .65);
    outline: 2px solid rgba(255, 255, 255, .65);
    outline-offset: 1px;
}
.sanction-warning i { transition: transform .16s ease; }
.sanction-warning:hover i,
.sanction-warning:focus-visible i { animation: sanctionWarnNudge .5s ease; }
@keyframes sanctionWarnNudge {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(-14deg); }
    60% { transform: rotate(12deg); }
}
/* The hero banner is blue in both themes, so the warning keeps a solid red
   fill with white letters instead of a tint that would sink into it. */
:root[data-bs-theme="dark"] .sanction-warning {
    background: #dc2626;
    border-color: #991b1b;
    color: #ffffff;
}
:root[data-bs-theme="dark"] .sanction-warning:hover,
:root[data-bs-theme="dark"] .sanction-warning:focus-visible {
    background: #b91c1c;
    border-color: #7f1d1d;
    color: #ffffff;
}
@media (hover: none) {
    .sanction-warning:hover { transform: none; }
}
@media (prefers-reduced-motion: reduce) {
    .sanction-warning,
    .sanction-warning i { transition: none !important; }
    .sanction-warning:hover,
    .sanction-warning:focus-visible { transform: none !important; }
    .sanction-warning:hover i,
    .sanction-warning:focus-visible i { animation: none !important; }
}


.vp-hero-hud { background:rgba(15,23,42,.45); backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,.18); border-radius:16px; padding:.75rem 1.25rem; display:flex; flex-direction:column; gap:.35rem; box-shadow:0 8px 24px rgba(0,0,0,.2); min-width:0; }
.vp-hud-status { display:inline-flex; align-items:center; gap:.45rem; font-size:.72rem; letter-spacing:.06em; text-transform:uppercase; }
.vp-hud-status--paid { color:#4ade80; }
.vp-hud-status--pending { color:#fca5a5; }
.vp-hud-status--cancelled { color:#cbd5e1; }
.vp-radar-dot { width:9px; height:9px; border-radius:50%; flex-shrink:0; }
.vp-radar-dot--paid { background:#4ade80; box-shadow:0 0 0 0 rgba(74,222,128,.7); animation:vpRadar 1.8s infinite; }
.vp-radar-dot--pending { background:#ef4444; box-shadow:0 0 0 0 rgba(239,68,68,.7); animation:vpRadarAmber 1.8s infinite; }
.vp-radar-dot--cancelled { background:#94a3b8; }
@keyframes vpRadar { 0%{transform:scale(.95);box-shadow:0 0 0 0 rgba(74,222,128,.7)} 70%{transform:scale(1.1);box-shadow:0 0 0 8px rgba(74,222,128,0)} 100%{transform:scale(.95);box-shadow:0 0 0 0 rgba(74,222,128,0)} }
@keyframes vpRadarAmber { 0%{transform:scale(.95);box-shadow:0 0 0 0 rgba(239,68,68,.7)} 70%{transform:scale(1.1);box-shadow:0 0 0 8px rgba(239,68,68,0)} 100%{transform:scale(.95);box-shadow:0 0 0 0 rgba(239,68,68,0)} }
.vp-hud-money { color:#fff; font-size:.95rem; display:flex; align-items:baseline; }
.vp-hud-money small { color:rgba(255,255,255,.75); font-size:.72rem; }
.vp-hud-last { color:rgba(255,255,255,.78); font-size:.72rem; display:flex; align-items:center; flex-wrap:wrap; }

.vp-hero-upload { display:flex; flex-direction:column; align-items:flex-start; }
.vp-btn-upload { display:inline-flex; align-items:center; background:rgba(255,255,255,.2); backdrop-filter:blur(6px); border:1px solid rgba(255,255,255,.38); color:#fff; font-size:.85rem; font-weight:600; border-radius:12px; padding:.55rem 1.15rem; cursor:pointer; transition:all .22s ease; white-space:nowrap; box-shadow:0 4px 14px rgba(0,0,0,.15); margin:0; }
.vp-btn-upload:hover { background:rgba(255,255,255,.32); transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,.22); }
.vp-btn-remove { display:inline-flex; align-items:center; background:rgba(239,68,68,.22); backdrop-filter:blur(6px); border:1px solid rgba(239,68,68,.45); color:#fee2e2; font-size:.85rem; font-weight:600; border-radius:12px; padding:.55rem 1rem; cursor:pointer; transition:all .22s ease; box-shadow:0 4px 14px rgba(239,68,68,.15); }
.vp-btn-remove:hover { background:rgba(239,68,68,.45); color:#fff; transform:translateY(-2px); box-shadow:0 6px 20px rgba(239,68,68,.25); }
.vp-hero-hint { display:block; text-align:left; margin-top:.35rem; color:rgba(255,255,255,.55); font-size:.72rem; }

.vp-hero-orb { position:absolute; border-radius:50%; pointer-events:none; z-index:1; filter:blur(55px); opacity:.38; }
.vp-hero-orb-1 { width:240px; height:240px; background:#818cf8; top:-70px; right:8%; animation:vpOrbFloat 14s ease-in-out infinite alternate; }
.vp-hero-orb-2 { width:160px; height:160px; background:#c084fc; bottom:-55px; right:28%; animation:vpOrbFloat 18s ease-in-out infinite alternate-reverse; }
.vp-hero-orb-3 { width:120px; height:120px; background:#38bdf8; top:20%; right:4%; animation:vpOrbFloat 11s ease-in-out infinite alternate; }
@keyframes vpOrbFloat { from { transform:translate(0,0); } to { transform:translate(18px,14px); } }

/* ── OUTCOME STRIP — the three numbers behind the banner ──────────────── */
.vp-outcome-card { background:#fff; border:1px solid #f1f5f9; border-radius:16px; box-shadow:0 3px 12px rgba(15,23,42,.05); padding:.9rem 1rem; display:flex; align-items:center; gap:.85rem; height:100%; }
.vp-outcome-icon { width:46px; height:46px; border-radius:12px; flex-shrink:0; display:inline-flex; align-items:center; justify-content:center; }
.vp-outcome-icon--pending { background:#fee2e2; color:#b91c1c; }
.vp-outcome-icon--paid { background:#dcfce7; color:#15803d; }
.vp-outcome-icon--cancelled { background:#f1f5f9; color:#475569; }
.vp-outcome-icon--info { background:#dbeafe; color:#1d4ed8; }
.vp-outcome-label { display:block; font-size:.66rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#52627a; }
.vp-outcome-value { display:block; font-size:1.05rem; font-weight:800; color:#0b1730; line-height:1.3; }
.vp-outcome-value--pending { color:#b91c1c; }
.vp-outcome-value--paid { color:#15803d; }
.vp-outcome-value--cancelled { color:#475569; }
.vp-outcome-sub { display:block; font-size:.72rem; color:#52627a; margin-top:.1rem; overflow-wrap:anywhere; }

.vp-section-card { background:#fff; border:1px solid var(--vp-line); border-radius:14px; box-shadow:0 3px 12px rgba(15,23,42,.045); overflow:hidden; }
.vp-section-head { display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; padding:1rem 1.15rem; border-bottom:1px solid #eaf0f7; background:linear-gradient(180deg,#fff,#fbfdff); }
.vp-section-title { font-weight:800; color:var(--vp-ink); font-size:1rem; }
.vp-section-title i { color:#0066cc; }
.vp-section-sub { font-size:.82rem; color:#52627a; margin-top:.16rem; }
.vp-count-badge { background:#eff6ff; border:1px solid #bfdbfe; color:#174ea6; font-size:.78rem; font-weight:800; border-radius:999px; padding:.28rem .7rem; white-space:nowrap; }

.vp-status-dot { width:7px; height:7px; border-radius:50%; background:#ef4444; box-shadow:0 0 6px rgba(239,68,68,.6); animation:vpPulse 2s infinite ease-in-out; }

.vp-ticket-list { padding:.85rem; display:flex; flex-direction:column; gap:.75rem; }
.vp-ticket { border:1px solid #e1e9f2; border-radius:12px; padding:.9rem 1rem; background:#fff; position:relative; transition:border-color .18s ease, box-shadow .18s ease; }
.vp-ticket:hover { border-color:#b9d5ff; box-shadow:0 4px 14px rgba(15,23,42,.06); }
.vp-ticket::before { content:''; position:absolute; left:0; top:12px; bottom:12px; width:3px; border-radius:0 3px 3px 0; background:#ef4444; }
.vp-ticket--paid::before { background:#10b981; }
.vp-ticket--cancelled::before { background:#ef4444; }
.vp-ticket-head { display:flex; align-items:center; justify-content:space-between; gap:.6rem; flex-wrap:wrap; }
.vp-ticket-id { display:inline-flex; align-items:center; gap:.4rem; background:#eff6ff; border:1px solid #bfdbfe; color:#0052cc; border-radius:8px; padding:.3rem .6rem; font-size:.84rem; font-weight:800; cursor:pointer; font-variant-numeric:tabular-nums; transition:background .18s ease, border-color .18s ease; }
.vp-ticket-id:hover { background:#dbeafe; border-color:#93c5fd; }
.vp-ticket-id i:last-child { opacity:.55; font-size:.68rem; }
.vp-ticket-status { display:inline-flex; align-items:center; gap:4px; font-size:.73rem; font-weight:800; border-radius:999px; padding:.28rem .65rem; text-transform:uppercase; letter-spacing:.04em; }
.vp-ticket-status--pending { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }
.vp-ticket-status--paid { background:#ecfccb; color:#3f6212; border:1px solid #84cc16; }
.vp-ticket-status--cancelled { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
.vp-ticket-date { font-size:.8rem; color:#475569; font-weight:650; white-space:nowrap; }
.vp-ticket-date i { margin-right:.2rem; color:#0066cc; }
.vp-ticket-meta { display:flex; flex-wrap:wrap; gap:.3rem .9rem; margin-top:.48rem; font-size:.8rem; color:#475569; }
.vp-ticket-meta i { color:#64748b; }
/* The apprehending enforcer is part of the citation's identity, so it reads
   as a labelled violet badge instead of blending into the metadata line. */
.vp-ticket-enforcer { display:inline-flex; align-items:center; gap:.35rem; background:#f5f3ff; border:1px solid #c4b5fd; color:#6d28d9; border-radius:999px; padding:.2rem .62rem; font-weight:700; }
.vp-ticket-meta .vp-ticket-enforcer i { color:#7c3aed; }
.vp-ticket-enforcer-label { font-size:.68rem; font-weight:900; text-transform:uppercase; letter-spacing:.05em; opacity:.8; }

.vp-infraction-table { margin-top:.6rem; border-top:1px dashed #e2e8f0; }
.vp-infraction-row { display:flex; justify-content:space-between; align-items:center; gap:.75rem; padding:.48rem 0; border-bottom:1px dashed #e7edf5; font-size:.86rem; }
.vp-infraction-row:last-child { border-bottom:none; }
.vp-infraction-name { color:#17243a; font-weight:650; display:flex; align-items:center; gap:.45rem; flex-wrap:wrap; }
.vp-infraction-dot { width:5px; height:5px; border-radius:50%; background:#ef4444; flex-shrink:0; }
.vp-infraction-state { font-size:.68rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; padding:.16rem .4rem; border-radius:5px; }
.vp-infraction-state--pending { background:#fee2e2; color:#991b1b; }
.vp-infraction-state--paid { background:#ecfccb; color:#3f6212; }
.vp-infraction-state--cancelled { background:#fef2f2; color:#b91c1c; }
.vp-infraction-amount { min-width:92px; text-align:right; font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-size:.84rem; font-weight:800; color:#1e293b; white-space:nowrap; font-variant-numeric:tabular-nums; }

.vp-ticket-foot { display:flex; align-items:flex-end; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin-top:.65rem; padding-top:.6rem; border-top:1px solid #f1f5f9; }
.vp-ticket-totals { display:flex; gap:1rem; flex-wrap:wrap; }
/* Hairline between ticket total, paid and balance so the three figures read as
   one small statement rather than three loose pairs. */
.vp-ticket-totals > div + div { padding-left:1rem; border-left:1px solid #eef2f7; }
.vp-total-label { display:block; font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#52627a; }
.vp-total-value { font-size:.86rem; font-weight:900; color:#1e293b; font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-variant-numeric:tabular-nums; }
.vp-total-value--paid { color:var(--vp-green-dark); }
.vp-total-value--balance { color:#b91c1c; }
.vp-total-value--clear { color:var(--text-subtle, #5c6b80); }
.vp-ticket-actions { display:flex; flex-wrap:wrap; align-items:center; gap:.35rem; }
/* The row's tools sit apart from its money, so the eye reaches the figures
   without buttons in the path. */
.vp-ticket-foot .vp-ticket-actions { padding-left:.8rem; border-left:1px solid #eef2f7; }
.vp-action { width:36px; height:36px; border-radius:9px; border:1.5px solid #d1dbe8; background:#fff; color:#334155; display:inline-flex; align-items:center; justify-content:center; font-size:.9rem; text-decoration:none; transition:all .18s ease; padding:0; }
.vp-action:hover { transform:translateY(-1px); box-shadow:0 3px 8px rgba(15,23,42,.12); }
.vp-action--pay { background:#ecfdf5; border-color:#86efac; color:#047857; }
.vp-action--pay:hover,
.vp-action--pay:focus-visible { background:#65a30d; border-color:#65a30d; color:#fff; box-shadow:0 4px 12px rgba(101,163,13,.3); }
/* Cancel voids a ticket but keeps it on the record, while delete removes it.
   They sit side by side, so cancel takes the amber "warning" tone and delete
   keeps the only red on the row. */
.vp-action--cancel { background:#fffbeb; border-color:#fde68a; color:#b45309; }
.vp-action--cancel:hover { background:#b45309; border-color:#b45309; color:#fff; }
.vp-action--view { background:#eff6ff; border-color:#bfdbfe; color:#0052cc; }
.vp-action--view:hover { background:#0052cc; border-color:#0052cc; color:#fff; }
.vp-action--print { background:#f8fafc; border-color:#cbd5e1; color:#334155; }
.vp-action--print:hover { background:#334155; border-color:#334155; color:#fff; }
/* Delete is the only irreversible action on the row, so it is the only one
   that reads red at rest. It matches the cancel styling weight so the row
   does not turn into a traffic light. */
.vp-action--delete { background:#fff1f2; border-color:#fecdd3; color:#be123c; }
.vp-action--delete:hover { background:#be123c; border-color:#be123c; color:#fff; }
.vp-delete-form { display:inline-flex; margin:0; }
.vp-action.is-busy { opacity:.55; pointer-events:none; }
.vp-ticket-notes { margin-top:.55rem; padding-top:.5rem; border-top:1px dashed #e2e8f0; font-size:.8rem; color:#475569; }

.vp-empty { padding:2.5rem 1rem; text-align:center; }
.vp-empty-icon { width:54px; height:54px; border-radius:50%; background:#f1f5f9; border:1.5px solid #e2e8f0; color:var(--text-subtle, #5c6b80); display:inline-flex; align-items:center; justify-content:center; font-size:1.4rem; margin-bottom:.65rem; }
.vp-empty-title { font-weight:800; color:var(--vp-ink); }
.vp-empty-sub { color:#52627a; font-size:.86rem; }
.vp-empty .btn { background:linear-gradient(135deg,#0875ff,#0052cc); border-color:#0052cc; box-shadow:0 2px 8px rgba(0,102,255,.2); }
.vp-empty .btn:hover { background:#0047b3; border-color:#0047b3; }

.vp-side-empty { padding:1.4rem 1rem; text-align:center; color:#52627a; font-size:.86rem; display:flex; flex-direction:column; gap:.4rem; align-items:center; }
.vp-side-empty i { font-size:1.4rem; color:#cbd5e1; }
.vp-vehicle-list { padding:.85rem; display:flex; flex-direction:column; gap:.5rem; }
.vp-vehicle { display:flex; align-items:center; justify-content:space-between; gap:.6rem; padding:.55rem .65rem; border:1px solid #eef2f7; border-radius:9px; background:#f8fafc; }
.vp-vehicle-id { display:flex; flex-direction:column; gap:.25rem; min-width:0; }
.vp-vehicle-label { font-size:.66rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#52627a; }
.vp-vehicle-plate { font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-weight:900; font-size:.85rem; color:#0f172a; letter-spacing:.06em; background:#fff; border:1.5px solid #cbd5e1; border-radius:5px; padding:.12rem .45rem; }
.vp-vehicle-meta { display:flex; flex-direction:column; align-items:flex-end; font-size:.76rem; color:#52627a; }

.vp-detail-list { padding:.65rem 1rem .9rem; }
.vp-detail-row { display:flex; justify-content:space-between; gap:.75rem; padding:.58rem 0; border-bottom:1px dashed #e7edf5; font-size:.84rem; }
.vp-detail-row:last-child { border-bottom:none; }
.vp-detail-label { color:#52627a; font-weight:700; font-size:.78rem; }
.vp-detail-value { color:#1e293b; font-weight:650; text-align:right; word-break:break-word; font-variant-numeric:tabular-nums; }

:root[data-bs-theme="dark"] .vp-page {
    --vp-ink: #f8fafc;
    --vp-line: #334155;
    --vp-green-dark: #a3e635;
}
:root[data-bs-theme="dark"] .vp-ticket-status--paid,
:root[data-bs-theme="dark"] .vp-infraction-state--paid {
    background: rgba(132, 204, 22, .16);
    border-color: rgba(163, 230, 53, .42);
    color: #bef264;
}
:root[data-bs-theme="dark"] .vp-breadcrumb-current,
:root[data-bs-theme="dark"] .vp-ticket-date,
:root[data-bs-theme="dark"] .vp-ticket-meta,
:root[data-bs-theme="dark"] .vp-infraction-name,
:root[data-bs-theme="dark"] .vp-infraction-amount,
:root[data-bs-theme="dark"] .vp-total-value,
:root[data-bs-theme="dark"] .vp-detail-value,
:root[data-bs-theme="dark"] .vp-vehicle-meta {
    color: #e2e8f0;
}
:root[data-bs-theme="dark"] .vp-section-card,
:root[data-bs-theme="dark"] .vp-ticket,
:root[data-bs-theme="dark"] .vp-outcome-card {
    background: #111b2f;
    border-color: #334155;
}
:root[data-bs-theme="dark"] .vp-section-head {
    background: linear-gradient(180deg, #172033, #111b2f);
    border-color: #334155;
}
:root[data-bs-theme="dark"] .vp-section-sub,
:root[data-bs-theme="dark"] .vp-total-label,
:root[data-bs-theme="dark"] .vp-detail-label,
:root[data-bs-theme="dark"] .vp-empty-sub,
:root[data-bs-theme="dark"] .vp-side-empty,
:root[data-bs-theme="dark"] .vp-breadcrumb,
:root[data-bs-theme="dark"] .vp-ticket-notes {
    color: #94a3b8;
}
:root[data-bs-theme="dark"] .vp-section-title,
:root[data-bs-theme="dark"] .vp-empty-title {
    color: #f8fafc;
}
:root[data-bs-theme="dark"] .vp-ticket-date,
:root[data-bs-theme="dark"] .vp-ticket-meta {
    color: #cbd5e1;
}
:root[data-bs-theme="dark"] .vp-ticket-meta i {
    color: #94a3b8;
}
:root[data-bs-theme="dark"] .vp-ticket-enforcer {
    background: #2e1065;
    border-color: #5b21b6;
    color: #c4b5fd;
}
:root[data-bs-theme="dark"] .vp-ticket-meta .vp-ticket-enforcer i {
    color: #a78bfa;
}
:root[data-bs-theme="dark"] .vp-ticket-id,
:root[data-bs-theme="dark"] .vp-count-badge {
    background: #172554;
    border-color: #1d4ed8;
    color: #bfdbfe;
}
:root[data-bs-theme="dark"] .vp-outcome-label,
:root[data-bs-theme="dark"] .vp-outcome-sub {
    color: #94a3b8;
}
:root[data-bs-theme="dark"] .vp-outcome-value {
    color: #f8fafc;
}
:root[data-bs-theme="dark"] .vp-outcome-value--pending { color: #fca5a5; }
:root[data-bs-theme="dark"] .vp-outcome-value--paid { color: #bef264; }
:root[data-bs-theme="dark"] .vp-outcome-value--cancelled { color: #cbd5e1; }
:root[data-bs-theme="dark"] .vp-ticket-id:hover {
    background: #1e3a8a;
    border-color: #3b82f6;
}
:root[data-bs-theme="dark"] .vp-ticket {
    box-shadow: none;
}
:root[data-bs-theme="dark"] .vp-ticket:hover {
    border-color: #475569;
    box-shadow: 0 4px 14px rgba(0, 0, 0, .28);
}
:root[data-bs-theme="dark"] .vp-infraction-table,
:root[data-bs-theme="dark"] .vp-infraction-row,
:root[data-bs-theme="dark"] .vp-ticket-foot,
:root[data-bs-theme="dark"] .vp-ticket-foot .vp-ticket-actions,
:root[data-bs-theme="dark"] .vp-detail-row,
:root[data-bs-theme="dark"] .vp-ticket-notes {
    border-color: #334155;
}
:root[data-bs-theme="dark"] .vp-total-value--balance {
    color: #fca5a5;
}
:root[data-bs-theme="dark"] .vp-action {
    background: #1e293b;
    border-color: #475569;
    color: #e2e8f0;
}
:root[data-bs-theme="dark"] .vp-action--pay {
    background: rgba(16, 185, 129, .16);
    border-color: rgba(52, 211, 153, .4);
    color: #6ee7b7;
}
:root[data-bs-theme="dark"] .vp-action--pay:hover,
:root[data-bs-theme="dark"] .vp-action--pay:focus-visible {
    background: #65a30d;
    border-color: #a3e635;
    color: #fff;
    box-shadow: 0 4px 12px rgba(132, 204, 22, .35);
}
:root[data-bs-theme="dark"] .vp-action--view {
    background: rgba(37, 99, 235, .18);
    border-color: rgba(96, 165, 250, .4);
    color: #93c5fd;
}
:root[data-bs-theme="dark"] .vp-action--print {
    background: #1e293b;
    border-color: #475569;
    color: #cbd5e1;
}
:root[data-bs-theme="dark"] .vp-action--delete {
    background: rgba(190, 18, 60, .16);
    border-color: rgba(244, 63, 94, .4);
    color: #fda4af;
}
:root[data-bs-theme="dark"] .vp-vehicle {
    background: #172033;
    border-color: #334155;
}
:root[data-bs-theme="dark"] .vp-vehicle-plate {
    background: #0f172a;
    border-color: #475569;
    color: #f8fafc;
}
:root[data-bs-theme="dark"] .vp-vehicle-label { color: #94a3b8; }
:root[data-bs-theme="dark"] .vp-empty-icon {
    background: #1e293b;
    border-color: #334155;
    color: #94a3b8;
}

@keyframes vpPulse { 0%,100% { opacity:1; transform:scale(1);} 50% { opacity:.35; transform:scale(.7);} }

@media (max-width: 991px) {
    /* The HUD drops below the identity and spans the banner, so the outcome
       stays on screen without squeezing the name on a tablet. */
    .vp-hero-content { align-items:stretch; }
    .vp-hero-hud { width:100%; margin:.75rem 0 0 0 !important; flex-direction:row; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:.75rem; }
    .vp-hero-upload { width:100%; margin-top:.5rem; }
    .vp-hero-upload .d-flex { width:100%; justify-content:flex-start !important; }
    .vp-hero-hint { text-align:left; }
}
@media (max-width: 768px) {
    .vp-hero { padding:1.5rem 1.2rem; border-radius:18px; }
    .vp-hero-content { flex-direction:column; align-items:flex-start; gap:1.1rem; }
    /* In column direction the desktop flex-basis (320px) would become a
       minimum height and leave an empty gap under the name. */
    .vp-hero-info { flex:0 0 auto; width:100%; }
    .vp-hero-avatar-wrap { width:90px; height:90px; }
    .vp-avatar-initials { font-size:2rem; }
    .vp-hero-name { font-size:1.35rem; }
    .vp-hero-meta { font-size:.8rem; gap:.3rem .75rem; }
    .vp-hero-upload .d-flex { width:100%; }
    .vp-hero-upload .vp-btn-upload,
    .vp-hero-upload .vp-btn-remove { flex:1 1 auto; justify-content:center; min-height:44px; }
    .vp-ticket-head { align-items:flex-start; flex-direction:column; }
    .vp-ticket-foot { flex-direction:column; align-items:stretch; }
    .vp-ticket-actions { justify-content:flex-end; }
    /* Stacked on a phone, the tools move below the figures, so the divider
       follows them there instead of leaving a stray vertical line. */
    .vp-ticket-foot .vp-ticket-actions { padding-left:0; border-left:0; padding-top:.55rem; border-top:1px solid #eef2f7; }
    /* Five actions on a phone are easy to hit by mistake, so each one gets a
       full thumb-sized target and the row is allowed to wrap instead of
       squeezing them. */
    .vp-action { width:40px; height:40px; font-size:.9rem; }
}
@media (max-width: 576px) {
    /* The HUD stacks on narrow phones; the money line stays the loud part. */
    .vp-hero-hud { flex-direction:column; align-items:flex-start; gap:.4rem; padding:.75rem 1rem; }
}
@media (max-width: 480px) {
    .vp-section-head { padding:.9rem; }
    .vp-section-sub { font-size:.78rem; }
    .vp-ticket { padding:.8rem .85rem; }
    .vp-ticket-totals { gap:.65rem; }
    .vp-ticket-totals > div + div { padding-left:.65rem; }
    .vp-infraction-amount { min-width:0; }
}

/* ── PRINT ──────────────────────────────────────────────────────────────
   The banner is a blue gradient carrying white ink; paper has no gradient,
   so flatten it or the name and outcome print white-on-white. */
@media print {
    .vp-hero { background:#fff !important; border:1px solid #94a3b8 !important; box-shadow:none !important; border-radius:0 !important; padding:10px 0 !important; }
    .vp-hero-orb,
    .vp-outcome-dot,
    .vp-avatar-overlay,
    .vp-hero-upload,
    .vp-avatar-spinner,
    .vp-radar-dot { display:none !important; }
    .vp-hero-name,
    .vp-hero-meta,
    .vp-hero-meta *,
    .vp-hero-plate,
    .vp-hero-outcome,
    .vp-hero-outcome *,
    .vp-hud-status,
    .vp-hud-status *,
    .vp-hud-money,
    .vp-hud-money *,
    .vp-hud-last,
    .vp-hud-last *,
    .vp-avatar-initials {
        color:#0f172a !important;
    }
    .vp-hero-outcome,
    .vp-hero-hud,
    .vp-hero-plate {
        background:#fff !important;
        border:1px solid #0f172a !important;
        border-radius:6px !important;
        box-shadow:none !important;
    }
    .vp-outcome-card { border:1px solid #cbd5e1 !important; box-shadow:none !important; }
    #violatorPhotoForm { display:none !important; }
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

/* Deleting a ticket removes the citation and every infraction on it, so it
   always asks first, then posts in place and refreshes the page. Submitting
   through fetch instead of a page navigation keeps the officer where they
   were instead of throwing them back to the worklist. */
(function () {
    const forms = document.querySelectorAll('.vp-delete-form');
    if (!forms.length) return;

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const button   = form.querySelector('button[type="submit"]');
            const ticketId = form.dataset.ticketId || 'this ticket';
            const isLast   = document.querySelectorAll('.vp-ticket').length <= 1;

            const ask = window.AppConfirm
                ? window.AppConfirm({
                    title: 'Delete this ticket?',
                    html: 'Ticket <strong>' + ticketId + '</strong> and all of its infractions will be removed from the records. This cannot be undone.',
                    confirmText: '<i class="bi bi-trash3 me-1"></i> Delete',
                    confirmColor: '#be123c',
                    customClass: { popup: 'swal-danger' }
                })
                : Promise.resolve({ isConfirmed: window.confirm('Delete ticket ' + ticketId + '? This cannot be undone.') });

            ask.then(function (result) {
                if (!result || !result.isConfirmed) return;

                if (button) {
                    button.disabled = true;
                    button.classList.add('is-busy');
                }

                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    credentials: 'same-origin'
                })
                    .then(function (response) {
                        if (!response.ok) throw new Error('HTTP ' + response.status);

                        // Removing the violator's only ticket prunes the
                        // violator too, so this record would no longer exist.
                        if (isLast) {
                            window.location.assign('<?= base_url('violators') ?>');
                            return;
                        }

                        window.location.reload();
                    })
                    .catch(function () {
                        if (button) {
                            button.disabled = false;
                            button.classList.remove('is-busy');
                        }

                        if (window.Toast) {
                            Toast.fire({ icon: 'error', title: 'Could not delete that ticket.' });
                        }
                    });
            });
        });
    });
})();

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
<script>
/* Violator photo: upload, live preview, remove — the same flow the enforcer
   record uses, so the dossier behaves identically for every role. */
(function () {
    const form = document.getElementById('violatorPhotoForm');
    if (!form) return;

    const input      = document.getElementById('violatorPhotoInput');
    const display    = document.getElementById('violatorAvatarDisplay');
    const spinner    = document.getElementById('violatorPhotoSpinner');
    const btnRemove  = document.getElementById('btnRemoveViolatorPhoto');
    const uploadText = document.getElementById('violatorUploadText');

    const fallbackInitial = <?= json_encode($initial) ?>;
    const removeUrl       = <?= json_encode(base_url('officer/violator/' . $photoViolatorId . '/photo/remove')) ?>;

    function showToast(icon, title) {
        if (window.Swal) {
            Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true, icon: icon, title: title });
        }
    }

    function showError(title, text) {
        if (window.Swal) {
            Swal.fire({ icon: 'error', title: title, text: text, confirmButtonColor: '#2563eb' });
        } else {
            window.alert(text);
        }
    }

    function refreshCsrf(data) {
        if (!data.csrf_token || !data.csrf_hash) return;
        document.querySelectorAll('input[name="' + data.csrf_token + '"]').forEach(function (field) {
            field.value = data.csrf_hash;
        });
    }

    function setInitial(text) {
        const span = document.createElement('span');
        span.id = 'violatorAvatarInitials';
        span.className = 'vp-avatar-initials';
        span.textContent = text;
        display.replaceChildren(span);
    }

    if (input) {
        input.addEventListener('change', async function () {
            const file = this.files && this.files[0];
            if (!file) return;

            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!validTypes.includes((file.type || '').toLowerCase())) {
                showError('Unsupported Image Format', 'Please choose a valid JPG, PNG, or WEBP photo.');
                this.value = '';
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                showError('File Too Large', 'Photo must be under 10MB.');
                this.value = '';
                return;
            }

            const previousHTML = display.innerHTML;
            const previewUrl   = URL.createObjectURL(file);
            display.innerHTML  = '<img src="' + previewUrl + '" alt="Photo preview" class="vp-avatar-img" id="violatorAvatarImg">';
            if (spinner) spinner.classList.remove('d-none');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(form)
                });
                const data = await response.json().catch(function () { return {}; });

                if (response.ok && data.status === 'success') {
                    display.innerHTML = '<img src="' + data.photo_url + '?t=' + Date.now() + '" alt="Violator photo" class="vp-avatar-img" id="violatorAvatarImg">';
                    if (btnRemove) btnRemove.classList.remove('d-none');
                    if (uploadText) uploadText.textContent = 'Change Photo';
                    refreshCsrf(data);
                    showToast('success', data.message || 'Violator photo updated.');
                } else {
                    throw new Error(data.message || 'Could not save the photo.');
                }
            } catch (err) {
                display.innerHTML = previousHTML;
                showError('Upload Failed', err.message || 'Failed to upload the photo. Please try again.');
            } finally {
                if (spinner) spinner.classList.add('d-none');
                input.value = '';
                URL.revokeObjectURL(previewUrl);
            }
        });
    }

    if (btnRemove) {
        btnRemove.addEventListener('click', function () {
            const ask = window.AppConfirm
                ? window.AppConfirm({
                    title: 'Remove this photo?',
                    html: 'The photo will be deleted and the record falls back to its initial.',
                    confirmText: '<i class="bi bi-trash3 me-1"></i> Remove',
                    confirmColor: '#be123c',
                    customClass: { popup: 'swal-danger' }
                })
                : Promise.resolve({ isConfirmed: window.confirm('Remove this violator photo?') });

            ask.then(async function (result) {
                if (!result || !result.isConfirmed) return;

                if (spinner) spinner.classList.remove('d-none');

                try {
                    const body      = new FormData();
                    const csrfInput = form.querySelector('input[type="hidden"]');
                    if (csrfInput) body.append(csrfInput.name, csrfInput.value);

                    const response = await fetch(removeUrl, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: body
                    });
                    const data = await response.json().catch(function () { return {}; });

                    if (response.ok && data.status === 'success') {
                        setInitial(data.initial || fallbackInitial);
                        btnRemove.classList.add('d-none');
                        if (uploadText) uploadText.textContent = 'Add Photo';
                        refreshCsrf(data);
                        showToast('success', data.message || 'Violator photo removed.');
                    } else {
                        throw new Error(data.message || 'Could not remove the photo.');
                    }
                } catch (err) {
                    showError('Error', err.message || 'Failed to remove the photo.');
                } finally {
                    if (spinner) spinner.classList.add('d-none');
                }
            });
        });
    }
})();
</script>
<?= $this->endSection() ?>
