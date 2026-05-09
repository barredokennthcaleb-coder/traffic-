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
                <div class="col-md-6"><strong>Status:</strong> <?= esc($violation['status'] ?? '-') ?></div>
                <div class="col-md-6"><strong>Date:</strong> <?= isset($violation['violation_date']) ? date('M d, Y h:i A', strtotime($violation['violation_date'])) : '-' ?></div>
                
                <div class="col-12 mt-4">
                    <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-exclamation-circle me-2"></i>Violation Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Violation Type</th>
                                    <th class="text-end" style="width: 150px;">Penalty</th>
                                    <th class="text-center" style="width: 100px;">Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalFine = 0;
                                $totalPoints = 0;
                                $vList = isset($all_violations) ? $all_violations : [$violation];
                                foreach ($vList as $v): 
                                    $totalFine += (float) ($v['penalty_amount'] ?? 0);
                                    $totalPoints += (int) ($v['points'] ?? 0);
                                ?>
                                <tr>
                                    <td><?= esc($v['violation_type'] ?? '-') ?></td>
                                    <td class="text-end"><?= number_format((float) ($v['penalty_amount'] ?? 0), 2) ?></td>
                                    <td class="text-center"><?= esc((string) ($v['points'] ?? 0)) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td>Total</td>
                                    <td class="text-end text-danger"><?= number_format($totalFine, 2) ?></td>
                                    <td class="text-center"><?= $totalPoints ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
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
