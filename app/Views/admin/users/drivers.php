<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Driver Management - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <h4 class="mb-0"><i class="bi bi-people me-2 text-primary"></i>Driver Management</h4>
        </div>
        <div class="col-md-5">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Search by name, email, license plate...">
            </div>
        </div>
        <div class="col-md-3 text-end">
            <a href="/users/create" class="btn btn-primary shadow-sm">
                <i class="bi bi-person-plus me-1"></i> Add New Driver
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="userTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Driver Details</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr id="noDataRow">
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                                    No drivers found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr class="user-row">
                                <td class="ps-4 text-muted font-monospace">#<?= esc($user['id']) ?></td>
                                <td>
                                    <div class="fw-bold"><?= esc($user['username']) ?></div>
                                    <div class="small text-muted"><?= esc($user['email']) ?></div>
                                </td>
                                <td>
                                    <?php if ($user['status'] == 'active'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 rounded-pill">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 rounded-pill"><?= ucfirst(esc($user['status'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm">
                                        <a href="/users/drivers/view/<?= $user['id'] ?>" class="btn btn-sm btn-white border" title="View Violation Records">
                                            <i class="bi bi-eye text-info"></i>
                                        </a>
                                        <a href="/users/edit/<?= $user['id'] ?>" class="btn btn-sm btn-white border" title="Edit Driver">
                                            <i class="bi bi-pencil text-primary"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-white border" title="Reset Password" onclick="confirmResetPassword(<?= $user['id'] ?>, '<?= esc($user['username']) ?>')">
                                            <i class="bi bi-key text-warning"></i>
                                        </button>
                                        <a href="/users/delete/<?= $user['id'] ?>" class="btn btn-sm btn-white border" title="Delete Driver" onclick="return confirm('Are you sure you want to delete this driver?')">
                                            <i class="bi bi-trash text-danger"></i>
                                        </a>
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

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-key me-2"></i>Reset User Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="resetPasswordForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="fw-bold">Reset password for driver <span id="resetUsername" class="text-primary"></span>?</p>
                    <div class="alert alert-warning border-0 shadow-sm" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        A new random password will be generated. You will need to provide this to the user.
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning px-4 shadow-sm">Generate New Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .user-row { transition: all 0.2s ease; }
    .btn-white { background: #fff; }
    .btn-white:hover { background: #f8f9fa; }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Live Search Filtering
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const keyword = this.value.toLowerCase();
        const rows = document.querySelectorAll('.user-row');
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
                const tbody = document.querySelector('#userTable tbody');
                const row = tbody.insertRow();
                row.id = 'noDataRow';
                row.innerHTML = `<td colspan="4" class="text-center py-5 text-muted">No matching drivers found.</td>`;
            }
        } else if (noDataRow) {
            noDataRow.remove();
        }
    });

    function confirmResetPassword(userId, username) {
        document.getElementById('resetUsername').textContent = username;
        const form = document.getElementById('resetPasswordForm');
        form.action = `/users/reset-password/${userId}`;
        var resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
        resetModal.show();
    }
</script>
<?= $this->endSection() ?>
