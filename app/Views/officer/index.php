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
                                    <img src="<?= base_url('img/pic 1.png') ?>" alt="Kabankalan Logo" class="tct-seal-img">
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
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm enforcer-card premium-reveal analytics-tilt" style="--reveal-delay:.18s;">
                <div class="card-header py-3 enforcer-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                    <div>
                        <h5 class="mb-0"><i class="bi bi-list-ul me-2 text-primary"></i>My Recorded Violations</h5>
                        <small class="text-muted">Recent records and quick actions.</small>
                    </div>
                    <div class="d-flex gap-2 flex-wrap justify-content-end align-items-center">
                        <div class="d-flex gap-2 align-items-center">
                            <select id="statusFilter" class="form-select shadow-sm" style="max-width: 140px;">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                            </select>
                            <div class="input-group shadow-sm" style="max-width: 280px;">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" id="tableSearchInput" class="form-control border-start-0 ps-0" placeholder="Search ticket, driver, plate...">
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#recordViolationModal">
                            <i class="bi bi-plus-circle me-1"></i> Record New Violation
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-premium-mobile officer-violations-table" id="officerViolationTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Ticket ID</th>
                                    <th>Driver Information</th>
                                    <th>Violation Type</th>
                                    <th>Amount</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th class="text-end pe-4 col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($violations)): ?>
                                    <tr id="noTableDataRow">
                                        <td colspan="8">
                                            <div class="empty-state">
                                                <i class="bi bi-inbox"></i>
                                                <div class="empty-state-title">No Violation Records</div>
                                                <div>Your submitted violations will appear in this table.</div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($violations as $v): ?>
                                    <tr class="officer-violation-row">
                                        <td class="ps-4" data-label="Ticket ID"><span class="badge bg-dark-subtle text-dark border border-dark-subtle px-2 font-monospace"><?= esc($v['ticket_id'] ?? 'N/A') ?></span></td>
                                        <td data-label="Driver Information">
                                            <div class="fw-bold"><?= esc(trim(($v['first_name'] ?? '') . ' ' . ($v['last_name'] ?? '')) ?: ($v['driver_name'] ?? '-')) ?></div>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small font-monospace"><?= esc($v['license_plate'] ?? '-') ?></span>
                                            <?php if (!empty($v['license_number'])): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle small font-monospace ms-1">DL: <?= esc($v['license_number']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small fw-semibold text-dark" data-label="Violation Type">
                                            <?php 
                                                $vTypes = explode('||', $v['concatenated_violations'] ?? $v['violation_type'] ?? '-');
                                                echo '<ul class="list-unstyled mb-0 gap-1 d-flex flex-column">';
                                                foreach ($vTypes as $vt) {
                                                    $parts = explode('::', $vt);
                                                    $vName = $parts[0];
                                                    echo '<li><i class="bi bi-dot me-1 text-primary"></i>' . esc($vName) . '</li>';
                                                }
                                                echo '</ul>';
                                            ?>
                                        </td>
                                        <td class="small font-monospace text-muted" data-label="Amount">
                                            <?php 
                                                $vTypes = explode('||', $v['concatenated_violations'] ?? $v['violation_type'] ?? '-');
                                                echo '<ul class="list-unstyled mb-0 gap-1 d-flex flex-column">';
                                                foreach ($vTypes as $vt) {
                                                    $parts = explode('::', $vt);
                                                    $vAmt  = isset($parts[1]) && $parts[1] !== '' ? (float)$parts[1] : null;
                                                    echo '<li>';
                                                    if ($vAmt !== null) {
                                                        echo '₱' . number_format($vAmt, 2);
                                                    } else {
                                                        echo '-';
                                                    }
                                                    echo '</li>';
                                                }
                                                echo '</ul>';
                                            ?>
                                        </td>
                                        <td data-label="Total">
                                            <span class="fw-bold text-danger font-monospace fs-6">₱<?= number_format((float) ($v['total_penalty_sum'] ?? $v['penalty_amount'] ?? 0), 2) ?></span>
                                        </td>
                                        <td data-label="Status">
                                            <?php if (($v['status'] ?? '') === 'Pending'): ?>
                                                <span class="badge bg-warning rounded-pill px-3">Pending</span>
                                            <?php elseif (($v['status'] ?? '') === 'Paid'): ?>
                                                <span class="badge bg-success rounded-pill px-3">Paid</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger rounded-pill px-3">Cancelled</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted small" data-label="Date"><?= isset($v['max_violation_date']) ? date('M d, Y', strtotime($v['max_violation_date'])) : (isset($v['violation_date']) ? date('M d, Y', strtotime($v['violation_date'])) : '-') ?></td>
                                        <td class="text-end pe-4 col-actions" data-label="Actions">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-white border shadow-sm dropdown-toggle no-caret" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    <li>
                                                        <button type="button" class="dropdown-item" onclick="printTicket('<?= base_url('officer/view/' . $v['id'] . '?print=1') ?>')">
                                                            <i class="bi bi-printer me-2 text-secondary"></i> Print Ticket
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <a href="<?= base_url('officer/view/' . $v['id']) ?>" class="dropdown-item">
                                                            <i class="bi bi-eye me-2 text-info"></i> View Details
                                                        </a>
                                                    </li>
                                                    <?php if (($v['status'] ?? '') === 'Pending'): ?>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button type="button" class="dropdown-item btn-edit-violation"
                                                                data-id="<?= $v['id'] ?>"
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
                                                                <i class="bi bi-pencil me-2 text-primary"></i> Edit Ticket
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item btn-cancel-violation" data-id="<?= $v['id'] ?>">
                                                                <i class="bi bi-x-circle me-2 text-warning"></i> Cancel Ticket
                                                            </button>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button type="button" class="dropdown-item btn-delete-violation text-danger" data-id="<?= $v['id'] ?>">
                                                            <i class="bi bi-trash me-2"></i> Delete Ticket
                                                        </button>
                                                    </li>
                                                </ul>
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

