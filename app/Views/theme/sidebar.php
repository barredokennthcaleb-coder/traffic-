<?php 
    $role = session()->get('role'); 
    $username = session()->get('username') ?? 'User';
    $profileImg = session()->get('profile_image');
    $hasSidebarImg = !empty($profileImg) && is_file(FCPATH . 'uploads/profile/' . $profileImg);
    $initial = strtoupper(substr((string) $username, 0, 1));

    // A violator's own record is served from penalties/violator/{id} for
    // historical reasons, but it is part of the Violators registry, not the
    // money screens. Without this the sidebar moved its highlight (and popped
    // the Payment group open) every time someone opened a violator.
    $currentUri        = uri_string();
    $onViolatorProfile = strpos($currentUri, 'penalties/violator/') === 0;

    // A single ticket opened from a violator's record belongs to that section;
    // the same page opened from History stays under Payment. The referring
    // screen says which by passing from=violators.
    $cameFromViolators = (string) (service('request')->getGet('from') ?? '') === 'violators';
    $onViolatorTicket  = strpos($currentUri, 'penalties/view/') === 0 && $cameFromViolators;

    // penalties/all is an "All Violators" list that predates the dedicated
    // directory. It belongs to the Violators section, so opening it (from an
    // old bookmark or a redirect) must not move the highlight to Payment.
    $onLegacyViolatorList = $currentUri === 'penalties/all';
?>

