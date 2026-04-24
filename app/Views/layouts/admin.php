<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Traffic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 10px 20px;
            display: block;
        }
        .sidebar a:hover, .sidebar a.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        .main-content {
            padding: 20px;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-0">
            <div class="position-sticky pt-3">
                <h5 class="text-center mb-4">Traffic Admin</h5>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= (uri_string() == 'dashboard') ? 'active' : '' ?>" href="/dashboard">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (uri_string() == 'analytics') ? 'active' : '' ?>" href="/analytics">
                            <i class="bi bi-graph-up me-2"></i> Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (uri_string() == 'about') ? 'active' : '' ?>" href="/about">
                            <i class="bi bi-info-circle me-2"></i> About System
                        </a>
                    </li>
                    <li class="nav-item mt-5">
                        <a class="nav-link text-danger" href="/logout">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?= $this->renderSection('title') ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="text-muted">Welcome, <?= session()->get('username') ?></span>
                </div>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
