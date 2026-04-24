<?php $role = session()->get('role'); ?>

<!-- Sidebar -->
<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-0">
    <div class="position-sticky pt-3">
        <h5 class="text-center mb-4">
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
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'analytics') ? 'active' : '' ?>" href="/analytics">
                        <i class="bi bi-graph-up me-2"></i> Analytics
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'penalties' || strpos(uri_string(), 'penalties/') === 0) ? 'active' : '' ?>" href="/penalties">
                        <i class="bi bi-wallet2 me-2"></i> Penalty Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'users' || strpos(uri_string(), 'users/') === 0) ? 'active' : '' ?>" href="/users">
                        <i class="bi bi-people me-2"></i> User Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'violation-types' || strpos(uri_string(), 'violation-types/') === 0) ? 'active' : '' ?>" href="/violation-types">
                        <i class="bi bi-list-ul me-2"></i> Violation Types
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'about') ? 'active' : '' ?>" href="/about">
                        <i class="bi bi-info-circle me-2"></i> About System
                    </a>
                </li>

            <?php elseif ($role === 'traffic_officer'): ?>
                <!-- Traffic Officer Navigation -->
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'officer' || uri_string() == 'officer/') ? 'active' : '' ?>" href="/officer">
                        <i class="bi bi-clipboard-plus me-2"></i> Record Violation
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'officer/violations') ? 'active' : '' ?>" href="/officer/violations">
                        <i class="bi bi-list-ul me-2"></i> My Violations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'user/dashboard') ? 'active' : '' ?>" href="/user/dashboard">
                        <i class="bi bi-person me-2"></i> My Driver Portal
                    </a>
                </li>

            <?php else: ?>
                <!-- Driver Navigation -->
                <li class="nav-item">
                    <a class="nav-link <?= (uri_string() == 'user/dashboard') ? 'active' : '' ?>" href="/user/dashboard">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item mt-5">
                <a class="nav-link text-danger" href="/logout">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>