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

    /* Admin Chip */
    .stat-chip.chip-admin:hover {
        border-color: #fca5a5;
        box-shadow: 0 14px 28px -6px rgba(239, 68, 68, 0.18);
    }
    .stat-chip.chip-admin:hover::before {
        background: radial-gradient(circle at top right, rgba(239, 68, 68, 0.1), transparent 70%);
        opacity: 1;
    }
    .stat-chip.chip-admin.active {
        border-color: #dc2626;
        background: linear-gradient(145deg, #fef2f2 0%, #ffffff 100%);
        box-shadow: 0 8px 22px -4px rgba(220, 38, 38, 0.25);
    }

    /* Enforcer Chip */
    .stat-chip.chip-enforcer:hover {
        border-color: #93c5fd;
        box-shadow: 0 14px 28px -6px rgba(59, 130, 246, 0.18);
    }
    .stat-chip.chip-enforcer:hover::before {
        background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.1), transparent 70%);
        opacity: 1;
    }
    .stat-chip.chip-enforcer.active {
        border-color: #2563eb;
        background: linear-gradient(145deg, #eff6ff 0%, #ffffff 100%);
        box-shadow: 0 8px 22px -4px rgba(37, 99, 235, 0.25);
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
        color: #94a3b8;
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
        color: #94a3b8;
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
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .role-pill.enforcer {
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
        border: 1px solid rgba(59, 130, 246, 0.2);
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
        background-color: #94a3b8;
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
        $statsTotal = $stats['total'] ?? count($users);
        $statsAdmin = $stats['admin'] ?? 0;
        $statsEnforcer = $stats['enforcer'] ?? 0;
    ?>
    <div class="row g-3 mb-4 anim-fade-up anim-delay-1">
        <div class="col-12 col-md-4">
            <a href="<?= base_url('users') ?>" class="stat-chip chip-total <?= $currentRole === '' ? 'active' : '' ?>">
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
            <a href="<?= base_url('users?role=admin') ?>" class="stat-chip chip-admin <?= $currentRole === 'admin' ? 'active' : '' ?>">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;">Admins</div>
                    <div class="fw-bold fs-4 text-dark lh-1 mt-1"><?= esc($statsAdmin) ?></div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="<?= base_url('users?role=enforcer') ?>" class="stat-chip chip-enforcer <?= $currentRole === 'enforcer' ? 'active' : '' ?>">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
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
            <div class="col-12 col-md-6 col-lg-5">
                <div class="search-input-wrap">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by username, email, name..." autocomplete="off">
                    <button type="button" id="searchClearBtn" class="search-clear-btn" aria-label="Clear search">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                <select class="form-select role-select" name="role" onchange="this.form.submit()">
                    <option value="" <?= $currentRole === '' ? 'selected' : '' ?>>All Roles</option>
                    <option value="admin" <?= $currentRole === 'admin' ? 'selected' : '' ?>>Administrators</option>
                    <option value="enforcer" <?= $currentRole === 'enforcer' ? 'selected' : '' ?>>Traffic Enforcers</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-4 d-flex justify-content-sm-end gap-2">
                <a href="<?= base_url('users') ?>" class="btn btn-light border btn-reset-filter px-3.5 d-inline-flex align-items-center">
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

    <!-- Users Table Card -->
    <div class="users-table-card anim-fade-up anim-delay-3">
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
                        <th style="width: 120px;">Status</th>
                        <th class="pe-4 text-end" style="width: 170px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
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
                                    'admin' => ['bg' => 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)', 'icon' => 'bi-shield-check'],
                                    'enforcer' => ['bg' => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)', 'icon' => 'bi-person-badge']
                                ];
                                $avatarMeta = $roleColors[$userRole] ?? ['bg' => '#64748b', 'icon' => 'bi-person'];
                                $fullName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
                                if (empty($fullName)) {
                                    $fullName = '—';
                                }
                            ?>
                            <tr class="user-row" data-search="<?= esc(strtolower($user['username'] . ' ' . $user['email'] . ' ' . $fullName . ' ' . $userRole)) ?>">
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
                                    <?php if ($user['status'] === 'active'): ?>
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
                                        <?php if ($user['role'] === 'enforcer'): ?>
                                            <a href="<?= base_url('users/view-enforcer/' . $user['id']) ?>" class="action-btn btn-view" title="View Enforcer Profile">
                                                <i class="bi bi-person-vcard"></i>
                                            </a>
                                        <?php endif; ?>

                                        <a href="<?= base_url('users/edit/' . $user['id']) ?>" class="action-btn btn-edit" title="Edit User">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <button type="button" class="action-btn btn-reset btn-reset-password" 
                                                title="Reset Password" 
                                                data-id="<?= $user['id'] ?>" 
                                                data-username="<?= esc($user['username']) ?>">
                                            <i class="bi bi-key"></i>
                                        </button>

                                        <button type="button" class="action-btn btn-delete btn-delete-user" 
                                                title="Delete User" 
                                                data-id="<?= $user['id'] ?>"
                                                data-username="<?= esc($user['username']) ?>">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile & Tablet Responsive Cards -->
        <div class="d-lg-none p-3">
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
                        ?>
                        <div class="col-12 mobile-user-card" data-search="<?= esc(strtolower($user['username'] . ' ' . $user['email'] . ' ' . $fullName . ' ' . $userRole)) ?>">
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
                                        <?php if ($user['status'] === 'active'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <?php if ($user['role'] === 'enforcer'): ?>
                                        <a href="<?= base_url('users/view-enforcer/' . $user['id']) ?>" class="btn btn-sm btn-outline-info flex-grow-1">
                                            <i class="bi bi-person-vcard me-1"></i> View
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('users/edit/' . $user['id']) ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-warning btn-reset-password" data-id="<?= $user['id'] ?>" data-username="<?= esc($user['username']) ?>" title="Reset Password">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-user" data-id="<?= $user['id'] ?>" data-username="<?= esc($user['username']) ?>" title="Delete User">
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
            <div class="pagination-container">
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fast Live Search Filter
        const searchInput = document.getElementById('searchInput');
        const clearBtn = document.getElementById('searchClearBtn');
        const desktopRows = document.querySelectorAll('.user-row');
        const mobileCards = document.querySelectorAll('.mobile-user-card');

        function filterUsers(query) {
            const term = query.trim().toLowerCase();
            if (clearBtn) {
                clearBtn.style.display = term ? 'block' : 'none';
            }

            let matchCount = 0;

            // Desktop Rows
            desktopRows.forEach(row => {
                const haystack = row.getAttribute('data-search') || '';
                const match = haystack.includes(term);
                row.style.display = match ? '' : 'none';
                if (match) matchCount++;
            });

            // Mobile Cards
            mobileCards.forEach(card => {
                const haystack = card.getAttribute('data-search') || '';
                const match = haystack.includes(term);
                card.style.display = match ? '' : 'none';
            });

            // Dynamic Empty State
            let liveEmptyRow = document.getElementById('liveSearchEmptyRow');
            if (matchCount === 0 && (desktopRows.length > 0 || mobileCards.length > 0)) {
                if (!liveEmptyRow) {
                    const tbody = document.querySelector('#userTable tbody');
                    if (tbody) {
                        liveEmptyRow = document.createElement('tr');
                        liveEmptyRow.id = 'liveSearchEmptyRow';
                        liveEmptyRow.innerHTML = `
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-search fs-2 d-block mb-2 opacity-50"></i>
                                <h6 class="fw-bold mb-1">No matching users</h6>
                                <p class="small text-muted mb-2">No users matched "${escapeHtml(query)}"</p>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="clearSearchActionBtn">Clear Search</button>
                            </td>
                        `;
                        tbody.appendChild(liveEmptyRow);
                        document.getElementById('clearSearchActionBtn')?.addEventListener('click', clearSearch);
                    }
                }
            } else if (liveEmptyRow) {
                liveEmptyRow.remove();
            }
        }

        function clearSearch() {
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
                filterUsers('');
            }
        }

        function escapeHtml(string) {
            const div = document.createElement('div');
            div.innerText = string;
            return div.innerHTML;
        }

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterUsers(this.value);
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', clearSearch);
        }

        // Reset Password Modal
        const resetPasswordButtons = document.querySelectorAll('.btn-reset-password');
        const resetUsernameSpan = document.getElementById('resetUsername');
        const resetPasswordForm = document.getElementById('resetPasswordForm');
        const resetModalEl = document.getElementById('resetPasswordModal');
        const resetModal = resetModalEl ? new bootstrap.Modal(resetModalEl) : null;

        resetPasswordButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const username = this.getAttribute('data-username');
                
                if (resetUsernameSpan) resetUsernameSpan.textContent = username;
                const newPasswordInput = document.getElementById('new_password');
                if (newPasswordInput) newPasswordInput.value = '';
                if (resetPasswordForm) resetPasswordForm.action = `<?= base_url('users/reset-password') ?>/${userId}`;
                if (resetModal) resetModal.show();
            });
        });

        // Toggle Password Visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('new_password');
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('bi-eye');
                this.querySelector('i').classList.toggle('bi-eye-slash');
            });
        }

        // Delete Confirmation with SweetAlert2
        const deleteButtons = document.querySelectorAll('.btn-delete-user');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const username = this.getAttribute('data-username');
                
                Swal.fire({
                    title: 'Delete User?',
                    html: `Are you sure you want to delete user <strong>"${escapeHtml(username)}"</strong>?<br><span class="text-danger small">This action cannot be undone.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Yes, Delete',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-4 shadow-lg'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `<?= base_url('users/delete') ?>/${userId}`;
                        
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '<?= csrf_token() ?>';
                        csrfInput.value = '<?= csrf_hash() ?>';
                        
                        form.appendChild(csrfInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    });
</script>
<?= $this->endSection() ?>
