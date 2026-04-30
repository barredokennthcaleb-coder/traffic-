<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>User Dashboard - Driver Portal<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-person-circle fs-2 text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">Welcome, <?= esc($username) ?>!</h4>
                            <p class="text-muted mb-0">Role: <span class="badge bg-info text-dark text-uppercase"><?= esc($role) ?></span></p>
                        </div>
                        <div>
                            <a href="<?= base_url('logout') ?>" class="btn btn-outline-danger">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Pending Violations</h6>
                            <h2 class="mb-0"><?= count($pending_violations) ?></h2>
                        </div>
                        <i class="bi bi-clock-history fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Paid Violations</h6>
                            <h2 class="mb-0"><?= count($paid_violations) ?></h2>
                        </div>
                        <i class="bi bi-check-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Amount Due</h6>
                            <h2 class="mb-0">$<?= number_format(array_sum(array_column($pending_violations, 'penalty_amount')), 2) ?></h2>
                        </div>
                        <i class="bi bi-currency-dollar fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Paid</h6>
                            <h2 class="mb-0">$<?= number_format(array_sum(array_column($paid_violations, 'penalty_amount')), 2) ?></h2>
                        </div>
                        <i class="bi bi-receipt fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div id="pending-violations" class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Pending Violations</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_violations)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle text-success fs-1 d-block mb-2"></i>
                            <p class="text-muted mb-0">No pending violations. Great job!</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Violation</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_violations as $violation): ?>
                                    <tr>
                                        <td><span class="badge bg-dark"><?= esc($violation['ticket_id'] ?? 'N/A') ?></span></td>
                                        <td><?= esc($violation['violation_type']) ?></td>
                                        <td><strong class="text-danger">$<?= number_format($violation['penalty_amount'], 2) ?></strong></td>
                                        <td><?= date('M d, Y', strtotime($violation['violation_date'])) ?></td>
                                        <td>
                                            <a href="<?= base_url('user/pay/' . esc($violation['ticket_id'])) ?>" class="btn btn-sm btn-success">
                                                <i class="bi bi-credit-card me-1"></i> Pay Now
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div id="payment-history" class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-check-circle me-2 text-success"></i>Payment History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($paid_violations)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted fs-1 d-block mb-2"></i>
                            <p class="text-muted mb-0">No payment history yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Violation</th>
                                        <th>Amount Paid</th>
                                        <th>Paid Date</th>
                                        <th>Receipt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paid_violations as $violation): ?>
                                    <tr>
                                        <td><span class="badge bg-secondary"><?= esc($violation['ticket_id'] ?? 'N/A') ?></span></td>
                                        <td><?= esc($violation['violation_type']) ?></td>
                                        <td>$<?= number_format($violation['penalty_amount'], 2) ?></td>
                                        <td><?= date('M d, Y', strtotime($violation['paid_date'])) ?></td>
                                        <td>
                                            <a href="<?= base_url('user/receipt/' . esc($violation['ticket_id'])) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-receipt me-1"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Quick Info</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 shadow-sm mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        <strong>How to Pay:</strong>
                        <ol class="mb-0 mt-2 ps-3">
                            <li>Click "Pay Now" on any pending violation</li>
                            <li>Select your payment method</li>
                            <li>Confirm payment</li>
                            <li>Download your digital receipt</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-question-circle me-2 text-primary"></i>Need Help?</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">If you have any questions about your violations or payment, please contact your local traffic authority.</p>
                    <button class="btn btn-outline-primary w-100">
                        <i class="bi bi-headset me-1"></i> Contact Support
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .container-fluid.py-4 {
            padding-top: 0.5rem !important;
        }
        .card-body .d-flex.align-items-center {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 0.75rem;
        }
        .card-body .d-flex.align-items-center > div:last-child {
            width: 100%;
        }
        .card-body .d-flex.align-items-center > div:last-child .btn {
            width: 100%;
        }
        .table {
            min-width: 700px;
        }
    }
</style>

<?= $this->endSection() ?>
