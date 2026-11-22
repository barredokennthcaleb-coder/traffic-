<?php
/**
 * Shared TCT "Record New Violation" form.
 *
 * This is the exact ticket the enforcer issues, down to the letterhead, the
 * violations checklist, the acknowledgment, both signature boxes and the TCT
 * number. It is included by both the enforcer ledger and the admin violators
 * directory, so the two can never drift into different "ticket formats".
 *
 * Callers may supply: $violationTypes, $submissionToken, $prefillViolator,
 * $prefillOffenses, $nextTctNumber and $officerPrintName.
 */
$prefillViolator = is_array($prefillViolator ?? null) ? $prefillViolator : [];
$prefillOffenses = array_map('strval', (array) ($prefillOffenses ?? []));
$nextTctNumber   = (string) ($nextTctNumber ?? '');
$officerPrintName = trim((string) ($officerPrintName ?? ''));
if ($officerPrintName === '') {
    $officerPrintName = (string) session()->get('username');
}
?>
    <!-- Record Violation Modal -->
    <!-- Vehicle / Place suggestions live in one shared partial so the record
         form, the edit modal and the violator record page all agree on them
         (and phones get a picker that can actually be opened). -->
    <?= $this->include('officer/partials/tct_field_options') ?>

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
                        <?php
                            $prefill = $prefillViolator;

                            // A person on the ledger can have no plate or MTOP on file
                            // (an unregistered vehicle, a pedestrian case). Plate# is a
                            // required field on the TCT, so a blank left the officer
                            // staring at "Please fill out this field". Prefilled rows get
                            // N/A for those two blanks; a brand-new violator still types
                            // the real value.
                            $prefillPlate   = trim((string) ($prefill['license_plate'] ?? ''));
                            $prefillMtop    = trim((string) ($prefill['mtop_number'] ?? ''));
                            $prefillLicense = trim((string) ($prefill['license_number'] ?? ''));
                            if ($prefill !== []) {
                                $prefillPlate   = $prefillPlate !== '' ? $prefillPlate : 'N/A';
                                $prefillMtop    = $prefillMtop !== '' ? $prefillMtop : 'N/A';
                                $prefillLicense = $prefillLicense !== '' ? $prefillLicense : 'N/A';
                            }

                        ?>
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
                                    // A stored last name can already carry its suffix
                                    // ("Dimagiba Jr."); the form shows the two parts in
                                    // separate boxes so the officer can change either one.
                                    $newRecordSuffix = tct_split_name_suffix($newRecordName['last']);
                                    $newSuffixValue  = old('name_suffix', $newRecordSuffix['suffix']);
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
                                        <div class="d-flex flex-column text-center" style="flex: 1.6; min-width: 0;">
                                            <input type="text" name="last_name" id="last_name"
                                                   class="form-control form-control-sm tct-input w-100 fw-bold" style="text-align: center;"
                                                   placeholder="Last" required
                                                   value="<?= esc($newRecordSuffix['last']) ?>">
                                            <span style="font-size: 0.72rem; font-style: italic; font-family: Arial, sans-serif; color: #64748b; margin-top: 2px;">(Last)</span>
                                        </div>
                                        <div class="d-flex flex-column text-center" style="width: 56px; flex-shrink: 0;">
                                            <input type="text" name="name_suffix" id="name_suffix"
                                                   class="form-control form-control-sm tct-input w-100 fw-bold" style="text-align: center;"
                                                   placeholder="Suffix" maxlength="20" aria-label="Name suffix, e.g. Jr. or III"
                                                   value="<?= esc($newSuffixValue) ?>">
                                            <span style="font-size: 0.72rem; font-style: italic; font-family: Arial, sans-serif; color: #64748b; margin-top: 2px;">(Suffix)</span>
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

                                <div class="tct-form-grid tct-form-grid-3 mb-3">
                                    <div class="tct-form-cell">
                                        <span class="tct-bold-label">DL/Permit No.</span>
                                        <input type="text" name="license_number" id="license_number" 
                                               class="form-control form-control-sm tct-input flex-1 fw-bold" 
                                               placeholder="e.g. F02-00-123456" value="<?= esc(old('license_number', $prefillLicense)) ?>">
                                    </div>
                                    <div class="tct-form-cell">
                                        <span class="tct-bold-label">Plate#</span>
                                        <input type="text" name="license_plate" id="license_plate" 
                                               class="form-control form-control-sm tct-input flex-1 fw-bold" 
                                               placeholder="e.g. ABC 1234" value="<?= esc(old('license_plate', $prefillPlate)) ?>">
                                    </div>
                                    <div class="tct-form-cell">
                                        <span class="tct-bold-label">MTOP#</span>
                                        <input type="text" name="mtop_number" id="mtop_number" 
                                               class="form-control form-control-sm tct-input flex-1 fw-bold" 
                                               placeholder="e.g. 1234 (optional)" value="<?= esc(old('mtop_number', $prefillMtop)) ?>">
                                    </div>
                                </div>

                                <div class="tct-form-grid tct-form-grid-3">
                                    <div class="tct-form-cell">
                                        <span class="tct-bold-label">Vehicle</span>
                                        <div class="combo-wrap flex-1">
                                            <input type="text" name="vehicle_type" id="vehicle_type" 
                                                   class="form-control form-control-sm tct-input fw-bold" 
                                                   data-combo-source="vehicleTypeOptions"
                                                   autocomplete="off"
                                                   placeholder="e.g. Tricycle - Honda TMX 155" 
                                                   value="<?= esc(old('vehicle_type', $prefill['vehicle_type'] ?? '')) ?>">
                                            <button type="button" class="combo-toggle" aria-expanded="false" aria-label="Show vehicle suggestions" tabindex="-1"><i class="bi bi-chevron-down"></i></button>
                                            <div class="combo-list" hidden></div>
                                        </div>
                                    </div>
                                    <div class="tct-form-cell">
                                        <span class="tct-bold-label">Owner:</span>
                                        <input type="text" name="owner_name" id="owner_name" 
                                               class="form-control form-control-sm tct-input flex-1 fw-bold" 
                                               placeholder="e.g. Same as driver" value="<?= esc(old('owner_name', $prefill['owner_name'] ?? '')) ?>">
                                    </div>
                                    <div class="tct-form-cell">
                                        <span class="tct-bold-label">Address</span>
                                        <input type="text" name="owner_address" id="owner_address" 
                                               class="form-control form-control-sm tct-input flex-1 fw-bold" 
                                               placeholder="e.g. Same as above" value="<?= esc(old('owner_address', $prefill['owner_address'] ?? '')) ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Violations Checklist -->
                            <div class="tct-doc-section">
                                <div class="tct-violations-head d-flex justify-content-between align-items-center mb-1 gap-2 flex-wrap">
                                    <div class="tct-sec-heading text-center flex-1">VIOLATIONS</div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-light text-dark border fw-bold" id="fineBadge" style="font-size: 0.82rem;">Fine: ₱<span id="penalty_amount_display">0.00</span></span>
                                        <button type="button" class="btn btn-xs btn-outline-primary" id="addNewInfractionBtn" title="Add violation">
                                            <i class="bi bi-plus-lg me-1"></i> Add Violation
                                        </button>
                                    </div>
                                </div>
                                <div class="tct-sec-notice text-center mb-3">
                                    You are hereby charged/cited for comitting the violations marked "x" hereunder:
                                </div>
                                <div class="tct-prev-legend<?= $prefillOffenses === [] ? ' d-none' : '' ?>">
                                    <i class="bi bi-bookmark-fill"></i> Shaded offenses are ones this driver was cited for before
                                </div>

                                <div class="tct-checklist-container" id="violationChecklist">
                                    <?php foreach ($violationTypes as $type): ?>
                                        <?php
                                            $isChecked  = in_array((string) $type['id'], (array) old('violation_type_id', []), true);
                                            $isPrevious = in_array(trim((string) $type['violation_name']), $prefillOffenses, true);
                                        ?>
                                        <label class="tct-check-label<?= $isPrevious ? ' is-previous' : '' ?>"<?= $isPrevious ? ' title="This driver was cited for this offense before"' : '' ?>>
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

                                <!-- Additional violation entries added with the + Add Violation button -->
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
                                <div class="tct-form-grid tct-form-grid-2 mb-3">
                                    <div class="tct-form-cell">
                                        <span class="tct-bold-label">Place:</span>
                                        <div class="combo-wrap flex-1">
                                            <input type="text" name="location" id="location" 
                                                   class="form-control form-control-sm tct-input fw-bold"
                                                   required data-combo-source="locationOptions" autocomplete="off"
                                                   placeholder="e.g. Ceres Terminal, City Mall or Bread Box"
                                                   value="<?= esc(old('location')) ?>">
                                            <button type="button" class="combo-toggle" aria-expanded="false" aria-label="Show place suggestions" tabindex="-1"><i class="bi bi-chevron-down"></i></button>
                                            <div class="combo-list" hidden></div>
                                        </div>
                                    </div>
                                    <div class="tct-form-cell">
                                        <span class="tct-bold-label">Date &amp; Time</span>
                                        <input type="datetime-local" name="violation_datetime" id="violation_datetime"
                                               class="form-control form-control-sm tct-input flex-1 fw-bold"
                                               value="<?= esc(old('violation_datetime') ?: date('Y-m-d\TH:i')) ?>">
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
                                        <span class="officer-printed-name"><?= esc($officerPrintName ?? '') ?></span>
                                    </div>
                                    <input type="hidden" name="officer_signature" id="officer_signature">
                                </div>

                                <div class="mt-2 mb-2">
                                    <label for="notes" class="tct-sub-label mb-1">Notes / Remarks (Optional):</label>
                                    <input type="text" name="notes" id="notes" class="form-control form-control-sm tct-input" placeholder="Optional remarks" value="<?= esc(old('notes')) ?>">
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
