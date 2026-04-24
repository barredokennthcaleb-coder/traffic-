<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Pay Violation - <?= $violation['ticket_id'] ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-credit-card me-2 text-success"></i>Pay Violation</h4>
                        <a href="/user/dashboard" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-primary border-0 shadow-sm mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Ticket ID</h6>
                                <strong class="fs-4"><?= esc($violation['ticket_id']) ?></strong>
                            </div>
                            <i class="bi bi-receipt fs-1 text-primary opacity-50"></i>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted text-uppercase">Driver</small>
                                <p class="mb-0 fw-semibold"><?= esc($violation['driver_name']) ?></p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted text-uppercase">License Plate</small>
                                <p class="mb-0 fw-semibold"><?= esc($violation['license_plate']) ?></p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted text-uppercase">Violation Type</small>
                                <p class="mb-0 fw-semibold"><?= esc($violation['violation_type']) ?></p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted text-uppercase">Violation Date</small>
                                <p class="mb-0 fw-semibold"><?= date('F d, Y - h:i A', strtotime($violation['violation_date'])) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center p-4 bg-success text-white rounded mb-4">
                        <small class="text-uppercase">Amount Due</small>
                        <h1 class="mb-0 display-4 fw-bold">$<?= number_format($violation['penalty_amount'], 2) ?></h1>
                    </div>

                    <form action="/user/process-payment" method="POST" id="paymentForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="ticket_id" value="<?= esc($violation['ticket_id']) ?>">
                        
                        <div class="mb-4">
                            <label class="form-label">Select Payment Method</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="form-check custom-radio selected">
                                        <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="Credit Card" checked>
                                        <label class="form-check-label w-100 p-3 border rounded d-flex align-items-center" for="credit_card">
                                            <i class="bi bi-credit-card me-2 fs-4"></i>
                                            <div>
                                                <strong>Credit Card</strong>
                                                <small class="d-block text-muted">Visa, Mastercard</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check custom-radio">
                                        <input class="form-check-input" type="radio" name="payment_method" id="gcash" value="GCash">
                                        <label class="form-check-label w-100 p-3 border rounded d-flex align-items-center" for="gcash">
                                            <i class="bi bi-phone me-2 fs-4"></i>
                                            <div>
                                                <strong>GCash</strong>
                                                <small class="d-block text-muted">Mobile payment</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check custom-radio">
                                        <input class="form-check-input" type="radio" name="payment_method" id="paymaya" value="PayMaya">
                                        <label class="form-check-label w-100 p-3 border rounded d-flex align-items-center" for="paymaya">
                                            <i class="bi bi-phone-fill me-2 fs-4"></i>
                                            <div>
                                                <strong>PayMaya</strong>
                                                <small class="d-block text-muted">Mobile payment</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check custom-radio">
                                        <input class="form-check-input" type="radio" name="payment_method" id="cash" value="Cash">
                                        <label class="form-check-label w-100 p-3 border rounded d-flex align-items-center" for="cash">
                                            <i class="bi bi-cash me-2 fs-4"></i>
                                            <div>
                                                <strong>Cash</strong>
                                                <small class="d-block text-muted">Over the counter</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                <i class="bi bi-lock me-2"></i> Pay Now - $<?= number_format($violation['penalty_amount'], 2) ?>
                            </button>
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
    document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.form-check-label').forEach(function(label) {
                label.classList.remove('border-primary', 'bg-primary bg-opacity-10');
            });
            this.nextElementSibling.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
        });
    });

    document.getElementById('paymentForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing Payment...';
    });
</script>
<?= $this->endSection() ?>
