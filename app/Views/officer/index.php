<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Violation - Traffic Officer<?= $this->endSection() ?>

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

    <!-- Record Violation Modal -->
    <div class="modal fade" id="recordViolationModal" tabindex="-1" aria-labelledby="recordViolationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold" id="recordViolationModalLabel"><i class="bi bi-file-earmark-text me-2"></i>Record New Violation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-md-4" style="background-color: #f8f9fa;">
                    <form action="<?= base_url('officer/store') ?>" method="POST" id="violationForm">
                        <?= csrf_field() ?>
                        
                        <!-- Physical Ticket Container -->
                        <div class="tct-modal-card">

                            <!-- Header -->
                            <div class="tct-modal-header">
                                <div class="tct-modal-seal">
                                    <img src="<?= base_url('img/pic-1.png') ?>" alt="Kabankalan Logo" class="tct-seal-img">
                                </div>
                                <div class="tct-header-text">
                                    <div class="tct-rep">Republic of the Philippines</div>
                                    <div class="tct-office">OFFICE OF THE CITY MAYOR</div>
                                    <div class="tct-city">Kabankalan City</div>
                                    <h4 class="tct-doc-title">TRAFFIC CITATION TICKET (TCT)</h4>
                                    <div class="tct-ord-no">C.O. # 2023-006</div>
                                </div>
                            </div>

                            <!-- Section 1: Driver & Vehicle Details -->
                            <div class="tct-doc-section">
                                <div class="row align-items-center mb-2">
                                    <div class="col-6">
                                        <span class="tct-bold-label">TO:</span>
                                    </div>
                                    <div class="col-6 text-end">
                                        <div class="d-inline-flex align-items-center">
                                            <span class="tct-bold-label text-danger me-1">TCT No.</span>
                                            <input type="text" name="custom_ticket_no" id="custom_ticket_no" class="form-control form-control-sm tct-input text-danger fw-bold font-monospace"
                                                   style="width: 140px; font-size: 0.9rem;" placeholder="2024-0030268" value="<?= old('custom_ticket_no') ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2 align-items-end">
                                    <div class="col-12 col-md-auto">
                                        <span class="tct-bold-label">Driver's Name <span class="text-danger">*</span></span>
                                    </div>
                                    <div class="col-md">
                                        <div class="row g-2">
                                            <div class="col-4">
                                                <input type="text" name="first_name" id="first_name" class="form-control form-control-sm tct-input"
                                                       placeholder="First" required pattern="[A-Za-z\s\-']{1,100}"
                                                       value="<?= old('first_name') ?>">
                                                <span class="tct-sub-label d-block text-center">(first)</span>
                                            </div>
                                            <div class="col-4">
                                                <input type="text" name="middle_name" id="middle_name" class="form-control form-control-sm tct-input"
                                                       placeholder="Middle" pattern="[A-Za-z\s\-']{0,100}"
                                                       value="<?= old('middle_name') ?>">
                                                <span class="tct-sub-label d-block text-center">(middle)</span>
                                            </div>
                                            <div class="col-4">
                                                <input type="text" name="last_name" id="last_name" class="form-control form-control-sm tct-input"
                                                       placeholder="Last" required pattern="[A-Za-z\s\-']{1,100}"
                                                       value="<?= old('last_name') ?>">
                                                <span class="tct-sub-label d-block text-center">(last)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2 align-items-center">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center">
                                            <span class="tct-bold-label me-2">Address <span class="text-danger">*</span>:</span>
                                            <input type="text" name="address" id="address" class="form-control form-control-sm tct-input flex-1"
                                                   required value="<?= old('address') ?>" placeholder="Driver address">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <span class="tct-bold-label me-2">Age <span class="text-danger">*</span>:</span>
                                            <input type="number" name="age" id="age" class="form-control form-control-sm tct-input flex-1"
                                                   min="16" max="120" required value="<?= old('age') ?>" placeholder="Age">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2 align-items-center">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <span class="tct-bold-label me-1">DL/Permit No.:</span>
                                            <input type="text" name="license_number" id="license_number" class="form-control form-control-sm tct-input flex-1"
                                                   maxlength="50" value="<?= old('license_number') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <span class="tct-bold-label me-1">Plate# <span class="text-danger">*</span>:</span>
                                            <input type="text" name="license_plate" id="license_plate" class="form-control form-control-sm tct-input flex-1"
                                                   required maxlength="20" value="<?= old('license_plate') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <span class="tct-bold-label me-1">MTOP#:</span>
                                            <input type="text" name="mtop_number" id="mtop_number" class="form-control form-control-sm tct-input flex-1"
                                                   value="<?= old('mtop_number') ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 align-items-center">
                                    <div class="col-12">
                                        <div class="d-flex align-items-center">
                                            <span class="tct-bold-label me-2">Owner:</span>
                                            <input type="text" name="owner_name" id="owner_name" class="form-control form-control-sm tct-input flex-1"
                                                   value="<?= old('owner_name') ?>" placeholder="Vehicle Owner Name">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Violations Checklist -->
                            <div class="tct-doc-section">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="tct-sec-heading text-center flex-1">VIOLATIONS</div>
                                    <button type="button" class="btn btn-xs btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addViolationTypeModal">
                                        <i class="bi bi-plus-circle me-1"></i> New Type
                                    </button>
                                </div>
                                <div class="tct-sec-notice text-center mb-3">
                                    You are hereby charged/cited for comitting the violations marked "x" hereunder:
                                </div>

                                <div class="tct-checklist" id="violationChecklist">
                                    <?php foreach ($violationTypes as $type): ?>
                                        <?php $isChecked = in_array((string)$type['id'], (array)old('violation_type_id', []), true); ?>
                                        <label class="tct-check-item">
                                            <input type="checkbox" name="violation_type_id[]" class="violation-check"
                                                   value="<?= $type['id'] ?>"
                                                   data-amount="<?= $type['fine_amount'] ?>"
                                                   data-points="<?= $type['points'] ?>"
                                                   <?= $isChecked ? 'checked' : '' ?>>
                                            <span><?= esc($type['violation_name']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <div class="mt-3 p-2 bg-light border rounded d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="tct-bold-label me-2">Penalty Amount:</span>
                                        <span class="fw-bold text-danger fs-5">₱<span id="penalty_amount_display">0.00</span></span>
                                        <input type="hidden" id="penalty_amount" value="0.00">
                                    </div>
                                    <div class="text-muted small" id="violationInfo">Select violation(s) above</div>
                                </div>
                            </div>

                            <!-- Section 3: Place, Date & Legal Notice -->
                            <div class="tct-doc-section">
                                <div class="row g-2 mb-3">
                                    <div class="col-md-7">
                                        <div class="d-flex align-items-center">
                                            <span class="tct-bold-label me-2">Place <span class="text-danger">*</span>:</span>
                                            <input type="text" name="location" id="location" class="form-control form-control-sm tct-input flex-1"
                                                   required list="locationOptions" placeholder="e.g. Ceres Terminal, Biyarin, or specific address..."
                                                   value="<?= esc(old('location')) ?>">
                                            <datalist id="locationOptions">
                                                <option value="Ceres Terminal, Kabankalan City">
                                                <option value="Biyarin, Kabankalan City">
                                                <?php
                                                $barangays = [
                                                    'Barangay 1 (Poblacion)', 'Barangay 2 (Poblacion)', 'Barangay 3 (Poblacion)', 'Barangay 4 (Poblacion)',
                                                    'Barangay 5 (Poblacion)', 'Barangay 6 (Poblacion)', 'Barangay 7 (Poblacion)', 'Barangay 8 (Poblacion)',
                                                    'Barangay 9 (Poblacion)', 'Bantayan', 'Binicuil', 'Camansi', 'Camingawan', 'Camugao', 'Carol-an',
                                                    'Daan Banua', 'Hilamonan', 'Inapoy', 'Linao', 'Locotan', 'Magballo', 'Oringao', 'Orong',
                                                    'Pinaguinpinan', 'Salong', 'Tabugon', 'Tagoc', 'Tagukon', 'Talubangi', 'Tampalon', 'Tan-Awan', 'Tapi'
                                                ];
                                                foreach ($barangays as $brgy):
                                                ?>
                                                <option value="<?= esc($brgy) ?>, Kabankalan City">
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="d-flex align-items-center">
                                            <span class="tct-bold-label me-2">Date &amp; Time:</span>
                                            <input type="datetime-local" name="violation_datetime" id="violation_datetime"
                                                   class="form-control form-control-sm tct-input flex-1"
                                                   value="<?= old('violation_datetime') ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="tct-legal-text">
                                    You are likewise directed to pay before the Office of the City Treasurer of Kabankalan City within three (3) days from the date of this citation and to secure clearance from the CTRAMO for disposition, Failure on your part to comply will constrain the Office to file the appropriate criminal action against you with the proper court.
                                </div>
                            </div>

                            <!-- Section 4: Acknowledgment, Notes & Signatures -->
                            <div class="tct-doc-section border-bottom-0 pb-0">
                                <div class="tct-ack-heading mb-2">I HEREBY ACKNOWLEDGE RECEIPT OF THIS TCT.</div>
                                <div class="d-flex gap-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="acknowledgment" id="ackAdmitted" value="Admitted" <?= old('acknowledgment') === 'Admitted' ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold" for="ackAdmitted">Admitted</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="acknowledgment" id="ackProtest" value="Under Protest" <?= old('acknowledgment') === 'Under Protest' ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold" for="ackProtest">Under Protest</label>
                                    </div>
                                </div>

                                <!-- Mobile Touch Signature Pads -->
                                <div class="row g-3 my-2">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="tct-bold-label mb-0" style="font-size: 0.82rem;">
                                                <i class="bi bi-fingerprint text-primary me-1"></i>Driver Signature (Touch/Finger Pad)
                                            </label>
                                            <button type="button" class="btn btn-link text-danger p-0" id="clearDriverSigBtn" style="font-size: 0.75rem; text-decoration: none;">Clear</button>
                                        </div>
                                        <div class="position-relative">
                                            <canvas id="driverSigCanvas" width="320" height="90" style="border: 1.5px solid #000; background: #fff; width: 100%; height: 90px; touch-action: none; cursor: crosshair; border-radius: 4px;"></canvas>
                                        </div>
                                        <input type="hidden" name="driver_signature" id="driver_signature">
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="tct-bold-label mb-0" style="font-size: 0.82rem;">
                                                <i class="bi bi-pen text-primary me-1"></i>Officer Signature (Touch/Finger Pad)
                                            </label>
                                            <button type="button" class="btn btn-link text-danger p-0" id="clearOfficerSigBtn" style="font-size: 0.75rem; text-decoration: none;">Clear</button>
                                        </div>
                                        <div class="position-relative">
                                            <canvas id="officerSigCanvas" width="320" height="90" style="border: 1.5px solid #000; background: #fff; width: 100%; height: 90px; touch-action: none; cursor: crosshair; border-radius: 4px;"></canvas>
                                        </div>
                                        <input type="hidden" name="officer_signature" id="officer_signature">
                                    </div>
                                </div>

                                <div class="mt-3 mb-2">
                                    <label for="notes" class="tct-bold-label mb-1">Notes / Remarks:</label>
                                    <textarea name="notes" id="notes" class="form-control form-control-sm" rows="2" placeholder="Optional remarks"><?= old('notes') ?></textarea>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-3">
                                <span class="tct-driver-copy-tag text-muted">Driver's Copy &bull; Ticket Creation</span>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm px-4" id="submitPrintBtn" name="print_ticket" value="1">
                                        <i class="bi bi-printer me-1"></i> Save &amp; Print Ticket
                                    </button>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


<div class="container-fluid pb-4">


    <!-- ═══ CITATION TABLE CARD ═══════════════════════════════════ -->
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm main-table-glass-card premium-reveal" style="--reveal-delay:.3s;">
                
                <!-- Card Header with Filters & Actions -->
                <div class="card-header py-3 px-4 main-table-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    
                    <!-- Left: Title & Filter Chips -->
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="fw-bold text-dark me-2" style="font-size: 0.95rem;">
                            <i class="bi bi-list-check me-1 text-primary"></i>My Recorded Violations
                        </span>
                        <div class="vr mx-1 d-none d-md-block" style="height: 20px;"></div>
                        <button type="button" class="btn btn-sm btn-filter-chip active" data-status="">
                            All <span class="badge bg-white text-dark ms-1 rounded-pill"><?= $totalTickets ?? count($violations ?? []) ?></span>
                        </button>
                        <button type="button" class="btn btn-sm btn-filter-chip" data-status="pending">
                            Pending <span class="badge bg-warning text-dark ms-1 rounded-pill"><?= $pendingCount ?? 0 ?></span>
                        </button>
                        <button type="button" class="btn btn-sm btn-filter-chip" data-status="paid">
                            Paid <span class="badge bg-success text-white ms-1 rounded-pill"><?= $paidCount ?? 0 ?></span>
                        </button>
                    </div>

                    <!-- Right: Search & Record Violation Button -->
                    <div class="d-flex gap-2 flex-wrap align-items-center ms-auto">
                        <div class="modern-search-box position-relative">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" id="tableSearchInput" class="form-control form-control-sm modern-search-input" placeholder="Search ticket, driver, plate #...">
                            <button type="button" class="btn btn-sm btn-clear-search d-none" id="btnClearSearch">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#recordViolationModal">
                            <i class="bi bi-plus-circle-fill me-1"></i> Record Violation
                        </button>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-premium-mobile officer-violations-table" id="officerViolationTable">
                            <thead>
                                <tr>
                                    <th class="ps-4" style="width: 145px; min-width: 135px;">Ticket ID</th>
                                    <th style="width: 220px; min-width: 200px;">Driver & Vehicle</th>
                                    <th style="min-width: 220px;">Violations Infraction</th>
                                    <th style="width: 120px; min-width: 110px;">Breakdown</th>
                                    <th class="text-end" style="width: 125px; min-width: 115px;">Total Fine</th>
                                    <th class="text-center" style="width: 125px; min-width: 115px;">Status</th>
                                    <th style="width: 130px; min-width: 120px;">Date Issued</th>
                                    <th class="text-end pe-4 col-actions" style="width: 135px; min-width: 130px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($violations)): ?>
                                    <tr id="noTableDataRow">
                                        <td colspan="8">
                                            <div class="empty-state py-5 text-center">
                                                <div class="empty-state-icon-wrap mb-3 mx-auto">
                                                    <i class="bi bi-ticket-perforated fs-1 text-primary"></i>
                                                </div>
                                                <h6 class="fw-bold text-dark">No Violations Found</h6>
                                                <p class="text-muted small mb-3">You have not recorded any traffic citations yet.</p>
                                                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#recordViolationModal">
                                                    <i class="bi bi-plus-circle me-1"></i> Record First Violation
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($violations as $v): ?>
                                    <?php
                                        $rowStatusClass = match(strtolower($v['status'] ?? '')) {
                                            'paid'      => 'row-paid',
                                            'cancelled' => 'row-cancelled',
                                            default     => 'row-pending',
                                        };
                                    ?>
                                    <tr class="officer-violation-row <?= $rowStatusClass ?>">
                                        <!-- Ticket ID with 1-click copy -->
                                        <td class="ps-4" data-label="Ticket ID">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <span class="ticket-id-pill font-monospace" title="Click to copy" onclick="copyTicketId('<?= esc($v['ticket_id'] ?? '') ?>', this)">
                                                    <i class="bi bi-ticket-perforated text-primary me-1"></i><?= esc($v['ticket_id'] ?? 'N/A') ?>
                                                    <i class="bi bi-clipboard ms-1 text-muted copy-icon" style="font-size: 0.75rem;"></i>
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Driver Information -->
                                        <td data-label="Driver & Vehicle">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="driver-mini-avatar">
                                                    <?= strtoupper(substr(trim(($v['first_name'] ?? '') ?: ($v['driver_name'] ?? 'D')), 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark" style="font-size: 0.92rem; line-height: 1.2;">
                                                        <?= esc(trim(($v['first_name'] ?? '') . ' ' . ($v['last_name'] ?? '')) ?: ($v['driver_name'] ?? '-')) ?>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                                        <span class="plate-pill font-monospace" title="License Plate">
                                                            <i class="bi bi-car-front-fill me-1"></i><?= esc($v['license_plate'] ?? '-') ?>
                                                        </span>
                                                        <?php if (!empty($v['license_number']) && !in_array(strtolower(trim($v['license_number'])), ['no', 'none', 'n/a', '-'])): ?>
                                                            <span class="dl-pill font-monospace" title="Driver License / Permit">
                                                                DL: <?= esc($v['license_number']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Violation Type -->
                                        <td class="small fw-semibold text-dark" data-label="Violations Infraction">
                                            <?php 
                                                $vTypes = explode('||', $v['concatenated_violations'] ?? $v['violation_type'] ?? '-');
                                                echo '<div class="d-flex flex-column gap-1">';
                                                foreach ($vTypes as $vt) {
                                                    $parts = explode('::', $vt);
                                                    $vName = $parts[0];
                                                    echo '<div class="violation-chip"><i class="bi bi-exclamation-circle-fill text-danger me-1"></i>' . esc($vName) . '</div>';
                                                }
                                                echo '</div>';
                                            ?>
                                        </td>

                                        <!-- Amount Breakdown -->
                                        <td class="small font-monospace text-muted" data-label="Breakdown">
                                            <?php 
                                                $vTypes = explode('||', $v['concatenated_violations'] ?? $v['violation_type'] ?? '-');
                                                echo '<div class="d-flex flex-column gap-1">';
                                                foreach ($vTypes as $vt) {
                                                    $parts = explode('::', $vt);
                                                    $vAmt  = isset($parts[1]) && $parts[1] !== '' ? (float)$parts[1] : null;
                                                    echo '<div class="text-secondary">';
                                                    if ($vAmt !== null) {
                                                        echo '₱' . number_format($vAmt, 2);
                                                    } else {
                                                        echo '-';
                                                    }
                                                    echo '</div>';
                                                }
                                                echo '</div>';
                                            ?>
                                        </td>

                                        <!-- Total Fine -->
                                        <td class="text-end" data-label="Total Fine">
                                            <?php $isPaid = strtolower($v['status'] ?? '') === 'paid'; ?>
                                            <span class="total-fine-badge font-monospace <?= $isPaid ? 'total-fine-paid' : 'total-fine-pending' ?>">
                                                ₱<?= number_format((float) ($v['total_penalty_sum'] ?? $v['penalty_amount'] ?? 0), 2) ?>
                                            </span>
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="text-center" data-label="Status">
                                            <?php if (($v['status'] ?? '') === 'Pending'): ?>
                                                <span class="status-glow-badge status-pending">
                                                    <span class="status-dot"></span> Pending
                                                </span>
                                            <?php elseif (($v['status'] ?? '') === 'Paid'): ?>
                                                <span class="status-glow-badge status-paid">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Paid
                                                </span>
                                            <?php else: ?>
                                                <span class="status-glow-badge status-cancelled">
                                                    <i class="bi bi-x-circle me-1"></i> Cancelled
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Date Issued -->
                                        <td class="text-muted small" data-label="Date Issued" style="white-space: nowrap;">
                                            <div class="d-flex align-items-center gap-1">
                                                <i class="bi bi-calendar3 text-muted" style="font-size: 0.8rem; flex-shrink: 0;"></i>
                                                <span style="white-space: nowrap;"><?= isset($v['max_violation_date']) ? date('M d, Y', strtotime($v['max_violation_date'])) : (isset($v['violation_date']) ? date('M d, Y', strtotime($v['violation_date'])) : '-') ?></span>
                                            </div>
                                        </td>

                                        <!-- Actions Bar -->
                                        <td class="text-end pe-4 col-actions" data-label="Actions">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <!-- Quick Print Button -->
                                                <button type="button" class="btn btn-sm btn-action-icon btn-action-print" title="Print Citation Ticket (TCT)" onclick="printTicket('<?= base_url('officer/view/' . $v['id'] . '?print=1') ?>')">
                                                    <i class="bi bi-printer"></i>
                                                </button>

                                                <!-- Quick View Button -->
                                                <a href="<?= base_url('officer/view/' . $v['id']) ?>" class="btn btn-sm btn-action-icon btn-action-view" title="View Detailed Record">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                <!-- Dropdown Menu for Management Actions -->
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-action-icon btn-action-more dropdown-toggle no-caret" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false" title="More Management Actions">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 py-1">
                                                        <?php if (($v['status'] ?? '') === 'Pending'): ?>
                                                            <li>
                                                                <button type="button" class="dropdown-item py-2 btn-edit-violation text-dark"
                                                                    data-id="<?= $v['id'] ?>"
                                                                    data-ticket-id="<?= esc($v['ticket_id'] ?? '') ?>"
                                                                    data-first-name="<?= esc($v['first_name'] ?? '') ?>"
                                                                    data-last-name="<?= esc($v['last_name'] ?? '') ?>"
                                                                    data-middle-name="<?= esc($v['middle_name'] ?? '') ?>"
                                                                    data-age="<?= esc((string) ($v['age'] ?? '')) ?>"
                                                                    data-address="<?= esc($v['address'] ?? '') ?>"
                                                                    data-license-plate="<?= esc($v['license_plate'] ?? '') ?>"
                                                                    data-license-number="<?= esc($v['license_number'] ?? '') ?>"
                                                                    data-mtop-number="<?= esc($v['mtop_number'] ?? '') ?>"
                                                                    data-owner-name="<?= esc($v['owner_name'] ?? '') ?>"
                                                                    data-acknowledgment="<?= esc($v['acknowledgment'] ?? '') ?>"
                                                                    data-violation-type-id="<?= esc((string) ($v['violation_type_id'] ?? '')) ?>"
                                                                    data-location="<?= esc($v['location'] ?? '') ?>"
                                                                    data-notes="<?= esc($v['notes'] ?? '') ?>">
                                                                    <i class="bi bi-pencil me-2 text-warning"></i> Edit Citation
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button type="button" class="dropdown-item py-2 btn-cancel-violation text-danger" data-id="<?= $v['id'] ?>">
                                                                    <i class="bi bi-x-circle me-2 text-danger"></i> Cancel Citation
                                                                </button>
                                                            </li>
                                                            <li><hr class="dropdown-divider my-1"></li>
                                                        <?php endif; ?>
                                                        <li>
                                                            <button type="button" class="dropdown-item py-2 btn-delete-violation text-danger" data-id="<?= $v['id'] ?>">
                                                                <i class="bi bi-trash3 me-2"></i> Delete Permanently
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
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
                    <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-center">
                        <?= $pager->links('violations', 'bootstrap_pagination') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

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

<!-- Edit Violation Modal (Formatted matching Physical TCT Citation Ticket) -->
<div class="modal fade" id="editViolationModal" tabindex="-1" aria-labelledby="editViolationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold" id="editViolationModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Update Citation Ticket (TCT)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4" style="background-color: #f8f9fa;">
                <form id="editViolationForm" method="POST">
                    <?= csrf_field() ?>

                    <!-- Physical Ticket Container -->
                    <div class="tct-modal-card">

                        <!-- Header -->
                        <div class="tct-modal-header">
                            <div class="tct-modal-seal">
                                <img src="<?= base_url('img/pic-1.png') ?>" alt="Kabankalan Logo" class="tct-seal-img">
                            </div>
                            <div class="tct-header-text">
                                <div class="tct-rep">Republic of the Philippines</div>
                                <div class="tct-office">OFFICE OF THE CITY MAYOR</div>
                                <div class="tct-city">Kabankalan City</div>
                                <h4 class="tct-doc-title">TRAFFIC CITATION TICKET (TCT)</h4>
                                <div class="tct-ord-no">C.O. # 2023-006</div>
                            </div>
                        </div>

                        <!-- Section 1: Driver & Vehicle Details -->
                        <div class="tct-doc-section">
                            <div class="row align-items-center mb-2">
                                <div class="col-6">
                                    <span class="tct-bold-label">TO:</span>
                                </div>
                                <div class="col-6 text-end">
                                    <div class="d-inline-flex align-items-center">
                                        <span class="tct-bold-label text-danger me-1">TCT No.</span>
                                        <span id="edit_ticket_no_display" class="font-monospace text-danger fw-bold fs-6"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-2 align-items-end">
                                <div class="col-12 col-md-auto">
                                    <span class="tct-bold-label">Driver's Name <span class="text-danger">*</span></span>
                                </div>
                                <div class="col-md">
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <input type="text" name="first_name" id="edit_first_name" class="form-control form-control-sm tct-input"
                                                   placeholder="First" required pattern="[A-Za-z\s\-']{1,100}">
                                            <span class="tct-sub-label d-block text-center">(first)</span>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" name="middle_name" id="edit_middle_name" class="form-control form-control-sm tct-input"
                                                   placeholder="Middle" pattern="[A-Za-z\s\-']{0,100}">
                                            <span class="tct-sub-label d-block text-center">(middle)</span>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" name="last_name" id="edit_last_name" class="form-control form-control-sm tct-input"
                                                   placeholder="Last" required pattern="[A-Za-z\s\-']{1,100}">
                                            <span class="tct-sub-label d-block text-center">(last)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center">
                                        <span class="tct-bold-label me-2">Address <span class="text-danger">*</span>:</span>
                                        <input type="text" name="address" id="edit_address" class="form-control form-control-sm tct-input flex-1"
                                               required placeholder="Driver address">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <span class="tct-bold-label me-2">Age <span class="text-danger">*</span>:</span>
                                        <input type="number" name="age" id="edit_age" class="form-control form-control-sm tct-input flex-1"
                                               min="16" max="120" required placeholder="Age">
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <span class="tct-bold-label me-1">DL/Permit No.:</span>
                                        <input type="text" name="license_number" id="edit_license_number" class="form-control form-control-sm tct-input flex-1"
                                               maxlength="50" placeholder="License No">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <span class="tct-bold-label me-1">Plate# <span class="text-danger">*</span>:</span>
                                        <input type="text" name="license_plate" id="edit_license_plate" class="form-control form-control-sm tct-input flex-1"
                                               required maxlength="20" placeholder="Plate Number">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <span class="tct-bold-label me-1">MTOP#:</span>
                                        <input type="text" name="mtop_number" id="edit_mtop_number" class="form-control form-control-sm tct-input flex-1"
                                               placeholder="MTOP #">
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 align-items-center">
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <span class="tct-bold-label me-2">Owner:</span>
                                        <input type="text" name="owner_name" id="edit_owner_name" class="form-control form-control-sm tct-input flex-1"
                                               placeholder="Vehicle Owner Name">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Violations Details -->
                        <div class="tct-doc-section">
                            <div class="tct-sec-heading text-center mb-1">VIOLATION DETAILS</div>
                            <div class="tct-sec-notice text-center mb-3">
                                You are hereby charged/cited for committing the violation specified hereunder:
                            </div>

                            <div class="row g-3 align-items-center">
                                <div class="col-md-8">
                                    <label class="tct-bold-label mb-1" for="edit_violation_type_id">Violation Type <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm tct-input" name="violation_type_id" id="edit_violation_type_id" required style="border-radius: 0; background-color: #fff;">
                                        <option value="">-- Select Violation Type --</option>
                                        <?php foreach ($violationTypes as $type): ?>
                                            <option value="<?= $type['id'] ?>" data-amount="<?= $type['fine_amount'] ?>" data-points="<?= $type['points'] ?>">
                                                <?= esc($type['violation_name']) ?> - ₱<?= number_format((float) $type['fine_amount'], 2) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="tct-bold-label mb-1">Fine Amount</label>
                                    <div class="p-2 bg-light border rounded d-flex align-items-center justify-content-between">
                                        <span class="small text-muted">Amount:</span>
                                        <span class="fw-bold text-danger fs-5">₱<span id="edit_penalty_amount_display">0.00</span></span>
                                    </div>
                                    <input type="hidden" id="edit_penalty_amount" value="0.00">
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Place & Legal Notice -->
                        <div class="tct-doc-section">
                            <div class="row g-2 mb-3">
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center">
                                        <span class="tct-bold-label me-2">Place <span class="text-danger">*</span>:</span>
                                        <input type="text" name="location" id="edit_location" class="form-control form-control-sm tct-input flex-1"
                                               required list="locationOptions" placeholder="e.g. Ceres Terminal, Biyarin, or specific address...">
                                    </div>
                                </div>
                            </div>

                            <div class="tct-legal-text">
                                You are likewise directed to pay before the Office of the City Treasurer of Kabankalan City within three (3) days from the date of this citation and to secure clearance from the CTRAMO for disposition, Failure on your part to comply will constrain the Office to file the appropriate criminal action against you with the proper court.
                            </div>
                        </div>

                        <!-- Section 4: Acknowledgment & Notes -->
                        <div class="tct-doc-section border-bottom-0 pb-0">
                            <div class="tct-ack-heading mb-2">I HEREBY ACKNOWLEDGE RECEIPT OF THIS TCT.</div>
                            <div class="d-flex gap-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="acknowledgment" id="edit_ackAdmitted" value="Admitted">
                                    <label class="form-check-label fw-semibold" for="edit_ackAdmitted">Admitted</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="acknowledgment" id="edit_ackProtest" value="Under Protest">
                                    <label class="form-check-label fw-semibold" for="edit_ackProtest">Under Protest</label>
                                </div>
                            </div>

                            <div class="mt-3 mb-2">
                                <label for="edit_notes" class="tct-bold-label mb-1">Notes / Remarks:</label>
                                <textarea name="notes" id="edit_notes" class="form-control form-control-sm" rows="2" placeholder="Optional remarks"></textarea>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-3">
                            <span class="tct-driver-copy-tag text-muted">Driver's Copy &bull; Ticket Amendment</span>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm px-4">
                                    <i class="bi bi-check-lg me-1"></i> Save Changes
                                </button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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

<!-- Add Violation Type Modal -->
<div class="modal fade" id="addViolationTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Violation Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="vtError" class="alert alert-danger d-none"></div>

                <div class="mb-3">
                    <label class="form-label">Violation Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="vtName" placeholder="e.g., No Helmet" />
                </div>
                <div class="mb-3">
                    <label class="form-label">Fine Amount <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="vtFine" step="0.01" min="0" placeholder="0.00" />
                </div>
                <div class="mb-0">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="vtDesc" rows="3" placeholder="Optional details"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveViolationTypeBtn">
                    <span class="spinner-border spinner-border-sm me-2 d-none" id="vtSpinner"></span>
                    Save
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* ── DASHBOARD HERO BANNER ─────────────────────────────────── */
    .officer-hero-banner {
        position: relative;
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #2563eb 100%);
        border-radius: 20px;
        padding: 1.75rem 2rem;
        overflow: hidden;
        box-shadow: 0 12px 32px rgba(30, 58, 138, 0.25);
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
    .orb-2 { width: 150px; height: 150px; background: #38bdf8; bottom: -50px; right: 25%; }
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
        background: #f8faff;
        color: #2563eb;
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
        background: #f59e0b;
        box-shadow: 0 0 6px rgba(245, 158, 11, 0.8);
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
        background: #f8faff;
        border-bottom: 1px solid #e2e8f0;
    }
    .btn-filter-chip {
        background: #fff;
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
        background: #f8faff;
        border-color: #cbd5e1;
        color: #1e293b;
        transform: translateY(-1px);
    }
    .btn-filter-chip.active {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border-color: #1d4ed8;
        color: #fff;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.28);
    }
    .btn-filter-chip.active .badge {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #fff !important;
    }

    /* ── SEARCH BOX ────────────────────────────────────────────── */
    .modern-search-box {
        min-width: 260px;
    }
    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.85rem;
        pointer-events: none;
    }
    .modern-search-input {
        padding-left: 34px !important;
        padding-right: 30px !important;
        border-radius: 20px !important;
        border: 1px solid #cbd5e1 !important;
        background: #fff !important;
        font-size: 0.85rem !important;
        height: 36px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .modern-search-input:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18) !important;
    }
    .btn-clear-search {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        padding: 0;
        border: none;
        background: transparent;
        font-size: 0.85rem;
    }
    .btn-clear-search:hover { color: #ef4444; }

    /* ── TABLE ROW & CELL BADGES (SMOOTH & RESPONSIVE DESIGN) ── */
    .officer-violations-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    .officer-violations-table thead th {
        background: #1e293b;
        color: #e2e8f0;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 0.9rem 1rem;
        border-bottom: 2px solid #334155;
        white-space: nowrap;
    }
    .officer-violations-table thead th:first-child { padding-left: 1.5rem; }
    .officer-violations-table thead th:last-child  { padding-right: 1.5rem; }
    .officer-violations-table tbody td {
        padding: 1rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.88rem;
        vertical-align: middle;
        background: inherit;
        transition: background 0.18s ease;
    }
    /* ── STATUS BARS AT REST (inset — vivid, works on any border-collapse) ── */
    .officer-violations-table tbody tr {
        transition: background  0.28s cubic-bezier(0.34,1.56,0.64,1),
                    box-shadow  0.28s cubic-bezier(0.34,1.56,0.64,1),
                    transform   0.28s cubic-bezier(0.34,1.56,0.64,1);
        position: relative;
    }
    .officer-violations-table tbody tr.row-pending {
        background: rgba(254,252,232,0.5);
        box-shadow: inset 5px 0 0 #f59e0b, inset 8px 0 14px rgba(245,158,11,0.09);
    }
    .officer-violations-table tbody tr.row-paid {
        background: rgba(240,253,248,0.6);
        box-shadow: inset 5px 0 0 #10b981, inset 8px 0 14px rgba(16,185,129,0.09);
    }
    .officer-violations-table tbody tr.row-cancelled {
        background: rgba(248,250,252,0.7);
        box-shadow: inset 5px 0 0 #94a3b8, inset 8px 0 14px rgba(148,163,184,0.09);
    }
    /* ── HOVER: vivid glow + spring slide ── */
    .officer-violations-table tbody tr.row-pending:hover {
        background: #fffbeb !important;
        box-shadow: inset 6px 0 0 #f59e0b,
                    inset 12px 0 22px rgba(245,158,11,0.15),
                    0 4px 24px rgba(245,158,11,0.13);
        transform: translateX(4px);
    }
    .officer-violations-table tbody tr.row-paid:hover {
        background: #ecfdf5 !important;
        box-shadow: inset 6px 0 0 #10b981,
                    inset 12px 0 22px rgba(16,185,129,0.15),
                    0 4px 24px rgba(16,185,129,0.12);
        transform: translateX(4px);
    }
    .officer-violations-table tbody tr.row-cancelled:hover {
        background: #f8fafc !important;
        box-shadow: inset 6px 0 0 #94a3b8,
                    inset 12px 0 22px rgba(148,163,184,0.15),
                    0 4px 24px rgba(148,163,184,0.10);
        transform: translateX(4px);
    }
    /* Responsive: mobile card layout */
    @media (max-width: 767px) {
        .officer-violations-table thead { display: none; }
        .officer-violations-table tbody tr {
            display: block;
            margin-bottom: 0.75rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            border-left-width: 4px !important;
            box-shadow: 0 2px 8px rgba(15,23,42,0.05);
            transform: none !important;
        }
        .officer-violations-table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.55rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.83rem;
        }
        .officer-violations-table tbody td:last-child { border-bottom: none; }
        .officer-violations-table tbody td::before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            flex-shrink: 0;
            margin-right: 0.75rem;
            margin-top: 2px;
        }
        .col-actions { justify-content: flex-end; }
    }

    /* Ticket ID Glass Pill */
    .ticket-id-pill {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
        padding: 0.28rem 0.65rem;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        letter-spacing: 0.02em;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        user-select: none;
    }
    .ticket-id-pill:hover {
        background: #dbeafe;
        border-color: #93c5fd;
        color: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.15);
    }
    .ticket-id-pill.copied {
        background: #ecfdf5 !important;
        border-color: #86efac !important;
        color: #059669 !important;
        box-shadow: 0 0 10px rgba(16, 185, 129, 0.25) !important;
    }

    /* Driver Avatar with Modern Multi-stop Gradient */
    .driver-mini-avatar {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: linear-gradient(135deg, #3b82f6 0%, #6366f1 50%, #8b5cf6 100%);
        color: #fff;
        font-size: 0.84rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25);
        transition: transform 0.2s ease;
    }
    .officer-violation-row:hover .driver-mini-avatar {
        transform: scale(1.05);
    }

    /* Vehicle Plate & DL Pills */
    .plate-pill {
        background: #0f172a;
        color: #f8fafc;
        border: 1px solid #334155;
        border-radius: 6px;
        padding: 0.18rem 0.55rem;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
        transition: all 0.2s ease;
    }
    .dl-pill {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
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
        word-break: break-word;
        transition: all 0.18s ease;
    }
    .officer-violation-row:hover .violation-chip {
        background: #ffffff;
        border-color: #cbd5e1;
    }

    /* Total Fine Badge (Dynamic Status Responsive) */
    .total-fine-badge {
        font-size: 0.95rem;
        font-weight: 800;
        letter-spacing: -0.01em;
        padding: 0.25rem 0.6rem;
        border-radius: 8px;
        display: inline-block;
        transition: all 0.2s ease;
    }
    .total-fine-pending {
        color: #92400e;
        background: rgba(245,158,11,0.10);
        border: 1.5px solid rgba(245,158,11,0.30);
    }
    .total-fine-paid {
        color: #047857;
        background: rgba(16,185,129,0.09);
        border: 1.5px solid rgba(16,185,129,0.28);
    }
    .officer-violation-row:hover .total-fine-badge {
        transform: scale(1.02);
    }

    /* Status Badges with Subtle Translucent Glow */
    .status-glow-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.38rem 0.85rem;
        border-radius: 20px;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .status-pending {
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
        border: 1px solid rgba(245, 158, 11, 0.32);
    }
    .status-pending .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #f59e0b;
        margin-right: 6px;
        box-shadow: 0 0 8px rgba(245, 158, 11, 0.7);
        animation: pulseAmber 1.8s infinite;
    }
    .status-paid {
        background: rgba(16, 185, 129, 0.12);
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.32);
    }
    .status-cancelled {
        background: rgba(239, 68, 68, 0.10);
        color: #b91c1c;
        border: 1px solid rgba(239, 68, 68, 0.28);
    }
    .officer-violation-row:hover .status-glow-badge {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.06);
    }

    /* Smooth Action Icon Buttons */
    .btn-action-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #64748b;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        padding: 0;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .btn-action-icon:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 12px rgba(15, 23, 42, 0.10);
    }
    .btn-action-print:hover {
        background: #ecfdf5;
        border-color: #86efac;
        color: #059669;
    }
    .btn-action-view:hover {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #2563eb;
    }
    .btn-action-more:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a;
    }

    /* Dropdown Menu Polish */
    .dropdown-menu {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12) !important;
        padding: 0.4rem !important;
    }
    .dropdown-item {
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 500;
        padding: 0.45rem 0.85rem;
        transition: all 0.15s ease;
    }
    .dropdown-item:hover {
        background-color: #f1f5f9;
    }
    .dropdown-item.btn-cancel-violation:hover,
    .dropdown-item.btn-delete-violation:hover {
        background-color: #fef2f2;
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
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border-color: #1d4ed8;
        color: #fff;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
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
        background: #94a3b8;
    }

    /* ── DARK THEME ADAPTATIONS ────────────────────────────────── */
    [data-bs-theme="dark"] .stat-glass-card,
    [data-bs-theme="dark"] .main-table-glass-card {
        background: #111b2f;
        border-color: #1e293b;
    }
    [data-bs-theme="dark"] .stat-counter-value { color: #f8fafc; }
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
    [data-bs-theme="dark"] .btn-action-icon { background: #1e293b; border-color: #334155; color: #94a3b8; }
    [data-bs-theme="dark"] .btn-action-more:hover { background: #334155; color: #f8fafc; }
    [data-bs-theme="dark"] .modern-search-input { background: #1e293b !important; border-color: #334155 !important; color: #f8fafc !important; }
    [data-bs-theme="dark"] .dropdown-menu { background: #111b2f !important; border-color: #334155 !important; }
    [data-bs-theme="dark"] .dropdown-item { color: #e2e8f0 !important; }
    [data-bs-theme="dark"] .dropdown-item:hover { background: #1e293b !important; }
    [data-bs-theme="dark"] .table-responsive::-webkit-scrollbar-track { background: #0f172a; }
    [data-bs-theme="dark"] .table-responsive::-webkit-scrollbar-thumb { background: #334155; }

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
        border: 1px solid #dbe1ff;
        border-radius: 10px;
        background: #fbfcff;
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
        border: 1px solid #dbe1ff;
        border-radius: 10px;
        cursor: text;
    }
    .pill-select-wrapper:focus-within {
        border-color: #5865f2;
        box-shadow: 0 0 0 0.2rem rgba(88, 101, 242, 0.25);
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
        background: #eef3ff;
        color: #5865f2;
        border: 1px solid #dbe4ff;
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
        border: 1px solid #dbe1ff;
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
        background: #f8faff;
        color: #5865f2;
    }
    .pill-option.selected {
        background: #eef3ff;
        color: #5865f2;
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
        color: #d8e3ff;
    }
    [data-bs-theme="dark"] .pill-dropdown {
        background: #111b2f;
        border-color: #2a3b60;
        box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    }
    [data-bs-theme="dark"] .pill-option:hover, [data-bs-theme="dark"] .pill-option.focused {
        background: #16233f;
        color: #dce7ff;
    }
    [data-bs-theme="dark"] .pill-option.selected {
        background: #1e293b;
        color: #e5edff;
    }
    [data-bs-theme="dark"] .pill-badge {
        background: #1e293b;
        color: #dce7ff;
        border-color: #2e3a59;
    }
    .enforcer-card {
        overflow: hidden;
        border: 1px solid #e4e9ff !important;
        border-radius: 16px;
        background: linear-gradient(165deg, rgba(255,255,255,0.95) 0%, rgba(247,250,255,0.88) 100%);
        transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.3s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .enforcer-header {
        background: linear-gradient(120deg, rgba(97,116,227,0.14) 0%, rgba(255,255,255,0.95) 65%);
        border-bottom: 1px solid #e4e9ff;
    }
    .enforcer-header h4 {
        color: #2a356d;
        font-weight: 700;
    }
    .enforcer-card .form-label {
        color: #34406f;
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
        color: #5e6b98;
        background: #eef3ff;
        border: 1px solid #dce4ff;
        border-radius: 999px;
        padding: 0.35rem 0.75rem;
    }
    .enforcer-card .form-text {
        font-size: 0.76rem;
        color: #7b86ad;
        margin-top: 0.25rem;
    }
    .enforcer-card .input-group-text {
        background: #f7f9ff;
        border-color: #dbe1ff;
        color: #5a68a9;
    }
    .enforcer-card .form-control,
    .enforcer-card .form-select {
        border-color: #dbe1ff;
        border-radius: 10px;
    }
    .enforcer-card .form-control,
    .enforcer-card .form-select,
    .enforcer-card .input-group-text {
        min-height: 42px;
    }
    .enforcer-card .form-control.bg-light {
        background: #f7f9ff !important;
    }
    .info-box {
        background: linear-gradient(120deg, #e7f2ff 0%, #f1f8ff 100%);
        color: #22495f;
        border: 1px solid #d6e8ff;
    }
    .btn-white { background: #fff; }
    .btn-white:hover { background: #f8f9fa; }
    .enforcer-card .table thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #4d5678;
        border-bottom: 1px solid #e8ecff;
    }
    .enforcer-card .table tbody tr:hover {
        background-color: #f8faff;
    }
    .enforcer-card .btn-group .btn {
        border-radius: 8px !important;
    }
    .officer-violations-table {
        min-width: 1040px;
        table-layout: auto;
    }
    .officer-violations-table th,
    .officer-violations-table td {
        vertical-align: middle;
    }
    .officer-violations-table .col-actions {
        width: 135px;
        white-space: nowrap;
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
        background: #94a3b8;
    }
    .enforcer-card .card-header h4,
    .enforcer-card .card-header h5 {
        letter-spacing: -0.01em;
    }
    .enforcer-card .card-header small {
        display: block;
        margin-top: 0.25rem;
        color: #7b86ad !important;
    }
    #violationForm .row.g-4 {
        --bs-gutter-y: 1rem;
    }
    #violationForm hr {
        opacity: 0.12;
    }
    .form-footer {
        background: #f8faff;
        border: 1px solid #e4eaff;
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
    #tableSearchInput:focus {
        box-shadow: none;
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

    /* TCT Modal Document Styling */
    .tct-modal-card {
        background: #fff;
        border: 2px solid #000;
        padding: 20px 24px;
        font-family: 'Times New Roman', Times, serif, Arial, sans-serif;
        color: #000;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .tct-modal-header {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 18px;
        min-height: 80px;
    }
    .tct-modal-seal {
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
    .tct-doc-section {
        border-bottom: 1.5px solid #000;
        padding-bottom: 14px;
        margin-bottom: 14px;
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
    .tct-legal-text {
        font-size: 0.86rem;
        font-style: italic;
        text-align: justify;
        line-height: 1.35;
        border-top: 1px dashed #444;
        padding-top: 8px;
        margin-top: 8px;
    }
    .tct-ack-heading {
        font-size: 0.92rem;
        font-weight: bold;
        font-family: Arial, sans-serif;
    }
    .tct-driver-copy-tag {
        font-size: 0.82rem;
        font-weight: bold;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    .tct-input {
        border: none;
        border-bottom: 1.5px solid #000;
        border-radius: 0;
        padding: 2px 6px;
        background: transparent;
        font-family: Arial, sans-serif;
        font-weight: bold;
        font-size: 0.95rem;
    }
    .tct-input:focus {
        background: #f0f7ff;
        border-bottom-color: #0d6efd;
        box-shadow: none;
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function printTicket(url) {
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
    function updateFineAmount() {
        const checks = document.querySelectorAll('.violation-check:checked');
        let totalAmount = 0;
        let details = [];
        Array.from(checks).forEach(check => {
            totalAmount += parseFloat(check.dataset.amount || 0);
            const label = check.closest('label')?.querySelector('span')?.textContent || 'Violation';
            details.push(`<strong>${label}</strong>`);
        });
        
        const penaltyInput = document.getElementById('penalty_amount');
        if (penaltyInput) penaltyInput.value = totalAmount.toFixed(2);
        
        const disp = document.getElementById('penalty_amount_display');
        if (disp) disp.textContent = totalAmount.toFixed(2);

        const violationInfo = document.getElementById('violationInfo');
        if (violationInfo) {
            if (details.length > 0) {
                violationInfo.innerHTML = `${details.join(', ')}`;
            } else {
                violationInfo.textContent = 'Select violation(s) above';
            }
        }
    }

    // Checkbox violations logic
    document.querySelectorAll('.violation-check').forEach(check => {
        check.addEventListener('change', updateFineAmount);
    });
    updateFineAmount();

    // Signature Pad Initialization (Touch & Mouse friendly for Mobile Phones)
    function setupSignaturePad(canvasId, hiddenInputId, clearBtnId) {
        const canvas = document.getElementById(canvasId);
        const hiddenInput = document.getElementById(hiddenInputId);
        const clearBtn = document.getElementById(clearBtnId);
        if (!canvas || !hiddenInput) return;

        const ctx = canvas.getContext('2d');
        let drawing = false;
        let hasDrawn = false;

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            let clientX = e.clientX;
            let clientY = e.clientY;
            if (e.touches && e.touches.length > 0) {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            }
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function startDrawing(e) {
            e.preventDefault();
            drawing = true;
            hasDrawn = true;
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#000000';
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!drawing) return;
            e.preventDefault();
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }

        function stopDrawing(e) {
            if (!drawing) return;
            drawing = false;
            if (hasDrawn) {
                hiddenInput.value = canvas.toDataURL('image/png');
            }
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDrawing);

        clearBtn?.addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hiddenInput.value = '';
            hasDrawn = false;
        });
    }

    // Initialize Canvas Pads when Modal Opens
    document.getElementById('recordViolationModal')?.addEventListener('shown.bs.modal', function () {
        setupSignaturePad('driverSigCanvas', 'driver_signature', 'clearDriverSigBtn');
        setupSignaturePad('officerSigCanvas', 'officer_signature', 'clearOfficerSigBtn');
    });

    document.getElementById('violationForm').addEventListener('submit', function(e) {
        const hasSelectedViolation = document.querySelectorAll('.violation-check:checked').length > 0;
        if (!hasSelectedViolation) {
            e.preventDefault();
            alert('Please select at least one violation.');
            return;
        }
        const printBtn = document.getElementById('submitPrintBtn');
        printBtn.disabled = true;
        printBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
    });

    const firstNameInput = document.getElementById('first_name');
    const middleNameInput = document.getElementById('middle_name');
    const lastNameInput = document.getElementById('last_name');
    const plateInput = document.getElementById('license_plate');
    const ageInput = document.getElementById('age');

    const sanitizeName = (value) => value.replace(/[^A-Za-z\s\-']/g, '');
    const sanitizePlate = (value) => value.toUpperCase().replace(/[^A-Z0-9\s\-]/g, '').trimStart();

    firstNameInput?.addEventListener('input', function() {
        this.value = sanitizeName(this.value);
    });
    middleNameInput?.addEventListener('input', function() {
        this.value = sanitizeName(this.value);
    });
    lastNameInput?.addEventListener('input', function() {
        this.value = sanitizeName(this.value);
    });
    plateInput?.addEventListener('input', function() {
        this.value = sanitizePlate(this.value);
    });
    ageInput?.addEventListener('input', function() {
        const v = parseInt(this.value || '0', 10);
        if (v > 120) this.value = '120';
        if (v > 0 && v < 16) this.setCustomValidity('Minimum age is 16.');
        else this.setCustomValidity('');
    });

    async function saveViolationType() {
        const btn = document.getElementById('saveViolationTypeBtn');
        const spinner = document.getElementById('vtSpinner');
        const errorBox = document.getElementById('vtError');

        const name = document.getElementById('vtName').value.trim();
        const fine = document.getElementById('vtFine').value;
        const desc = document.getElementById('vtDesc').value.trim();

        errorBox.classList.add('d-none');
        errorBox.innerHTML = '';

        btn.disabled = true;
        spinner.classList.remove('d-none');

        try {
            const res = await fetch('<?= base_url('officer/violation-types/store') ?>', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                    violation_name: name,
                    fine_amount: fine,

                    description: desc,
                })
            });

            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.success) {
                const errors = data.errors ? Object.values(data.errors).join('<br>') : (data.message || 'Failed to save.');
                errorBox.innerHTML = errors;
                errorBox.classList.remove('d-none');
                return;
            }

            const type = data.type;
            const checklist = document.getElementById('violationChecklist');
            if (checklist) {
                const wrap = document.createElement('label');
                wrap.className = 'tct-check-item';
                wrap.innerHTML = `
                    <input type="checkbox" name="violation_type_id[]" class="violation-check"
                           value="${type.id}" data-amount="${type.fine_amount}" checked>
                    <span>${type.violation_name}</span>
                `;
                checklist.appendChild(wrap);
                wrap.querySelector('.violation-check')?.addEventListener('change', updateFineAmount);
                updateFineAmount();
            }

            const editSelect = document.getElementById('edit_violation_type_id');
            if (editSelect) {
                const opt2 = document.createElement('option');
                opt2.value = type.id;
                opt2.dataset.amount = type.fine_amount;

                opt2.textContent = `${type.violation_name} - ${parseFloat(type.fine_amount).toFixed(2)}`;
                editSelect.appendChild(opt2);
            }

            document.getElementById('vtName').value = '';
            document.getElementById('vtFine').value = '';
            document.getElementById('vtDesc').value = '';
            bootstrap.Modal.getInstance(document.getElementById('addViolationTypeModal')).hide();
            bootstrap.Modal.getInstance(document.getElementById('recordViolationModal')).show();
        } finally {
            btn.disabled = false;
            spinner.classList.add('d-none');
        }
    }

    document.getElementById('saveViolationTypeBtn')?.addEventListener('click', saveViolationType);

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
    const filterChips = document.querySelectorAll('.btn-filter-chip');
    let currentStatusFilter = '';

    filterChips.forEach(chip => {
        chip.addEventListener('click', function() {
            filterChips.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            currentStatusFilter = (this.dataset.status || '').toLowerCase();
            applyTableFilters();
        });
    });

    const applyTableFilters = () => {
        const keyword = (tableSearchInput?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.officer-violation-row');
        let hasResults = false;

        if (btnClearSearch) {
            if (keyword.length > 0) {
                btnClearSearch.classList.remove('d-none');
            } else {
                btnClearSearch.classList.add('d-none');
            }
        }

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const statusCell = row.querySelector('td[data-label="Status"]');
            const rowStatus = (statusCell?.textContent || '').toLowerCase();
            const matchesSearch = text.includes(keyword);
            const matchesStatus = !currentStatusFilter || rowStatus.includes(currentStatusFilter);
            const show = matchesSearch && matchesStatus;
            row.style.display = show ? '' : 'none';
            if (show) hasResults = true;
        });

        const noDataRow = document.getElementById('noTableDataRow');
        if (!hasResults) {
            if (!noDataRow) {
                const tbody = document.querySelector('#officerViolationTable tbody');
                const row = tbody.insertRow();
                row.id = 'noTableDataRow';
                row.innerHTML = `<td colspan="8"><div class="empty-state py-4 text-center"><i class="bi bi-search fs-2 text-muted mb-2"></i><div class="empty-state-title fw-bold">No Matching Citations</div><div class="text-muted small">No records match "${keyword || currentStatusFilter}". Try resetting your filters.</div></div></td>`;
            }
        } else if (noDataRow) {
            noDataRow.remove();
        }
    };

    tableSearchInput?.addEventListener('input', applyTableFilters);
    if (btnClearSearch) {
        btnClearSearch.addEventListener('click', function() {
            if (tableSearchInput) {
                tableSearchInput.value = '';
                applyTableFilters();
                tableSearchInput.focus();
            }
        });
    }

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
        document.querySelectorAll('.btn-cancel-violation').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                cancelViolationForm.action = `<?= base_url('officer/cancel') ?>/${id}`;
                cancelViolationModal.show();
            });
        });
    }

    const editModalEl = document.getElementById('editViolationModal');
    const editForm = document.getElementById('editViolationForm');
    const editTypeSelect = document.getElementById('edit_violation_type_id');
    const editAmount = document.getElementById('edit_penalty_amount');

    function updateEditComputedFields() {
        const option = editTypeSelect?.options[editTypeSelect.selectedIndex];
        const amount = option?.dataset?.amount ? parseFloat(option.dataset.amount) : 0;
        if (editAmount) editAmount.value = amount.toFixed(2);
        const editAmountDisplay = document.getElementById('edit_penalty_amount_display');
        if (editAmountDisplay) editAmountDisplay.textContent = amount.toFixed(2);
    }

    editTypeSelect?.addEventListener('change', updateEditComputedFields);

    if (editModalEl && editForm) {
        const editModal = new bootstrap.Modal(editModalEl);
        document.querySelectorAll('.btn-edit-violation').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                editForm.action = `<?= base_url('officer/update') ?>/${id}`;

                if (document.getElementById('edit_ticket_no_display')) {
                    document.getElementById('edit_ticket_no_display').textContent = this.dataset.ticketId || ('#' + id);
                }

                document.getElementById('edit_first_name').value = this.dataset.firstName || '';
                if(document.getElementById('edit_middle_name')) {
                    document.getElementById('edit_middle_name').value = this.dataset.middleName || '';
                }
                document.getElementById('edit_last_name').value = this.dataset.lastName || '';
                if(document.getElementById('edit_license_number')) {
                    document.getElementById('edit_license_number').value = this.dataset.licenseNumber || '';
                }
                if(document.getElementById('edit_mtop_number')) {
                    document.getElementById('edit_mtop_number').value = this.dataset.mtopNumber || '';
                }
                if(document.getElementById('edit_owner_name')) {
                    document.getElementById('edit_owner_name').value = this.dataset.ownerName || '';
                }
                
                const ack = this.dataset.acknowledgment || '';
                if(document.getElementById('edit_ackAdmitted')) {
                    document.getElementById('edit_ackAdmitted').checked = (ack === 'Admitted');
                }
                if(document.getElementById('edit_ackProtest')) {
                    document.getElementById('edit_ackProtest').checked = (ack === 'Under Protest');
                }

                document.getElementById('edit_age').value = this.dataset.age || '';
                document.getElementById('edit_address').value = this.dataset.address || '';
                document.getElementById('edit_license_plate').value = this.dataset.licensePlate || '';
                document.getElementById('edit_location').value = this.dataset.location || '';
                document.getElementById('edit_notes').value = this.dataset.notes || '';
                if (editTypeSelect) {
                    editTypeSelect.value = this.dataset.violationTypeId || '';
                }
                updateEditComputedFields();
                editModal.show();
            });
        });
    }

    const deleteModalEl = document.getElementById('deleteViolationModal');
    const deleteForm = document.getElementById('deleteViolationForm');
    if (deleteModalEl && deleteForm) {
        const deleteModal = new bootstrap.Modal(deleteModalEl);
        document.querySelectorAll('.btn-delete-violation').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                deleteForm.action = `<?= base_url('officer/delete') ?>/${id}`;
                deleteModal.show();
            });
        });
    }

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

    // Auto-open modal if there are flash errors
    <?php if (session()->getFlashdata('error') || session()->getFlashdata('errors')): ?>
        const recordViolationModal = new bootstrap.Modal(document.getElementById('recordViolationModal'));
        recordViolationModal.show();
    <?php endif; ?>

</script>
<?= $this->endSection() ?>
