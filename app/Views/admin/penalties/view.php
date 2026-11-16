<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Violator Details - <?= esc($violation['ticket_id'] ?? 'TCT Ticket') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $isPrintMode  = service('request')->getGet('print') === '1';

    $totalFine   = 0;
    $vList       = !empty($all_violations) ? $all_violations : [$violation];
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
?>

<!-- Screen toolbar (hidden on print) -->
<div class="container-fluid py-3 no-print">
    <div class="d-flex justify-content-between align-items-center">
        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm btn-back-electric">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <div class="d-flex gap-2">
            <?php if (! empty($violation['violator_id'])): ?>
                <a href="<?= base_url('penalties/violator/' . (int) $violation['violator_id']) ?>" class="btn btn-outline-dark btn-sm shadow-sm">
                    <i class="bi bi-person-vcard me-1"></i> Violator Profile
                </a>
            <?php endif; ?>
            <?php if (($violation['status'] ?? '') === 'Pending'): ?>
                <a href="<?= base_url('penalties/pay/' . $violation['id']) ?>" class="btn btn-success btn-sm shadow-sm">
                    <i class="bi bi-cash-coin me-1"></i> Record Payment
                </a>
                <button type="button" class="btn btn-outline-danger btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="bi bi-x-circle me-1"></i> Cancel Violation
                </button>
            <?php endif; ?>
            <button onclick="window.print()" class="btn btn-primary btn-sm shadow-sm">
                <i class="bi bi-printer me-1"></i> Print Ticket
            </button>
        </div>
    </div>
</div>

<!-- TCT Official Document Wrapper -->
<div class="container-fluid pb-4">
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
                <div class="d-flex flex-1 align-items-end">
                    <span class="tct-bold-label me-1">MTOP#</span>
                    <span class="tct-field-underline flex-1"><?= esc($violation['mtop_number'] ?? '—') ?></span>
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

<!-- Cancel Modal -->
<?php if (($violation['status'] ?? '') === 'Pending'): ?>
<div class="modal fade no-print" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Cancel Violation Ticket</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('penalties/cancel/' . $violation['id']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="fw-bold">Are you sure you want to cancel this ticket (<?= esc($violation['ticket_id']) ?>)?</p>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Reason for Cancellation</label>
                        <textarea name="reason" class="form-control shadow-sm" rows="3" required placeholder="Enter reason..."></textarea>
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
<?php endif; ?>

<!-- Styles -->
<style>
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

</style>

<?php if ($isPrintMode): ?>
<script>
    window.addEventListener('load', function () {
        // On iOS the dialog only opens from a user tap; the helper shows a
        // "Tap to Print" button there instead of failing silently.
        if (window.TrafficPrint) { window.TrafficPrint.autoOrPrompt(); } else { window.print(); }
    });
</script>
<?php endif; ?>

<?= $this->endSection() ?>
