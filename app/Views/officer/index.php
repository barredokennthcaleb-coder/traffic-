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
    <div class="modal fade" id="recordViolationModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="recordViolationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold" id="recordViolationModalLabel"><i class="bi bi-file-earmark-text me-2"></i>Record New Violation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-md-4" style="background-color: #f8f9fa;">
                    <form action="<?= base_url('officer/store') ?>" method="POST" id="violationForm">
                        <?= csrf_field() ?>
                        <?php $prefill = is_array($prefillViolator ?? null) ? $prefillViolator : []; ?>
                        <input type="hidden" name="submission_token" id="submission_token" value="<?= esc($submissionToken ?? '') ?>">
                        <input type="hidden" name="violator_id" id="violator_id" value="<?= esc(old('violator_id', $prefill['id'] ?? '')) ?>">

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
                                <div class="tct-row mb-2">
                                    <span class="tct-bold-label">TO:</span>
                                </div>

                                <div class="tct-row mb-2" style="display: flex; align-items: flex-start;">
                                    <span class="tct-bold-label me-2" style="white-space: nowrap; padding-top: 3px;">Driver's Name:</span>
                                    <?php
                                    // Three visible name fields instead of one run-on input, so each
                                    // word sits above its own (First) / (Middle) / (Last) label. Any
                                    // values kept from a failed submit win over the violator prefill.
                                    $oldFirst = old('first_name');
                                    $oldLast  = old('last_name');
                                    if ($oldFirst === null && $oldLast === null) {
                                        $newRecordName = tct_split_name($prefill['first_name'] ?? null, $prefill['middle_name'] ?? null, $prefill['last_name'] ?? null, $prefill['driver_name'] ?? null);
                                    } else {
                                        $newRecordName = tct_split_name($oldFirst, old('middle_name'), $oldLast, null);
                                    }
                                    ?>
                                    <div class="d-flex flex-1 gap-2" style="min-width: 0;">
                                        <div class="flex-1 d-flex flex-column text-center">
                                            <input type="text" name="first_name" id="first_name"
                                                   class="form-control form-control-sm tct-input w-100 fw-bold" style="text-align: center;"
                                                   placeholder="First" required
                                                   value="<?= esc($newRecordName['first']) ?>">
                                            <span style="font-size: 0.72rem; font-style: italic; font-family: Arial, sans-serif; color: #64748b; margin-top: 2px;">(First)</span>
                                        </div>
                                        <div class="flex-1 d-flex flex-column text-center">
                                            <input type="text" name="middle_name" id="middle_name"
                                                   class="form-control form-control-sm tct-input w-100 fw-bold" style="text-align: center;"
                                                   placeholder="Middle"
                                                   value="<?= esc($newRecordName['middle']) ?>">
                                            <span style="font-size: 0.72rem; font-style: italic; font-family: Arial, sans-serif; color: #64748b; margin-top: 2px;">(Middle)</span>
                                        </div>
                                        <div class="flex-1 d-flex flex-column text-center">
                                            <input type="text" name="last_name" id="last_name"
                                                   class="form-control form-control-sm tct-input w-100 fw-bold" style="text-align: center;"
                                                   placeholder="Last" required
                                                   value="<?= esc($newRecordName['last']) ?>">
                                            <span style="font-size: 0.72rem; font-style: italic; font-family: Arial, sans-serif; color: #64748b; margin-top: 2px;">(Last)</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="tct-row mb-2 align-items-end">
                                    <span class="tct-bold-label me-2">Address:</span>
                                    <input type="text" name="address" id="address" 
                                           class="form-control form-control-sm tct-input flex-1 fw-bold" 
                                           placeholder="e.g. Brgy. 1, Kabankalan City" required 
                                           value="<?= esc(old('address', $prefill['address'] ?? '')) ?>">
                                    <input type="hidden" name="age" id="age" value="<?= esc(old('age', $prefill['age'] ?? '25')) ?>">
                                </div>

                                <div class="tct-row mb-2 align-items-end">
                                    <div class="d-flex flex-1 align-items-end me-3">
                                        <span class="tct-bold-label me-1">DL/Permit No.</span>
                                        <input type="text" name="license_number" id="license_number" 
                                               class="form-control form-control-sm tct-input flex-1 fw-bold" 
                                               placeholder="e.g. F02-00-123456" value="<?= esc(old('license_number', $prefill['license_number'] ?? '')) ?>">
                                    </div>
                                    <div class="d-flex flex-1 align-items-end me-3">
                                        <span class="tct-bold-label me-1">Plate#</span>
                                        <input type="text" name="license_plate" id="license_plate" 
                                               class="form-control form-control-sm tct-input flex-1 fw-bold" 
                                               placeholder="e.g. ABC 1234" required value="<?= esc(old('license_plate', $prefill['license_plate'] ?? '')) ?>">
                                    </div>
                                    <div class="d-flex flex-1 align-items-end">
                                        <span class="tct-bold-label me-1">MTOP#</span>
                                        <input type="text" name="mtop_number" id="mtop_number" 
                                               class="form-control form-control-sm tct-input flex-1 fw-bold" 
                                               placeholder="e.g. 1234 (optional)" value="<?= esc(old('mtop_number', $prefill['mtop_number'] ?? '')) ?>">
                                    </div>
                                </div>

                                <div class="tct-row align-items-end">
                                    <div class="d-flex flex-1 align-items-end me-3">
                                        <span class="tct-bold-label me-1">Owner:</span>
                                        <input type="text" name="owner_name" id="owner_name" 
                                               class="form-control form-control-sm tct-input flex-1 fw-bold" 
                                               placeholder="e.g. Same as driver" value="<?= esc(old('owner_name', $prefill['owner_name'] ?? '')) ?>">
                                    </div>
                                    <div class="d-flex flex-1 align-items-end">
                                        <span class="tct-bold-label me-1">Address</span>
                                        <input type="text" name="owner_address" id="owner_address" 
                                               class="form-control form-control-sm tct-input flex-1 fw-bold" 
                                               placeholder="e.g. Same as above" value="<?= esc(old('owner_address', $prefill['owner_address'] ?? '')) ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Violations Checklist -->
                            <div class="tct-doc-section">
                                <div class="d-flex justify-content-between align-items-center mb-1 gap-2 flex-wrap">
                                    <div class="tct-sec-heading text-center flex-1">VIOLATIONS</div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-light text-dark border fw-bold" id="fineBadge" style="font-size: 0.82rem;">Fine: ₱<span id="penalty_amount_display">0.00</span></span>
                                        <button type="button" class="btn btn-xs btn-outline-primary" id="addNewInfractionBtn" title="Add infraction">
                                            <i class="bi bi-plus-lg me-1"></i> Add Infraction
                                        </button>
                                    </div>
                                </div>
                                <div class="tct-sec-notice text-center mb-3">
                                    You are hereby charged/cited for comitting the violations marked "x" hereunder:
                                </div>

                                <div class="tct-checklist-container" id="violationChecklist">
                                    <?php foreach ($violationTypes as $type): ?>
                                        <?php $isChecked = in_array((string)$type['id'], (array)old('violation_type_id', []), true); ?>
                                        <label class="tct-check-label">
                                            <input type="checkbox" name="violation_type_id[]" class="violation-check d-none"
                                                   value="<?= $type['id'] ?>"
                                                   data-amount="<?= $type['fine_amount'] ?>"
                                                   data-points="<?= $type['points'] ?>"
                                                   <?= $isChecked ? 'checked' : '' ?>>
                                            <div class="tct-box <?= $isChecked ? 'is-checked' : '' ?>">
                                                <span class="tct-box-x"><?= $isChecked ? 'X' : '' ?></span>
                                            </div>
                                            <span class="tct-item-text"><?= esc($type['violation_name']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Additional infraction entries added with the + Add Infraction button -->
                                <div id="newInfractionRows" class="d-flex flex-column gap-2 mt-2"></div>
                                <template id="newInfractionRowTemplate">
                                    <div class="infraction-entry d-flex align-items-center gap-2">
                                        <select name="violation_type_id[]" class="form-select form-select-sm tct-input infraction-select flex-1" aria-label="Additional infraction">
                                            <option value="">-- Select additional violation --</option>
                                            <?php foreach ($violationTypes as $type): ?>
                                                <option value="<?= $type['id'] ?>" data-amount="<?= $type['fine_amount'] ?>" data-points="<?= $type['points'] ?>">
                                                    <?= esc($type['violation_name']) ?> - ₱<?= number_format((float) $type['fine_amount'], 2) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-sm btn-outline-danger infraction-remove" title="Remove infraction" aria-label="Remove infraction">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </template>

                                <input type="hidden" id="penalty_amount" value="0.00">
                            </div>

                            <!-- Section 3: Place, Date & Legal Notice -->
                            <div class="tct-doc-section">
                                <div class="tct-row align-items-end mb-3">
                                    <div class="d-flex flex-1 align-items-end me-4">
                                        <span class="tct-bold-label me-1">Place:</span>
                                        <input type="text" name="location" id="location" 
                                               class="form-control form-control-sm tct-input flex-1 fw-bold"
                                               required list="locationOptions" placeholder="e.g. Ceres Terminal, Kabankalan City"
                                               value="<?= esc(old('location')) ?>">
                                        <datalist id="locationOptions">
                                            <option value="Ceres Terminal, Kabankalan City">
                                            <option value="Biyarin, Kabankalan City">
                                            <?php foreach (kabankalan_areas() as $brgy): ?>
                                            <option value="<?= esc($brgy) ?>, Kabankalan City">
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                    <div class="d-flex flex-1 align-items-end">
                                        <span class="tct-bold-label me-1">Date &amp; Time</span>
                                        <input type="datetime-local" name="violation_datetime" id="violation_datetime"
                                               class="form-control form-control-sm tct-input flex-1 fw-bold"
                                               value="<?= old('violation_datetime') ?: date('Y-m-d\TH:i') ?>">
                                    </div>
                                </div>

                                <div class="tct-legal-text">
                                    You are likewise directed to pay before the Office of the City Treasurer of Kabankalan City within three (3) days from the date of this citation and to secure clearance from the CTRAMO for disposition, Failure on your part to comply will constrain the Office to file the appropriate criminal action against you with the proper court.
                                </div>
                            </div>

                            <!-- Section 4: Acknowledgment & Signatures -->
                            <div class="tct-doc-section border-bottom-0 pb-0">
                                <div class="tct-ack-heading mb-2">I HEREBY ACKNOWLEDGE RECEIPT OF THIS TCT.</div>

                                <div class="d-flex gap-5 mb-4">
                                    <label class="d-flex align-items-center cursor-pointer tct-ack-toggle">
                                        <input type="radio" name="acknowledgment" id="ackAdmitted" value="Admitted" class="d-none" <?= old('acknowledgment', 'Admitted') === 'Admitted' ? 'checked' : '' ?>>
                                        <div class="tct-box me-2 tct-ack-box <?= old('acknowledgment', 'Admitted') === 'Admitted' ? 'is-checked' : '' ?>" id="ackAdmittedBox">
                                            <span class="tct-box-x"><?= old('acknowledgment', 'Admitted') === 'Admitted' ? 'X' : '' ?></span>
                                        </div>
                                        <span class="fw-bold">Admitted</span>
                                    </label>
                                    <label class="d-flex align-items-center cursor-pointer tct-ack-toggle">
                                        <input type="radio" name="acknowledgment" id="ackProtest" value="Under Protest" class="d-none" <?= old('acknowledgment') === 'Under Protest' ? 'checked' : '' ?>>
                                        <div class="tct-box me-2 tct-ack-box <?= old('acknowledgment') === 'Under Protest' ? 'is-checked' : '' ?>" id="ackProtestBox">
                                            <span class="tct-box-x"><?= old('acknowledgment') === 'Under Protest' ? 'X' : '' ?></span>
                                        </div>
                                        <span class="fw-bold">Under Protest</span>
                                    </label>
                                </div>

                                <!-- Driver Signature Area (Centered Line) -->
                                <div class="tct-driver-signature-area text-center mb-4">
                                    <div class="tct-sig-wrapper mx-auto position-relative" style="width: 280px; height: 55px;">
                                        <img id="driverSigPreview" src="" alt="Signature of the Driver"
                                             style="display: none; position: absolute; bottom: 6px; left: 50%; transform: translateX(-50%); max-height: 44px; z-index: 2;">
                                        <div class="tct-sig-underline w-100" style="position: absolute; bottom: 0; left: 0;"></div>
                                    </div>
                                    <div class="d-flex justify-content-center align-items-center gap-2 mt-1">
                                        <span class="tct-sub-label">Signature of the Driver</span>
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-0" data-open-signature="driver">
                                            <i class="bi bi-pen-fill"></i> <span id="driverSigBtnLabel">Sign Here</span>
                                        </button>
                                        <button type="button" class="btn btn-link text-danger p-0" id="clearDriverSigBtn" style="display: none;" title="Remove Signature">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="driver_signature" id="driver_signature">
                                </div>

                                <!-- Officer Signature Area (Rectangular Box) -->
                                <div class="tct-officer-signature-area mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="tct-sub-label">Printed Name and Signature of Apprehending Officer.</div>
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-0" data-open-signature="officer">
                                                <i class="bi bi-pen-fill"></i> <span id="officerSigBtnLabel">Sign Here</span>
                                            </button>
                                            <button type="button" class="btn btn-link text-danger p-0" id="clearOfficerSigBtn" style="display: none;" title="Remove Signature">
                                                <i class="bi bi-x-circle-fill"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="tct-officer-box position-relative" style="border: 1.5px solid #000; height: 60px; width: 100%; display: flex; align-items: center; justify-content: center; background: #fff;">
                                        <img id="officerSigPreview" src="" alt="Signature of the Apprehending Officer"
                                             style="display: none; max-height: 52px; z-index: 2;">
                                    </div>
                                    <input type="hidden" name="officer_signature" id="officer_signature">
                                </div>

                                <div class="mt-2 mb-2">
                                    <label for="notes" class="tct-sub-label mb-1">Notes / Remarks (Optional):</label>
                                    <input type="text" name="notes" id="notes" class="form-control form-control-sm tct-input" placeholder="Optional remarks" value="<?= old('notes') ?>">
                                </div>
                            </div>

                            <!-- Footer Area matching Ticket Format -->
                            <?php
                                $defaultTct = old('custom_ticket_no') ?: ($nextTctNumber ?? '');
                                $defaultTctParts = parse_tct_number($defaultTct);
                            ?>
                            <div class="tct-footer-area d-flex justify-content-between align-items-center pt-2">
                                <div class="tct-driver-copy-tag">Driver's Copy</div>
                                <div class="text-end">
                                    <div class="tct-number-tag d-flex align-items-center justify-content-end">
                                        <span class="me-1">TCT No.</span>
                                        <span class="tct-year-prefix" id="tctYearPrefix"><?= esc($defaultTctParts['prefix']) ?></span>
                                        <input type="text" id="tctSerialInput"
                                               class="form-control form-control-sm tct-input tct-red-no p-0 border-0 text-start"
                                               style="width: 92px; background: transparent;"
                                               maxlength="7"
                                               inputmode="numeric"
                                               pattern="\d{7}"
                                               placeholder="0030268"
                                               value="<?= esc($defaultTctParts['serial']) ?>"
                                               title="Auto-assigned serial number. Edit only when using a physical TCT booklet.">
                                        <input type="hidden" name="custom_ticket_no" id="custom_ticket_no"
                                               value="<?= esc($defaultTctParts['full']) ?>"
                                               data-from-old="<?= old('custom_ticket_no') ? '1' : '0' ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm" id="submitPrintBtn" name="print_ticket" value="1">
                                    <i class="bi bi-printer me-1"></i> Save &amp; Print Ticket
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


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
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="fw-bold text-dark me-2" style="font-size: 0.95rem;">
                            <i class="bi bi-list-check me-1 text-primary"></i>My Recorded Violations
                        </span>
                        <div class="vr mx-1 d-none d-md-block" style="height: 20px;"></div>
                        <button type="button" class="btn btn-sm btn-filter-chip btn-filter-all active" data-status="">
                            All <span class="badge ms-1 rounded-pill"><?= $totalViolators ?? 0 ?></span>
                        </button>
                        <button type="button" class="btn btn-sm btn-filter-chip btn-filter-pending" data-status="pending">
                            Pending <span class="badge filter-badge-pending ms-1 rounded-pill"><?= $pendingViolators ?? 0 ?></span>
                        </button>
                    </div>

                    <!-- Right: Search & Record Violation Button -->
                    <div class="d-flex gap-2 flex-wrap align-items-center ms-auto">
                        <div class="modern-search-box position-relative">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" id="tableSearchInput" class="form-control form-control-sm modern-search-input" placeholder="Search violator name...">
                            <button type="button" class="btn btn-sm btn-clear-search d-none" id="btnClearSearch">
                                <i class="bi bi-x-circle-fill"></i>
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

                <div class="px-4 py-2 border-top d-flex align-items-start gap-2" style="background:#f8fafc; font-size:.78rem;">
                    <i class="bi bi-info-circle text-primary mt-1"></i>
                    <span class="text-muted">
                        One row per violator. A repeat apprehension is added to that person's history instead of
                        creating a duplicate row. Settled tickets are kept in the administrator's <strong>Payment History</strong>.
                    </span>
                </div>

                <!-- Table Content -->
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 officer-violations-table" id="officerViolationTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="min-width: 300px;">Violator</th>
                                    <th class="text-end pe-4 col-actions" style="width: 210px; min-width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($violations)): ?>
                                    <tr id="noTableDataRow">
                                        <td colspan="2">
                                            <div class="empty-state py-5 text-center">
                                                <div class="empty-state-icon-wrap mb-3 mx-auto">
                                                    <i class="bi bi-people fs-1 text-primary"></i>
                                                </div>
                                                <h6 class="fw-bold text-dark">No Recorded Violators</h6>
                                                <p class="text-muted small mb-3">You have no violator with an outstanding citation. Settled tickets are kept in the administrator's Payment History.</p>
                                                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#recordViolationModal">
                                                    <i class="bi bi-plus-circle me-1"></i> Record First Violation
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($violations as $v): ?>
                                    <?php
                                        // Ledger names stay compact: the middle name is
                                        // shown as an initial here and in full on the record.
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
                                    ?>
                                    <tr class="officer-violation-row row-pending" data-status="pending">
                                        <!-- One row per violator: the name is the whole record on this ledger -->
                                        <td class="ps-4" data-label="Violator">
                                            <div class="driver-name-text fw-semibold lh-sm">
                                                <?= esc($vName) ?>
                                            </div>
                                        </td>

                                        <!-- View this violator's records, or add another violation to them -->
                                        <td class="text-end pe-4 col-actions" data-label="Actions">
                                            <div class="d-inline-flex align-items-center gap-2 justify-content-end">
                                                <a href="<?= esc($vHistoryUrl) ?>"
                                                   class="btn btn-sm btn-white border shadow-sm"
                                                   title="View the records of <?= esc($vName) ?>">
                                                    <i class="bi bi-eye text-primary me-1"></i> View
                                                </a>
                                                <a href="<?= esc($vAddUrl) ?>"
                                                   class="btn btn-sm btn-primary rounded-circle shadow-sm"
                                                   style="width: 32px; height: 32px;"
                                                   title="Record another violation for <?= esc($vName) ?>">
                                                    <i class="bi bi-plus-lg fw-bold"></i>
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
                    <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-center">
                        <?= $pager->links('violations', 'bootstrap_pagination') ?>
                    </div>
                <?php endif; ?>
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

<!-- Edit Violation Modal (Formatted matching Physical TCT Citation Ticket) -->
<div class="modal fade" id="editViolationModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="editViolationModalLabel" aria-hidden="true">
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
                            <div class="tct-row mb-2">
                                <span class="tct-bold-label">TO:</span>
                            </div>

                            <div class="tct-row mb-2" style="display: flex; align-items: flex-start;">
                                <span class="tct-bold-label me-2" style="white-space: nowrap; padding-top: 3px;">Driver's Name:</span>
                                <div class="d-flex flex-1 gap-2" style="min-width: 0;">
                                    <div class="flex-1 d-flex flex-column text-center">
                                        <input type="text" name="first_name" id="edit_first_name" class="form-control form-control-sm tct-input w-100 fw-bold" style="text-align: center;" placeholder="First" required>
                                        <span style="font-size: 0.72rem; font-style: italic; font-family: Arial, sans-serif; color: #64748b; margin-top: 2px;">(First)</span>
                                    </div>
                                    <div class="flex-1 d-flex flex-column text-center">
                                        <input type="text" name="middle_name" id="edit_middle_name" class="form-control form-control-sm tct-input w-100 fw-bold" style="text-align: center;" placeholder="Middle">
                                        <span style="font-size: 0.72rem; font-style: italic; font-family: Arial, sans-serif; color: #64748b; margin-top: 2px;">(Middle)</span>
                                    </div>
                                    <div class="flex-1 d-flex flex-column text-center">
                                        <input type="text" name="last_name" id="edit_last_name" class="form-control form-control-sm tct-input w-100 fw-bold" style="text-align: center;" placeholder="Last" required>
                                        <span style="font-size: 0.72rem; font-style: italic; font-family: Arial, sans-serif; color: #64748b; margin-top: 2px;">(Last)</span>
                                    </div>
                                </div>
                            </div>

                            <div class="tct-row mb-2 align-items-end">
                                <span class="tct-bold-label me-2">Address:</span>
                                <input type="text" name="address" id="edit_address" class="form-control form-control-sm tct-input flex-1 fw-bold" placeholder="Driver address" required>
                                <input type="hidden" name="age" id="edit_age" value="25">
                            </div>

                            <div class="tct-row mb-2 align-items-end">
                                <div class="d-flex flex-1 align-items-end me-3">
                                    <span class="tct-bold-label me-1">DL/Permit No.</span>
                                    <input type="text" name="license_number" id="edit_license_number" class="form-control form-control-sm tct-input flex-1 fw-bold" placeholder="no">
                                </div>
                                <div class="d-flex flex-1 align-items-end me-3">
                                    <span class="tct-bold-label me-1">Plate#</span>
                                    <input type="text" name="license_plate" id="edit_license_plate" class="form-control form-control-sm tct-input flex-1 fw-bold" placeholder="890" required>
                                </div>
                                <div class="d-flex flex-1 align-items-end">
                                    <span class="tct-bold-label me-1">MTOP#</span>
                                    <input type="text" name="mtop_number" id="edit_mtop_number" class="form-control form-control-sm tct-input flex-1 fw-bold" placeholder="m789">
                                </div>
                            </div>

                            <div class="tct-row align-items-end">
                                <div class="d-flex flex-1 align-items-end me-3">
                                    <span class="tct-bold-label me-1">Owner:</span>
                                    <input type="text" name="owner_name" id="edit_owner_name" class="form-control form-control-sm tct-input flex-1 fw-bold" placeholder="no">
                                </div>
                                <div class="d-flex flex-1 align-items-end">
                                    <span class="tct-bold-label me-1">Address</span>
                                    <input type="text" name="owner_address" id="edit_owner_address" class="form-control form-control-sm tct-input flex-1 fw-bold" placeholder="bocana ilog">
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Violations Checklist (same format as the record form) -->
                        <div class="tct-doc-section">
                            <div class="d-flex justify-content-between align-items-center mb-1 gap-2 flex-wrap">
                                <div class="tct-sec-heading text-center flex-1">VIOLATIONS</div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-light text-dark border fw-bold" style="font-size: 0.82rem;">Fine: ₱<span id="edit_penalty_amount_display">0.00</span></span>
                                </div>
                            </div>
                            <div class="tct-sec-notice text-center mb-3">
                                You are hereby charged/cited for comitting the violations marked "x" hereunder:
                            </div>

                            <div class="tct-checklist-container" id="editViolationChecklist">
                                <?php foreach ($violationTypes as $type): ?>
                                    <label class="tct-check-label">
                                        <input type="checkbox" name="violation_type_id[]" class="edit-violation-check d-none"
                                               value="<?= $type['id'] ?>"
                                               data-amount="<?= $type['fine_amount'] ?>"
                                               data-name="<?= esc($type['violation_name'], 'attr') ?>">
                                        <div class="tct-box"><span class="tct-box-x"></span></div>
                                        <span class="tct-item-text"><?= esc($type['violation_name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <input type="hidden" id="edit_penalty_amount" value="0.00">
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
                            <div class="d-flex gap-5 mb-4">
                                <label class="d-flex align-items-center cursor-pointer tct-ack-toggle">
                                    <input type="radio" name="acknowledgment" id="edit_ackAdmitted" value="Admitted" class="d-none">
                                    <div class="tct-box me-2 tct-ack-box" id="edit_ackAdmittedBox">
                                        <span class="tct-box-x"></span>
                                    </div>
                                    <span class="fw-bold">Admitted</span>
                                </label>
                                <label class="d-flex align-items-center cursor-pointer tct-ack-toggle">
                                    <input type="radio" name="acknowledgment" id="edit_ackProtest" value="Under Protest" class="d-none">
                                    <div class="tct-box me-2 tct-ack-box" id="edit_ackProtestBox">
                                        <span class="tct-box-x"></span>
                                    </div>
                                    <span class="fw-bold">Under Protest</span>
                                </label>
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

<!-- Floating Quick-Add Button -->
<button type="button" class="fab-new-violation no-print" data-bs-toggle="modal" data-bs-target="#recordViolationModal" title="Add New Violation" aria-label="Add New Violation">
    <i class="bi bi-plus-lg"></i>
</button>

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
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #1e293b;
        transform: translateY(-1px);
    }
    .btn-filter-chip.active,
    .btn-filter-all.active {
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.28);
    }
    .btn-filter-chip.active:hover,
    .btn-filter-all.active:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #ffffff;
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
    .filter-badge-pending {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .btn-filter-pending:hover {
        border-color: #fde68a;
        color: #92400e;
        background: #fffbeb;
    }
    .btn-filter-pending.active {
        background: #f59e0b;
        border-color: #f59e0b;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.28);
    }

    /* ── NEW VIOLATION BUTTON ── */
    .btn-new-violation {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border: none;
        color: #ffffff;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.45rem 1rem;
        border-radius: 999px;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
        white-space: nowrap;
    }
    .btn-new-violation:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 5px 14px rgba(37, 99, 235, 0.35);
    }
    .btn-new-violation:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2);
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
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff;
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4);
        z-index: 1030;
        transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s ease;
    }
    .fab-new-violation i {
        font-size: 1.55rem;
        line-height: 1;
    }
    .fab-new-violation:hover {
        color: #ffffff;
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 12px 30px rgba(37, 99, 235, 0.5);
    }
    .fab-new-violation:active {
        transform: translateY(0) scale(0.97);
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
    }
    .fab-new-violation:focus-visible {
        outline: 3px solid rgba(37, 99, 235, 0.35);
        outline-offset: 3px;
    }
    @media (max-width: 768px) {
        .fab-new-violation {
            right: 1rem;
            bottom: 1rem;
            width: 52px;
            height: 52px;
        }
        .fab-new-violation i {
            font-size: 1.35rem;
        }
    }
    @media print {
        .fab-new-violation {
            display: none !important;
        }
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
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
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
    .btn-clear-search:hover { color: #334155; }

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
        color: #2563eb !important;
        text-decoration: underline;
    }
    .driver-link-icon {
        transition: transform 0.18s ease, opacity 0.18s ease;
    }
    .driver-name-text {
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
        background-color: #f8faff !important;
    }
    .officer-violations-table tbody tr:hover {
        z-index: 2;
        transform: translateY(-1.5px);
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.06);
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
        .col-actions { justify-content: flex-end; }
    }

    /* Ticket ID Glass Pill */
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
        color: #2563eb !important;
        margin-right: 0.25rem;
    }
    .plate-pill:hover {
        background: #f1f5f9 !important;
        border-color: #94a3b8 !important;
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
        color: #b45309;
        background: #fffbeb;
        border: 1px solid #fde68a;
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
    @keyframes pulseAmber {
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
        color: #2563eb;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
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
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border-color: #1d4ed8;
        color: #fff;
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.25);
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
        table-layout: auto;
    }
    @media (min-width: 768px) {
        .officer-violations-table {
            min-width: 1040px;
        }
    }
    .officer-violations-table th,
    .officer-violations-table td {
        vertical-align: middle;
    }
    @media (min-width: 768px) {
        .officer-violations-table .col-actions {
            width: 135px;
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
    .tct-doc-section .btn-xs {
        padding: 0.25rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 600;
        border-radius: 6px;
        white-space: nowrap;
    }
    .infraction-entry {
        padding: 0.35rem 0.45rem;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
    }
    .infraction-entry .infraction-select {
        border-radius: 6px;
        background-color: #fff;
        min-width: 0;
    }
    .infraction-entry .infraction-remove {
        flex: 0 0 auto;
        border-radius: 6px;
        line-height: 1;
    }
    .infraction-entry .infraction-remove:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    @media (max-width: 575.98px) {
        .infraction-entry {
            align-items: stretch !important;
        }
        .infraction-entry .infraction-select {
            font-size: 0.78rem;
        }
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
    .tct-row {
        display: flex;
        width: 100%;
    }
    .flex-1 {
        flex: 1;
    }
    .tct-checklist-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 5px 12px;
    }
    .tct-check-label {
        display: flex;
        align-items: center;
        cursor: pointer;
        margin-bottom: 2px;
        user-select: none;
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
        color: #000;
    }
    .tct-box.is-checked {
        background: #000;
        color: #fff;
    }
    .tct-box-x {
        display: inline-block;
        line-height: 1;
    }
    .tct-item-text {
        font-family: Arial, sans-serif;
        font-size: 0.92rem;
    }
    .tct-sig-underline {
        width: 260px;
        border-bottom: 1.5px solid #000;
        height: 35px;
    }
    .tct-officer-box {
        border: 1.5px solid #000;
        height: 60px;
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
    .tct-number-tag {
        font-weight: bold;
        font-size: 1.15rem;
        font-family: Arial, sans-serif;
    }
    .tct-year-prefix {
        font-weight: 700;
        font-size: 1.05rem;
        font-family: Arial, sans-serif;
        color: #000;
    }
    .tct-red-no {
        color: #dc3545;
        font-family: 'Courier New', monospace;
        font-size: 1.25rem;
        font-weight: 900;
        letter-spacing: 0.05em;
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
    .tct-input::placeholder {
        color: #94a3b8 !important;
        font-weight: 400 !important;
        font-style: italic !important;
        opacity: 0.65 !important;
    }
    .tct-input:focus {
        background: #f0f7ff;
        border-bottom-color: #0d6efd;
        box-shadow: none;
    }

    /* ── MOBILE: stack TCT form rows so nothing runs off-screen ── */
    @media (max-width: 767px) {
        .tct-modal-card {
            padding: 14px 12px;
        }
        .tct-row,
        .tct-row > * {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.35rem;
        }
        .tct-row > *,
        .tct-row > * > * {
            flex: none !important;
            width: 100% !important;
            min-width: 0 !important;
            margin-right: 0 !important;
        }
        .tct-modal-card .tct-sig-wrapper {
            width: 100% !important;
            max-width: 280px;
        }
        .tct-driver-signature-area .d-flex {
            flex-wrap: wrap;
        }
        .tct-officer-signature-area > .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 0.35rem;
        }
        .tct-officer-signature-area > .d-flex > .d-flex {
            align-self: flex-end;
            justify-content: flex-end;
        }
        .tct-doc-section .d-flex.gap-5 {
            gap: 1rem !important;
        }
        .tct-footer-area {
            flex-wrap: wrap;
            gap: 0.35rem 0.75rem;
        }
        .tct-modal-card .d-flex.justify-content-end.gap-2.border-top {
            flex-direction: column !important;
            gap: 0.5rem !important;
        }
        .tct-modal-card .d-flex.justify-content-end.gap-2.border-top .btn {
            width: 100%;
        }
    }
</style>

<!-- E-Signature Modal (same draw/upload flow as the report sign-off) -->
<div class="modal fade" id="tctSignatureModal" tabindex="-1" aria-labelledby="tctSignatureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="tctSignatureModalLabel">
                    <i class="bi bi-pen-fill text-danger me-2"></i>Apply Digital E-Signature
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <ul class="nav nav-pills nav-justified mb-3" id="tctSigTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="tctDrawTab" data-bs-toggle="tab" data-bs-target="#tctDrawPanel" type="button" role="tab">
                            <i class="bi bi-fingerprint me-1"></i> Draw Signature
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="tctUploadTab" data-bs-toggle="tab" data-bs-target="#tctUploadPanel" type="button" role="tab">
                            <i class="bi bi-upload me-1"></i> Upload Image
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="tctSigTabContent">
                    <div class="tab-pane fade show active" id="tctDrawPanel" role="tabpanel">
                        <p class="text-muted small mb-2">Use your mouse, touch pen, or finger to draw your signature inside the pad below:</p>
                        <div class="border rounded-3 p-2 bg-light text-center position-relative">
                            <canvas id="tctSigCanvas" width="420" height="170" style="touch-action: none; cursor: crosshair; background: #fff; border: 1px dashed #cbd5e1; border-radius: 8px; width: 100%; max-width: 420px; height: 170px;"></canvas>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="tctSigClearPad">
                                <i class="bi bi-eraser-fill me-1"></i> Clear Pad
                            </button>
                            <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Smooth Digital Ink</span>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tctUploadPanel" role="tabpanel">
                        <p class="text-muted small mb-2">Select a transparent PNG or JPG image of your signature:</p>
                        <input type="file" id="tctSigFileInput" class="form-control" accept="image/png, image/jpeg, image/webp">
                        <div class="text-center mt-3 p-3 border rounded-3 bg-light" id="tctSigUploadPreviewBox" style="display:none;">
                            <img id="tctSigUploadPreviewImg" src="" alt="Preview" style="max-height: 100px;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger fw-bold px-4" id="tctSigApplyBtn">
                    <i class="bi bi-check-circle-fill me-1"></i> Apply Signature
                </button>
            </div>
        </div>
    </div>
</div>

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
    // ═══ NEW VIOLATION INFRACTIONS (CHECKLIST + ADDED ENTRIES) ═══
    const newInfractionRows = document.getElementById('newInfractionRows');
    const newInfractionRowTemplate = document.getElementById('newInfractionRowTemplate');
    const addNewInfractionBtn = document.getElementById('addNewInfractionBtn');

    function showInfractionWarning(message) {
        if (window.AppToast) {
            AppToast.fire({ icon: 'warning', title: message });
        } else {
            alert(message);
        }
    }

    function getNewInfractionRows() {
        return Array.from(newInfractionRows?.querySelectorAll('.infraction-entry') || []);
    }

    function getSelectedNewInfractionIds() {
        const ids = Array.from(document.querySelectorAll('#violationChecklist .violation-check:checked')).map(check => check.value);
        getNewInfractionRows().forEach(row => {
            const select = row.querySelector('.infraction-select');
            if (select?.value) ids.push(select.value);
        });
        return ids;
    }

    function setCheckboxVisual(check) {
        const box = check.closest('label')?.querySelector('.tct-box');
        if (!box) return;
        if (check.checked) {
            box.classList.add('is-checked');
            box.innerHTML = '<span class="tct-box-x">X</span>';
        } else {
            box.classList.remove('is-checked');
            box.innerHTML = '';
        }
    }

    function syncNewInfractionOptions() {
        const rows = getNewInfractionRows();
        const checkedValues = Array.from(document.querySelectorAll('#violationChecklist .violation-check:checked')).map(check => check.value);

        rows.forEach(row => {
            const select = row.querySelector('.infraction-select');
            if (!select) return;
            const ownValue = select.value;
            Array.from(select.options).forEach(option => {
                if (!option.value) return;
                if (option.value === ownValue) {
                    option.disabled = false;
                    return;
                }
                const usedByOtherRow = rows.some(otherRow => {
                    if (otherRow === row) return false;
                    return otherRow.querySelector('.infraction-select')?.value === option.value;
                });
                option.disabled = usedByOtherRow || checkedValues.includes(option.value);
            });
        });
    }

    function clearMatchingCheckbox(value) {
        document.querySelectorAll('#violationChecklist .violation-check:checked').forEach(check => {
            if (check.value !== value) return;
            check.checked = false;
            setCheckboxVisual(check);
        });
    }

    function clearMatchingInfractionRows(value, exceptRow = null) {
        getNewInfractionRows().forEach(row => {
            if (row === exceptRow) return;
            const select = row.querySelector('.infraction-select');
            if (select && select.value === value) {
                select.value = '';
            }
        });
    }

    function updateFineAmount() {
        let totalAmount = 0;
        document.querySelectorAll('#violationChecklist .violation-check:checked').forEach(check => {
            totalAmount += parseFloat(check.dataset.amount || 0);
        });
        getNewInfractionRows().forEach(row => {
            const select = row.querySelector('.infraction-select');
            const selectedOption = select?.options[select.selectedIndex];
            if (select?.value && selectedOption) {
                totalAmount += parseFloat(selectedOption.dataset.amount || 0);
            }
        });

        const penaltyInput = document.getElementById('penalty_amount');
        if (penaltyInput) penaltyInput.value = totalAmount.toFixed(2);

        const disp = document.getElementById('penalty_amount_display');
        if (disp) disp.textContent = totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function addNewInfractionRow(selectedId = '') {
        if (!newInfractionRows || !newInfractionRowTemplate) return null;
        const row = newInfractionRowTemplate.content.firstElementChild.cloneNode(true);
        const select = row.querySelector('.infraction-select');
        const removeBtn = row.querySelector('.infraction-remove');

        if (select) {
            select.required = true;
            if (selectedId) {
                select.value = String(selectedId);
            }
            select.addEventListener('change', function () {
                if (this.value) {
                    clearMatchingCheckbox(this.value);
                    clearMatchingInfractionRows(this.value, row);
                }
                syncNewInfractionOptions();
                updateFineAmount();
            });
        }

        removeBtn?.addEventListener('click', function () {
            row.remove();
            syncNewInfractionOptions();
            updateFineAmount();
        });

        newInfractionRows.appendChild(row);
        syncNewInfractionOptions();
        updateFineAmount();
        return row;
    }

    function handleViolationCheckChange() {
        if (this.checked) {
            clearMatchingInfractionRows(this.value);
        }
        setCheckboxVisual(this);
        syncNewInfractionOptions();
        updateFineAmount();
    }

    // Checkbox violations logic & authentic [X] toggle
    document.querySelectorAll('#violationChecklist .violation-check').forEach(check => {
        check.addEventListener('change', handleViolationCheckChange);
    });

    addNewInfractionBtn?.addEventListener('click', function () {
        const row = addNewInfractionRow();
        row?.querySelector('.infraction-select')?.focus();
    });

    updateFineAmount();

    // Acknowledgment [X] toggle for Admitted / Under Protest
    document.querySelectorAll('input[name="acknowledgment"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.tct-ack-box').forEach(b => {
                b.classList.remove('is-checked');
                b.innerHTML = '';
            });
            const curBox = this.closest('label')?.querySelector('.tct-ack-box');
            if (curBox && this.checked) {
                curBox.classList.add('is-checked');
                curBox.innerHTML = '<span class="tct-box-x">X</span>';
            }
        });
    });

    // The first / middle / last fields are entered directly now (each under its
    // own label), so nothing needs to be split out of a combined name input.

    // Signature capture: the same draw/upload e-signature dialog used by the
    // report sign-off, wired to the driver and apprehending-officer fields.
    const tctSigState = { role: 'driver', drawing: false, hasDrawn: false, uploaded: '' };
    let tctSigCanvas = null;
    let tctSigCtx = null;

    function tctSigParts(role) {
        const cap = role.charAt(0).toUpperCase() + role.slice(1);
        return {
            hidden: document.getElementById(role + '_signature'),
            preview: document.getElementById(role + 'SigPreview'),
            label: document.getElementById(role + 'SigBtnLabel'),
            clearBtn: document.getElementById('clear' + cap + 'SigBtn'),
            storageKey: 'tct_sig_' + role
        };
    }

    function setTctSignature(role, dataUrl) {
        const parts = tctSigParts(role);
        const signed = !!dataUrl;
        if (parts.preview) {
            parts.preview.src = signed ? dataUrl : '';
            parts.preview.style.display = signed ? 'block' : 'none';
        }
        if (parts.hidden) parts.hidden.value = signed ? dataUrl : '';
        if (parts.label) parts.label.innerText = signed ? 'Change Signature' : 'Sign Here';
        if (parts.clearBtn) parts.clearBtn.style.display = signed ? 'inline-block' : 'none';
    }

    function applyTctSignature(role, dataUrl) {
        setTctSignature(role, dataUrl);
        if (role === 'officer') {
            // The officer's own signature is reused across tickets; the driver's
            // is never stored because it belongs to whoever is being cited.
            localStorage.setItem('tct_sig_officer', dataUrl);
        }
    }

    function clearTctSignature(role) {
        setTctSignature(role, '');
        localStorage.removeItem('tct_sig_' + role);
    }

    function restoreSavedOfficerSignature() {
        const saved = localStorage.getItem('tct_sig_officer');
        if (saved) setTctSignature('officer', saved);
    }

    function initTctSigCanvas() {
        tctSigCanvas = document.getElementById('tctSigCanvas');
        if (!tctSigCanvas) return;
        tctSigCtx = tctSigCanvas.getContext('2d');

        function getPos(e) {
            const rect = tctSigCanvas.getBoundingClientRect();
            let clientX = e.clientX;
            let clientY = e.clientY;
            if (e.touches && e.touches.length > 0) {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            }
            return {
                x: (clientX - rect.left) * (tctSigCanvas.width / rect.width),
                y: (clientY - rect.top) * (tctSigCanvas.height / rect.height)
            };
        }

        function startDrawing(e) {
            e.preventDefault();
            tctSigState.drawing = true;
            tctSigState.hasDrawn = true;
            tctSigCtx.lineWidth = 2.5;
            tctSigCtx.lineCap = 'round';
            tctSigCtx.lineJoin = 'round';
            tctSigCtx.strokeStyle = '#0f172a';
            const pos = getPos(e);
            tctSigCtx.beginPath();
            tctSigCtx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!tctSigState.drawing) return;
            e.preventDefault();
            const pos = getPos(e);
            tctSigCtx.lineTo(pos.x, pos.y);
            tctSigCtx.stroke();
        }

        function stopDrawing() {
            tctSigState.drawing = false;
        }

        tctSigCanvas.addEventListener('mousedown', startDrawing);
        tctSigCanvas.addEventListener('mousemove', draw);
        tctSigCanvas.addEventListener('mouseup', stopDrawing);
        tctSigCanvas.addEventListener('mouseleave', stopDrawing);

        tctSigCanvas.addEventListener('touchstart', startDrawing, { passive: false });
        tctSigCanvas.addEventListener('touchmove', draw, { passive: false });
        tctSigCanvas.addEventListener('touchend', stopDrawing);

        document.getElementById('tctSigClearPad')?.addEventListener('click', function() {
            tctSigCtx.clearRect(0, 0, tctSigCanvas.width, tctSigCanvas.height);
            tctSigState.hasDrawn = false;
        });

        document.getElementById('tctSigFileInput')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(evt) {
                tctSigState.uploaded = evt.target.result;
                document.getElementById('tctSigUploadPreviewImg').src = tctSigState.uploaded;
                document.getElementById('tctSigUploadPreviewBox').style.display = 'block';
            };
            reader.readAsDataURL(file);
        });

        document.getElementById('tctSigApplyBtn')?.addEventListener('click', function() {
            const activeTab = document.querySelector('#tctSigTab .nav-link.active')?.id;
            let sigData = '';

            if (activeTab === 'tctUploadTab') {
                if (!tctSigState.uploaded) {
                    Swal.fire({ icon: 'warning', title: 'No File Selected', text: 'Please select an image file first.' });
                    return;
                }
                sigData = tctSigState.uploaded;
            } else {
                if (!tctSigState.hasDrawn) {
                    Swal.fire({ icon: 'warning', title: 'Empty Signature Pad', text: 'Please draw your signature before applying.' });
                    return;
                }
                sigData = tctSigCanvas.toDataURL('image/png');
            }

            applyTctSignature(tctSigState.role, sigData);
            bootstrap.Modal.getInstance(document.getElementById('tctSignatureModal'))?.hide();
        });
    }

    function openTctSignatureModal(role) {
        tctSigState.role = role;
        const titleEl = document.getElementById('tctSignatureModalLabel');
        if (titleEl) {
            const title = role === 'driver' ? 'Driver Signature' : 'Apprehending Officer Signature';
            titleEl.innerHTML = `<i class="bi bi-pen-fill text-danger me-2"></i>Sign: ${title}`;
        }

        if (!tctSigCanvas) initTctSigCanvas();
        if (tctSigCtx) tctSigCtx.clearRect(0, 0, tctSigCanvas.width, tctSigCanvas.height);
        tctSigState.hasDrawn = false;
        tctSigState.uploaded = '';
        const fileInput = document.getElementById('tctSigFileInput');
        if (fileInput) fileInput.value = '';
        const previewBox = document.getElementById('tctSigUploadPreviewBox');
        if (previewBox) previewBox.style.display = 'none';

        // Always land on the draw tab, exactly like the report sign-off.
        const drawTab = document.getElementById('tctDrawTab');
        if (drawTab) new bootstrap.Tab(drawTab).show();

        new bootstrap.Modal(document.getElementById('tctSignatureModal')).show();
    }

    document.querySelectorAll('[data-open-signature]').forEach(btn => {
        btn.addEventListener('click', () => openTctSignatureModal(btn.dataset.openSignature));
    });
    document.getElementById('clearDriverSigBtn')?.addEventListener('click', () => clearTctSignature('driver'));
    document.getElementById('clearOfficerSigBtn')?.addEventListener('click', () => clearTctSignature('officer'));
    restoreSavedOfficerSignature();

    function syncTctNumberFields() {
        const yearPrefix = document.getElementById('tctYearPrefix');
        const serialInput = document.getElementById('tctSerialInput');
        const hiddenInput = document.getElementById('custom_ticket_no');
        if (!yearPrefix || !serialInput || !hiddenInput) return;

        const year = (yearPrefix.textContent || '').replace(/[^\d]/g, '').slice(0, 4) || String(new Date().getFullYear());
        const serial = String(serialInput.value || '').replace(/\D/g, '').slice(0, 7);
        serialInput.value = serial;
        hiddenInput.value = serial ? `${year}-${serial.padStart(7, '0')}` : '';
    }

    async function refreshNextTctNumber() {
        const yearPrefix = document.getElementById('tctYearPrefix');
        const serialInput = document.getElementById('tctSerialInput');
        const hiddenInput = document.getElementById('custom_ticket_no');
        if (!yearPrefix || !serialInput || !hiddenInput || hiddenInput.dataset.manual === '1' || hiddenInput.dataset.fromOld === '1') {
            return;
        }

        try {
            const res = await fetch('<?= base_url('officer/next-tct-number') ?>', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.success || !data.number) return;

            const parts = String(data.number).split('-');
            if (parts.length === 2) {
                yearPrefix.textContent = `${parts[0]}-`;
                serialInput.value = parts[1].padStart(7, '0');
                hiddenInput.value = data.number;
            }
        } catch (err) {
            // Keep the server-rendered preview if refresh fails.
        }
    }

    document.getElementById('tctSerialInput')?.addEventListener('input', function () {
        const hiddenInput = document.getElementById('custom_ticket_no');
        if (hiddenInput) hiddenInput.dataset.manual = '1';
        syncTctNumberFields();
    });

    // Initialize & Reset Form when Modal Opens for a fresh, blank record.
    // When the page opens with a violator prefill, preserve the server-rendered
    // values the first time and clear everything on later "New Violation" clicks.
    const hasPrefillViolator = <?= !empty($prefillViolator) ? 'true' : 'false' ?>;
    let preservePrefillOnOpen = hasPrefillViolator;

    document.getElementById('recordViolationModal')?.addEventListener('show.bs.modal', function () {
        <?php if (!session()->getFlashdata('error') && !session()->getFlashdata('errors')): ?>
            if (preservePrefillOnOpen) {
                preservePrefillOnOpen = false;
                return;
            }

            const form = document.getElementById('violationForm');
            if (form) {
                // Clear driver, vehicle, and location inputs
                ['address', 'license_number', 'license_plate', 'mtop_number', 'owner_name', 'owner_address', 'location', 'notes'].forEach(id => {
                    const input = document.getElementById(id);
                    if (input) input.value = '';
                });
                ['first_name', 'middle_name', 'last_name', 'driver_signature', 'officer_signature', 'violator_id'].forEach(id => {
                    const input = document.getElementById(id);
                    if (input) input.value = '';
                });
                // Reset acknowledgment radio buttons to default (Admitted)
                const ackAdmitted = document.getElementById('ackAdmitted');
                if (ackAdmitted) ackAdmitted.checked = true;

                // Uncheck all violations and reset total fine
                document.querySelectorAll('#recordViolationModal .violation-check').forEach(chk => {
                    chk.checked = false;
                    setCheckboxVisual(chk);
                });
                if (newInfractionRows) {
                    newInfractionRows.innerHTML = '';
                }
                syncNewInfractionOptions();
                updateFineAmount();

                // A fresh ticket never inherits the previous driver's signature;
                // the apprehending officer's own saved signature stays applied.
                clearTctSignature('driver');
                restoreSavedOfficerSignature();

                // Set violation datetime to accurate real-time (browser local time)
                const dtInput = document.getElementById('violation_datetime');
                if (dtInput) {
                    const now = new Date();
                    const y = now.getFullYear();
                    const m = String(now.getMonth() + 1).padStart(2, '0');
                    const d = String(now.getDate()).padStart(2, '0');
                    const h = String(now.getHours()).padStart(2, '0');
                    const min = String(now.getMinutes()).padStart(2, '0');
                    dtInput.value = `${y}-${m}-${d}T${h}:${min}`;
                }
            }
        <?php endif; ?>
    });

    // Refresh the TCT sequence when the modal opens; signatures are captured
    // in their own draw/upload dialog instead of pads inside the form.
    document.getElementById('recordViolationModal')?.addEventListener('shown.bs.modal', function () {
        refreshNextTctNumber();
    });

    document.getElementById('recordViolationModal')?.addEventListener('hidden.bs.modal', function () {
        const hiddenInput = document.getElementById('custom_ticket_no');
        if (hiddenInput) {
            hiddenInput.dataset.manual = '0';
            hiddenInput.dataset.fromOld = '0';
        }
    });

    document.getElementById('violationForm')?.addEventListener('submit', function(e) {
        syncDriverName();
        syncTctNumberFields();
        const selectedIds = getSelectedNewInfractionIds();

        if (selectedIds.length === 0) {
            e.preventDefault();
            showInfractionWarning('Please select at least one violation infraction.');
            return;
        }

        if (new Set(selectedIds).size !== selectedIds.length) {
            e.preventDefault();
            showInfractionWarning('Duplicate infractions are not allowed. Please remove the duplicate entry.');
            return;
        }

        const printBtn = document.getElementById('submitPrintBtn');
        if (printBtn) {
            printBtn.disabled = true;
            printBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing Ticket...';
        }
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
            const rowStatus = (row.dataset.status || statusCell?.textContent || '').toLowerCase();
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
                row.innerHTML = `<td colspan="2"><div class="empty-state py-4 text-center"><i class="bi bi-search fs-2 text-muted mb-2"></i><div class="empty-state-title fw-bold">No Matching Violators</div><div class="text-muted small">No records match "${keyword || currentStatusFilter}". Try resetting your filters.</div></div></td>`;
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
        cancelViolationForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Cancelling...';
            }
        });
    }

    // ═══ EDIT VIOLATION CHECKLIST ═════════════════════════════
    const editModalEl = document.getElementById('editViolationModal');
    const editForm = document.getElementById('editViolationForm');
    const editChecklist = document.getElementById('editViolationChecklist');
    const editAmount = document.getElementById('edit_penalty_amount');

    function getEditChecks() {
        return Array.from(editChecklist?.querySelectorAll('.edit-violation-check') || []);
    }

    function updateEditComputedFields() {
        let totalAmount = 0;
        getEditChecks().forEach(check => {
            if (check.checked) {
                totalAmount += parseFloat(check.dataset.amount || 0);
            }
        });

        if (editAmount) editAmount.value = totalAmount.toFixed(2);

        const editAmountDisplay = document.getElementById('edit_penalty_amount_display');
        if (editAmountDisplay) {
            editAmountDisplay.textContent = totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    // An infraction whose type is no longer active is re-added to the list so
    // saving the ticket does not silently drop it.
    function addEditChecklistEntry(id, name, amount, checked) {
        if (!editChecklist || !id) return null;

        const existing = getEditChecks().find(check => check.value === String(id));
        if (existing) {
            existing.checked = !!checked;
            setCheckboxVisual(existing);
            return existing;
        }

        const wrap = document.createElement('label');
        wrap.className = 'tct-check-label';

        const check = document.createElement('input');
        check.type = 'checkbox';
        check.name = 'violation_type_id[]';
        check.className = 'edit-violation-check d-none';
        check.value = String(id);
        check.dataset.amount = amount ?? '';
        check.dataset.name = name || '';
        check.checked = !!checked;

        const box = document.createElement('div');
        box.className = 'tct-box';
        const boxX = document.createElement('span');
        boxX.className = 'tct-box-x';
        box.appendChild(boxX);

        const text = document.createElement('span');
        text.className = 'tct-item-text';
        text.textContent = name || ('Violation #' + id);

        wrap.append(check, box, text);
        editChecklist.appendChild(wrap);
        setCheckboxVisual(check);

        return check;
    }

    function setEditChecklist(typeIds) {
        const wanted = (Array.isArray(typeIds) ? typeIds : []).map(String).filter(Boolean);
        getEditChecks().forEach(check => {
            check.checked = wanted.includes(check.value);
            setCheckboxVisual(check);
        });
    }

    editChecklist?.addEventListener('change', function (event) {
        if (!event.target.classList.contains('edit-violation-check')) return;
        setCheckboxVisual(event.target);
        updateEditComputedFields();
    });

    updateEditComputedFields();

    if (editModalEl && editForm) {
        const editModal = new bootstrap.Modal(editModalEl);
        const openEditTicketModal = (data, infractions) => {
            const id = data.id;
            editForm.action = `<?= base_url('officer/update') ?>/${id}`;

            if (document.getElementById('edit_ticket_no_display')) {
                document.getElementById('edit_ticket_no_display').textContent = data.ticketId || data.ticket_id || ('#' + id);
            }

            document.getElementById('edit_first_name').value = data.firstName || data.first_name || '';
            if(document.getElementById('edit_middle_name')) {
                document.getElementById('edit_middle_name').value = data.middleName || data.middle_name || '';
            }
            document.getElementById('edit_last_name').value = data.lastName || data.last_name || '';
            if(document.getElementById('edit_license_number')) {
                document.getElementById('edit_license_number').value = data.licenseNumber || data.license_number || '';
            }
            if(document.getElementById('edit_mtop_number')) {
                document.getElementById('edit_mtop_number').value = data.mtopNumber || data.mtop_number || '';
            }
            if(document.getElementById('edit_owner_name')) {
                document.getElementById('edit_owner_name').value = data.ownerName || data.owner_name || '';
            }
            if(document.getElementById('edit_owner_address')) {
                document.getElementById('edit_owner_address').value = data.ownerAddress || data.owner_address || '';
            }
            
            const ack = data.acknowledgment || '';
            const setAck = (inputId, boxId, active) => {
                const input = document.getElementById(inputId);
                const box   = document.getElementById(boxId);
                if (input) input.checked = active;
                if (box) {
                    box.classList.toggle('is-checked', active);
                    box.innerHTML = active ? '<span class="tct-box-x">X</span>' : '';
                }
            };
            setAck('edit_ackAdmitted', 'edit_ackAdmittedBox', ack === 'Admitted');
            setAck('edit_ackProtest', 'edit_ackProtestBox', ack === 'Under Protest');

            document.getElementById('edit_age').value = data.age || '';
            document.getElementById('edit_address').value = data.address || '';
            document.getElementById('edit_license_plate').value = data.licensePlate || data.license_plate || '';
            document.getElementById('edit_location').value = data.location || '';
            document.getElementById('edit_notes').value = data.notes || '';

            // Mark the violations already saved on this ticket. The checklist is
            // formatted exactly like the record form.
            const saved = Array.isArray(infractions) ? infractions : [];
            setEditChecklist(saved.map(item => item.violation_type_id));

            saved.forEach(item => {
                const typeId = String(item.violation_type_id || '');
                if (!typeId) return;
                const isKnown = getEditChecks().some(check => check.value === typeId);
                if (!isKnown) {
                    addEditChecklistEntry(typeId, item.violation_type || '', item.penalty_amount ?? '', true);
                }
            });

            updateEditComputedFields();
            editModal.show();
        };

        document.querySelectorAll('.btn-edit-violation').forEach(btn => {
            btn.addEventListener('click', function() {
                let rowInfractions = [];
                try {
                    rowInfractions = JSON.parse(this.closest('.officer-violation-row')?.dataset.infractions || '[]');
                } catch (e) {
                    rowInfractions = [];
                }
                openEditTicketModal(this.dataset, rowInfractions);
            });
        });

        // A ticket opened from a violator's history page comes back as
        // ?edit_ticket=<violation id>; re-open the modal for that citation.
        const editTicketSeed = <?= json_encode(($editTicket ?? null) ? array_merge([
            'id'          => (int) $editTicket['id'],
            'ticket_id'   => $editTicket['ticket_id'] ?? '',
            'first_name'  => $editTicket['first_name'] ?? '',
            'middle_name' => $editTicket['middle_name'] ?? '',
            'last_name'   => $editTicket['last_name'] ?? '',
            'age'         => (string) ($editTicket['age'] ?? ''),
            'address'     => $editTicket['address'] ?? '',
            'license_number' => $editTicket['license_number'] ?? '',
            'mtop_number'    => $editTicket['mtop_number'] ?? '',
            'owner_name'     => $editTicket['owner_name'] ?? '',
            'owner_address'  => $editTicket['owner_address'] ?? '',
            'acknowledgment' => $editTicket['acknowledgment'] ?? '',
            'license_plate'  => $editTicket['license_plate'] ?? '',
            'location'       => $editTicket['location'] ?? '',
            'notes'          => $editTicket['notes'] ?? '',
        ], ['infractions' => $ticketInfractions[$editTicket['ticket_id']] ?? []]) : null) ?>;
        if (editTicketSeed && editTicketSeed.id) {
            const seedInfractions = editTicketSeed.infractions || [];
            delete editTicketSeed.infractions;
            openEditTicketModal(editTicketSeed, seedInfractions);
        }
        editForm.addEventListener('submit', function(e) {
            const checked = getEditChecks().filter(check => check.checked);

            if (checked.length === 0) {
                e.preventDefault();
                showInfractionWarning('Please mark at least one violation for this ticket.');
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
            }
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
        deleteForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Deleting...';
            }
        });
    }

    // Elevate row stacking context dynamically whenever action dropdown opens
    document.querySelectorAll('.officer-violations-table .dropdown').forEach(dropdownEl => {
        dropdownEl.addEventListener('show.bs.dropdown', function () {
            const tr = this.closest('tr');
            if (tr) {
                tr.classList.add('has-open-dropdown');
                tr.style.zIndex = '1065';
            }
        });
        dropdownEl.addEventListener('hidden.bs.dropdown', function () {
            const tr = this.closest('tr');
            if (tr) {
                tr.classList.remove('has-open-dropdown');
                tr.style.zIndex = '';
            }
        });
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

    // Auto-open the form for validation errors or when recording another
    // violation for a prefilled violator.
    <?php if (session()->getFlashdata('error') || session()->getFlashdata('errors') || ! empty($prefillViolator)): ?>
        bootstrap.Modal.getOrCreateInstance(document.getElementById('recordViolationModal')).show();
    <?php endif; ?>


</script>
<?= $this->endSection() ?>
