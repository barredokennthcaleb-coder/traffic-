<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Violation Details - <?= esc($violation['ticket_id']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Violation Details</h4>
            <p class="text-muted small">Full record for ticket <strong><?= esc($violation['ticket_id']) ?></strong></p>
        </div>
        <div class="col-md-6 text-end">
            <button onclick="window.print()" class="btn btn-outline-primary me-2 no-print">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="javascript:history.back()" class="btn btn-outline-secondary no-print">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Details -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Ticket Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase d-block">Ticket ID</label>
                            <span class="fw-bold fs-5 text-primary"><?= esc($violation['ticket_id']) ?></span>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <label class="text-muted small text-uppercase d-block">Status</label>
                            <?php if ($violation['status'] == 'Pending'): ?>
                                <span class="badge bg-warning text-dark px-3 py-2">Pending</span>
                            <?php elseif ($violation['status'] == 'Paid'): ?>
                                <span class="badge bg-success px-3 py-2">Paid</span>
                            <?php else: ?>
                                <span class="badge bg-danger px-3 py-2">Cancelled</span>
                            <?php endif; ?>
                        </div>
                        <hr>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase d-block">Violation Type</label>
                            <span class="fw-bold"><?= esc($violation['violation_type']) ?></span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase d-block">Fine Amount</label>
                            <span class="fw-bold text-danger">$<?= number_format($violation['penalty_amount'], 2) ?></span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase d-block">Penalty Points</label>
                            <span><?= esc($violation['points']) ?> Points</span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase d-block">Violation Date</label>
                            <span><?= date('M d, Y H:i', strtotime($violation['violation_date'])) ?></span>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small text-uppercase d-block">Location</label>
                            <span><?= esc($violation['location'] ?? 'Not specified') ?></span>
                        </div>
                        <?php if ($violation['notes']): ?>
                        <div class="col-12">
                            <label class="text-muted small text-uppercase d-block">Notes</label>
                            <p class="mb-0 italic"><?= esc($violation['notes']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($violation['status'] == 'Paid'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Payment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase d-block">Receipt #</label>
                            <span class="fw-bold"><?= esc($violation['receipt_number']) ?></span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase d-block">Date Paid</label>
                            <span><?= date('M d, Y H:i', strtotime($violation['paid_date'])) ?></span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase d-block">Method</label>
                            <span class="badge bg-light text-dark border"><?= esc($violation['payment_method']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Driver & Officer Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Parties Involved</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="text-muted small text-uppercase d-block mb-1">Driver</label>
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3">
                                <i class="bi bi-person fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold"><?= esc($violation['driver_name']) ?></div>
                                <div class="small text-muted"><?= esc($violation['license_plate']) ?></div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-0">
                        <label class="text-muted small text-uppercase d-block mb-1">Issuing Officer</label>
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3">
                                <i class="bi bi-shield-check fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold"><?= esc($violation['officer_name'] ?? 'System') ?></div>
                                <div class="small text-muted">ID: <?= esc($violation['officer_id'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($violation['status'] == 'Pending'): ?>
            <div class="card border-0 shadow-sm no-print">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-danger">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= base_url('penalties/pay/' . $violation['id']) ?>" class="btn btn-success">
                            <i class="bi bi-credit-card me-2"></i> Record Payment
                        </a>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="bi bi-x-circle me-2"></i> Cancel Violation
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<?php if ($violation['status'] == 'Pending'): ?>
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Violation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('penalties/cancel/' . $violation['id']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this violation ticket?</p>
                    <div class="mb-3">
                        <label class="form-label">Reason for Cancellation</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Enter the reason for cancelling this ticket..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
    @media print {
        .no-print, .sidebar, .sidebar-toggle {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
            padding: 0 !important;
        }
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
    }
</style>

<?= $this->endSection() ?>
