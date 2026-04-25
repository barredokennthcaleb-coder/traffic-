<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Violation Types - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <h4 class="mb-0"><i class="bi bi-list-ul me-2 text-primary"></i>Violation Types</h4>
        </div>
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Search violation types...">
            </div>
        </div>
        <div class="col-md-3 text-end">
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-circle me-1"></i> Add Violation Type
            </button>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i id="toastIcon" class="bi me-2"></i>
                <strong class="me-auto" id="toastTitle">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="violationTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Violation Name</th>
                            <th>Description</th>
                            <th>Fine Amount</th>
                            <th>Points</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($violationTypes)): ?>
                            <tr id="noDataRow">
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No violation types found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($violationTypes as $type): ?>
                            <tr id="row-<?= $type['id'] ?>" class="violation-row">
                                <td class="ps-4"><strong><?= esc($type['violation_name']) ?></strong></td>
                                <td class="text-muted small"><?= esc($type['description'] ?? 'N/A') ?></td>
                                <td><span class="badge bg-success-subtle text-success border border-success-subtle px-3">$<?= number_format($type['fine_amount'], 2) ?></span></td>
                                <td><span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3"><?= $type['points'] ?> pts</span></td>
                                <td>
                                    <?php if ($type['status'] == 'active'): ?>
                                        <span class="badge bg-success rounded-pill px-3">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill px-3">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm">
                                        <button class="btn btn-sm btn-white border btn-edit" 
                                                data-type="<?= htmlspecialchars(json_encode($type)) ?>" title="Edit">
                                            <i class="bi bi-pencil text-primary"></i>
                                        </button>
                                        <button class="btn btn-sm btn-white border btn-delete" 
                                                data-id="<?= $type['id'] ?>" data-name="<?= esc($type['violation_name']) ?>" title="Delete">
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
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Violation Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addForm">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Violation Name *</label>
                        <input type="text" name="violation_name" class="form-control shadow-sm" required placeholder="e.g. Over Speeding">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control shadow-sm" rows="2" placeholder="Brief description of the violation..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Fine Amount ($) *</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="fine_amount" class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Points *</label>
                                <input type="number" name="points" class="form-control shadow-sm" min="0" value="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select shadow-sm">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" id="saveBtn">Save Violation Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Violation Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Violation Name *</label>
                        <input type="text" name="violation_name" id="edit_violation_name" class="form-control shadow-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" id="edit_description" class="form-control shadow-sm" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Fine Amount ($) *</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="fine_amount" id="edit_fine_amount" class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Points *</label>
                                <input type="number" name="points" id="edit_points" class="form-control shadow-sm" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" id="edit_status" class="form-select shadow-sm">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-white px-4 shadow-sm" id="updateBtn">Update Violation Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
                </div>
                <h5 class="mb-2">Confirm Delete</h5>
                <p class="text-muted small">Are you sure you want to delete <strong id="deleteTargetName"></strong>? This action cannot be undone.</p>
                <div class="d-flex justify-content-center gap-2 mt-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger px-4 shadow-sm" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .violation-row { transition: all 0.3s ease; }
    .violation-row.highlight { background-color: #f0f7ff; }
    .btn-white { background: #fff; }
    .btn-white:hover { background: #f8f9fa; }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const toast = new bootstrap.Toast(document.getElementById('liveToast'));
    let deleteId = null;

    function showToast(message, type = 'success') {
        const title = document.getElementById('toastTitle');
        const icon = document.getElementById('toastIcon');
        const msg = document.getElementById('toastMessage');
        const header = document.querySelector('.toast-header');

        title.textContent = type === 'success' ? 'Success' : 'Error';
        icon.className = `bi me-2 ${type === 'success' ? 'bi-check-circle text-success' : 'bi-exclamation-circle text-danger'}`;
        msg.textContent = message;
        
        toast.show();
    }

    // Search Filtering
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
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
                    row.innerHTML = `<td colspan="6" class="text-center py-5 text-muted">No matching results found.</td>`;
                }
            } else if (noDataRow) {
                noDataRow.remove();
            }
        });
    }

    // Add Submission
    document.getElementById('addForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('saveBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        try {
            const formData = new FormData(this);
            const response = await fetch('<?= base_url('violation-types/store') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();

            if (result.status === 'success') {
                showToast(result.message);
                bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();
                this.reset();
                setTimeout(() => location.reload(), 1000); // Reload to show new row
            } else {
                showToast(Object.values(result.errors).join('\n'), 'error');
            }
        } catch (error) {
            showToast('An unexpected error occurred.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });

    // Edit Modal Populator
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = JSON.parse(this.dataset.type);
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_violation_name').value = data.violation_name;
            document.getElementById('edit_description').value = data.description || '';
            document.getElementById('edit_fine_amount').value = data.fine_amount;
            document.getElementById('edit_points').value = data.points;
            document.getElementById('edit_status').value = data.status;
            
            editModal.show();
        });
    });

    // Update Submission
    document.getElementById('editForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('updateBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

        try {
            const formData = new FormData(this);
            const response = await fetch('<?= base_url('violation-types/update') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();

            if (result.status === 'success') {
                showToast(result.message);
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                
                // Dynamically update the row
                const row = document.getElementById(`row-${result.data.id}`);
                row.classList.add('highlight');
                row.cells[0].innerHTML = `<strong>${result.data.violation_name}</strong>`;
                row.cells[1].textContent = result.data.description || 'N/A';
                row.cells[2].innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle px-3">$${parseFloat(result.data.fine_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</span>`;
                row.cells[3].innerHTML = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3">${result.data.points} pts</span>`;
                row.cells[4].innerHTML = result.data.status === 'active' 
                    ? '<span class="badge bg-success rounded-pill px-3">Active</span>' 
                    : '<span class="badge bg-secondary rounded-pill px-3">Inactive</span>';
                
                setTimeout(() => row.classList.remove('highlight'), 2000);
            } else {
                showToast(Object.values(result.errors).join('\n'), 'error');
            }
        } catch (error) {
            showToast('An unexpected error occurred.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });

    // Delete Confirmation
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteTargetName = document.getElementById('deleteTargetName');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            deleteId = this.dataset.id;
            deleteTargetName.textContent = this.dataset.name;
            deleteModal.show();
        });
    });

    confirmDeleteBtn.addEventListener('click', async function() {
        if (!deleteId) return;
        
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

        try {
            const response = await fetch(`<?= base_url('violation-types/delete') ?>/${deleteId}`, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();

            if (result.status === 'success') {
                showToast(result.message);
                const row = document.getElementById(`row-${deleteId}`);
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            showToast('An unexpected error occurred.', 'error');
        } finally {
            this.disabled = false;
            this.textContent = 'Delete';
            deleteId = null;
        }
    });
</script>
<?= $this->endSection() ?>
