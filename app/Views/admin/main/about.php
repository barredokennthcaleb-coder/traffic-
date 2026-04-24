<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>About System<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-body p-4">
        <h3 class="mb-4">Traffic Violation & Penalty System</h3>
        <p class="lead">A comprehensive digital solution for managing traffic law enforcement and penalty collection.</p>
        
        <div class="row mt-5">
            <div class="col-md-4">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-check text-primary fs-1"></i>
                    <h5 class="mt-3">Efficient Management</h5>
                    <p class="text-muted">Streamline the process of recording violations and issuing penalties to drivers.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center mb-4">
                    <i class="bi bi-bar-chart-line text-success fs-1"></i>
                    <h5 class="mt-3">Real-time Analytics</h5>
                    <p class="text-muted">Gain insights into traffic patterns and enforcement effectiveness through data visualization.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center mb-4">
                    <i class="bi bi-cash-stack text-info fs-1"></i>
                    <h5 class="mt-3">Revenue Tracking</h5>
                    <p class="text-muted">Monitor penalty payments and manage outstanding fines with automated tracking.</p>
                </div>
            </div>
        </div>

        <hr class="my-5">

        <h4>System Features</h4>
        <ul class="list-group list-group-flush mb-4">
            <li class="list-group-item"><i class="bi bi-check2-circle text-success me-2"></i> Admin authentication and authorization</li>
            <li class="list-group-item"><i class="bi bi-check2-circle text-success me-2"></i> Violation record management</li>
            <li class="list-group-item"><i class="bi bi-check2-circle text-success me-2"></i> Penalty status tracking (Pending, Paid, Cancelled)</li>
            <li class="list-group-item"><i class="bi bi-check2-circle text-success me-2"></i> Statistical reporting and visual analytics</li>
            <li class="list-group-item"><i class="bi bi-check2-circle text-success me-2"></i> Mobile-responsive admin dashboard</li>
        </ul>

        <h4>Version Information</h4>
        <p class="text-muted">System Version: 1.0.0 (Stable)<br>
        Framework: CodeIgniter 4.4.1<br>
        Database: MySQL 8.0</p>
    </div>
</div>

<?= $this->endSection() ?>
