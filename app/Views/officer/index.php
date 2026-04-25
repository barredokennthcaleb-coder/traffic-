<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Record Violation - Traffic Officer<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm enforcer-card">
                <div class="card-header py-3 enforcer-header">
                    <h4 class="mb-0"><i class="bi bi-clipboard-plus me-2 text-primary"></i>Record New Violation</h4>
                    <small class="text-muted">Fill in the incident details and submit an official traffic ticket.</small>
                </div>
                <div class="card-body p-4">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <ul class="mb-0 ps-3">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('officer/store') ?>" method="POST" id="violationForm">
                        <?= csrf_field() ?>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="driver_name" class="form-label">Driver Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="driver_name" id="driver_name" class="form-control" 
                                           placeholder="Enter driver's full name" required
                                           value="<?= old('driver_name') ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="license_plate" class="form-label">License Plate Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-car-front"></i></span>
                                    <input type="text" name="license_plate" id="license_plate" class="form-control" 
                                           placeholder="e.g., ABC 1234" required
                                           value="<?= old('license_plate') ?>">
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="violation_type" class="form-label">Violation Type <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-exclamation-triangle"></i></span>
                                    <select name="violation_type_id" id="violation_type" class="form-select" required onchange="updateFineAmount()">
                                        <option value="">-- Select Violation Type --</option>
                                        <?php foreach ($violationTypes as $type): ?>
                                            <option value="<?= $type['id'] ?>" data-amount="<?= $type['fine_amount'] ?>" data-points="<?= $type['points'] ?>">
                                                <?= esc($type['violation_name']) ?> - $<?= number_format($type['fine_amount'], 2) ?>
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
                                <div class="form-text">Amount is automatically set based on the violation type</div>
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
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-calendar-event me-1"></i> Date: <?= date('F d, Y - h:i A') ?>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="<?= base_url('officer/violations') ?>" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-left me-1"></i> Back
                                        </a>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="bi bi-save me-1"></i> Record Violation
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
    }
    .enforcer-header {
        background: linear-gradient(180deg, #f3f5ff 0%, #ffffff 100%);
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
    .enforcer-card .input-group-text {
        background: #f7f9ff;
        border-color: #dbe1ff;
        color: #5a68a9;
    }
    .enforcer-card .form-control,
    .enforcer-card .form-select {
        border-color: #dbe1ff;
    }
    .enforcer-card .form-control.bg-light {
        background: #f7f9ff !important;
    }
    .info-box {
        background: #eaf4ff;
        color: #22495f;
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
        
        document.getElementById('penalty_amount').value = amount ? parseFloat(amount).toFixed(2) : '0.00';
        document.getElementById('points').value = points || '0';
        
        const violationInfo = document.getElementById('violationInfo');
        if (select.value) {
            const selectedText = select.options[select.selectedIndex].text;
            violationInfo.innerHTML = `<strong>${selectedText.split(' - $')[0]}</strong><br>
                This violation will be recorded with a fine of <strong>$${parseFloat(amount).toFixed(2)}</strong> 
                and <strong>${points} penalty points</strong>.`;
        } else {
            violationInfo.textContent = 'Select a violation type to see its details.';
        }
    }

    document.getElementById('violationForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Recording...';
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
</script>
<?= $this->endSection() ?>
