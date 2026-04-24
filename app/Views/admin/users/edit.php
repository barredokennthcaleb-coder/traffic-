<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Edit User<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body p-4">
                <form action="/users/update/<?= $user['id'] ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" id="username" required value="<?= old('username', $user['username']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" id="email" required value="<?= old('email', $user['email']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password (Leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control" id="password">
                        <div class="form-text">Minimum 8 characters if provided.</div>
                    </div>

                    <div class="mb-4">
                        <label for="role" class="form-label">Role</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="user" <?= (old('role', $user['role']) == 'user') ? 'selected' : '' ?>>Standard User</option>
                            <option value="admin" <?= (old('role', $user['role']) == 'admin') ? 'selected' : '' ?>>Administrator</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">Update User</button>
                        <a href="/users" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
