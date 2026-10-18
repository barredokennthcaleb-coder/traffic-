<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Edit User - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    /* Smooth Entrance */
    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateY(14px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-wrapper {
        max-width: 860px;
        margin: 0 auto;
        animation: fadeSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .form-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 8px 30px -6px rgba(15, 23, 42, 0.07);
        padding: 2rem 2.25rem;
        transition: box-shadow 0.25s ease;
    }

    /* Section Subheadings */
    .section-divider {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.82rem;
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
        margin-top: 0.5rem;
    }

    .section-icon {
        width: 26px;
        height: 26px;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }

    /* Labels */
    .form-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Clean Input Icons & Controls */
    .field-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .field-group .field-icon {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.95rem;
        pointer-events: none;
        transition: color 0.2s ease;
        z-index: 3;
    }

    .field-group .form-control,
    .field-group .form-select {
        height: 42px;
        padding-left: 38px;
        padding-right: 14px;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        border-radius: 10px;
        font-size: 0.88rem;
        color: #0f172a;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .field-group .form-control:hover,
    .field-group .form-select:hover {
        border-color: #cbd5e1;
    }

    .field-group .form-control:focus,
    .field-group .form-select:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        outline: none;
    }

    .field-group:focus-within .field-icon {
        color: #2563eb;
    }

    .field-eye-btn {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: #94a3b8;
        padding: 6px 8px;
        border-radius: 6px;
        cursor: pointer;
        z-index: 4;
        transition: color 0.18s ease;
    }

    .field-eye-btn:hover {
        color: #334155;
    }

    /* Compact Role Selector */
    .role-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .role-card-item {
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 12px;
        padding: 10px 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        user-select: none;
        position: relative;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .role-card-item:hover {
        border-color: #cbd5e1;
        background: #ffffff;
        transform: translateY(-1px);
    }

    .role-card-item.selected.is-enforcer {
        border-color: #2563eb;
        background: linear-gradient(145deg, #eff6ff 0%, #ffffff 100%);
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.12);
    }

    .role-card-item.selected.is-admin {
        border-color: #dc2626;
        background: linear-gradient(145deg, #fef2f2 0%, #ffffff 100%);
        box-shadow: 0 4px 14px rgba(220, 38, 38, 0.12);
    }

    .role-card-item .role-badge-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .role-card-item .role-check {
        margin-left: auto;
        font-size: 1rem;
        color: #cbd5e1;
        transition: color 0.2s ease;
    }

    .role-card-item.selected.is-enforcer .role-check {
        color: #2563eb;
    }

    .role-card-item.selected.is-admin .role-check {
        color: #dc2626;
    }

    .calc-tag {
        font-size: 0.68rem;
        font-weight: 600;
        color: #059669;
        background: #ecfdf5;
        border: 1px solid rgba(16, 185, 129, 0.3);
        padding: 1px 6px;
        border-radius: 6px;
        text-transform: none;
        letter-spacing: normal;
    }

    .btn-submit-save {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: #ffffff;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 9px 24px;
        border-radius: 10px;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-submit-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
        color: #ffffff;
    }

    @media (max-width: 576px) {
        .form-card {
            padding: 1.25rem 1rem;
            border-radius: 14px;
        }

        .role-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid py-4">
    <div class="form-wrapper">
        <!-- Top Navigation -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="<?= base_url('users') ?>" class="btn btn-sm btn-light border text-secondary px-3 py-1.5" style="border-radius: 8px;">
                <i class="bi bi-arrow-left me-1"></i> Back to Users
            </a>
            <span class="text-muted small">Edit: <?= esc($user['username']) ?></span>
        </div>

        <!-- Form Card Container -->
        <div class="form-card">
            <!-- Header -->
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.3rem;">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0.5">Edit User Account</h5>
                    <p class="text-muted small mb-0">Update credentials, system role, and personal details.</p>
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

            <form action="<?= base_url('users/update/' . $user['id']) ?>" method="POST" id="editUserForm">
                <?= csrf_field() ?>

                <?php $currentRoleVal = old('role', $user['role']); ?>

                <!-- SECTION 1: Credentials & Role -->
                <div class="section-divider">
                    <span class="section-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-shield-lock"></i></span>
                    Account Credentials &amp; Role
                </div>

                <!-- Role Selection -->
                <div class="mb-3">
                    <label class="form-label">System Role <span class="text-danger">*</span></label>
                    <input type="hidden" name="role" id="roleInput" value="<?= esc($currentRoleVal) ?>">
                    <div class="role-grid">
                        <div class="role-card-item is-enforcer <?= $currentRoleVal === 'enforcer' ? 'selected' : '' ?>" data-role="enforcer">
                            <div class="role-badge-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem; line-height: 1.2;">Traffic Enforcer</div>
                                <div class="text-muted" style="font-size: 0.74rem;">Issue citations & inspections</div>
                            </div>
                            <i class="bi bi-check-circle-fill role-check"></i>
                        </div>

                        <div class="role-card-item is-admin <?= $currentRoleVal === 'admin' ? 'selected' : '' ?>" data-role="admin">
                            <div class="role-badge-icon bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem; line-height: 1.2;">Administrator</div>
                                <div class="text-muted" style="font-size: 0.74rem;">Full system & staff controls</div>
                            </div>
                            <i class="bi bi-check-circle-fill role-check"></i>
                        </div>
                    </div>
                </div>

                <!-- Username & Email (Clean 2-Column Row) -->
                <div class="row g-2.5 g-md-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <div class="field-group">
                            <i class="bi bi-person field-icon"></i>
                            <input type="text" name="username" id="username" class="form-control" 
                                   placeholder="Username" required autocomplete="off"
                                   value="<?= old('username', $user['username']) ?>">
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <div class="field-group">
                            <i class="bi bi-envelope field-icon"></i>
                            <input type="email" name="email" id="email" class="form-control" 
                                   placeholder="user@example.com" required autocomplete="off"
                                   value="<?= old('email', $user['email']) ?>">
                        </div>
                    </div>
                </div>

                <!-- Status & Password (2-Column Row) -->
                <div class="row g-2.5 g-md-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="status" class="form-label">Account Status <span class="text-danger">*</span></label>
                        <div class="field-group">
                            <i class="bi bi-activity field-icon"></i>
                            <select name="status" id="status" class="form-select" required>
                                <option value="active" <?= old('status', $user['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= old('status', $user['status']) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="suspended" <?= old('status', $user['status']) === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="password" class="form-label">
                            <span>New Password</span>
                            <span class="text-muted fw-normal small" style="font-size: 0.72rem;">Optional</span>
                        </label>
                        <div class="field-group">
                            <i class="bi bi-key field-icon"></i>
                            <input type="password" name="password" id="password" class="form-control pe-5" 
                                   placeholder="Leave blank to keep current" autocomplete="new-password">
                            <button class="field-eye-btn" type="button" id="togglePasswordBtn" aria-label="Toggle password visibility">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: Personal Information -->
                <div class="section-divider">
                    <span class="section-icon bg-info bg-opacity-10 text-info"><i class="bi bi-person-lines-fill"></i></span>
                    Personal Information
                </div>

                <!-- First Name, Last Name, Middle Initial (Balanced 3-Column Row) -->
                <div class="row g-2.5 g-md-3 mb-3">
                    <div class="col-12 col-sm-4 col-md-4">
                        <label for="firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                        <div class="field-group">
                            <i class="bi bi-card-text field-icon"></i>
                            <input type="text" name="firstname" id="firstname" class="form-control"
                                   placeholder="First name" required
                                   value="<?= old('firstname', $user['firstname'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="col-12 col-sm-4 col-md-4">
                        <label for="lastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <div class="field-group">
                            <i class="bi bi-card-text field-icon"></i>
                            <input type="text" name="lastname" id="lastname" class="form-control"
                                   placeholder="Last name" required
                                   value="<?= old('lastname', $user['lastname'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="col-12 col-sm-4 col-md-4">
                        <label for="middle_initial" class="form-label">Middle Initial <span class="text-muted fw-normal">(Optional)</span></label>
                        <div class="field-group">
                            <i class="bi bi-fonts field-icon"></i>
                            <input type="text" name="middle_initial" id="middle_initial" class="form-control text-center text-sm-start"
                                   placeholder="M.I." maxlength="10"
                                   value="<?= old('middle_initial', $user['middle_initial'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Birthdate & Age (2-Column Row) -->
                <div class="row g-2.5 g-md-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="birthdate" class="form-label">Birthdate <span class="text-danger">*</span></label>
                        <div class="field-group">
                            <i class="bi bi-calendar-event field-icon"></i>
                            <input type="date" name="birthdate" id="birthdate" class="form-control" required
                                   value="<?= old('birthdate', $user['birthdate'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="age" class="form-label">
                            <span>Age <span class="text-danger">*</span></span>
                            <span class="calc-tag"><i class="bi bi-magic me-1"></i>Calculated</span>
                        </label>
                        <div class="field-group">
                            <i class="bi bi-hash field-icon"></i>
                            <input type="number" name="age" id="age" class="form-control"
                                   min="1" max="120" step="1" placeholder="Calculated from birthdate" required
                                   value="<?= old('age', $user['age'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: Address -->
                <div class="section-divider">
                    <span class="section-icon bg-success bg-opacity-10 text-success"><i class="bi bi-geo-alt"></i></span>
                    Location Details
                </div>

                <div class="mb-4">
                    <label for="address" class="form-label">Complete Address <span class="text-danger">*</span></label>
                    <div class="field-group">
                        <i class="bi bi-pin-map field-icon"></i>
                        <input type="text" name="address" id="address" class="form-control"
                               placeholder="e.g. Brgy. Talubangi, Kabankalan City, Negros Occidental" required
                               value="<?= old('address', $user['address'] ?? '') ?>">
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-4">
                    <a href="<?= base_url('users') ?>" class="btn btn-light border px-3.5 py-2 text-secondary" style="border-radius: 9px; font-weight: 500;">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-submit-save">
                        <i class="bi bi-check2-circle me-1.5"></i> Update Account
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
        // Toggle Password Visibility
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn && passwordInput && toggleIcon) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                toggleIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
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
                        ageInput.style.backgroundColor = '';
                    }, 400);
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>
