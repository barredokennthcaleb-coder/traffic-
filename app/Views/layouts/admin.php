<?php $layoutRole = (string) session()->get('role'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Traffic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --transition-speed: 0.3s;
            --brand-primary: #667eea;
            --brand-secondary: #764ba2;
            --brand-gradient: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
            --surface-bg: #f5f7ff;
            --surface-muted: #eef1ff;
            --text-strong: #1f2440;
        }

        body {
            background-color: var(--surface-bg);
            overflow-x: hidden;
            color: #2f3555;
        }

        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, #1e2237 0%, #262c49 100%);
            color: white;
            transition: width var(--transition-speed) ease;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            white-space: nowrap;
            overflow: hidden;
            transition: padding var(--transition-speed) ease;
        }

        .sidebar.collapsed a {
            padding: 12px 0;
            justify-content: center;
        }

        .sidebar a i {
            font-size: 1.25rem;
            min-width: 40px;
            text-align: center;
        }

        .sidebar .nav-text {
            transition: opacity var(--transition-speed) ease, visibility var(--transition-speed) ease;
            opacity: 1;
            visibility: visible;
        }

        .sidebar.collapsed .nav-text,
        .sidebar.collapsed .sidebar-header-text {
            opacity: 0;
            visibility: hidden;
            width: 0;
            display: none;
        }

        .sidebar a:hover, .sidebar a.active {
            color: white;
            background: rgba(255, 255, 255, 0.12);
        }

        .sidebar-toggle {
            position: absolute;
            right: -20px;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background: var(--brand-gradient);
            border: 3px solid white;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1001;
            transition: all var(--transition-speed) ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            font-size: 1.2rem;
        }

        .sidebar-toggle:hover {
            filter: brightness(0.95);
            transform: translateY(-50%) scale(1.15);
            box-shadow: 0 6px 12px rgba(0,0,0,0.3);
        }

        .sidebar.collapsed .sidebar-toggle {
            transform: translateY(-50%) rotate(180deg);
        }

        .sidebar.collapsed .sidebar-toggle:hover {
            transform: translateY(-50%) rotate(180deg) scale(1.15);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left var(--transition-speed) ease;
            width: calc(100% - var(--sidebar-width));
            padding: 20px;
        }

        .sidebar.collapsed + .main-content {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }

        .card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 12px 35px rgba(42, 56, 119, 0.08);
        }

        .btn-primary {
            border: none;
            background: var(--brand-gradient);
        }

        .btn-primary:hover {
            filter: brightness(0.96);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.2);
        }

        .main-content .h2 {
            color: var(--text-strong);
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .alert {
            border: none;
            border-radius: 12px;
        }

        body.role-enforcer .card-header,
        body.role-enforcer .table-light {
            background: var(--surface-muted) !important;
        }

        body.role-enforcer .border-bottom {
            border-color: #dbe2ff !important;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: calc(-1 * var(--sidebar-width));
            }
            .sidebar.active {
                left: 0;
            }
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="role-<?= esc($layoutRole) ?>">

<div class="d-flex">
    <?= $this->include('theme/sidebar') ?>

    <!-- Main Content -->
    <div class="main-content">
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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.sidebar');
        const toggle = document.querySelector('.sidebar-toggle');
        
        if (toggle) {
            toggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                
                // Optional: Save state to localStorage
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            });
        }

        // Restore state
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            sidebar.classList.add('collapsed');
        }
    });
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
