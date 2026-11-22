<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>User Management - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    /* Entrance & Reveal Animations */
    @keyframes fadeSlideUp {
        0% {
            opacity: 0;
            transform: translateY(16px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .anim-fade-up {
        animation: fadeSlideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .anim-delay-1 { animation-delay: 0.04s; }
    .anim-delay-2 { animation-delay: 0.1s; }
    .anim-delay-3 { animation-delay: 0.16s; }

    /* Stats & KPI Cards */
    .stat-chip {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 16px;
        padding: 16px 20px;
        transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), 
                    box-shadow 0.28s cubic-bezier(0.16, 1, 0.3, 1), 
                    border-color 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 14px;
        color: inherit;
        position: relative;
        overflow: hidden;
    }

    .stat-chip::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
        border-radius: 16px;
    }

    .stat-chip:hover {
        transform: translateY(-4px);
        color: inherit;
    }

    /* Total Users Chip */
    .stat-chip.chip-total:hover {
        border-color: #818cf8;
        box-shadow: 0 14px 28px -6px rgba(99, 102, 241, 0.18);
    }
    .stat-chip.chip-total:hover::before {
        background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.1), transparent 70%);
        opacity: 1;
    }
    .stat-chip.chip-total.active {
        border-color: #4f46e5;
        background: linear-gradient(145deg, #f5f3ff 0%, #ffffff 100%);
        box-shadow: 0 8px 22px -4px rgba(79, 70, 229, 0.25);
    }

    /* Admin Chip — electric blue */
    .stat-chip.chip-admin:hover {
        border-color: #7fb3ff;
        box-shadow: 0 14px 28px -6px rgba(0, 102, 255, 0.18);
    }
    .stat-chip.chip-admin:hover::before {
        background: radial-gradient(circle at top right, rgba(0, 102, 255, 0.1), transparent 70%);
        opacity: 1;
    }
    .stat-chip.chip-admin.active {
        border-color: #0066ff;
        background: linear-gradient(145deg, #eef4ff 0%, #ffffff 100%);
        box-shadow: 0 8px 22px -4px rgba(0, 102, 255, 0.25);
    }

    /* Enforcer Chip — lime green */
    .stat-chip.chip-enforcer:hover {
        border-color: #bef264;
        box-shadow: 0 14px 28px -6px rgba(132, 204, 22, 0.18);
    }
    .stat-chip.chip-enforcer:hover::before {
        background: radial-gradient(circle at top right, rgba(132, 204, 22, 0.12), transparent 70%);
        opacity: 1;
    }
    .stat-chip.chip-enforcer.active {
        border-color: #84cc16;
        background: linear-gradient(145deg, #f7fee7 0%, #ffffff 100%);
        box-shadow: 0 8px 22px -4px rgba(132, 204, 22, 0.25);
    }

    .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .stat-chip:hover .stat-icon {
        transform: scale(1.12) rotate(-4deg);
    }

    /* Primary Action Button */
    .btn-add-user {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: #ffffff;
        font-weight: 600;
        border-radius: 12px;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
    }

    .btn-add-user:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.42);
        color: #ffffff;
    }

    .btn-add-user:active {
        transform: translateY(1px) scale(0.98);
    }

    /* Filter Toolbar */
    .filter-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 16px;
        box-shadow: 0 4px 18px -4px rgba(15, 23, 42, 0.05);
        transition: box-shadow 0.25s ease;
    }

    .search-input-wrap {
        position: relative;
    }

    .search-input-wrap i.search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-subtle, #5c6b80);
        pointer-events: none;
        font-size: 0.95rem;
        transition: color 0.2s ease;
    }

    .search-input-wrap .form-control {
        padding-left: 42px;
        padding-right: 36px;
        height: 44px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        font-size: 0.9rem;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .search-input-wrap .form-control:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    .search-input-wrap .form-control:focus ~ i.search-icon {
        color: #3b82f6;
    }

    .search-clear-btn {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: var(--text-subtle, #5c6b80);
        cursor: pointer;
        padding: 4px;
        display: none;
        transition: color 0.18s, transform 0.18s;
    }

    .search-clear-btn:hover {
        color: #475569;
        transform: translateY(-50%) scale(1.15);
    }

    .role-select {
        height: 44px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        font-size: 0.9rem;
        font-weight: 500;
        color: #334155;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .role-select:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    .btn-reset-filter {
        height: 44px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.88rem;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-reset-filter:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .btn-reset-filter i {
        display: inline-block;
        transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .btn-reset-filter:hover i {
        transform: rotate(180deg);
    }

    /* Table Container & Row Styling */
    .users-table-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 20px;
        box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        padding: 14px 18px;
        border-bottom: 1px solid #e2e8f0;
    }

    .table tbody td {
        padding: 15px 18px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .user-row {
        transition: background-color 0.22s ease, box-shadow 0.22s ease;
    }

    .user-row:hover {
        background-color: rgba(59, 130, 246, 0.04) !important;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.07);
        transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease;
    }

    .user-row:hover .user-avatar {
        transform: scale(1.08);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
    }

    /* Role Badges */
    .role-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        transition: transform 0.2s ease;
    }

    .role-pill:hover {
        transform: scale(1.05);
    }

    .role-pill.admin {
        background: rgba(0, 102, 255, 0.1);
        color: #0066ff;
        border: 1px solid rgba(0, 102, 255, 0.2);
    }

    .role-pill.enforcer {
        background: rgba(132, 204, 22, 0.14);
        color: #4d7c0f;
        border: 1px solid rgba(132, 204, 22, 0.28);
    }

    /* Status Pills with Pulse */
    @keyframes pulseGreen {
        0% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6);
        }
        70% {
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }

    .status-dot-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-dot-pill.active {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }

    .status-dot-pill.active .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: #10b981;
        animation: pulseGreen 2.2s infinite;
    }

    .status-dot-pill.inactive {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .status-dot-pill.inactive .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: var(--text-subtle, #5c6b80);
    }

    /* Enforcer duty status — read-only. The enforcer sets it in the officer
       portal; the admin list only displays (and live-refreshes) the value. */
    @keyframes pulseAmber {
        0%   { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.55); }
        70%  { box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
        100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
    }

    .duty-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 11px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid transparent;
        background: none;
        font-family: inherit;
        cursor: default;
        user-select: none;
    }

    .duty-status-pill .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .duty-status-pill.active-patrol {
        background: #ecfdf5;
        color: #059669;
        border-color: rgba(16, 185, 129, 0.25);
    }
    .duty-status-pill.active-patrol .dot {
        background-color: #10b981;
        animation: pulseGreen 2.2s infinite;
    }

    .duty-status-pill.on-break {
        background: #fffbeb;
        color: #b45309;
        border-color: rgba(245, 158, 11, 0.3);
    }
    .duty-status-pill.on-break .dot {
        background-color: #f59e0b;
        animation: pulseAmber 2.2s infinite;
    }

    .duty-status-pill.off-duty {
        background: #f1f5f9;
        color: #64748b;
        border-color: #e2e8f0;
    }
    .duty-status-pill.off-duty .dot {
        background-color: #94a3b8;
    }

    /* Field Status dialog (same three choices the enforcer sees) */
    .duty-status-swal {
        max-width: 430px !important;
        padding: 1.2rem 1rem 0.9rem !important;
        border-radius: 20px !important;
    }
    .duty-status-swal-title {
        display: flex !important;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.2rem;
        width: 100%;
        padding: 0 0.1rem 0.8rem !important;
        border-bottom: 1px solid #e2e8f0;
        text-align: left !important;
    }
    .duty-status-title-kicker {
        color: #64748b;
        font-size: 0.64rem;
        font-weight: 700;
        letter-spacing: 0.06em;
    }
    .duty-status-title-main {
        color: #0f172a;
        font-size: 0.9rem;
        font-weight: 800;
    }
    .duty-status-title-main i {
        color: #7c3aed;
        margin-right: 0.2rem;
    }
    .duty-status-swal-html {
        margin: 0.75rem 0 0 !important;
        text-align: left !important;
    }
    .duty-status-swal-options {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }
    .duty-status-swal-options .duty-option {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        width: 100%;
        min-height: 54px;
        padding: 0.6rem 0.7rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        font: inherit;
        color: inherit;
        text-align: left;
        cursor: pointer;
        transition: border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
    }
    .duty-status-swal-options .duty-option:hover {
        border-color: #ddd6fe;
        background: #f8fafc;
        transform: translateY(-1px);
    }
    .duty-status-swal-options .duty-option.is-selected {
        border-color: #7c3aed;
        background: #f5f3ff;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.12);
    }
    .duty-status-swal-options .duty-option.is-selected .fw-bold {
        color: #6d28d9 !important;
    }
    .duty-option-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        flex-shrink: 0;
        background: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
    }
    .duty-dot-on-break {
        background: #ca8a04;
        box-shadow: 0 0 0 4px rgba(202, 138, 4, 0.15);
    }
    .duty-dot-off-duty {
        background: #94a3b8;
        box-shadow: 0 0 0 4px rgba(148, 163, 184, 0.18);
    }
    .duty-option-check {
        color: #7c3aed;
        opacity: 0;
        transform: scale(0.7);
        transition: opacity 0.18s ease, transform 0.18s ease;
    }
    .duty-status-swal-options .duty-option.is-selected .duty-option-check {
        opacity: 1;
        transform: scale(1);
    }
    .duty-status-swal-note {
        display: block;
        margin-top: 0.8rem;
        color: #64748b;
        font-size: 0.7rem;
    }

    /* Action Buttons with Vibrant Hover Glow */
    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #64748b;
        transition: all 0.24s cubic-bezier(0.34, 1.56, 0.64, 1);
        text-decoration: none;
    }

    .action-btn i {
        font-size: 0.95rem;
        transition: transform 0.2s ease;
    }

    .action-btn:hover {
        transform: translateY(-3px) scale(1.08);
    }

    .action-btn.btn-view:hover {
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
    }

    .action-btn.btn-edit:hover {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #ffffff;
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35);
    }

    .action-btn.btn-reset:hover {
        background: #d97706;
        border-color: #d97706;
        color: #ffffff;
        box-shadow: 0 6px 16px rgba(217, 119, 6, 0.35);
    }

    .action-btn.btn-delete:hover {
        background: #dc2626;
        border-color: #dc2626;
        color: #ffffff;
        box-shadow: 0 6px 16px rgba(220, 38, 38, 0.35);
    }

    .action-btn:active {
        transform: translateY(0) scale(0.96);
    }

    /* Mobile Cards */
    .user-mobile-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px;
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
    }

    .user-mobile-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.07);
    }

    /* Results are swapped in place by fetch(), so the page never reloads.
       aria-busy + a faint fade give immediate feedback without a layout jump. */
    #usersResults {
        transition: opacity 0.14s ease;
    }
    #usersResults[aria-busy="true"] {
        opacity: 0.55;
        pointer-events: none;
    }
    .visually-hidden {
        position: absolute !important;
        width: 1px; height: 1px;
        padding: 0; margin: -1px;
        overflow: hidden; clip: rect(0, 0, 0, 0);
        white-space: nowrap; border: 0;
    }
    /* Keyboard focus must always be visible on custom controls. */
    .btn:focus-visible, .action-btn:focus-visible, a:focus-visible,
    button:focus-visible, select:focus-visible, input:focus-visible {
        outline: 3px solid rgba(124, 58, 237, 0.55);
        outline-offset: 2px;
    }
    /* 44px minimum hit area for every icon-only control. */
    .action-btn { width: 44px; height: 44px; }
    #mobileUsersRegion .btn { min-height: 44px; min-width: 44px; }
    #mobileUsersRegion .d-flex.gap-2 { flex-wrap: wrap; }
    .dropdown-item.btn-reset-password,
    .dropdown-item.btn-recovery-code,
    .dropdown-item.btn-delete-user {
        cursor: pointer;
    }
    /* The row action menu is portalled to <body> and positioned explicitly
       from the three-dots button (see the show.bs.dropdown handler). Fixed
       coordinates override Popper so the menu appears in one place — right at
       the button — with no flash or jump. Above the fixed sidebar (1000/1001),
       below Bootstrap modals (1055). */
    .dropdown-menu.user-actions-menu {
        position: fixed !important;
        left: var(--action-menu-left, 0) !important;
        top: var(--action-menu-top, 0) !important;
        right: auto !important;
        bottom: auto !important;
        margin: 0 !important;
        transform: none !important;
        z-index: 1040 !important;
    }
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>

