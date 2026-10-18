<?php 
    $role = session()->get('role'); 
    $username = session()->get('username') ?? 'User';
    $profileImg = session()->get('profile_image');
    $hasSidebarImg = !empty($profileImg) && is_file(FCPATH . 'uploads/profile/' . $profileImg);
    $initial = strtoupper(substr((string) $username, 0, 1));
?>

<!-- Sidebar -->
<nav class="sidebar p-0">
    <div class="sidebar-toggle" title="Toggle Sidebar">
        <i class="bi bi-chevron-left"></i>
    </div>
    
    <div class="position-sticky pt-3 d-flex flex-column" style="min-height: 100vh;">
        <!-- Brand Header -->
        <div class="sidebar-brand-wrap px-3 mb-3 text-center">
            <div class="brand-logo-container mx-auto">
                <img src="<?= base_url('img/pic-1.png') ?>" alt="Traffic System Logo" class="brand-logo-img">
            </div>
            <div class="brand-title mt-2 fw-bold">Traffic System</div>
            <div class="brand-subtitle text-muted"><?= $role === 'enforcer' ? 'Traffic Enforcer' : 'Citation & Enforcement' ?></div>
        </div>

        <!-- User Quick Card -->
        <div class="sidebar-user-card mx-3 mb-3 p-2 rounded-3">
            <div class="d-flex align-items-center gap-2">
                <div class="sidebar-user-avatar position-relative flex-shrink-0" style="width:36px; height:36px;">
                    <div class="w-100 h-100 rounded-circle overflow-hidden bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="font-size: 0.85rem;">
                        <?php if ($hasSidebarImg): ?>
                            <img src="<?= base_url('uploads/profile/' . esc($profileImg)) ?>" 
                                 alt="" 
                                 class="w-100 h-100 rounded-circle" 
                                 style="object-fit: cover;"
                                 onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                            <span style="display:none;"><?= esc($initial) ?></span>
                        <?php else: ?>
                            <span><?= esc($initial) ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="user-online-dot"></span>
                </div>
                <div class="sidebar-user-info overflow-hidden">
                    <div class="fw-bold text-white text-truncate" style="font-size: 0.85rem;"><?= esc($username) ?></div>
                    <div class="text-white-50 text-uppercase text-truncate" style="font-size: 0.65rem; letter-spacing: 0.05em;">
                        <?= $role === 'enforcer' ? 'Traffic Enforcer' : ($role === 'admin' ? 'System Admin' : 'Driver') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <ul class="nav flex-column sidebar-nav flex-grow-1 px-2">
            <?php if ($role === 'admin'): ?>
                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'dashboard') ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
                        <i class="bi bi-speedometer2 nav-icon"></i>
                        <span class="nav-text ms-2">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'reports' || strpos(uri_string(), 'reports') === 0) ? 'active' : '' ?>" href="<?= base_url('reports') ?>">
                        <i class="bi bi-file-earmark-bar-graph nav-icon"></i>
                        <span class="nav-text ms-2">Reports</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'violation-types' || strpos(uri_string(), 'violation-types/') === 0) ? 'active' : '' ?>" href="<?= base_url('violation-types') ?>">
                        <i class="bi bi-card-list nav-icon"></i>
                        <span class="nav-text ms-2">Violation Types</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'penalties') ? 'active' : '' ?>" href="<?= base_url('penalties') ?>">
                        <i class="bi bi-hourglass-split nav-icon"></i>
                        <span class="nav-text ms-2">Payment</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'penalties/history') ? 'active' : '' ?>" href="<?= base_url('penalties/history') ?>">
                        <i class="bi bi-receipt nav-icon"></i>
                        <span class="nav-text ms-2">Paid History</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'inspections' || strpos(uri_string(), 'inspections/') === 0) ? 'active' : '' ?>" href="<?= base_url('inspections') ?>">
                        <i class="bi bi-file-earmark-check nav-icon"></i>
                        <span class="nav-text ms-2">Inspection</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'users' || strpos(uri_string(), 'users/') === 0) ? 'active' : '' ?>" href="<?= base_url('users') ?>">
                        <i class="bi bi-people nav-icon"></i>
                        <span class="nav-text ms-2">Users</span>
                    </a>
                </li>

            <?php elseif ($role === 'enforcer'): ?>
                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'officer/violations' || uri_string() == 'officer' || uri_string() == 'officer/') ? 'active' : '' ?>" href="<?= base_url('officer/violations') ?>">
                        <i class="bi bi-list-ul nav-icon"></i>
                        <span class="nav-text ms-2">Violations</span>
                    </a>
                </li>

                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'officer/profile') ? 'active' : '' ?>" href="<?= base_url('officer/profile') ?>">
                        <i class="bi bi-person-badge nav-icon"></i>
                        <span class="nav-text ms-2">My Profile</span>
                    </a>
                </li>


            <?php else: ?>
                <!-- Driver portal navigation -->
                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'user/dashboard') ? 'active' : '' ?>" href="<?= base_url('user/dashboard') ?>">
                        <i class="bi bi-speedometer2 nav-icon"></i>
                        <span class="nav-text ms-2">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'user/violations') ? 'active' : '' ?>" href="<?= base_url('user/violations') ?>">
                        <i class="bi bi-exclamation-triangle nav-icon"></i>
                        <span class="nav-text ms-2">My Violations</span>
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item mt-auto pt-3 pb-3">
                <a class="nav-link nav-link-logout" href="<?= base_url('logout') ?>">
                    <i class="bi bi-box-arrow-right nav-icon"></i>
                    <span class="nav-text ms-2">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
.brand-logo-container {
    width: 58px;
    height: 58px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    padding: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease;
}
.brand-logo-container:hover {
    transform: scale(1.05);
}
.brand-logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.brand-title {
    font-size: 0.98rem;
    color: #fff;
    letter-spacing: -0.01em;
}
.brand-subtitle {
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.6);
}
.sidebar-user-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    transition: all 0.25s ease;
}
.sidebar-user-card:hover {
    background: rgba(255, 255, 255, 0.09);
    border-color: rgba(255, 255, 255, 0.15);
}
.sidebar-user-avatar {
    width: 36px;
    height: 36px;
    flex-shrink: 0;
}
.user-online-dot {
    position: absolute;
    bottom: -1px;
    right: -1px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #10b981;
    border: 2px solid #1b233a;
    box-shadow: 0 0 6px rgba(16, 185, 129, 0.8);
}
.sidebar-nav .nav-link {
    border-radius: 12px;
    padding: 0.65rem 1rem;
    font-size: 0.9rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.75);
    display: flex;
    align-items: center;
    transition: all 0.22s cubic-bezier(0.2, 0, 0, 1);
    position: relative;
}
.sidebar-nav .nav-link:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.08);
    transform: translateX(4px);
}
.sidebar-nav .nav-link.active {
    color: #fff;
    background: rgba(255, 255, 255, 0.14);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
    font-weight: 600;
    border: 1px solid rgba(255, 255, 255, 0.1);
}
.sidebar-nav .nav-link.active::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 20px;
    border-radius: 4px;
    background: #8b5cf6;
}
.nav-icon {
    font-size: 1.15rem;
    width: 24px;
    text-align: center;
}
.nav-link-logout {
    color: #f87171 !important;
    background: rgba(239, 68, 68, 0.06);
    border: 1px solid rgba(239, 68, 68, 0.15);
}
.nav-link-logout:hover {
    color: #fff !important;
    background: #dc2626 !important;
    box-shadow: 0 4px 16px rgba(220, 38, 38, 0.35);
    transform: translateX(4px);
}
</style>
