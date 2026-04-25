<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Record Penalty Payment<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary">Violation Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-6">
                        <p class="text-muted mb-1">Driver Name</p>
                        <h6><?= $violation['driver_name'] ?></h6>
                    </div>
                    <div class="col-6">
                        <p class="text-muted mb-1">License Plate</p>
                        <h6><?= $violation['license_plate'] ?></h6>
                    </div>
                    <div class="col-6 mt-3">
                        <p class="text-muted mb-1">Violation Type</p>
                        <h6><?= $violation['violation_type'] ?></h6>
                    </div>
                    <div class="col-6 mt-3">
                        <p class="text-muted mb-1">Penalty Amount</p>
                        <h6 class="text-danger">$<?= number_format($violation['penalty_amount'], 2) ?></h6>
                    </div>
                </div>

                <hr>

                <form action="<?= base_url('penalties/store') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="violation_id" value="<?= $violation['id'] ?>">
                    
                    <div class="mb-3">
                        <label for="amount_paid" class="form-label">Amount Paid</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" name="amount_paid" class="form-control" id="amount_paid" required value="<?= $violation['penalty_amount'] ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            <option value="Cash">Cash</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Online Payment">Online Payment</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="transaction_id" class="form-label">Transaction Reference ID</label>
                        <input type="text" name="transaction_id" class="form-control" id="transaction_id" placeholder="Bank ref, Receipt #, etc.">
                    </div>

                    <div class="mb-4">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-check-lg me-1"></i> Confirm Payment
                        </button>
                        <a href="<?= base_url('penalties') ?>" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