<div class="container-fluid py-4">
    <!-- Top Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 anim-fade-up">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary bg-opacity-10 text-primary px-2.5 py-1 rounded-pill" style="font-size: 0.72rem; font-weight: 700;">
                    <i class="bi bi-shield-lock me-1"></i> ACCESS MANAGEMENT
                </span>
            </div>
            <h3 class="fw-bold mb-1 text-dark">User Management</h3>
            <p class="text-muted small mb-0">Manage credentials, permissions, and roles for administrators and traffic enforcers.</p>
        </div>
        <a href="<?= base_url('users/create') ?>" class="btn btn-add-user px-4 py-2.5 shadow-sm d-inline-flex align-items-center justify-content-center">
            <i class="bi bi-person-plus-fill me-2 fs-6"></i>
            <span>Add New User</span>
        </a>
    </div>

    <!-- Quick Stats Cards (3 Columns: Total Users, Admins, Enforcers) -->
    <?php 
        $currentRole = $selectedRole ?? '';
        $currentDuty = $selectedDuty ?? '';
        $currentSearch = (string) ($selectedSearch ?? '');
        $statsTotal = $stats['total'] ?? count($users);
        $statsAdmin = $stats['admin'] ?? 0;
        $statsEnforcer = $stats['enforcer'] ?? 0;
        $dutyStats = $stats['duty'] ?? [];
        $dutyStatuses = $dutyStatuses ?? ['Active Patrol', 'On Break', 'Off Duty'];

        // One place that maps a saved duty_status to its pill style/label,
        // used by both the table and the mobile cards.
        $dutyMeta = static function (string $status): array {
            return match ($status) {
                'Active Patrol' => ['class' => 'active-patrol', 'label' => 'Active Patrol'],
                'On Break'      => ['class' => 'on-break', 'label' => 'On Break'],
                'Off Duty'      => ['class' => 'off-duty', 'label' => 'Off Duty'],
                default         => ['class' => 'off-duty', 'label' => 'Off Duty'],
            };
        };
    ?>
    <div class="row g-3 mb-4 anim-fade-up anim-delay-1">
        <div class="col-12 col-md-4">
            <a href="<?= base_url('users') ?>" class="stat-chip chip-total js-filter-link <?= $currentRole === '' ? 'active' : '' ?>">
                <div class="stat-icon bg-indigo bg-opacity-10 text-primary" style="background: rgba(99, 102, 241, 0.1); color: #4f46e5 !important;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;">Total Users</div>
                    <div class="fw-bold fs-4 text-dark lh-1 mt-1"><?= esc($statsTotal) ?></div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="<?= base_url('users?role=admin') ?>" class="stat-chip chip-admin js-filter-link <?= $currentRole === 'admin' ? 'active' : '' ?>">
                <div class="stat-icon" style="background: rgba(0, 102, 255, 0.1); color: #0066ff;">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;">Admins</div>
                    <div class="fw-bold fs-4 text-dark lh-1 mt-1"><?= esc($statsAdmin) ?></div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="<?= base_url('users?role=enforcer') ?>" class="stat-chip chip-enforcer js-filter-link <?= $currentRole === 'enforcer' ? 'active' : '' ?>">
                <div class="stat-icon" style="background: rgba(132, 204, 22, 0.14); color: #4d7c0f;">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;">Enforcers</div>
                    <div class="fw-bold fs-4 text-dark lh-1 mt-1"><?= esc($statsEnforcer) ?></div>
                </div>
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="filter-card p-3 mb-4 anim-fade-up anim-delay-2">
        <form method="GET" action="<?= base_url('users') ?>" class="row g-2 align-items-center" id="filterForm">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="search-input-wrap">
                    <label class="visually-hidden" for="searchInput">Search users by username, email or name</label>
                    <i class="bi bi-search search-icon" aria-hidden="true"></i>
                    <input type="search" name="search" id="searchInput" class="form-control" placeholder="Search by username, email, name..." autocomplete="off"
                           value="<?= esc($currentSearch) ?>" aria-describedby="searchHelp">
                    <span id="searchHelp" class="visually-hidden">Searches all users, not only the current page. Results update as you type.</span>
                    <button type="button" id="searchClearBtn" class="search-clear-btn" aria-label="Clear search">
                        <i class="bi bi-x-circle-fill" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                <select class="form-select role-select" name="role" id="roleSelect" aria-label="Filter by role">
                    <option value="" <?= $currentRole === '' ? 'selected' : '' ?>>All Roles</option>
                    <option value="admin" <?= $currentRole === 'admin' ? 'selected' : '' ?>>Administrators</option>
                    <option value="enforcer" <?= $currentRole === 'enforcer' ? 'selected' : '' ?>>Traffic Enforcers</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                <select class="form-select role-select" name="duty" id="dutySelect" aria-label="Filter by duty status">
                    <option value="" <?= $currentDuty === '' ? 'selected' : '' ?>>All Duty Statuses</option>
                    <?php foreach ($dutyStatuses as $dutyOption): ?>
                        <option value="<?= esc($dutyOption) ?>" <?= $currentDuty === $dutyOption ? 'selected' : '' ?>>
                            <?= esc($dutyOption) ?> (<?= (int) ($dutyStats[$dutyOption] ?? 0) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-3 d-flex justify-content-sm-end gap-2">
                <a href="<?= base_url('users') ?>" class="btn btn-light border btn-reset-filter js-filter-link px-3.5 d-inline-flex align-items-center" data-reset="1">
                    <i class="bi bi-arrow-clockwise me-1.5 fs-6"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 d-flex align-items-center" style="border-radius: 12px;" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div><?= session()->getFlashdata('success') ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 d-flex align-items-center" style="border-radius: 12px;" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div><?= session()->getFlashdata('error') ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php $recoveryCodeOnce = session()->getFlashdata('recovery_code_once'); ?>
    <?php if (is_array($recoveryCodeOnce) && !empty($recoveryCodeOnce['code'])): ?>
        <div class="alert alert-warning border-0 shadow-sm anim-fade-up mb-4 position-relative" style="border-radius: 16px;">
            <div class="d-flex align-items-start gap-3 pe-4">
                <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                    <i class="bi bi-shield-lock-fill fs-5"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-1">Recovery code for <?= esc($recoveryCodeOnce['username'] ?? 'this account') ?> — shown only once</h6>
                    <p class="small mb-2 text-dark">
                        Write it down or hand it to the account owner now. It works <strong>one time</strong> on the
                        Forgot Password page and can never be viewed again.
                    </p>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <code id="recoveryCodeValue" class="fs-5 fw-bold px-3 py-2 bg-dark text-warning rounded-3" style="letter-spacing: 2px;"><?= esc($recoveryCodeOnce['code']) ?></code>
                        <button type="button" class="btn btn-sm btn-dark" id="copyRecoveryCode">
                            <i class="bi bi-clipboard me-1"></i>Copy
                        </button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <script>
        (function () {
            const copyBtn = document.getElementById('copyRecoveryCode');
            const codeEl = document.getElementById('recoveryCodeValue');
            if (copyBtn && codeEl) {
                copyBtn.addEventListener('click', function () {
                    const code = codeEl.textContent.trim();
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(code);
                    }
                    copyBtn.innerHTML = '<i class="bi bi-check2 me-1"></i>Copied';
                });
            }
        })();
        </script>
    <?php endif; ?>

    <!-- Users Table Card -->
    <div class="users-table-card anim-fade-up anim-delay-3" id="usersResults" aria-busy="false">
        <!-- Desktop Table View -->
        <div class="table-responsive d-none d-lg-block">
            <table class="table align-middle mb-0" id="userTable">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 70px;">ID</th>
                        <th style="min-width: 220px;">User / Email</th>
                        <th style="min-width: 180px;">Full Name</th>
                        <th class="text-center" style="width: 80px;">Age</th>
                        <th style="width: 140px;">Role</th>
                        <th style="width: 155px;">Status</th>
                        <th class="pe-4 text-end" style="width: 170px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <?php if (empty($users)): ?>
                        <tr id="emptyStaticRow">
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-person-x fs-1 d-block mb-2 opacity-50 text-secondary"></i>
                                    <h6 class="fw-bold mb-1">No users found</h6>
                                    <p class="small text-muted mb-0">No records match the selected filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <?php 
                                $userRole = strtolower((string) ($user['role'] ?? 'enforcer'));
                                $roleColors = [
                                    'admin' => ['bg' => 'linear-gradient(135deg, #0066ff 0%, #0052cc 100%)', 'icon' => 'bi-shield-check'],
                                    'enforcer' => ['bg' => 'linear-gradient(135deg, #84cc16 0%, #4d7c0f 100%)', 'icon' => 'bi-person-badge']
                                ];
                                $avatarMeta = $roleColors[$userRole] ?? ['bg' => '#64748b', 'icon' => 'bi-person'];
                                $fullName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
                                if (empty($fullName)) {
                                    $fullName = '—';
                                }
                                $isEnforcer = $userRole === 'enforcer';
                                $dutyStatus = $isEnforcer ? (string) ($user['duty_status'] ?? 'Active Patrol') : '';
                                $dutyInfo   = $isEnforcer ? $dutyMeta($dutyStatus) : null;
                            ?>
                            <tr class="user-row" data-search="<?= esc(strtolower($user['username'] . ' ' . $user['email'] . ' ' . $fullName . ' ' . $userRole . ' ' . $dutyStatus)) ?>">
                                <td class="ps-4">
                                    <span class="text-muted fw-bold small">#<?= esc($user['id']) ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar text-white me-3" style="background: <?= $avatarMeta['bg'] ?>;">
                                            <?= strtoupper(substr((string) $user['username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark lh-sm mb-0.5"><?= esc($user['username']) ?></div>
                                            <div class="text-muted small" style="font-size: 0.8rem;"><?= esc($user['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark"><?= esc($fullName) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border px-2 py-1"><?= esc((string) ($user['age'] ?? '—')) ?></span>
                                </td>
                                <td>
                                    <span class="role-pill <?= esc($userRole) ?>">
                                        <i class="bi <?= $avatarMeta['icon'] ?>"></i>
                                        <?= esc($userRole) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($isEnforcer): ?>
                                        <span class="duty-status-pill <?= esc($dutyInfo['class']) ?>"
                                              data-id="<?= (int) $user['id'] ?>"
                                              data-duty="<?= esc($dutyStatus) ?>"
                                              title="Duty status set by this enforcer">
                                            <span class="dot"></span> <?= esc($dutyInfo['label']) ?>
                                        </span>
                                    <?php elseif (($user['status'] ?? '') === 'active'): ?>
                                        <span class="status-dot-pill active">
                                            <span class="dot"></span> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="status-dot-pill inactive">
                                            <span class="dot"></span> Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-1.5 align-items-center">
                                        <?php if ($isEnforcer): ?>
                                            <a href="<?= base_url('users/view-enforcer/' . $user['id']) ?>" class="action-btn btn-view" title="View Enforcer Profile" aria-label="View enforcer profile for <?= esc($user['username']) ?>">
                                                <i class="bi bi-person-vcard"></i>
                                            </a>
                                        <?php endif; ?>

                                        <a href="<?= base_url('users/edit/' . $user['id']) ?>" class="action-btn btn-edit" title="Edit User" aria-label="Edit user <?= esc($user['username']) ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <!-- Credential + destructive actions share one menu so Delete can never be mis-tapped beside View/Edit. Bootstrap handles outside-click, Escape and arrow-key navigation. -->
                                         <div class="dropdown">
                                             <button type="button" class="action-btn" id="userActions<?= (int) $user['id'] ?>"
                                                     data-bs-toggle="dropdown" aria-expanded="false"
                                                     aria-label="More actions for <?= esc($user['username']) ?>">
                                                 <i class="bi bi-three-dots" aria-hidden="true"></i>
                                             </button>
                                             <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userActions<?= (int) $user['id'] ?>">
                                                 <li><button type="button" class="dropdown-item btn-reset-password"
                                                             data-id="<?= $user['id'] ?>" data-username="<?= esc($user['username']) ?>">
                                                         <i class="bi bi-key me-2" aria-hidden="true"></i>Reset password
                                                     </button></li>
                                                 <li><button type="button" class="dropdown-item btn-recovery-code"
                                                             data-id="<?= $user['id'] ?>" data-username="<?= esc($user['username']) ?>">
                                                         <i class="bi bi-shield-plus me-2" aria-hidden="true"></i>Generate recovery code
                                                     </button></li>
                                                 <li><hr class="dropdown-divider"></li>
                                                 <li><button type="button" class="dropdown-item text-danger btn-delete-user"
                                                             data-id="<?= $user['id'] ?>" data-username="<?= esc($user['username']) ?>">
                                                         <i class="bi bi-trash3 me-2" aria-hidden="true"></i>Delete user
                                                     </button></li>
                                             </ul>
                                         </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile & Tablet Responsive Cards -->
        <div class="d-lg-none p-3" id="mobileUsersRegion">
            <?php if (empty($users)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-person-x fs-1 d-block mb-2 opacity-50"></i>
                    No users found.
                </div>
            <?php else: ?>
                <div class="row g-3" id="mobileCardsContainer">
                    <?php foreach ($users as $user): ?>
                        <?php 
                            $userRole = strtolower((string) ($user['role'] ?? 'enforcer'));
                            $fullName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
                            if (empty($fullName)) $fullName = '—';
                            $isEnforcer = $userRole === 'enforcer';
                            $dutyStatus = $isEnforcer ? (string) ($user['duty_status'] ?? 'Active Patrol') : '';
                            $dutyInfo   = $isEnforcer ? $dutyMeta($dutyStatus) : null;
                        ?>
                        <div class="col-12 mobile-user-card" data-search="<?= esc(strtolower($user['username'] . ' ' . $user['email'] . ' ' . $fullName . ' ' . $userRole . ' ' . $dutyStatus)) ?>">
                            <div class="user-mobile-card">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar text-white me-2.5" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); width: 42px; height: 42px;">
                                            <?= strtoupper(substr((string) $user['username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= esc($user['username']) ?></div>
                                            <div class="text-muted small" style="font-size: 0.78rem;"><?= esc($user['email']) ?></div>
                                        </div>
                                    </div>
                                    <span class="role-pill <?= esc($userRole) ?>">
                                        <?= esc($userRole) ?>
                                    </span>
                                </div>

                                <div class="bg-light p-2.5 rounded-3 mb-3 small d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted">Name:</span> <strong><?= esc($fullName) ?></strong>
                                    </div>
                                    <div>
                                        <?php if ($isEnforcer): ?>
                                            <span class="duty-status-pill <?= esc($dutyInfo['class']) ?>"
                                                  data-id="<?= (int) $user['id'] ?>"
                                                  data-duty="<?= esc($dutyStatus) ?>"
                                                  title="Duty status set by this enforcer">
                                                <span class="dot"></span> <?= esc($dutyInfo['label']) ?>
                                            </span>
                                        <?php elseif (($user['status'] ?? '') === 'active'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <?php if ($isEnforcer): ?>
                                        <a href="<?= base_url('users/view-enforcer/' . $user['id']) ?>" class="btn btn-sm btn-outline-info flex-grow-1">
                                            <i class="bi bi-person-vcard me-1"></i> View
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('users/edit/' . $user['id']) ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-warning btn-reset-password" data-id="<?= $user['id'] ?>" data-username="<?= esc($user['username']) ?>" title="Reset Password" aria-label="Reset password for <?= esc($user['username']) ?>">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info btn-recovery-code" data-id="<?= $user['id'] ?>" data-username="<?= esc($user['username']) ?>" title="Generate Recovery Code" aria-label="Generate recovery code for <?= esc($user['username']) ?>">
                                        <i class="bi bi-shield-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-user" data-id="<?= $user['id'] ?>" data-username="<?= esc($user['username']) ?>" title="Delete User" aria-label="Delete user <?= esc($user['username']) ?>">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination Footer -->
        <div class="card-footer bg-white border-top py-3 px-4">
            <div class="pagination-container" id="paginationContainer" aria-label="Users pagination">
                <?= $pager->links('users', 'bootstrap_pagination') ?>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header bg-warning bg-opacity-10 border-bottom border-warning border-opacity-25 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-key-fill"></i>
                    </div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="resetPasswordModalTitle">Reset Password</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="resetPasswordForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-1">Target Account</label>
                        <div class="p-2.5 bg-light rounded-3 d-flex align-items-center gap-2">
                            <i class="bi bi-person-circle fs-5 text-primary"></i>
                            <span id="resetUsername" class="fw-bold text-dark fs-6"></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                            <input type="password" name="password" id="new_password" class="form-control border-start-0 border-end-0" placeholder="Enter new password (min. 5 chars)" required minlength="5">
                            <button class="btn btn-outline-secondary border-start-0 bg-light" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 bg-info bg-opacity-10 text-dark small mb-0 d-flex align-items-start gap-2" role="alert">
                        <i class="bi bi-info-circle-fill text-info fs-6 mt-0.5"></i>
                        <div>Please enter the updated password for this user. The password will be immediately encrypted and saved.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Recovery Code Modal -->
<div class="modal fade" id="recoveryCodeModal" tabindex="-1" aria-labelledby="recoveryCodeModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header bg-info bg-opacity-10 border-bottom border-info border-opacity-25 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-shield-plus"></i>
                    </div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="recoveryCodeModalTitle">Generate Recovery Code</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="recoveryCodeForm" method="POST">
                <?= csrf_field() ?>

                <!-- Step 1: Confirmation -->
                <div id="recoveryStep1">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Target Account</label>
                            <div class="p-2.5 bg-light rounded-3 d-flex align-items-center gap-2">
                                <i class="bi bi-person-circle fs-5 text-info"></i>
                                <span id="recoveryUsername" class="fw-bold text-dark fs-6"></span>
                            </div>
                        </div>

                        <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-dark small mb-0 d-flex align-items-start gap-2" role="alert">
                            <i class="bi bi-shield-exclamation text-warning fs-6 mt-0.5"></i>
                            <div>
                                Only continue if you have <strong>verified the person's identity</strong> (in person or by a
                                known contact). The code is shown <strong>once</strong>, works a <strong>single time</strong>
                                on the Forgot Password page, and replaces any previous code for this account.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3">
                        <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow text-white" id="recoveryGenerateBtn" style="background:#0d6efd; border-color:#0d6efd;">
                            <i class="bi bi-shield-plus me-1"></i> Generate Code
                        </button>
                    </div>
                </div>

                <!-- Step 2: Code Revealed -->
                <div id="recoveryStep2" class="d-none">
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 56px; height: 56px;">
                                <i class="bi bi-shield-check fs-2"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Recovery code for <span id="recoveryUsernameResult" class="text-info"></span></h6>
                            <p class="small text-muted mb-0">Write it down or hand it to the account owner now. It works <strong>one time</strong> on the Forgot Password page and can never be viewed again.</p>
                        </div>
                        <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap mb-3">
                            <code id="recoveryCodeResult" class="fs-3 fw-bold px-4 py-2 bg-dark text-warning rounded-3 d-inline-block" style="letter-spacing: 3px;"></code>
                        </div>
                        <button type="button" class="btn btn-dark btn-sm px-3" id="copyRecoveryCodeModal">
                            <i class="bi bi-clipboard me-1"></i> Copy Code
                        </button>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="bi bi-check-lg me-1"></i> Done
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const clearBtn = document.getElementById('searchClearBtn');
        const roleSelect = document.getElementById('roleSelect');
        const dutySelect = document.getElementById('dutySelect');
        const results = document.getElementById('usersResults');
        const filterForm = document.getElementById('filterForm');

        let lastStatuses = null;

        const escapeHtml = (value) => {
            const div = document.createElement('div');
            div.innerText = value == null ? '' : String(value);
            return div.innerHTML;
        };

        /* ============================================================
           AJAX results — at most ONE request in flight.
           A new keystroke/filter aborts the previous fetch instead of
           stacking requests, so the list never lags behind typing.
           Only the results regions are replaced; the page chrome,
           modals and sidebar are untouched (no full page reload).
           ============================================================ */
        let activeFetch = null;
        let searchTimer = null;

        function currentQuery() {
            const params = new URLSearchParams();
            if (searchInput && searchInput.value.trim() !== '') params.set('search', searchInput.value.trim());
            if (roleSelect && roleSelect.value !== '') params.set('role', roleSelect.value);
            if (dutySelect && dutySelect.value !== '') params.set('duty', dutySelect.value);
            return params;
        }

        function urlFor(params) {
            const url = new URL(<?= json_encode(base_url('users')) ?>, window.location.origin);
            params.forEach((value, key) => url.searchParams.set(key, value));
            return url.pathname + url.search;
        }

        function swapRegion(doc, selector) {
            const fresh = doc.querySelector(selector);
            const stale = document.querySelector(selector);
            if (fresh && stale) stale.replaceWith(fresh);
        }

        function syncControlsFromUrl(url) {
            const params = new URL(url, window.location.origin).searchParams;
            if (searchInput) searchInput.value = params.get('search') || '';
            if (roleSelect) roleSelect.value = params.get('role') || '';
            if (dutySelect) dutySelect.value = params.get('duty') || '';
            if (clearBtn) clearBtn.style.display = searchInput && searchInput.value ? 'block' : 'none';
        }

        async function loadUsers(url, push) {
            if (activeFetch) activeFetch.abort();
            const controller = new AbortController();
            activeFetch = controller;
            if (results) results.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal,
                    cache: 'no-store',
                });
                if (!response.ok) throw new Error('Request failed');

                const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                swapRegion(doc, '#userTableBody');
                swapRegion(doc, '#mobileUsersRegion');
                swapRegion(doc, '#paginationContainer');

                const freshInput = doc.querySelector('#searchInput');
                if (searchInput && freshInput) searchInput.value = freshInput.value;
                if (clearBtn) clearBtn.style.display = searchInput && searchInput.value ? 'block' : 'none';

                if (push !== false) history.pushState({}, '', url);

                // Fresh rows may contain new duty pills — repaint from the
                // last feed so the badges stay consistent without a reload.
                if (lastStatuses) {
                    Object.keys(lastStatuses).forEach(id => paintDutyStatus(id, lastStatuses[id]));
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    // Non-blocking: keep the current list and clear busy state.
                    console.warn('User list refresh failed:', error);
                }
            } finally {
                if (activeFetch === controller) activeFetch = null;
                if (results) results.setAttribute('aria-busy', 'false');
            }
        }

        function clearSearch() {
            if (!searchInput) return;
            searchInput.value = '';
            if (clearBtn) clearBtn.style.display = 'none';
            searchInput.focus();
            clearTimeout(searchTimer);
            loadUsers(urlFor(currentQuery()));
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                if (clearBtn) clearBtn.style.display = this.value ? 'block' : 'none';
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => loadUsers(urlFor(currentQuery())), 220);
            });
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && this.value) { e.preventDefault(); clearSearch(); }
                if (e.key === 'Enter') { e.preventDefault(); clearTimeout(searchTimer); loadUsers(urlFor(currentQuery())); }
            });
        }
        if (clearBtn) {
            if (searchInput && searchInput.value) clearBtn.style.display = 'block';
            clearBtn.addEventListener('click', clearSearch);
        }
        if (filterForm) filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            clearTimeout(searchTimer);
            loadUsers(urlFor(currentQuery()));
        });
        if (roleSelect) roleSelect.addEventListener('change', () => loadUsers(urlFor(currentQuery())));
        if (dutySelect) dutySelect.addEventListener('change', () => loadUsers(urlFor(currentQuery())));

        // Stat chips, Reset and pagination all flow through the same AJAX path.
        document.addEventListener('click', function (e) {
            const filterLink = e.target.closest('.js-filter-link');
            if (filterLink) {
                e.preventDefault();
                if (filterLink.dataset.reset === '1' && searchInput) searchInput.value = '';
                const url = new URL(filterLink.href, window.location.origin);
                syncControlsFromUrl(url.href);
                loadUsers(url.pathname + url.search);
                return;
            }
            const pageLink = e.target.closest('#paginationContainer a');
            if (pageLink) {
                e.preventDefault();
                const url = new URL(pageLink.href, window.location.origin);
                loadUsers(url.pathname + url.search);
            }
        });

        window.addEventListener('popstate', () => {
            syncControlsFromUrl(location.href);
            loadUsers(location.pathname + location.search, false);
        });

        /* ============================================================
           Row action menus (three dots) live inside two clipping containers
           (.table-responsive overflow-x:auto and .users-table-card
           overflow:hidden), so an in-place dropdown gets cut off. Instead of
           letting Popper fight those containers, portal the menu to <body>
           and place it explicitly from the button's own rectangle. The menu
           therefore appears directly at the actions button on the very first
           frame — no top-of-page flash, no jump, no clipping.
           ============================================================ */
        function positionActionMenu(toggle, menu) {
            if (!toggle.isConnected) return;
            const button = toggle.getBoundingClientRect();
            const mRect = menu.getBoundingClientRect();
            const mw = mRect.width;
            const mh = mRect.height;
            const gap = 6;
            const margin = 8;

            let left = button.right - mw;                       // right-align to the button
            left = Math.max(margin, Math.min(left, window.innerWidth - mw - margin));

            let top = button.bottom + gap;                      // prefer below
            if (top + mh > window.innerHeight - margin && button.top - gap - mh > margin) {
                top = button.top - gap - mh;                    // flip above only if needed
            }
            top = Math.max(margin, top);

            menu.style.setProperty('--action-menu-left', left + 'px');
            menu.style.setProperty('--action-menu-top', top + 'px');
        }

        document.addEventListener('show.bs.dropdown', function (e) {
            const toggle = e.target;
            if (!toggle.closest || !toggle.closest('#usersResults .table-responsive')) return;

            let menu = toggle.__actionMenu;
            if (!menu) {
                menu = toggle.nextElementSibling;
                if (!menu || !menu.classList.contains('dropdown-menu')) {
                    menu = document.querySelector('[aria-labelledby="' + toggle.id + '"]');
                }
                if (!menu || !menu.classList.contains('dropdown-menu')) return;
                toggle.__actionMenu = menu;
                toggle.__actionMenuParent = menu.parentNode;
            }

            menu.classList.add('user-actions-menu');
            if (menu.parentNode !== document.body) document.body.appendChild(menu);

            // Bootstrap adds .show synchronously right after this event; add
            // it just long enough to measure the menu at its real size.
            const alreadyShown = menu.classList.contains('show');
            if (!alreadyShown) menu.classList.add('show');
            positionActionMenu(toggle, menu);
            if (!alreadyShown) menu.classList.remove('show');

            toggle.__repositionMenu = () => positionActionMenu(toggle, menu);
            window.addEventListener('scroll', toggle.__repositionMenu, true);
            window.addEventListener('resize', toggle.__repositionMenu);
        });

        document.addEventListener('hidden.bs.dropdown', function (e) {
            const toggle = e.target;
            const menu = toggle.__actionMenu;
            const parent = toggle.__actionMenuParent;
            if (toggle.__repositionMenu) {
                window.removeEventListener('scroll', toggle.__repositionMenu, true);
                window.removeEventListener('resize', toggle.__repositionMenu);
                toggle.__repositionMenu = null;
            }
            if (!menu || !parent) return;
            if (!parent.isConnected) {
                menu.remove(); // its row was swapped out by an AJAX filter
                return;
            }
            if (menu.parentNode !== parent) parent.appendChild(menu);
        });

        /* Clicking Reset password / Generate recovery code / Delete user must
           leave the three-dots toggle in a clean, closed state. Bootstrap's
           document-level auto-close can race with the portalled menu, so close
           the owning dropdown explicitly (capture phase, before the action
           handlers open a modal) and clear the lingering focus/open styles. */
        document.addEventListener('click', function (e) {
            const item = e.target.closest('.dropdown-menu.user-actions-menu .dropdown-item');
            if (!item) return;
            const menu = item.closest('.dropdown-menu');
            const toggle = menu ? document.getElementById(menu.getAttribute('aria-labelledby') || '') : null;
            if (!toggle) return;

            const instance = window.bootstrap && bootstrap.Dropdown.getInstance(toggle);
            if (instance) instance.hide();
            toggle.classList.remove('show');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.blur();
            if (menu) menu.classList.remove('show');
        }, true);

        /* ============================================================
           Modals / destructive actions: delegated so they keep working
           after the results region is swapped by fetch().
           ============================================================ */
        const resetUsernameSpan = document.getElementById('resetUsername');
        const resetPasswordForm = document.getElementById('resetPasswordForm');
        const resetModalEl = document.getElementById('resetPasswordModal');
        const resetModal = resetModalEl ? new bootstrap.Modal(resetModalEl) : null;

        const recoveryUsernameSpan = document.getElementById('recoveryUsername');
        const recoveryCodeForm = document.getElementById('recoveryCodeForm');
        const recoveryModalEl = document.getElementById('recoveryCodeModal');
        const recoveryModal = recoveryModalEl ? new bootstrap.Modal(recoveryModalEl) : null;
        const recoveryStep1 = document.getElementById('recoveryStep1');
        const recoveryStep2 = document.getElementById('recoveryStep2');
        const recoveryGenerateBtn = document.getElementById('recoveryGenerateBtn');
        const recoveryUsernameResult = document.getElementById('recoveryUsernameResult');
        const recoveryCodeResult = document.getElementById('recoveryCodeResult');
        const copyRecoveryCodeModal = document.getElementById('copyRecoveryCodeModal');

        document.addEventListener('click', function (e) {
            const resetBtn = e.target.closest('.btn-reset-password');
            if (resetBtn) {
                if (resetUsernameSpan) resetUsernameSpan.textContent = resetBtn.dataset.username;
                const newPasswordInput = document.getElementById('new_password');
                if (newPasswordInput) newPasswordInput.value = '';
                if (resetPasswordForm) resetPasswordForm.action = `<?= base_url('users/reset-password') ?>/${resetBtn.dataset.id}`;
                if (resetModal) resetModal.show();
                return;
            }

            const recoveryBtn = e.target.closest('.btn-recovery-code');
            if (recoveryBtn) {
                if (recoveryUsernameSpan) recoveryUsernameSpan.textContent = recoveryBtn.dataset.username;
                if (recoveryCodeForm) recoveryCodeForm.action = `<?= base_url('users/recovery-code') ?>/${recoveryBtn.dataset.id}`;
                if (recoveryStep1) recoveryStep1.classList.remove('d-none');
                if (recoveryStep2) recoveryStep2.classList.add('d-none');
                if (recoveryGenerateBtn) {
                    recoveryGenerateBtn.disabled = false;
                    recoveryGenerateBtn.innerHTML = '<i class="bi bi-shield-plus me-1"></i> Generate Code';
                }
                if (copyRecoveryCodeModal) copyRecoveryCodeModal.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy Code';
                if (recoveryModal) recoveryModal.show();
                return;
            }

            const deleteBtn = e.target.closest('.btn-delete-user');
            if (deleteBtn) {
                const username = escapeHtml(deleteBtn.dataset.username);
                Swal.fire({
                    title: 'Delete User?',
                    html: `Are you sure you want to delete user <strong>"${username}"</strong>?<br><span class="text-danger small">This action cannot be undone.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: 'var(--text-subtle, #5c6b80)',
                    confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Yes, Delete',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    customClass: { popup: 'rounded-4 shadow-lg' }
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `<?= base_url('users/delete') ?>/${deleteBtn.dataset.id}`;
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '<?= csrf_token() ?>';
                    csrfInput.value = '<?= csrf_hash() ?>';
                    form.appendChild(csrfInput);
                    document.body.appendChild(form);
                    form.submit();
                });
            }
        });

        // Recovery code reveal stays in-modal — no blocking native alert().
        if (recoveryCodeForm) {
            recoveryCodeForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                if (recoveryGenerateBtn) {
                    recoveryGenerateBtn.disabled = true;
                    recoveryGenerateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating…';
                }
                const restore = () => {
                    if (recoveryGenerateBtn) {
                        recoveryGenerateBtn.disabled = false;
                        recoveryGenerateBtn.innerHTML = '<i class="bi bi-shield-plus me-1"></i> Generate Code';
                    }
                };
                try {
                    const res = await fetch(this.action, {
                        method: 'POST',
                        body: new FormData(this),
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (recoveryUsernameResult) recoveryUsernameResult.textContent = data.username;
                        if (recoveryCodeResult) recoveryCodeResult.textContent = data.code;
                        if (recoveryStep1) recoveryStep1.classList.add('d-none');
                        if (recoveryStep2) recoveryStep2.classList.remove('d-none');
                    } else {
                        restore();
                        Swal.fire({ icon: 'error', title: 'Could not generate code', text: data.message || 'Please try again.' });
                    }
                } catch (err) {
                    restore();
                    Swal.fire({ icon: 'error', title: 'Network error', text: 'Please try again.' });
                }
            });
        }

        if (copyRecoveryCodeModal) {
            copyRecoveryCodeModal.addEventListener('click', function () {
                const code = recoveryCodeResult ? recoveryCodeResult.textContent.trim() : '';
                if (code && navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(code);
                this.innerHTML = '<i class="bi bi-check2 me-1"></i> Copied!';
                setTimeout(() => { this.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy Code'; }, 2000);
            });
        }

        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('new_password');
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('bi-eye');
                this.querySelector('i').classList.toggle('bi-eye-slash');
            });
        }

        /* ============================================================
           Duty status: read-only, owned by the enforcer.
           Bounded polling — one in-flight request, 15s minimum gap,
           and completely paused while the tab is hidden. No delays,
           no stacking timers, no overlapping reloads.
           ============================================================ */
        const DUTY_POLL_MS = 15000;
        let pollTimer = null;
        let polling = false;

        function dutyPresentation(status) {
            if (status === 'Active Patrol') return { className: 'active-patrol', label: 'Active Patrol' };
            if (status === 'On Break') return { className: 'on-break', label: 'On Break' };
            return { className: 'off-duty', label: 'Off Duty' };
        }

        function paintDutyStatus(userId, status) {
            const meta = dutyPresentation(status);
            document.querySelectorAll('.duty-status-pill[data-id="' + userId + '"]').forEach(function (pill) {
                pill.dataset.duty = status;
                pill.className = 'duty-status-pill ' + meta.className;
                pill.innerHTML = '<span class="dot"></span> ' + meta.label;
                const row = pill.closest('.user-row, .mobile-user-card');
                if (row && row.dataset.search) {
                    row.dataset.search = row.dataset.search.replace(
                        /(active patrol|on break|off duty)/i,
                        status.toLowerCase()
                    );
                }
            });
        }

        async function refreshDutyStatuses() {
            if (polling || document.visibilityState !== 'visible') return;
            polling = true;
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000);
            try {
                const response = await fetch('<?= base_url('users/duty-status') ?>', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    cache: 'no-store',
                    signal: controller.signal,
                });
                if (!response.ok) return;
                const data = await response.json();
                if (!data.success || !data.statuses) return;
                lastStatuses = data.statuses;
                Object.keys(data.statuses).forEach(id => paintDutyStatus(id, data.statuses[id]));
            } catch (error) {
                // Offline / transient failure: keep the last rendered value.
            } finally {
                clearTimeout(timeoutId);
                polling = false;
            }
        }

        function schedulePoll() {
            clearTimeout(pollTimer);
            if (document.visibilityState !== 'visible') return;
            pollTimer = setTimeout(async () => {
                await refreshDutyStatuses();
                schedulePoll();
            }, DUTY_POLL_MS);
        }

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                refreshDutyStatuses();
                schedulePoll();
            } else {
                clearTimeout(pollTimer);
            }
        });

        refreshDutyStatuses();
        schedulePoll();
    });
</script>
<?= $this->endSection() ?>
