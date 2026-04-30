<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Create User - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0"><i class="bi bi-person-plus me-2 text-primary"></i>Create New User</h4>
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

                    <form action="<?= base_url('users/store') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <?php
                            $defaults = $defaults ?? ['username' => '', 'firstname' => '', 'lastname' => '', 'age' => '', 'address' => '', 'password' => '', 'role' => 'driver'];
                        ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" id="username" class="form-control" 
                                       placeholder="Enter username" required
                                       value="<?= old('username') ?: esc($defaults['username']) ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control" 
                                       placeholder="Enter email address" required
                                       value="<?= old('email') ?>">
                            </div>

                            <div class="col-md-4">
                                <label for="lastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="lastname" id="lastname" class="form-control"
                                       placeholder="Enter last name" required
                                       value="<?= old('lastname') ?: esc($defaults['lastname']) ?>">
                            </div>

                            <div class="col-md-4">
                                <label for="firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="firstname" id="firstname" class="form-control"
                                       placeholder="Enter first name" required
                                       value="<?= old('firstname') ?: esc($defaults['firstname']) ?>">
                            </div>

                            <div class="col-md-4">
                                <label for="middle_initial" class="form-label">Middle Initial</label>
                                <input type="text" name="middle_initial" id="middle_initial" class="form-control"
                                       placeholder="M.I." maxlength="10"
                                       value="<?= old('middle_initial') ?: esc($defaults['middle_initial'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="birthdate" class="form-label">Birthdate <span class="text-danger">*</span></label>
                                <input type="date" name="birthdate" id="birthdate" class="form-control" required
                                       value="<?= old('birthdate') ?: esc($defaults['birthdate'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="age" class="form-label">Age <span class="text-danger">*</span></label>
                                <input type="number" name="age" id="age" class="form-control"
                                       min="1" max="120" step="1" placeholder="Enter age" required
                                       value="<?= old('age') ?: esc((string) $defaults['age']) ?>">
                            </div>

                            <div class="col-md-12">
                                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" name="address" id="address" class="form-control"
                                       placeholder="Enter address" required
                                       value="<?= old('address') ?: esc($defaults['address']) ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" value="<?= esc($defaults['password']) ?>">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" aria-label="Show or hide password">
                                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text">Leave blank to use the default password (if configured).</div>
                            </div>

                            <div class="col-md-6">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select name="role" id="role" class="form-select" required>
                                    <option value="">-- Select Role --</option>
                                    <option value="admin" <?= old('role') == 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="enforcer" <?= old('role') == 'enforcer' ? 'selected' : '' ?>>Traffic Enforcer</option>
                                    <option value="driver" <?= (old('role') == 'driver' || (!old('role') && ($defaults['role'] ?? '') === 'driver')) ? 'selected' : '' ?>>Driver</option>
                                </select>
                            </div>

                            <div class="col-12 mt-4">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <a href="<?= base_url('users') ?>" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Back to User List
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> Create User
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
