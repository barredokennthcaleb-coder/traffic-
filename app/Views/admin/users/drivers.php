<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Driver Management - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0"><i class="bi bi-people me-2 text-primary"></i>Driver Management</h4>
        </div>
        <div class="col-md-6 text-end">
            <a href="/users/create" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i> Add New Driver
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                                    No drivers found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= esc($user['id']) ?></td>
                                <td><?= esc($user['username']) ?></td>
                                <td><?= esc($user['email']) ?></td>
                                <td>
                                    <?php if ($user['status'] == 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= esc($user['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="/users/drivers/view/<?= $user['id'] ?>" class="btn btn-sm btn-info text-white" title="View Violation Records">
                                        <i class="bi bi-eye me-1"></i> Records
                                    </a>
                                    <a href="/users/edit/<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit Driver">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-warning" title="Reset Password" onclick="confirmResetPassword(<?= $user['id'] ?>, '<?= esc($user['username']) ?>')">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <a href="/users/delete/<?= $user['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete Driver" onclick="return confirm('Are you sure you want to delete this driver?')">
                                        <i class="bi bi-trash"></i>
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

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel">Reset User Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="resetPasswordForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p>Are you sure you want to reset the password for user <strong id="resetUsername"></strong>?</p>
                    <div class="alert alert-warning" role="alert">
                        A new random password will be generated. You will need to provide this to the user.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function confirmResetPassword(userId, username) {
        document.getElementById('resetUsername').textContent = username;
        const form = document.getElementById('resetPasswordForm');
        form.action = `/users/reset-password/${userId}`;
        var resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
        resetModal.show();
    }
</script>
<?= $this->endSection() ?>
