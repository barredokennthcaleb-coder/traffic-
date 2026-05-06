<?php $role = session()->get('role'); ?>

<!-- Sidebar -->
<nav class="sidebar p-0">
    <div class="sidebar-toggle">
        <i class="bi bi-chevron-left"></i>
    </div>
    <div class="position-sticky pt-3">
        <div class="text-center mb-3 sidebar-header-text px-3">
            <img src="<?= base_url('img/pic 1.png') ?>" alt="Traffic System Logo" style="width: 58px; height: 58px; object-fit: contain; border-radius: 14px; background: rgba(255,255,255,0.09); padding: 8px; box-shadow: 0 8px 20px rgba(0,0,0,0.22);">
            <div class="mt-2 fw-semibold" style="font-size: 0.95rem; color: rgba(255,255,255,0.9);">Traffic System</div>
        </div>
        <h5 class="text-center mb-4 sidebar-header-text">
            <?php if ($role === 'admin'): ?>
                Traffic Admin
            <?php elseif ($role === 'enforcer'): ?>
                Traffic Enforcer
            <?php else: ?>
                Driver Portal
            <?php endif; ?>
        </h5>
        <ul class="nav flex-column">

            <?php if ($role === 'admin'): ?>
                <!-- Admin Navigation -->
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'dashboard') ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
                        <i class="bi bi-speedometer2"></i>
                        <span class="nav-text ms-2">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'penalties/all') ? 'active' : '' ?>" href="<?= base_url('penalties/all') ?>">
                        <i class="bi bi-list-ul"></i>
                        <span class="nav-text ms-2">Violators</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'violation-types' || strpos(uri_string(), 'violation-types/') === 0) ? 'active' : '' ?>" href="<?= base_url('violation-types') ?>">
                        <i class="bi bi-card-list"></i>
                        <span class="nav-text ms-2">Violation Types</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'penalties') ? 'active' : '' ?>" href="<?= base_url('penalties') ?>">
                        <i class="bi bi-hourglass-split"></i>
                        <span class="nav-text ms-2">Payment</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'penalties/history') ? 'active' : '' ?>" href="<?= base_url('penalties/history') ?>">
                        <i class="bi bi-receipt"></i>
                        <span class="nav-text ms-2">Paid History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'inspections' || strpos(uri_string(), 'inspections/') === 0) ? 'active' : '' ?>" href="<?= base_url('inspections') ?>">
                        <i class="bi bi-file-earmark-check"></i>
                        <span class="nav-text ms-2">Inspection</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'users' || strpos(uri_string(), 'users/') === 0) ? 'active' : '' ?>" href="<?= base_url('users') ?>">
                        <i class="bi bi-people"></i>
                        <span class="nav-text ms-2">Users</span>
                    </a>
                </li>

            <?php elseif ($role === 'enforcer'): ?>
                <!-- Traffic Enforcer Navigation -->
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'officer/profile') ? 'active' : '' ?>" href="<?= base_url('officer/profile') ?>">
                        <i class="bi bi-person-badge"></i>
                        <span class="nav-text ms-2">My Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'officer/violations' || uri_string() == 'officer' || uri_string() == 'officer/') ? 'active' : '' ?>" href="<?= base_url('officer/violations') ?>">
                        <i class="bi bi-list-ul"></i>
                        <span class="nav-text ms-2">Violation</span>
                    </a>
                </li>

            <?php else: ?>
                <!-- Driver portal removed -->
            <?php endif; ?>

            <li class="nav-item mt-5">
                <a class="nav-link text-danger" href="<?= base_url('logout') ?>">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="nav-text ms-2">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</nav>