<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Violation Details<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-ticket-detailed me-2 text-primary"></i>Ticket <?= esc($violation['ticket_id'] ?? 'N/A') ?></h5>
            <a href="<?= base_url('officer/violations') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6"><strong>First Name:</strong> <?= esc($violation['first_name'] ?? '-') ?></div>
                <div class="col-md-6"><strong>Last Name:</strong> <?= esc($violation['last_name'] ?? '-') ?></div>
                <div class="col-md-6"><strong>Age:</strong> <?= esc((string) ($violation['age'] ?? '-')) ?></div>
                <div class="col-md-6"><strong>Address:</strong> <?= esc($violation['address'] ?? '-') ?></div>
                <div class="col-md-6"><strong>Driver Name:</strong> <?= esc($violation['driver_name'] ?? '-') ?></div>
                <div class="col-md-6"><strong>License Plate:</strong> <?= esc($violation['license_plate'] ?? '-') ?></div>
                <div class="col-md-6"><strong>Violation Type:</strong> <?= esc($violation['violation_type'] ?? '-') ?></div>
                <div class="col-md-6"><strong>Penalty Amount:</strong> $<?= number_format((float) ($violation['penalty_amount'] ?? 0), 2) ?></div>
                <div class="col-md-6"><strong>Status:</strong> <?= esc($violation['status'] ?? '-') ?></div>
                <div class="col-md-6"><strong>Date:</strong> <?= isset($violation['violation_date']) ? date('M d, Y h:i A', strtotime($violation['violation_date'])) : '-' ?></div>
                <div class="col-md-6"><strong>Location:</strong> <?= esc($violation['location'] ?? '-') ?></div>
                <div class="col-md-6"><strong>Officer:</strong> <?= esc($violation['officer_name'] ?? '-') ?></div>
                <div class="col-12"><strong>Notes:</strong> <?= esc($violation['notes'] ?? '-') ?></div>
                <div class="col-12"><strong>Remarks:</strong> <?= esc($violation['remarks'] ?? '-') ?></div>
            </div>
        </div>
    </div>
</div>
<?php $isPrintMode = service('request')->getGet('print') === '1'; ?>
<?php $fromViolations = service('request')->getGet('from') === 'violations'; ?>
<?php if ($isPrintMode): ?>
<style>
    @media print {
        .sidebar, .sidebar-toggle, .mobile-topbar, .desktop-header, .btn, .breadcrumb {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
            padding: 0 !important;
        }
        .container-fluid {
            padding: 0 !important;
        }
    }
</style>
<script>
    window.addEventListener('load', function () {
        window.print();
    });

    <?php if ($fromViolations): ?>
    window.addEventListener('afterprint', function () {
        window.location.href = '<?= base_url('officer/violations') ?>';
    });
    <?php endif; ?>
</script>
<?php endif; ?>
<?= $this->endSection() ?>