<!-- Sidebar -->
<nav class="sidebar p-0">
    <button type="button" class="sidebar-toggle" title="Toggle Sidebar" aria-label="Collapse sidebar" aria-expanded="true">
        <i class="bi bi-chevron-left" aria-hidden="true"></i>
    </button>
    
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
                    <a class="nav-link <?= (uri_string() == 'dashboard') ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>" aria-label="Dashboard" onclick="try{localStorage.removeItem('admin_analytics_hidden');}catch(e){}">
                        <i class="bi bi-speedometer2 nav-icon"></i>
                        <span class="nav-text ms-2">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link <?= ($currentUri === 'violators' || strpos($currentUri, 'violators/') === 0 || $onViolatorProfile || $onViolatorTicket || $onLegacyViolatorList) ? 'active' : '' ?>" href="<?= base_url('violators') ?>" aria-label="Violators">
                        <i class="bi bi-person-vcard nav-icon"></i>
                        <span class="nav-text ms-2">Violators</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'reports' || strpos(uri_string(), 'reports') === 0) ? 'active' : '' ?>" href="<?= base_url('reports') ?>" aria-label="Reports">
                        <i class="bi bi-file-earmark-bar-graph nav-icon"></i>
                        <span class="nav-text ms-2">Reports</span>
                    </a>
                </li>
                <?php
                    // Payment opens a group instead of navigating: the two money
                    // screens live one level down, so the sidebar stays short and
                    // clicking the row never reloads the page.
                    $onPendingPayments = $currentUri === 'penalties';
                    $onPaidHistory     = $currentUri === 'penalties/history';
                    $onPayments        = $onPendingPayments || $onPaidHistory
                        || (strpos($currentUri, 'penalties/') === 0
                            && ! $onViolatorProfile
                            && ! $onViolatorTicket
                            && ! $onLegacyViolatorList);
                ?>
                <li class="nav-item mb-1">
                    <a class="nav-link nav-group-link <?= $onPayments ? 'active' : '' ?>"
                       href="#paymentSubmenu"
                       data-bs-toggle="collapse"
                       role="button"
                       aria-label="Toggle Payment submenu"
                       aria-expanded="<?= $onPayments ? 'true' : 'false' ?>"
                       aria-controls="paymentSubmenu">
                        <i class="bi bi-wallet2 nav-icon"></i>
                        <span class="nav-text ms-2">Payment</span>
                        <i class="bi bi-chevron-down nav-caret ms-auto"></i>
                    </a>
                    <div class="collapse <?= $onPayments ? 'show' : '' ?>" id="paymentSubmenu">
                        <ul class="nav flex-column sidebar-subnav">
                            <li class="nav-item">
                                <a class="nav-link <?= $onPendingPayments ? 'active' : '' ?>" href="<?= base_url('penalties') ?>" aria-label="Pending payments">
                                    <i class="bi bi-hourglass-split nav-icon"></i>
                                    <span class="nav-text ms-2">Payment</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $onPaidHistory ? 'active' : '' ?>" href="<?= base_url('penalties/history') ?>" aria-label="Payment history">
                                    <i class="bi bi-receipt nav-icon"></i>
                                    <span class="nav-text ms-2">History</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'inspections' || strpos(uri_string(), 'inspections/') === 0) ? 'active' : '' ?>" href="<?= base_url('inspections') ?>" aria-label="Inspection">
                        <i class="bi bi-file-earmark-check nav-icon"></i>
                        <span class="nav-text ms-2">Inspection</span>
                    </a>
                </li>
                <?php
                    // Violation Types and Users are configuration screens, so they
                    // live inside one Settings group instead of taking two slots.
                    $onViolationTypes = uri_string() === 'violation-types' || strpos(uri_string(), 'violation-types/') === 0;
                    $onUsers          = uri_string() === 'users' || strpos(uri_string(), 'users/') === 0;
                    $onSettings       = $onViolationTypes || $onUsers;
                ?>
                <li class="sidebar-nav-divider" aria-hidden="true"></li>
                <li class="nav-item mb-1">
                    <a class="nav-link nav-group-link <?= $onSettings ? 'active' : '' ?>"
                       href="#settingsSubmenu"
                       data-bs-toggle="collapse"
                       role="button"
                       aria-label="Toggle Settings submenu"
                       aria-expanded="<?= $onSettings ? 'true' : 'false' ?>"
                       aria-controls="settingsSubmenu">
                        <i class="bi bi-gear nav-icon"></i>
                        <span class="nav-text ms-2">Settings</span>
                        <i class="bi bi-chevron-down nav-caret ms-auto"></i>
                    </a>
                    <div class="collapse <?= $onSettings ? 'show' : '' ?>" id="settingsSubmenu">
                        <ul class="nav flex-column sidebar-subnav">
                            <li class="nav-item">
                                <a class="nav-link <?= $onViolationTypes ? 'active' : '' ?>" href="<?= base_url('violation-types') ?>" aria-label="Violation types">
                                    <i class="bi bi-card-list nav-icon"></i>
                                    <span class="nav-text ms-2">Violation Types</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $onUsers ? 'active' : '' ?>" href="<?= base_url('users') ?>" aria-label="Users">
                                    <i class="bi bi-people nav-icon"></i>
                                    <span class="nav-text ms-2">Users</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

            <?php elseif ($role === 'enforcer'): ?>
                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'officer/violations' || uri_string() == 'officer' || uri_string() == 'officer/' || strpos(uri_string(), 'officer/violator-profile/') === 0) ? 'active' : '' ?>" href="<?= base_url('officer/violations') ?>" aria-label="Violations">
                        <i class="bi bi-list-ul nav-icon"></i>
                        <span class="nav-text ms-2">Violations</span>
                    </a>
                </li>

                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'officer/profile') ? 'active' : '' ?>" href="<?= base_url('officer/profile') ?>" aria-label="My profile">
                        <i class="bi bi-person-badge nav-icon"></i>
                        <span class="nav-text ms-2">My Profile</span>
                    </a>
                </li>


            <?php else: ?>
                <!-- Driver portal navigation -->
                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'user/dashboard') ? 'active' : '' ?>" href="<?= base_url('user/dashboard') ?>" aria-label="Dashboard">
                        <i class="bi bi-speedometer2 nav-icon"></i>
                        <span class="nav-text ms-2">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link <?= (uri_string() == 'user/violations') ? 'active' : '' ?>" href="<?= base_url('user/violations') ?>" aria-label="My violations">
                        <i class="bi bi-exclamation-triangle nav-icon"></i>
                        <span class="nav-text ms-2">My Violations</span>
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item mt-auto pt-3 pb-4">
                <form method="post" action="<?= base_url('logout') ?>" class="m-0">
                    <?= csrf_field() ?>
                    <button type="submit" class="nav-link nav-link-logout w-100 border-0 bg-transparent text-start" aria-label="Logout">
                        <i class="bi bi-box-arrow-right nav-icon"></i>
                        <span class="nav-text ms-2">Logout</span>
                    </button>
                </form>
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
    background: color-mix(in srgb, var(--brand-tint-400) 16%, transparent);
    transform: translateX(6px);
}
.sidebar-nav .nav-link:active {
    transform: scale(0.97) translateX(2px);
    background: rgba(255, 255, 255, 0.18);
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
    left: 0;
    top: 50%;
    width: 3px;
    height: 24px;
    border-radius: 0 4px 4px 0;
    background: var(--brand-highlight);
    transform: translate(-4px, -50%);
    opacity: 0;
    transition: transform 0.2s ease, opacity 0.2s ease;
}
.sidebar-nav .nav-link.active::before {
    transform: translate(0, -50%);
    opacity: 1;
    animation: sidebar-active-accent 220ms ease both;
}
@keyframes sidebar-active-accent {
    from { opacity: 0; transform: translate(-4px, -50%); }
    to { opacity: 1; transform: translate(0, -50%); }
}
.nav-icon {
    font-size: 1.15rem;
    width: 24px;
    text-align: center;
}
/* Settings group: a parent row that expands to its configuration screens */
.sidebar .nav-group-link .nav-caret {
    font-size: 0.65rem;
    opacity: 0.55;
    transition: transform 0.2s ease, opacity 0.2s ease;
}
.sidebar .nav-group-link[aria-expanded="true"] .nav-caret {
    transform: rotate(180deg);
    opacity: 0.85;
}
.sidebar .nav-group-link:hover .nav-caret { opacity: 0.9; }
.sidebar-nav-divider {
    height: 1px;
    margin: 0.7rem 0.65rem 0.6rem;
    background: rgba(255, 255, 255, 0.14);
}
.sidebar-subnav {
    margin: 2px 0 4px 13px;
    padding-left: 8px;
    border-left: 1px solid rgba(255, 255, 255, 0.12);
}
.sidebar-subnav .nav-link {
    padding: 0.5rem 0.75rem;
    font-size: 0.84rem;
    color: rgba(255, 255, 255, 0.68);
}
.sidebar-subnav .nav-link .nav-icon {
    font-size: 0.98rem;
}
.sidebar-subnav .nav-link.active::before {
    display: none;
}
.sidebar.collapsed .sidebar-subnav {
    display: none;
}
.sidebar.collapsed .sidebar-nav-divider { display: none; }

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

