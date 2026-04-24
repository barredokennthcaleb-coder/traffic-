<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Record Violation - Traffic Officer<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0"><i class="bi bi-clipboard-plus me-2 text-primary"></i>Record New Violation</h4>
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

                    <form action="/officer/store" method="POST" id="violationForm">
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
                                <div class="alert alert-info border-0 shadow-sm">
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
                                        <a href="/officer/violations" class="btn btn-outline-secondary">
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
        
        // Update violation info
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
</script>
<?= $this->endSection() ?>
