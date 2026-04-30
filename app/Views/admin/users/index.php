<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>User Management - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <h4 class="mb-0"><i class="bi bi-people me-2 text-primary"></i>User Management</h4>
        </div>
        <div class="col-md-5">
            <div class="d-flex gap-2">
                <form method="GET" action="<?= base_url('users') ?>" class="d-flex gap-2 flex-grow-1">
                    <select class="form-select shadow-sm" name="role" aria-label="Filter by role" onchange="this.form.submit()">
                        <?php $selectedRole = $selectedRole ?? ''; ?>
                        <option value="" <?= $selectedRole === '' ? 'selected' : '' ?>>All roles</option>
                        <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="driver" <?= $selectedRole === 'driver' ? 'selected' : '' ?>>Driver</option>
                        <option value="enforcer" <?= $selectedRole === 'enforcer' ? 'selected' : '' ?>>Enforcer</option>
                    </select>
                    <div class="input-group shadow-sm flex-grow-1">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Search by username, full name, email...">
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-3 text-end">
            <a href="<?= base_url('users/create') ?>" class="btn btn-primary shadow-sm">
                <i class="bi bi-person-plus me-1"></i> Add New User
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-premium-mobile users-table" id="userTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 col-id">ID</th>
                            <th class="col-username">Username</th>
                            <th class="col-fullname">Full Name</th>
                            <th class="col-age text-center">Age</th>
                            <th class="col-address">Address</th>
                            <th class="col-email">Email</th>
                            <th class="col-role text-center">Role</th>
                            <th class="col-status text-center">Status</th>
                            <th class="text-end pe-4 col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr id="noDataRow">
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                                    No users found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr class="user-row">
                                <td class="ps-4 col-id" data-label="ID"><?= esc($user['id']) ?></td>
                                <td class="col-username text-truncate" data-label="Username" title="<?= esc($user['username']) ?>"><?= esc($user['username']) ?></td>
                                <td class="col-fullname text-truncate" data-label="Full Name" title="<?= esc(trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?: '-') ?>"><?= esc(trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?: '-') ?></td>
                                <td class="col-age text-center" data-label="Age"><?= esc((string) ($user['age'] ?? '-')) ?></td>
                                <td class="col-address text-truncate" data-label="Address" title="<?= esc($user['address'] ?? '-') ?>"><?= esc($user['address'] ?? '-') ?></td>
                                <td class="col-email text-truncate" data-label="Email" title="<?= esc($user['email']) ?>"><?= esc($user['email']) ?></td>
                                <td class="col-role text-center" data-label="Role"><span class="badge bg-info-subtle text-info border border-info-subtle text-uppercase px-2"><?= esc($user['role']) ?></span></td>
                                <td class="col-status text-center" data-label="Status">
                                    <?php if ($user['status'] == 'active'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 rounded-pill">Active</span>
                                    <?php elseif ($user['status'] == 'inactive'): ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 rounded-pill">Inactive</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 rounded-pill">Suspended</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 col-actions" data-label="Actions">
                                    <div class="btn-group shadow-sm actions-group">
                                        <?php if ($user['role'] == 'driver'): ?>
                                        <a href="<?= base_url('users/view/' . $user['id']) ?>" class="btn btn-sm btn-white border" title="View Details">
                                            <i class="bi bi-eye text-info"></i>
                                        </a>
                                        <?php elseif ($user['role'] == 'enforcer'): ?>
                                        <a href="<?= base_url('users/view-enforcer/' . $user['id']) ?>" class="btn btn-sm btn-white border" title="View Enforcer Profile">
                                            <i class="bi bi-person-vcard text-info"></i>
                                        </a>
                                        <?php endif; ?>
                                        <a href="<?= base_url('users/edit/' . $user['id']) ?>" class="btn btn-sm btn-white border" title="Edit User">
                                            <i class="bi bi-pencil text-primary"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-white border btn-reset-password" 
                                                title="Reset Password" 
                                                data-id="<?= $user['id'] ?>" 
                                                data-username="<?= esc($user['username']) ?>">
                                            <i class="bi bi-key text-warning"></i>
                                        </button>
                                        <a href="<?= base_url('users/delete/' . $user['id']) ?>" class="btn btn-sm btn-white border btn-delete" 
                                           title="Delete User" 
                                           data-username="<?= esc($user['username']) ?>">
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
                    <p class="fw-bold">Reset password for <span id="resetUsername" class="text-primary"></span>?</p>
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
    .btn-white { background: #fff; }
    .btn-white:hover { background: #f8f9fa; }
    .users-table { table-layout: fixed; }
    .users-table th,
    .users-table td { vertical-align: middle; }
    .users-table .col-id { width: 56px; }
    .users-table .col-username { width: 120px; }
    .users-table .col-fullname { width: 140px; }
    .users-table .col-age { width: 60px; white-space: nowrap; }
    .users-table .col-address { width: 150px; }
    .users-table .col-email { width: 170px; }
    .users-table .col-role { width: 95px; white-space: nowrap; }
    .users-table .col-status { width: 105px; white-space: nowrap; }
    .users-table .col-actions { width: 150px; white-space: nowrap; }
    .users-table .actions-group { flex-wrap: nowrap; }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Live Search Filtering
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
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
                        row.innerHTML = `<td colspan="9" class="text-center py-5 text-muted">No matching users found.</td>`;
                    }
                } else if (noDataRow) {
                    noDataRow.remove();
                }
            });
        }

        // Reset Password Modal
        const resetPasswordButtons = document.querySelectorAll('.btn-reset-password');
        const resetUsernameSpan = document.getElementById('resetUsername');
        const resetPasswordForm = document.getElementById('resetPasswordForm');
        const resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));

        resetPasswordButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const username = this.getAttribute('data-username');
                
                resetUsernameSpan.textContent = username;
                resetPasswordForm.action = `<?= base_url('users/reset-password') ?>/${userId}`;
                resetModal.show();
            });
        });

        // Delete Confirmation
        const deleteButtons = document.querySelectorAll('.btn-delete');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const username = this.getAttribute('data-username');
                if (!confirm(`Are you sure you want to delete user "${username}"?`)) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>