<script>
(function () {
    const groupToggles = document.querySelectorAll('.nav-group-link');
    const sidebar      = document.querySelector('.sidebar');
    const isMobile     = () => window.matchMedia('(max-width: 768px)').matches;

    if (!groupToggles.length || !sidebar) return;

    // The collapsed rail has no room for labels, so a group tapped there would
    // open a submenu nobody can see (and the next tap would close it again).
    // On the rail a tap restores the full sidebar and opens the group instead
    // of toggling: the event is consumed here so Bootstrap cannot undo it.
    groupToggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (event) {
            const compactRail = sidebar.classList.contains('collapsed')
                || (isMobile() && !sidebar.classList.contains('active'));
            if (!compactRail) return;

            event.preventDefault();
            event.stopPropagation();

            if (isMobile()) {
                sidebar.classList.add('active');
                sidebar.classList.remove('collapsed');
                document.body.classList.add('sidebar-open');
                const menuButton = document.getElementById('mobileMenuBtn');
                if (menuButton) {
                    menuButton.setAttribute('aria-expanded', 'true');
                    menuButton.setAttribute('aria-label', 'Collapse sidebar');
                }
            } else {
                sidebar.classList.remove('collapsed');
            }

            try { localStorage.setItem('sidebarCollapsed', 'false'); } catch (e) {}

            const target = document.querySelector(toggle.getAttribute('href'));

            if (target && window.bootstrap && window.bootstrap.Collapse) {
                window.bootstrap.Collapse.getOrCreateInstance(target).show();
            }
        });
    });
})();
</script>
