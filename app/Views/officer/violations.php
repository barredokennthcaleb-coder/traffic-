<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>My Recorded Violations<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <h4 class="mb-0"><i class="bi bi-list-ul me-2 text-primary"></i>Recorded Violations</h4>
            <small class="text-muted">Track and filter your recently issued tickets.</small>
        </div>
        <div class="col-md-5">
            <div class="input-group shadow-sm no-print">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Filter recorded violations...">
            </div>
        </div>
        <div class="col-md-3 text-end">
            <button onclick="window.print()" class="btn btn-outline-primary me-2 no-print shadow-sm">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="<?= base_url('officer') ?>" class="btn btn-primary no-print shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> New
            </a>
        </div>
    </div>

    <div class="print-header d-none text-center mb-4">
        <h2 class="fw-bold">Traffic Violation Management System</h2>
        <h4 class="text-muted">Official Officer Record Report</h4>
        <p class="small">Generated on: <?= date('M d, Y H:i') ?></p>
        <hr>
    </div>

    <div class="card border-0 shadow-sm violations-card">
        <div class="card-body p-0">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm m-3 no-print" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="violationTable">
                    <thead class="table-light violations-head">
                        <tr>
                            <th class="ps-4">Ticket ID</th>
                            <th>Driver Name</th>
                            <th>License Plate</th>
                            <th>Violation Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="pe-4">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($violations)): ?>
                            <tr id="noDataRow">
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No violations recorded yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($violations as $violation): ?>
                            <tr class="violation-row">
                                <td class="ps-4"><span class="badge bg-dark-subtle text-dark border border-dark-subtle px-2 font-monospace"><?= esc($violation['ticket_id'] ?? 'N/A') ?></span></td>
                                <td class="fw-bold"><?= esc($violation['driver_name']) ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace px-2"><?= esc($violation['license_plate']) ?></span></td>
                                <td class="small fw-semibold text-muted"><?= esc($violation['violation_type']) ?></td>
                                <td class="fw-bold text-danger">$<?= number_format($violation['penalty_amount'], 2) ?></td>
                                <td>
                                    <?php if ($violation['status'] == 'Pending'): ?>
                                        <span class="badge bg-warning rounded-pill px-3">Pending</span>
                                    <?php elseif ($violation['status'] == 'Paid'): ?>
                                        <span class="badge bg-success rounded-pill px-3">Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger rounded-pill px-3">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-muted small"><?= date('M d, Y', strtotime($violation['violation_date'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .violations-card {
        border: 1px solid #e4e9ff !important;
        overflow: hidden;
    }
    .violations-head {
        background: linear-gradient(180deg, #f3f5ff 0%, #ffffff 100%) !important;
    }
    .violations-head th {
        color: #2f3969;
        font-weight: 700;
        border-bottom-color: #dbe1ff;
    }
    .violation-row {
        transition: all 0.2s ease;
    }
    .violation-row:hover {
        background: #f8faff;
    }
    #searchInput {
        border-color: #dbe1ff;
    }
    @media (max-width: 768px) {
        .container-fluid.py-4 {
            padding-top: 0.5rem !important;
        }
        .row.mb-4.align-items-center > [class*='col-'] {
            margin-bottom: 0.75rem;
        }
        .row.mb-4.align-items-center .text-end {
            text-align: left !important;
        }
        .row.mb-4.align-items-center .text-end .btn {
            width: 100%;
            margin-right: 0 !important;
        }
        .row.mb-4.align-items-center .text-end .btn + .btn {
            margin-top: 0.5rem;
        }
        #violationTable {
            min-width: 760px;
        }
    }
    @media print {
        .no-print, .sidebar, .sidebar-toggle, .btn, .alert, .main-content h1, .breadcrumb {
            display: none !important;
        }
        .print-header {
            display: block !important;
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
        .container-fluid {
            padding: 0 !important;
        }
        body {
            background-color: white !important;
        }
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const keyword = this.value.toLowerCase();
        const rows = document.querySelectorAll('.violation-row');
        let hasResults = false;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(keyword)) {
                row.style.display = '';
                hasResults = true;
            } else {
                row.style.display = 'none';
            }
        });

        const noDataRow = document.getElementById('noDataRow');
        if (!hasResults) {
            if (!noDataRow) {
                const tbody = document.querySelector('#violationTable tbody');
                const row = tbody.insertRow();
                row.id = 'noDataRow';
                row.innerHTML = `<td colspan="7" class="text-center py-5 text-muted">No matching results found.</td>`;
            }
        } else if (noDataRow) {
            noDataRow.remove();
        }
    });
</script>
<?= $this->endSection() ?>