<div class="modal fade" id="editViolationModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Update Violation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editViolationForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" name="middle_name" id="edit_middle_name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" id="edit_last_name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Driver's License No.</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
                                <input type="text" name="license_number" id="edit_license_number" class="form-control" maxlength="50">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">License Plate</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-car-front"></i></span>
                                <input type="text" class="form-control" name="license_plate" id="edit_license_plate" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">MTOP #</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                <input type="text" class="form-control" name="mtop_number" id="edit_mtop_number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Owner</label>
                            <input type="text" class="form-control" name="owner_name" id="edit_owner_name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Age</label>
                            <input type="number" class="form-control" name="age" id="edit_age" min="16" max="120" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" id="edit_address" required>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label d-block mb-2">Driver Acknowledgment</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="acknowledgment" id="edit_ackAdmitted" value="Admitted">
                                    <label class="form-check-label" for="edit_ackAdmitted">Admitted</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="acknowledgment" id="edit_ackProtest" value="Under Protest">
                                    <label class="form-check-label" for="edit_ackProtest">Under Protest</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 pt-2 border-top mt-3">
                            <h6 class="mb-3 text-muted">Violation Information</h6>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Violation Type</label>
                            <select class="form-select" name="violation_type_id" id="edit_violation_type_id" required>
                                <option value="">-- Select Violation Type --</option>
                                <?php foreach ($violationTypes as $type): ?>
                                    <option value="<?= $type['id'] ?>" data-amount="<?= $type['fine_amount'] ?>" data-points="<?= $type['points'] ?>">
                                        <?= esc($type['violation_name']) ?> - <?= number_format((float) $type['fine_amount'], 2) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label"><i class="bi bi-lock-fill text-muted me-1" style="font-size:.75rem"></i>Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="text" class="form-control bg-light-subtle text-muted" id="edit_penalty_amount" readonly style="cursor: not-allowed;">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-lock-fill" style="font-size:.7rem"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" id="edit_location" list="locationOptions" placeholder="e.g. Ceres Terminal, Biyarin, or specific address...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Notes</label>
                            <input type="text" class="form-control" name="notes" id="edit_notes">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Save Changes</button>
                </div>
            </form>
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
        table-layout: fixed;
    }
    .officer-violations-table th,
    .officer-violations-table td {
        vertical-align: middle;
    }
    .officer-violations-table .col-actions {
        width: 170px;
        white-space: nowrap;
    }
    .officer-violations-table .actions-group {
        flex-wrap: nowrap;
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
    const tableSearchInput = document.getElementById('tableSearchInput');
    const statusFilter = document.getElementById('statusFilter');
    const applyTableFilters = () => {
        const keyword = (tableSearchInput?.value || '').toLowerCase().trim();
        const status = (statusFilter?.value || '').toLowerCase();
        const rows = document.querySelectorAll('.officer-violation-row');
        let hasResults = false;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const statusCell = row.querySelector('td[data-label="Status"]');
            const rowStatus = (statusCell?.textContent || '').toLowerCase();
            const matchesSearch = text.includes(keyword);
            const matchesStatus = !status || rowStatus.includes(status);
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
                row.innerHTML = `<td colspan="7"><div class="empty-state"><i class="bi bi-search"></i><div class="empty-state-title">No Matching Results</div><div>Try a different keyword or status.</div></div></td>`;
            }
        } else if (noDataRow) {
            noDataRow.remove();
        }
    };
    tableSearchInput?.addEventListener('keyup', applyTableFilters);
    statusFilter?.addEventListener('change', applyTableFilters);

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
    }

    editTypeSelect?.addEventListener('change', updateEditComputedFields);

    if (editModalEl && editForm) {
        const editModal = new bootstrap.Modal(editModalEl);
        document.querySelectorAll('.btn-edit-violation').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                editForm.action = `<?= base_url('officer/update') ?>/${id}`;
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
