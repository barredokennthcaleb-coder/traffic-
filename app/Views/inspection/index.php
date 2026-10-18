<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Inspections - Traffic System<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="mb-0 fw-bold"><i class="bi bi-file-earmark-check me-2 text-primary"></i>Inspection Reports</h4>
            <small class="text-muted">Manage and print vehicle inspection reports.</small>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('inspections/create') ?>" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> New Inspection
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">MTOP #</th>
                            <th>Route</th>
                            <th>Operator</th>
                            <th>Driver</th>
                            <th>MV Make/Type</th>
                            <th>Inspected By</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inspections)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="bi bi-file-earmark-text display-4 text-muted"></i>
                                        <h6 class="mt-3">No Inspection Reports Found</h6>
                                        <p class="text-muted">Start by creating a new inspection report.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inspections as $item): ?>
                                <tr>
                                    <td class="ps-4 font-monospace fw-bold text-primary"><?= esc($item['mtop_no']) ?></td>
                                    <td>
                                        <?php if (!empty($item['route'])): ?>
                                            <span class="badge bg-light text-dark border"><?= esc($item['route']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= esc($item['operator_name']) ?></div>
                                        <small class="text-muted"><?= esc($item['operator_cellphone'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= esc($item['driver_name'] ?? '—') ?></div>
                                        <?php if (!empty($item['driver_license_no'])): ?>
                                            <small class="text-muted">Lic: <?= esc($item['driver_license_no']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div><?= esc($item['mv_make_type'] ?? '—') ?></div>
                                        <?php if (!empty($item['body_color'])): ?>
                                            <small class="text-muted">Color: <?= esc($item['body_color']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($item['inspected_by']) ?></td>
                                    <td><?= date('M d, Y', strtotime($item['inspection_date'])) ?></td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm">
                                            <a href="<?= base_url('inspections/print/' . $item['id']) ?>" class="btn btn-sm btn-white border" title="Print Report">
                                                <i class="bi bi-printer text-info"></i>
                                            </a>
                                            <a href="<?= base_url('inspections/edit/' . $item['id']) ?>" class="btn btn-sm btn-white border" title="Edit Report">
                                                <i class="bi bi-pencil text-primary"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-white border btn-delete" data-id="<?= $item['id'] ?>" title="Delete">
                                                <i class="bi bi-trash text-danger"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (isset($pager)): ?>
            <div class="card-footer bg-white border-top-0 py-3">
                <?= $pager->links('inspections', 'bootstrap_pagination') ?>
            </div>
        <?php endif; ?>
    </div>
</div>



<style>
    .btn-white { background: #fff; }
    .btn-white:hover { background: #f8f9fa; }
    .empty-state { padding: 3rem 0; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                (window.AppConfirm || Swal.fire)({
                    title: 'Delete Inspection Report?',
                    text: 'Are you sure you want to delete this inspection report? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Yes, Delete',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#ef4444',
                    reverseButtons: true
                }).then(res => {
                    if (res.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `<?= base_url('inspections/delete') ?>/${id}`;
                        
                        const csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '<?= csrf_token() ?>';
                        csrf.value = '<?= csrf_hash() ?>';
                        
                        form.appendChild(csrf);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    });
</script>
<?= $this->endSection() ?>
