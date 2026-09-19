<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Search Results: "<?= esc($keyword) ?>"<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0"><i class="bi bi-search me-2 text-primary"></i>Search Results</h4>
            <p class="text-muted small">Found <strong><?= count($violations) ?></strong> results for "<?= esc($keyword) ?>"</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= base_url('penalties/all') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to All Violations
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Ticket ID</th>
                            <th>Driver</th>
                            <th>Violation Type</th>
                            <th>Amount</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($violations)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-search fs-1 d-block mb-2"></i>
                                    No results found matching your search.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($violations as $v): ?>
                            <tr>
                                <td><span class="badge bg-dark"><?= esc($v['ticket_id']) ?></span></td>
                                <td>
                                    <div class="fw-bold"><?= esc($v['driver_name']) ?></div>
                                    <small class="text-muted"><?= esc($v['license_plate']) ?></small>
                                </td>
                                <td class="small fw-semibold text-dark">
                                    <?php 
                                    $rawTypes = !empty($v['concatenated_violations']) ? $v['concatenated_violations'] : $v['violation_type'];
                                    $types = explode('||', $rawTypes);
                                    echo '<ul class="list-unstyled mb-0 gap-1 d-flex flex-column">';
                                    foreach ($types as $t) {
                                        $parts = explode('::', $t);
                                        $vName = $parts[0];
                                        echo '<li><i class="bi bi-dot me-1 text-primary"></i>' . esc($vName) . '</li>';
                                    }
                                    echo '</ul>';
                                    ?>
                                </td>
                                <td class="small font-monospace text-muted">
                                    <?php 
                                    $rawTypes = !empty($v['concatenated_violations']) ? $v['concatenated_violations'] : $v['violation_type'];
                                    $types = explode('||', $rawTypes);
                                    echo '<ul class="list-unstyled mb-0 gap-1 d-flex flex-column">';
                                    foreach ($types as $t) {
                                        $parts = explode('::', $t);
                                        $vAmt  = isset($parts[1]) && $parts[1] !== '' ? (float)$parts[1] : null;
                                        echo '<li>';
                                        if ($vAmt !== null) {
                                            echo '₱' . number_format($vAmt, 2);
                                        } else {
                                            echo '-';
                                        }
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                    ?>
                                </td>
                                <td>
                                    <span class="fw-bold text-danger font-monospace fs-6">₱<?= number_format((float) ($v['total_penalty_sum'] ?? $v['penalty_amount'] ?? 0), 2) ?></span>
                                </td>
                                <td>
                                    <?php if ($v['status'] == 'Pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php elseif ($v['status'] == 'Paid'): ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($v['violation_date'])) ?></td>
                                <td class="text-end">
                                    <a href="<?= base_url('penalties/view/' . $v['id']) ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
