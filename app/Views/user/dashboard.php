<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>User Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-person-circle fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h4 class="mb-1">Welcome, <?= esc($username) ?>!</h4>
                            <p class="text-muted mb-0">Role: <span class="badge bg-info text-dark text-uppercase"><?= esc($role) ?></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">My Notifications</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 shadow-sm">
                        <i class="bi bi-info-circle me-2"></i> Welcome to the Traffic System. Currently, you have a limited view.
                    </div>
                    <p class="text-muted">Your recent activity and specific traffic alerts will appear here once they are assigned to your profile.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary d-flex align-items-center justify-content-between py-2">
                            View My Profile <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="/logout" class="btn btn-outline-danger d-flex align-items-center justify-content-between py-2">
                            Logout <i class="bi bi-box-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
