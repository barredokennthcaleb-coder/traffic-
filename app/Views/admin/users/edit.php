<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Edit User - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0"><i class="bi bi-pencil me-2 text-primary"></i>Edit User: <?= esc($user['username']) ?></h4>
                </div>
                <div class="card-body p-4">
                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <ul class="mb-0 ps-3">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('users/update/' . $user['id']) ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" id="username" class="form-control" 
                                       placeholder="Enter username" required
                                       value="<?= old('username', $user['username']) ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control" 
                                       placeholder="Enter email address" required
                                       value="<?= old('email', $user['email']) ?>">
                            </div>

                            <div class="col-md-4">
                                <label for="lastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="lastname" id="lastname" class="form-control"
                                       placeholder="Enter last name" required
                                       value="<?= old('lastname', $user['lastname'] ?? '') ?>">
                            </div>

                            <div class="col-md-4">
                                <label for="firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="firstname" id="firstname" class="form-control"
                                       placeholder="Enter first name" required
                                       value="<?= old('firstname', $user['firstname'] ?? '') ?>">
                            </div>

                            <div class="col-md-4">
                                <label for="middle_initial" class="form-label">Middle Initial</label>
                                <input type="text" name="middle_initial" id="middle_initial" class="form-control"
                                       placeholder="M.I." maxlength="10"
                                       value="<?= old('middle_initial', $user['middle_initial'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="birthdate" class="form-label">Birthdate <span class="text-danger">*</span></label>
                                <input type="date" name="birthdate" id="birthdate" class="form-control" required
                                       value="<?= old('birthdate', $user['birthdate'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="age" class="form-label">Age <span class="text-danger">*</span></label>
                                <input type="number" name="age" id="age" class="form-control"
                                       min="1" max="120" step="1" placeholder="Enter age" required
                                       value="<?= old('age', $user['age'] ?? '') ?>">
                            </div>

                            <div class="col-md-12">
                                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" name="address" id="address" class="form-control"
                                       placeholder="Enter address" required
                                       value="<?= old('address', $user['address'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select name="role" id="role" class="form-select" required>
                                    <option value="admin" <?= old('role', $user['role']) == 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="enforcer" <?= old('role', $user['role']) == 'enforcer' ? 'selected' : '' ?>>Traffic Enforcer</option>
                                    <option value="driver" <?= old('role', $user['role']) == 'driver' ? 'selected' : '' ?>>Driver</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="active" <?= old('status', $user['status']) == 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= old('status', $user['status']) == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    <option value="suspended" <?= old('status', $user['status']) == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="password" class="form-label">New Password (Leave blank to keep current)</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter new password">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" aria-label="Show or hide password">
                                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text">Minimum 8 characters.</div>
                            </div>

                            <div class="col-12 mt-4">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <a href="<?= base_url('users') ?>" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Back to User List
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> Update User
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function () {
        const input = document.getElementById('password');
        const btn = document.getElementById('togglePasswordBtn');
        const icon = document.getElementById('togglePasswordIcon');
        if (!input || !btn || !icon) return;

        btn.addEventListener('click', function () {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.classList.toggle('bi-eye', !show);
            icon.classList.toggle('bi-eye-slash', show);
        });
    })();
</script>
<?= $this->endSection() ?>
