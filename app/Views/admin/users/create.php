<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Create User - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    /* Smooth Entrance & Keyframes */
    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateY(16px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulseSuccess {
        0% { transform: scale(1); }
        50% { transform: scale(1.03); }
        100% { transform: scale(1); }
    }

    .form-wrapper {
        max-width: 860px;
        margin: 0 auto;
        animation: fadeSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .form-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 10px 30px -8px rgba(15, 23, 42, 0.06), 0 4px 10px -3px rgba(15, 23, 42, 0.03);
        padding: 2.25rem 2.5rem;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Section Subheadings */
    .section-divider {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #475569;
        margin-top: 1.75rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .section-divider:first-of-type {
        margin-top: 0.25rem;
    }

    .section-icon {
        width: 26px;
        height: 26px;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        transition: transform 0.2s ease;
    }

    /* Labels */
    .form-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Input Controls: Pure Clean White Blanks */
    .field-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .field-group .field-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.95rem;
        pointer-events: none;
        transition: color 0.2s ease, transform 0.2s ease;
        z-index: 3;
    }

    .field-group .form-control {
        height: 44px;
        padding-left: 42px;
        padding-right: 14px;
        border: 1.5px solid #e2e8f0;
        background-color: #ffffff;
        border-radius: 11px;
        font-size: 0.9rem;
        color: #0f172a;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .field-group .form-control:hover {
        border-color: #cbd5e1;
        background-color: #ffffff;
    }

    .field-group .form-control:focus {
        background-color: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        outline: none;
    }

    .field-group:focus-within .field-icon {
        color: #2563eb;
        transform: translateY(-50%) scale(1.08);
    }

    /* Masked Secret Field: Suppresses Chrome Google Password Manager popup completely */
    .masked-secret-field.is-masked {
        -webkit-text-security: disc !important;
        text-security: disc !important;
        letter-spacing: 0.12em;
        font-family: inherit;
    }

    .masked-secret-field:not(.is-masked) {
        -webkit-text-security: none !important;
        text-security: none !important;
        letter-spacing: normal;
    }

    /* Toggle Password Button */
    .field-eye-btn {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: #94a3b8;
        padding: 6px 10px;
        border-radius: 8px;
        cursor: pointer;
        z-index: 4;
        transition: all 0.18s ease;
    }

    .field-eye-btn:hover {
        color: #1e293b;
        background: #f1f5f9;
    }

    /* Role Selector */
    .role-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .role-card-item {
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        border-radius: 13px;
        padding: 12px 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 12px;
        user-select: none;
        position: relative;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .role-card-item:hover {
        border-color: #94a3b8;
        background: #fafafa;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px -4px rgba(15, 23, 42, 0.08);
    }

    .role-card-item.selected.is-enforcer {
        border-color: #2563eb;
        background: linear-gradient(145deg, #f0f7ff 0%, #ffffff 100%);
        box-shadow: 0 4px 16px rgba(37, 99, 235, 0.14);
    }

    .role-card-item.selected.is-admin {
        border-color: #dc2626;
        background: linear-gradient(145deg, #fff5f5 0%, #ffffff 100%);
        box-shadow: 0 4px 16px rgba(220, 38, 38, 0.14);
    }

    .role-card-item .role-badge-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }

    .role-card-item:hover .role-badge-icon {
        transform: scale(1.06);
    }

    .role-card-item .role-check {
        margin-left: auto;
        font-size: 1.1rem;
        color: #cbd5e1;
        transition: all 0.2s ease;
    }

    .role-card-item.selected.is-enforcer .role-check {
        color: #2563eb;
        transform: scale(1.1);
    }

    .role-card-item.selected.is-admin .role-check {
        color: #dc2626;
        transform: scale(1.1);
    }

    /* Badges & Feedback */
    .calc-tag {
        font-size: 0.7rem;
        font-weight: 600;
        color: #059669;
        background: #ecfdf5;
        border: 1px solid rgba(16, 185, 129, 0.25);
        padding: 2px 7px;
        border-radius: 6px;
        text-transform: none;
        letter-spacing: normal;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: background 0.2s ease;
    }

    .match-status-tag {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 6px;
        display: none;
        transition: all 0.2s ease;
    }

    .match-status-tag.is-match {
        display: inline-block;
        background: #ecfdf5;
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.3);
        animation: pulseSuccess 0.25s ease;
    }

    .match-status-tag.is-mismatch {
        display: inline-block;
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    /* Buttons */
    .btn-action-cancel {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        color: #64748b;
        font-weight: 600;
        font-size: 0.88rem;
        padding: 9px 18px;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-action-cancel:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #1e293b;
    }

    .btn-action-clear {
        background: #ffffff;
        border: 1.5px dashed #cbd5e1;
        color: #64748b;
        font-weight: 600;
        font-size: 0.88rem;
        padding: 9px 16px;
        border-radius: 10px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }

    .btn-action-clear:hover {
        background: #fef2f2;
        border-color: #fca5a5;
        color: #dc2626;
    }

    .btn-submit-save {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border: none;
        color: #ffffff;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 10px 24px;
        border-radius: 11px;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-submit-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(37, 99, 235, 0.35);
        color: #ffffff;
    }

    .btn-submit-save:active {
        transform: translateY(1px);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .form-card {
            padding: 1.75rem 1.25rem;
            border-radius: 16px;
        }

        .footer-action-bar {
            flex-direction: column-reverse;
            gap: 12px;
        }

        .footer-left-buttons {
            display: flex;
            width: 100%;
            gap: 10px;
        }

        .footer-left-buttons .btn-action-cancel,
        .footer-left-buttons .btn-action-clear {
            flex: 1;
            justify-content: center;
        }

        .btn-submit-save {
            width: 100%;
            justify-content: center;
            padding: 11px 20px;
        }
    }

    @media (max-width: 576px) {
        .form-card {
            padding: 1.25rem 0.95rem;
            border-radius: 14px;
        }

        .role-grid {
            grid-template-columns: 1fr;
            gap: 9px;
        }

        .field-group .form-control {
            height: 44px;
            font-size: 16px; /* Smooth mobile typing without iOS auto-zoom */
        }

        .section-divider {
            font-size: 0.75rem;
            margin-top: 1.35rem;
        }
    }
</style>

<div class="container-fluid py-3 py-md-4">
    <div class="form-wrapper">
        <!-- Top Navigation -->
        <div class="d-flex align-items-center justify-content-between mb-3 px-1">
            <a href="<?= base_url('users') ?>" class="btn btn-sm btn-light border text-secondary px-3 py-1.5" style="border-radius: 8px;">
                <i class="bi bi-arrow-left me-1"></i> Back to Users
            </a>
            <span class="text-muted small fw-medium">New System Account</span>
        </div>

        <!-- Form Card Container -->
        <div class="form-card">
            <!-- Header -->
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.3rem;">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0.5">Create New User</h5>
                    <p class="text-muted small mb-0">Register a new administrator or traffic enforcer in the system.</p>
                </div>
            </div>

            <!-- Validation Errors -->
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-3 py-2.5 px-3" style="border-radius: 10px;" role="alert">
                    <div class="d-flex align-items-center mb-1">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                        <strong class="small">Please correct the following:</strong>
                    </div>
                    <ul class="mb-0 ps-4 small text-danger">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('users/store') ?>" method="POST" id="userForm" autocomplete="off">
                <?= csrf_field() ?>

                <?php
                    $defaults = $defaults ?? ['username' => '', 'firstname' => '', 'lastname' => '', 'middle_initial' => '', 'age' => '', 'birthdate' => '', 'address' => '', 'password' => '', 'role' => 'enforcer'];
                    $currentRoleVal = old('role', $defaults['role'] ?? 'enforcer');
                ?>

                <!-- 1. FIRST: SYSTEM ROLE -->
                <div class="section-divider">
                    <span class="section-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-shield-check"></i></span>
                    1. System Role &amp; Permissions
                </div>

                <div class="mb-3">
                    <label class="form-label">Select System Role <span class="text-danger">*</span></label>
                    <input type="hidden" name="role" id="roleInput" value="<?= esc($currentRoleVal) ?>">
                    <div class="role-grid">
                        <div class="role-card-item is-enforcer <?= $currentRoleVal === 'enforcer' ? 'selected' : '' ?>" data-role="enforcer">
                            <div class="role-badge-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem; line-height: 1.2;">Traffic Enforcer</div>
                                <div class="text-muted" style="font-size: 0.74rem;">Issue citations & roadside inspections</div>
                            </div>
                            <i class="bi bi-check-circle-fill role-check"></i>
                        </div>

                        <div class="role-card-item is-admin <?= $currentRoleVal === 'admin' ? 'selected' : '' ?>" data-role="admin">
                            <div class="role-badge-icon bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem; line-height: 1.2;">Administrator</div>
                                <div class="text-muted" style="font-size: 0.74rem;">Full system, reports & staff controls</div>
                            </div>
                            <i class="bi bi-check-circle-fill role-check"></i>
                        </div>
                    </div>
                </div>

                <!-- 2. THEN: PERSONAL INFORMATION -->
                <div class="section-divider">
                    <span class="section-icon bg-info bg-opacity-10 text-info"><i class="bi bi-person-lines-fill"></i></span>
                    2. Personal Information
                </div>

                <!-- First Name, Last Name, Middle Initial -->
                <div class="row g-2.5 g-md-3 mb-3">
                    <div class="col-12 col-sm-6 col-md-5">
                        <label for="firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                        <div class="field-group">
                            <i class="bi bi-person field-icon"></i>
                            <input type="text" name="firstname" id="firstname" class="form-control"
                                   required autocomplete="off"
                                   value="<?= old('firstname', $defaults['firstname']) ?>">
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-5">
                        <label for="lastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <div class="field-group">
                            <i class="bi bi-person field-icon"></i>
                            <input type="text" name="lastname" id="lastname" class="form-control"
                                   required autocomplete="off"
                                   value="<?= old('lastname', $defaults['lastname']) ?>">
                        </div>
                    </div>

                    <div class="col-12 col-sm-12 col-md-2">
                        <label for="middle_initial" class="form-label">M.I. <span class="text-muted fw-normal small">(Optional)</span></label>
                        <div class="field-group">
                            <i class="bi bi-fonts field-icon"></i>
                            <input type="text" name="middle_initial" id="middle_initial" class="form-control text-center"
                                   maxlength="10" autocomplete="off"
                                   value="<?= old('middle_initial', $defaults['middle_initial'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Birthdate & Age (Auto-calculated) -->
                <div class="row g-2.5 g-md-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="birthdate" class="form-label">Birthdate <span class="text-danger">*</span></label>
                        <div class="field-group">
                            <i class="bi bi-calendar-event field-icon"></i>
                            <input type="date" name="birthdate" id="birthdate" class="form-control" required
                                   value="<?= old('birthdate', $defaults['birthdate'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="age" class="form-label">
                            <span>Age <span class="text-danger">*</span></span>
                            <span class="calc-tag"><i class="bi bi-magic"></i>Auto-fills from birthdate</span>
                        </label>
                        <div class="field-group">
                            <i class="bi bi-hash field-icon"></i>
                            <input type="number" name="age" id="age" class="form-control"
                                   min="1" max="120" step="1" required
                                   value="<?= old('age', (string) $defaults['age']) ?>">
                        </div>
                    </div>
                </div>

                <!-- Complete Address -->
                <div class="mb-3">
                    <label for="address" class="form-label">Complete Address <span class="text-danger">*</span></label>
                    <div class="field-group">
                        <i class="bi bi-geo-alt field-icon"></i>
                        <input type="text" name="address" id="address" class="form-control"
                               required autocomplete="off"
                               value="<?= old('address', $defaults['address']) ?>">
                    </div>
                </div>

                <!-- 3. THEN: USERNAME & EMAIL -->
                <div class="section-divider">
                    <span class="section-icon bg-warning bg-opacity-10 text-dark"><i class="bi bi-person-circle"></i></span>
                    3. Account Credentials
                </div>

                <div class="row g-2.5 g-md-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <div class="field-group">
                            <i class="bi bi-person-badge field-icon"></i>
                            <input type="text" name="username" id="username" class="form-control" 
                                   required autocomplete="off"
                                   value="<?= old('username', $defaults['username']) ?>">
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <div class="field-group">
                            <i class="bi bi-envelope field-icon"></i>
                            <input type="email" name="email" id="email" class="form-control" 
                                   required autocomplete="off"
                                   value="<?= old('email') ?>">
                        </div>
                    </div>
                </div>

                <!-- 4. LASTLY: PASSWORD WITH 2 CONFIRMATION FIELDS -->
                <div class="section-divider">
                    <span class="section-icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-key-fill"></i></span>
                    4. Security &amp; Password
                </div>

                <div class="row g-2.5 g-md-3 mb-2">
                    <!-- Password 1: Masked text field to suppress Chrome Password Manager popup -->
                    <div class="col-12 col-md-6">
                        <label for="password" class="form-label">
                            <span>Password <span class="text-danger">*</span></span>
                            <span class="text-muted fw-normal small" style="font-size: 0.72rem;">Min. 5 characters</span>
                        </label>
                        <div class="field-group">
                            <i class="bi bi-lock field-icon"></i>
                            <input type="text" name="password" id="password" class="form-control pe-5 masked-secret-field is-masked" 
                                   required autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                                   data-lpignore="true" data-1p-ignore="true">
                            <button class="field-eye-btn" type="button" id="togglePasswordBtn" aria-label="Toggle password visibility">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Password 2: Confirmation -->
                    <div class="col-12 col-md-6">
                        <label for="confirm_password" class="form-label">
                            <span>Confirm Password <span class="text-danger">*</span></span>
                            <span class="match-status-tag" id="matchStatusTag"></span>
                        </label>
                        <div class="field-group">
                            <i class="bi bi-shield-lock field-icon"></i>
                            <input type="text" name="confirm_password" id="confirm_password" class="form-control pe-5 masked-secret-field is-masked" 
                                   required autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                                   data-lpignore="true" data-1p-ignore="true">
                            <button class="field-eye-btn" type="button" id="toggleConfirmPasswordBtn" aria-label="Toggle confirm password visibility">
                                <i class="bi bi-eye" id="toggleConfirmPasswordIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="text-muted small mb-4" style="font-size: 0.75rem;">
                    <i class="bi bi-info-circle me-1"></i> Enter the password above, then re-enter to confirm the final password that will be used.
                </div>

                <!-- Footer Actions -->
                <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-4 footer-action-bar">
                    <div class="footer-left-buttons d-flex align-items-center gap-2">
                        <a href="<?= base_url('users') ?>" class="btn-action-cancel">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="button" class="btn-action-clear" id="clearFormBtn" title="Clear all input blanks">
                            <i class="bi bi-eraser"></i> Clear Blanks
                        </button>
                    </div>
                    <button type="submit" class="btn btn-submit-save" id="submitBtn">
                        <i class="bi bi-check2-circle"></i> Save &amp; Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        const confirmInput = document.getElementById('confirm_password');
        const toggleConfirmBtn = document.getElementById('toggleConfirmPasswordBtn');
        const toggleConfirmIcon = document.getElementById('toggleConfirmPasswordIcon');

        const matchTag = document.getElementById('matchStatusTag');

        // Check CSS -webkit-text-security support; fallback to type=password if missing
        const supportsTextSecurity = ('webkitTextSecurity' in document.body.style) || 
            (window.CSS && CSS.supports && (CSS.supports('-webkit-text-security', 'disc') || CSS.supports('text-security', 'disc')));

        if (!supportsTextSecurity) {
            if (passwordInput) passwordInput.type = 'password';
            if (confirmInput) confirmInput.type = 'password';
        }

        // Toggle Password 1 Visibility
        if (toggleBtn && passwordInput && toggleIcon) {
            toggleBtn.addEventListener('click', function () {
                if (supportsTextSecurity) {
                    const isMasked = passwordInput.classList.contains('is-masked');
                    if (isMasked) {
                        passwordInput.classList.remove('is-masked');
                        toggleIcon.className = 'bi bi-eye-slash';
                    } else {
                        passwordInput.classList.add('is-masked');
                        toggleIcon.className = 'bi bi-eye';
                    }
                } else {
                    const isPass = passwordInput.type === 'password';
                    passwordInput.type = isPass ? 'text' : 'password';
                    toggleIcon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
                }
            });
        }

        // Toggle Password 2 (Confirm) Visibility
        if (toggleConfirmBtn && confirmInput && toggleConfirmIcon) {
            toggleConfirmBtn.addEventListener('click', function () {
                if (supportsTextSecurity) {
                    const isMasked = confirmInput.classList.contains('is-masked');
                    if (isMasked) {
                        confirmInput.classList.remove('is-masked');
                        toggleConfirmIcon.className = 'bi bi-eye-slash';
                    } else {
                        confirmInput.classList.add('is-masked');
                        toggleConfirmIcon.className = 'bi bi-eye';
                    }
                } else {
                    const isPass = confirmInput.type === 'password';
                    confirmInput.type = isPass ? 'text' : 'password';
                    toggleConfirmIcon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
                }
            });
        }

        // Live Password Match Indicator
        function validatePasswordMatch() {
            if (!passwordInput || !confirmInput || !matchTag) return;
            const p1 = passwordInput.value;
            const p2 = confirmInput.value;

            if (p2.length === 0) {
                matchTag.className = 'match-status-tag';
                matchTag.style.display = 'none';
                confirmInput.style.borderColor = '#e2e8f0';
                return;
            }

            if (p1 === p2) {
                matchTag.className = 'match-status-tag is-match';
                matchTag.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Passwords match';
                confirmInput.style.borderColor = '#10b981';
            } else {
                matchTag.className = 'match-status-tag is-mismatch';
                matchTag.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i>Passwords do not match';
                confirmInput.style.borderColor = '#ef4444';
            }
        }

        if (passwordInput && confirmInput) {
            passwordInput.addEventListener('input', validatePasswordMatch);
            confirmInput.addEventListener('input', validatePasswordMatch);
        }

        // Role Cards Selection
        const roleCards = document.querySelectorAll('.role-card-item');
        const roleInput = document.getElementById('roleInput');

        roleCards.forEach(card => {
            card.addEventListener('click', function () {
                roleCards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                if (roleInput) {
                    roleInput.value = this.getAttribute('data-role');
                }
            });
        });

        // Automatic Age Calculation from Birthdate
        const birthdateInput = document.getElementById('birthdate');
        const ageInput = document.getElementById('age');

        function calculateAge(birthdateStr) {
            if (!birthdateStr) return '';
            const birthDate = new Date(birthdateStr);
            if (isNaN(birthDate.getTime())) return '';
            
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return (age >= 0 && age <= 120) ? age : '';
        }

        if (birthdateInput && ageInput) {
            birthdateInput.addEventListener('change', function () {
                const computed = calculateAge(this.value);
                if (computed !== '') {
                    ageInput.value = computed;
                    ageInput.style.backgroundColor = '#ecfdf5';
                    setTimeout(() => {
                        ageInput.style.backgroundColor = '#ffffff';
                    }, 400);
                }
            });

            if (birthdateInput.value && !ageInput.value) {
                const computed = calculateAge(birthdateInput.value);
                if (computed !== '') ageInput.value = computed;
            }
        }

        // Clear Blanks Feature
        const clearBtn = document.getElementById('clearFormBtn');
        const userForm = document.getElementById('userForm');

        if (clearBtn && userForm) {
            clearBtn.addEventListener('click', function () {
                const inputs = userForm.querySelectorAll('input:not([type="hidden"])');
                inputs.forEach(input => {
                    input.value = '';
                    input.style.borderColor = '#e2e8f0';
                });
                if (passwordInput) {
                    passwordInput.classList.add('is-masked');
                    if (toggleIcon) toggleIcon.className = 'bi bi-eye';
                }
                if (confirmInput) {
                    confirmInput.classList.add('is-masked');
                    if (toggleConfirmIcon) toggleConfirmIcon.className = 'bi bi-eye';
                }
                if (matchTag) {
                    matchTag.style.display = 'none';
                    matchTag.className = 'match-status-tag';
                }
                roleCards.forEach(c => c.classList.remove('selected'));
                const defaultCard = document.querySelector('.role-card-item[data-role="enforcer"]');
                if (defaultCard) defaultCard.classList.add('selected');
                if (roleInput) roleInput.value = 'enforcer';

                inputs[0]?.focus();
            });
        }
    });
</script>
<?= $this->endSection() ?>
