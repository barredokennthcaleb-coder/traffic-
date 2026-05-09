<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>User Management - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">User Management</h4>
            <p class="text-muted small mb-0">Manage and monitor system users across all roles.</p>
        </div>
        <a href="<?= base_url('users/create') ?>" class="btn btn-primary px-4 py-2 shadow-sm d-flex align-items-center justify-content-center">
            <i class="bi bi-person-plus-fill me-2"></i>
            <span>Add New User</span>
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-3">
            <form method="GET" action="<?= base_url('users') ?>" class="row g-3 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control bg-light border-0 ps-0" placeholder="Search users...">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select border-0 bg-light" name="role" onchange="this.form.submit()">
                        <?php $selectedRole = $selectedRole ?? ''; ?>
                        <option value="" <?= $selectedRole === '' ? 'selected' : '' ?>>All Roles</option>
                        <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Administrators</option>
                        <option value="driver" <?= $selectedRole === 'driver' ? 'selected' : '' ?>>Drivers</option>
                        <option value="enforcer" <?= $selectedRole === 'enforcer' ? 'selected' : '' ?>>Traffic Enforcers</option>
                    </select>
                </div>
                <div class="col-12 col-md-5 d-flex justify-content-md-end gap-2">
                    <button type="button" class="btn btn-light border-0 px-3" onclick="window.location.href='<?= base_url('users') ?>'">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 12px;" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div><?= session()->getFlashdata('success') ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 12px;" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div><?= session()->getFlashdata('error') ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Users Table/Card Container -->
    <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 20px;">
        <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0" id="userTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-muted small fw-bold text-uppercase">ID</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase">User / Email</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase">Full Name</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase text-center">Age</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase">Role</th>
                        <th class="py-3 text-muted small fw-bold text-uppercase">Status</th>
                        <th class="pe-4 py-3 text-muted small fw-bold text-uppercase text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr id="noDataRow">
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-person-x fs-1 d-block mb-3 opacity-25"></i>
                                    <p class="mb-0 fw-semibold">No users found in this category.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr class="user-row">
                            <td class="ps-4">
                                <span class="text-muted fw-mono small">#<?= esc($user['id']) ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= esc($user['username']) ?></div>
                                        <div class="text-muted small"><?= esc($user['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= esc($user['firstname'] . ' ' . $user['lastname']) ?></div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark fw-normal"><?= esc((string) ($user['age'] ?? '-')) ?></span>
                            </td>
                            <td>
                                <?php 
                                    $roleBadge = [
                                        'admin' => 'bg-danger-subtle text-danger',
                                        'enforcer' => 'bg-primary-subtle text-primary',
                                        'driver' => 'bg-success-subtle text-success'
                                    ];
                                    $badgeClass = $roleBadge[$user['role']] ?? 'bg-secondary-subtle text-secondary';
                                ?>
                                <span class="badge <?= $badgeClass ?> text-uppercase px-2" style="font-size: 0.7rem; letter-spacing: 0.05em;"><?= esc($user['role']) ?></span>
                            </td>
                            <td>
                                <?php if ($user['status'] == 'active'): ?>
                                    <span class="badge bg-success rounded-pill px-3" style="font-size: 0.65rem;">Active</span>
                                <?php elseif ($user['status'] == 'inactive'): ?>
                                    <span class="badge bg-secondary rounded-pill px-3" style="font-size: 0.65rem;">Inactive</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark rounded-pill px-3" style="font-size: 0.65rem;">Suspended</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                                    
                                    <?php if ($user['role'] == 'driver'): ?>
                                    <a href="<?= base_url('users/view/' . $user['id']) ?>" class="btn btn-white btn-sm border-end" title="View Details">
                                        <i class="bi bi-eye text-info"></i>
                                    </a>
                                    <?php elseif ($user['role'] == 'enforcer'): ?>
                                    <a href="<?= base_url('users/view-enforcer/' . $user['id']) ?>" class="btn btn-white btn-sm border-end" title="View Profile">
                                        <i class="bi bi-person-vcard text-info"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('users/edit/' . $user['id']) ?>" class="btn btn-white btn-sm border-end" title="Edit">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </a>
                                    <button type="button" class="btn btn-white btn-sm border-end btn-reset-password" 
                                            title="Reset Password" 
                                            data-id="<?= $user['id'] ?>" 
                                            data-username="<?= esc($user['username']) ?>">
                                        <i class="bi bi-key text-warning"></i>
                                    </button>
                                    <button type="button" class="btn btn-white btn-sm btn-delete" 
                                            title="Delete" 
                                            data-id="<?= $user['id'] ?>"
                                            data-username="<?= esc($user['username']) ?>">
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

        <!-- Mobile/Tablet Card View -->
        <div class="d-lg-none p-3">
            <?php if (empty($users)): ?>
                <div class="text-center py-5 text-muted">No users found.</div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($users as $user): ?>
                    <div class="col-12 user-card">
                        <div class="card border border-light-subtle shadow-sm p-3" style="border-radius: 12px;">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?= esc($user['username']) ?></div>
                                        <div class="text-muted small"><?= esc($user['role']) ?></div>
                                    </div>
                                </div>
                                <span class="badge bg-<?= $user['status'] == 'active' ? 'success' : ($user['status'] == 'inactive' ? 'secondary' : 'warning text-dark') ?> rounded-pill" style="font-size: 0.6rem;">
                                    <?= ucfirst(esc($user['status'])) ?>
                                </span>
                            </div>
                            <div class="mb-3 small">
                                <div class="mb-1"><strong>Name:</strong> <?= esc($user['firstname'] . ' ' . $user['lastname']) ?></div>
                                <div class="mb-1"><strong>Email:</strong> <?= esc($user['email']) ?></div>
                            </div>
                            <div class="d-flex gap-2">
                                <?php if ($user['role'] == 'driver'): ?>
                                <a href="<?= base_url('users/view/' . $user['id']) ?>" class="btn btn-sm btn-outline-info flex-grow-1"><i class="bi bi-eye"></i></a>
                                <?php elseif ($user['role'] == 'enforcer'): ?>
                                <a href="<?= base_url('users/view-enforcer/' . $user['id']) ?>" class="btn btn-sm btn-outline-info flex-grow-1"><i class="bi bi-person-vcard"></i></a>
                                <?php endif; ?>
                                <a href="<?= base_url('users/edit/' . $user['id']) ?>" class="btn btn-sm btn-outline-primary flex-grow-1"><i class="bi bi-pencil"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-warning flex-grow-1 btn-reset-password" data-id="<?= $user['id'] ?>" data-username="<?= esc($user['username']) ?>"><i class="bi bi-key"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-danger flex-grow-1 btn-delete" data-id="<?= $user['id'] ?>" data-username="<?= esc($user['username']) ?>"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Card Footer with Pagination -->
        <div class="card-footer bg-white border-top py-3">
            <div class="pagination-container">
                <?= $pager->links('users', 'bootstrap_pagination') ?>
            </div>
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
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const username = this.getAttribute('data-username');
                
                Swal.fire({
                    title: 'Confirm Delete',
                    text: `Are you sure you want to delete user "${username}"? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `<?= base_url('users/delete') ?>/${userId}`;
                        
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '<?= csrf_token() ?>';
                        csrfInput.value = '<?= csrf_hash() ?>';
                        
                        form.appendChild(csrfInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    });
</script>
<?= $this->endSection() ?>
