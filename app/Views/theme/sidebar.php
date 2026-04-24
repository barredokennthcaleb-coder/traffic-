<?php $role = session()->get('role'); ?>

<!-- Sidebar -->
<nav class="sidebar p-0">
    <div class="sidebar-toggle">
        <i class="bi bi-chevron-left"></i>
    </div>
    <div class="position-sticky pt-3">
        <h5 class="text-center mb-4 sidebar-header-text">
            <?php if ($role === 'admin'): ?>
                Traffic Admin
            <?php elseif ($role === 'traffic_officer'): ?>
                Traffic Officer
            <?php else: ?>
                Driver Portal
            <?php endif; ?>
        </h5>
        <ul class="nav flex-column">

            <?php if ($role === 'admin'): ?>
                <!-- Admin Navigation -->
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'dashboard') ? 'active' : '' ?>" href="/dashboard">
                        <i class="bi bi-speedometer2"></i>
                        <span class="nav-text ms-2">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'penalties/all') ? 'active' : '' ?>" href="/penalties/all">
                        <i class="bi bi-list-ul"></i>
                        <span class="nav-text ms-2">All Violations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'penalties') ? 'active' : '' ?>" href="/penalties">
                        <i class="bi bi-hourglass-split"></i>
                        <span class="nav-text ms-2">Pending</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'penalties/history') ? 'active' : '' ?>" href="/penalties/history">
                        <i class="bi bi-receipt"></i>
                        <span class="nav-text ms-2">Paid History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'analytics') ? 'active' : '' ?>" href="/analytics">
                        <i class="bi bi-graph-up"></i>
                        <span class="nav-text ms-2">Analytics</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'users/enforcers') ? 'active' : '' ?>" href="/users/enforcers">
                        <i class="bi bi-shield-shaded"></i>
                        <span class="nav-text ms-2">Traffic Enforcers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'users/drivers' || strpos(uri_string(), 'users/drivers/') === 0) ? 'active' : '' ?>" href="/users/drivers">
                        <i class="bi bi-people"></i>
                        <span class="nav-text ms-2">Drivers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'violation-types' || strpos(uri_string(), 'violation-types/') === 0) ? 'active' : '' ?>" href="/violation-types">
                        <i class="bi bi-card-list"></i>
                        <span class="nav-text ms-2">Violation Types</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'about') ? 'active' : '' ?>" href="/about">
                        <i class="bi bi-info-circle"></i>
                        <span class="nav-text ms-2">About System</span>
                    </a>
                </li>

            <?php elseif ($role === 'traffic_officer'): ?>
                <!-- Traffic Officer Navigation -->
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'officer' || uri_string() == 'officer/') ? 'active' : '' ?>" href="/officer">
                        <i class="bi bi-clipboard-plus"></i>
                        <span class="nav-text ms-2">Record Violation</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'officer/violations') ? 'active' : '' ?>" href="/officer/violations">
                        <i class="bi bi-list-ul"></i>
                        <span class="nav-text ms-2">My Violations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'user/dashboard') ? 'active' : '' ?>" href="/user/dashboard">
                        <i class="bi bi-person"></i>
                        <span class="nav-text ms-2">Driver Portal</span>
                    </a>
                </li>

            <?php else: ?>
                <!-- Driver Navigation -->
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'user/dashboard') ? 'active' : '' ?>" href="/user/dashboard">
                        <i class="bi bi-speedometer2"></i>
                        <span class="nav-text ms-2">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'user/violations') ? 'active' : '' ?>" href="/user/violations">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span class="nav-text ms-2">My Violations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'user/history') ? 'active' : '' ?>" href="/user/history">
                        <i class="bi bi-receipt"></i>
                        <span class="nav-text ms-2">Payment History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'about') ? 'active' : '' ?>" href="/about">
                        <i class="bi bi-info-circle"></i>
                        <span class="nav-text ms-2">About System</span>
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item mt-5">
                <a class="nav-link text-danger" href="/logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="nav-text ms-2">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</nav>