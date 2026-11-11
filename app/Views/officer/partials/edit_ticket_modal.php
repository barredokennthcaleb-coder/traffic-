<?php
/**
 * Update Citation Ticket (TCT) modal.
 *
 * Shared by the citation ledger and a violator's record page so an edit can be
 * finished on the page it started from. It expects $violationTypes for the
 * checklist and $returnFrom / $returnViolator for the post-save destination.
 */
?>
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

                    <!-- Where to go after saving: the citation's own history page when
                         the edit started there, so a save never dumps the officer back
                         in the all-records ledger. -->
                    <input type="hidden" name="from" value="<?= esc($returnFrom ?? '') ?>">
                    <input type="hidden" name="violator" value="<?= (int) ($returnViolator ?? 0) ?>">

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
                                    <span class="tct-bold-label me-1">Vehicle</span>
                                    <div class="combo-wrap flex-1">
                                        <input type="text" name="vehicle_type" id="edit_vehicle_type" class="form-control form-control-sm tct-input fw-bold" data-combo-source="vehicleTypeOptions" autocomplete="off" placeholder="e.g. Tricycle">
                                        <button type="button" class="combo-toggle" aria-expanded="false" aria-label="Show vehicle suggestions" tabindex="-1"><i class="bi bi-chevron-down"></i></button>
                                        <div class="combo-list" hidden></div>
                                    </div>
                                </div>
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
                                <?php foreach (($violationTypes ?? []) as $type): ?>
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
                                        <div class="combo-wrap flex-1">
                                            <input type="text" name="location" id="edit_location" class="form-control form-control-sm tct-input"
                                                   required data-combo-source="locationOptions" autocomplete="off" placeholder="e.g. Ceres Terminal, Biyarin, or specific address...">
                                            <button type="button" class="combo-toggle" aria-expanded="false" aria-label="Show place suggestions" tabindex="-1"><i class="bi bi-chevron-down"></i></button>
                                            <div class="combo-list" hidden></div>
                                        </div>
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
