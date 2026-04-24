<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Payment Receipt - <?= $violation['receipt_number'] ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="bg-success bg-opacity-10 d-inline-flex p-3 rounded-circle mb-3">
                            <i class="bi bi-check-circle-fill text-success fs-1"></i>
                        </div>
                        <h3 class="mb-1">Payment Successful!</h3>
                        <p class="text-muted mb-0">Thank you for your payment</p>
                    </div>

                    <div class="bg-light p-4 rounded mb-4">
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <small class="text-muted text-uppercase">Receipt Number</small>
                                <h5 class="mb-0 mt-1"><?= esc($violation['receipt_number']) ?></h5>
                            </div>
                            <div class="col-6">
                                <small class="text-muted text-uppercase">Date Paid</small>
                                <h5 class="mb-0 mt-1"><?= date('M d, Y', strtotime($violation['paid_date'])) ?></h5>
                                <small class="text-muted"><?= date('h:i A', strtotime($violation['paid_date'])) ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted text-uppercase">Ticket ID</small>
                                <p class="mb-0 fw-semibold"><?= esc($violation['ticket_id']) ?></p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted text-uppercase">Payment Method</small>
                                <p class="mb-0 fw-semibold"><?= esc($violation['payment_method']) ?></p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted text-uppercase">Driver Name</small>
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
                                <small class="text-muted text-uppercase">Violation</small>
                                <p class="mb-0 fw-semibold"><?= esc($violation['violation_type']) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center p-4 bg-success text-white rounded mb-4">
                        <small class="text-uppercase">Amount Paid</small>
                        <h1 class="mb-0 display-4 fw-bold">$<?= number_format($violation['penalty_amount'], 2) ?></h1>
                    </div>

                    <div class="alert alert-success border-0 shadow-sm mb-4">
                        <div class="d-flex">
                            <i class="bi bi-shield-check me-3 mt-1"></i>
                            <div>
                                <strong>Official Receipt</strong>
                                <p class="mb-0 text-muted small">This serves as your official receipt for the payment of the above violation. Please keep this for your records.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button onclick="window.print()" class="btn btn-outline-primary flex-grow-1">
                            <i class="bi bi-printer me-1"></i> Print Receipt
                        </button>
                        <a href="/user/dashboard" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-house me-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
