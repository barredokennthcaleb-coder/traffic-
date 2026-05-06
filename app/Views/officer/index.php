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
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="recordViolationModalLabel"><i class="bi bi-clipboard-plus me-2"></i>Record New Violation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="<?= base_url('officer/store') ?>" method="POST" id="violationForm">
                        <?= csrf_field() ?>
                        
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="section-label">Driver Information</div>
                            </div>
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="first_name" id="first_name" class="form-control" 
                                           placeholder="Enter violator first name" required
                                           pattern="[A-Za-z\s\-']{2,100}"
                                           value="<?= old('first_name') ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                    <input type="text" name="last_name" id="last_name" class="form-control" 
                                           placeholder="Enter violator last name" required
                                           pattern="[A-Za-z\s\-']{2,100}"
                                           value="<?= old('last_name') ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="license_plate" class="form-label">License Plate Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-car-front"></i></span>
                                    <input type="text" name="license_plate" id="license_plate" class="form-control" 
                                           placeholder="e.g., ABC 1234" required
                                           maxlength="20"
                                           value="<?= old('license_plate') ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="age" class="form-label">Age <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-123"></i></span>
                                    <input type="number" name="age" id="age" class="form-control"
                                           min="16" max="120" required
                                           value="<?= old('age') ?>"
                                           placeholder="Enter violator age">
                                </div>
                            </div>

                            <div class="col-12 pt-1">
                                <div class="section-label">Violation Details</div>
                            </div>
                            <div class="col-12">
                                <label for="violation_type" class="form-label">Violation Type <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-exclamation-triangle"></i></span>
                                    <select name="violation_type_id" id="violation_type" class="form-select" required onchange="updateFineAmount()">
                                        <option value="">-- Select Violation Type --</option>
                                        <?php foreach ($violationTypes as $type): ?>
                                            <option value="<?= $type['id'] ?>" data-amount="<?= $type['fine_amount'] ?>" data-points="<?= $type['points'] ?>">
                                                <?= esc($type['violation_name']) ?> - <?= number_format((float)$type['fine_amount'], 2) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addViolationTypeModal">
                                        <i class="bi bi-plus-circle me-1"></i> Add
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="penalty_amount" class="form-label">Penalty Amount ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="text" id="penalty_amount" class="form-control bg-light" readonly 
                                           value="0.00" placeholder="Auto-calculated based on violation type">
                                </div>
                                <div class="form-text">Auto-calculated from selected violation type.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="points" class="form-label">Deductible Points</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-star"></i></span>
                                    <input type="text" id="points" class="form-control bg-light" readonly 
                                           value="0" placeholder="Points will be assigned automatically">
                                </div>
                            </div>


                            <div class="col-12">
                                <div class="alert alert-info border-0 shadow-sm info-box">
                                    <div class="d-flex">
                                        <i class="bi bi-info-circle-fill me-3 mt-1"></i>
                                        <div>
                                            <h6 class="alert-heading">Violation Information</h6>
                                            <p class="mb-0" id="violationInfo">Select a violation type to see its details.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 pt-1">
                                <div class="section-label">Location & Notes</div>
                            </div>
                            <div class="col-md-6">
                                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-house-door"></i></span>
                                    <input type="text" name="address" id="address" class="form-control" 
                                           placeholder="e.g., Brgy. Poblacion, City"
                                           required
                                           value="<?= old('address') ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="location" class="form-label">Location</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                    <input type="text" name="location" id="location" class="form-control" 
                                           placeholder="e.g., Main Street, Downtown"
                                           value="<?= old('location') ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="notes" class="form-label">Notes</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                    <input type="text" name="notes" id="notes" class="form-control" 
                                           placeholder="Additional notes (optional)"
                                           value="<?= old('notes') ?>">
                                </div>
                            </div>

                            <div class="col-12">
                                <hr class="my-2">
                                <div class="d-flex justify-content-end align-items-center form-footer">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary px-4" id="submitPrintBtn" name="print_ticket" value="1">
                                            <i class="bi bi-printer me-1"></i> Save & Print Ticket
                                        </button>
                                    </div>
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
                                <option value="cancelled">Cancelled</option>
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
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th class="text-end pe-4 col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($violations)): ?>
                                    <tr id="noTableDataRow">
                                        <td colspan="7">
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
                                        </td>
                                        <td class="small fw-semibold text-muted" data-label="Violation Type"><?= esc($v['violation_type'] ?? '-') ?></td>
                                        <td data-label="Amount"><span class="fw-bold text-danger"><?= number_format((float) ($v['penalty_amount'] ?? 0), 2) ?></span></td>
                                        <td data-label="Status">
                                            <?php if (($v['status'] ?? '') === 'Pending'): ?>
                                                <span class="badge bg-warning rounded-pill px-3">Pending</span>
                                            <?php elseif (($v['status'] ?? '') === 'Paid'): ?>
                                                <span class="badge bg-success rounded-pill px-3">Paid</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger rounded-pill px-3">Cancelled</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted small" data-label="Date"><?= isset($v['violation_date']) ? date('M d, Y', strtotime($v['violation_date'])) : '-' ?></td>
                                        <td class="text-end pe-4 col-actions" data-label="Actions">
                                            <div class="btn-group shadow-sm actions-group">
                                                <a href="<?= base_url('officer/view/' . $v['id']) ?>" class="btn btn-sm btn-white border" title="View Details">
                                                    <i class="bi bi-eye text-info"></i>
                                                </a>
                                                <?php if (($v['status'] ?? '') === 'Pending'): ?>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-white border btn-edit-violation"
                                                        title="Update Ticket"
                                                        data-id="<?= $v['id'] ?>"
                                                        data-first-name="<?= esc($v['first_name'] ?? '') ?>"
                                                        data-last-name="<?= esc($v['last_name'] ?? '') ?>"
                                                        data-age="<?= esc((string) ($v['age'] ?? '')) ?>"
                                                        data-address="<?= esc($v['address'] ?? '') ?>"
                                                        data-license-plate="<?= esc($v['license_plate'] ?? '') ?>"
                                                        data-violation-type-id="<?= esc((string) ($v['violation_type_id'] ?? '')) ?>"
                                                        data-location="<?= esc($v['location'] ?? '') ?>"
                                                        data-notes="<?= esc($v['notes'] ?? '') ?>">
                                                        <i class="bi bi-pencil text-primary"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-white border btn-cancel-violation" data-id="<?= $v['id'] ?>" title="Cancel Ticket">
                                                        <i class="bi bi-x-circle text-danger"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-white border btn-delete-violation" data-id="<?= $v['id'] ?>" title="Delete Ticket">
                                                        <i class="bi bi-trash text-danger"></i>
                                                    </button>
                                                <?php endif; ?>
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
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" id="edit_last_name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Age</label>
                            <input type="number" class="form-control" name="age" id="edit_age" min="16" max="120" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">License Plate</label>
                            <input type="text" class="form-control" name="license_plate" id="edit_license_plate" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" id="edit_address" required>
                        </div>
                        <div class="col-md-6">
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
                        <div class="col-md-3">
                            <label class="form-label">Amount</label>
                            <input type="text" class="form-control bg-light" id="edit_penalty_amount" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Points</label>
                            <input type="text" class="form-control bg-light" id="edit_points" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" id="edit_location">
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
                    <label class="form-label">Fine Amount ($) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="vtFine" step="0.01" min="0" placeholder="0.00" />
                </div>
                <div class="mb-3">
                    <label class="form-label">Points</label>
                    <input type="number" class="form-control" id="vtPoints" step="1" min="0" placeholder="0" />
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
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function updateFineAmount() {
        const select = document.getElementById('violation_type');
        const option = select.options[select.selectedIndex];
        const amount = option.dataset.amount;
        const points = option.dataset.points;
        const baseAmount = amount ? parseFloat(amount) : 0;
        const basePoints = points ? parseInt(points, 10) : 0;
        const finalAmount = baseAmount;
        const finalPoints = basePoints;
        
        document.getElementById('penalty_amount').value = finalAmount.toFixed(2);
        document.getElementById('points').value = String(finalPoints || 0);
        
        const violationInfo = document.getElementById('violationInfo');
        if (select.value) {
            const selectedText = select.options[select.selectedIndex].text;
            violationInfo.innerHTML = `<strong>${selectedText.split(' - $')[0]}</strong><br>
                This violation will be recorded with a fine of <strong>$${finalAmount.toFixed(2)}</strong>
                and <strong>${finalPoints} penalty points</strong>.`;
        } else {
            violationInfo.textContent = 'Select a violation type to see its details.';
        }
    }

    document.getElementById('violationForm').addEventListener('submit', function() {
        const printBtn = document.getElementById('submitPrintBtn');
        printBtn.disabled = true;
        printBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
    });

    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');
    const plateInput = document.getElementById('license_plate');
    const ageInput = document.getElementById('age');

    const sanitizeName = (value) => value.replace(/[^A-Za-z\s\-']/g, '');
    const sanitizePlate = (value) => value.toUpperCase().replace(/[^A-Z0-9\s\-]/g, '').trimStart();

    firstNameInput?.addEventListener('input', function() {
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
        const points = document.getElementById('vtPoints').value;
        const desc = document.getElementById('vtDesc').value.trim();

        errorBox.classList.add('d-none');
        errorBox.innerHTML = '';

        btn.disabled = true;
        spinner.classList.remove('d-none');

        try {
            const res = await fetch('<?= base_url('officer/violation-types/store') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                body: new URLSearchParams({
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                    violation_name: name,
                    fine_amount: fine,
                    points: points,
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
            const select = document.getElementById('violation_type');
            const opt = document.createElement('option');
            opt.value = type.id;
            opt.dataset.amount = type.fine_amount;
            opt.dataset.points = type.points;
            opt.textContent = `${type.violation_name} - $${parseFloat(type.fine_amount).toFixed(2)}`;
            select.appendChild(opt);
            select.value = String(type.id);
            updateFineAmount();

            document.getElementById('vtName').value = '';
            document.getElementById('vtFine').value = '';
            document.getElementById('vtPoints').value = '';
            document.getElementById('vtDesc').value = '';
            bootstrap.Modal.getInstance(document.getElementById('addViolationTypeModal')).hide();
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
    const editPoints = document.getElementById('edit_points');

    function updateEditComputedFields() {
        const option = editTypeSelect?.options[editTypeSelect.selectedIndex];
        const amount = option?.dataset?.amount ? parseFloat(option.dataset.amount) : 0;
        const points = option?.dataset?.points ? parseInt(option.dataset.points, 10) : 0;
        if (editAmount) editAmount.value = amount.toFixed(2);
        if (editPoints) editPoints.value = String(points || 0);
    }

    editTypeSelect?.addEventListener('change', updateEditComputedFields);

    if (editModalEl && editForm) {
        const editModal = new bootstrap.Modal(editModalEl);
        document.querySelectorAll('.btn-edit-violation').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                editForm.action = `<?= base_url('officer/update') ?>/${id}`;
                document.getElementById('edit_first_name').value = this.dataset.firstName || '';
                document.getElementById('edit_last_name').value = this.dataset.lastName || '';
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
