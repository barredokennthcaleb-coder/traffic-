<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Create User - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    /* Entrance Animation */
    @keyframes fadeSlideUp {
        from {
            opacity: 0;
            transform: translateY(16px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-container-card {
        animation: fadeSlideUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 20px;
        box-shadow: 0 12px 36px -8px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    /* Section Headers */
    .form-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .form-section-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    /* Input Controls */
    .form-group-wrap {
        position: relative;
    }

    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }

    .input-icon-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-icon-group .input-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1rem;
        pointer-events: none;
        transition: color 0.2s ease;
        z-index: 4;
    }

    .input-icon-group .form-control,
    .input-icon-group .form-select {
        padding-left: 42px;
        height: 46px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        font-size: 0.92rem;
        color: #0f172a;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .input-icon-group .form-control:focus,
    .input-icon-group .form-select:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    .input-icon-group .form-control:focus ~ .input-icon {
        color: #3b82f6;
    }

    /* Eye toggle inside input */
    .btn-toggle-eye {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        color: #94a3b8;
        padding: 4px;
        cursor: pointer;
        z-index: 5;
        transition: color 0.18s ease;
    }

    .btn-toggle-eye:hover {
        color: #334155;
    }

    /* Role Choice Cards */
    .role-choice-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .role-choice-card {
        border: 2px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 14px;
        padding: 14px 16px;
        cursor: pointer;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        align-items: center;
        gap: 12px;
        user-select: none;
        position: relative;
    }

    .role-choice-card:hover {
        background: #ffffff;
        border-color: #cbd5e1;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
    }

    .role-choice-card.selected.admin-card {
        border-color: #dc2626;
        background: linear-gradient(145deg, #fef2f2 0%, #ffffff 100%);
        box-shadow: 0 6px 18px rgba(220, 38, 38, 0.15);
    }

    .role-choice-card.selected.enforcer-card {
        border-color: #2563eb;
        background: linear-gradient(145deg, #eff6ff 0%, #ffffff 100%);
        box-shadow: 0 6px 18px rgba(37, 99, 235, 0.15);
    }

    .role-choice-card .check-circle {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        color: transparent;
        transition: all 0.2s ease;
    }

    .role-choice-card.selected .check-circle {
        color: #ffffff;
    }

    .role-choice-card.selected.admin-card .check-circle {
        background: #dc2626;
        border-color: #dc2626;
    }

    .role-choice-card.selected.enforcer-card .check-circle {
        background: #2563eb;
        border-color: #2563eb;
    }

    /* Auto-calc helper pill */
    .auto-badge {
        font-size: 0.7rem;
        background: #ecfdf5;
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.3);
        padding: 2px 7px;
        border-radius: 999px;
        font-weight: 600;
        margin-left: 6px;
    }

    /* Buttons */
    .btn-create-submit {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: #ffffff;
        font-weight: 600;
        padding: 10px 24px;
        border-radius: 12px;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.28);
    }

    .btn-create-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(37, 99, 235, 0.4);
        color: #ffffff;
    }

    .btn-create-submit:active {
        transform: translateY(1px) scale(0.98);
    }

    @media (max-width: 576px) {
        .role-choice-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
            <!-- Breadcrumb Navigation -->
            <div class="d-flex align-items-center gap-2 mb-3">
                <a href="<?= base_url('users') ?>" class="btn btn-sm btn-white border rounded-3 text-secondary shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Users
                </a>
                <span class="text-muted small">/</span>
                <span class="text-dark small fw-semibold">New Account Registration</span>
            </div>

            <!-- Main Form Card -->
            <div class="form-container-card">
                <div class="p-4 p-md-5">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-person-plus-fill text-primary"></i> Create New User
                            </h4>
                            <p class="text-muted small mb-0">Fill in the required information to register a new administrator or enforcer.</p>
                        </div>
                    </div>

                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 14px;" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-exclamation-triangle-fill fs-5 me-2 mt-0.5"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Please correct the following errors:</h6>
                                    <ul class="mb-0 ps-3 small">
                                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                            <li><?= esc($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('users/store') ?>" method="POST" id="createUserForm">
                        <?= csrf_field() ?>

                        <?php
                            $defaults = $defaults ?? ['username' => '', 'firstname' => '', 'lastname' => '', 'middle_initial' => '', 'age' => '', 'birthdate' => '', 'address' => '', 'password' => '', 'role' => 'enforcer'];
                            $currentRoleVal = old('role', $defaults['role'] ?? 'enforcer');
                        ?>

                        <!-- SECTION 1: Credentials & Role -->
                        <div class="form-section-title">
                            <div class="form-section-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <span>Account Credentials & Role</span>
                        </div>

                        <div class="row g-3 mb-4">
                            <!-- Role Choice (Interactive Cards) -->
                            <div class="col-12">
                                <label class="form-label">System Role <span class="text-danger">*</span></label>
                                <input type="hidden" name="role" id="roleInput" value="<?= esc($currentRoleVal) ?>">
                                <div class="role-choice-grid">
                                    <div class="role-choice-card enforcer-card <?= $currentRoleVal === 'enforcer' ? 'selected' : '' ?>" data-role="enforcer">
                                        <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 1.1rem;">
                                            <i class="bi bi-person-badge"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-6 lh-sm">Traffic Enforcer</div>
                                            <small class="text-muted" style="font-size: 0.76rem;">Issues citations & road inspections</small>
                                        </div>
                                        <div class="check-circle"><i class="bi bi-check-lg"></i></div>
                                    </div>

                                    <div class="role-choice-card admin-card <?= $currentRoleVal === 'admin' ? 'selected' : '' ?>" data-role="admin">
                                        <div class="bg-danger text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 1.1rem;">
                                            <i class="bi bi-shield-check"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-6 lh-sm">Administrator</div>
                                            <small class="text-muted" style="font-size: 0.76rem;">Full system, reports & staff control</small>
                                        </div>
                                        <div class="check-circle"><i class="bi bi-check-lg"></i></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <div class="input-icon-group">
                                    <i class="bi bi-person input-icon"></i>
                                    <input type="text" name="username" id="username" class="form-control" 
                                           placeholder="e.g. enforcer_mark" required autocomplete="off"
                                           value="<?= old('username', $defaults['username']) ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <div class="input-icon-group">
                                    <i class="bi bi-envelope input-icon"></i>
                                    <input type="email" name="email" id="email" class="form-control" 
                                           placeholder="e.g. mark@gmail.com" required autocomplete="off"
                                           value="<?= old('email') ?>">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-icon-group">
                                    <i class="bi bi-key input-icon"></i>
                                    <input type="password" name="password" id="password" class="form-control pe-5" 
                                           placeholder="Enter secure password (min. 5 chars)" autocomplete="new-password"
                                           value="<?= esc($defaults['password']) ?>">
                                    <button class="btn-toggle-eye" type="button" id="togglePasswordBtn" aria-label="Show or hide password">
                                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text small text-muted mt-1">Leave blank to use the system default password if configured.</div>
                            </div>
                        </div>

                        <!-- SECTION 2: Personal Information -->
                        <div class="form-section-title mt-4">
                            <div class="form-section-icon bg-info bg-opacity-10 text-info">
                                <i class="bi bi-person-lines-fill"></i>
                            </div>
                            <span>Personal Information</span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-5">
                                <label for="firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                                <div class="input-icon-group">
                                    <i class="bi bi-card-text input-icon"></i>
                                    <input type="text" name="firstname" id="firstname" class="form-control"
                                           placeholder="First name" required
                                           value="<?= old('firstname', $defaults['firstname']) ?>">
                                </div>
                            </div>

                            <div class="col-md-5">
                                <label for="lastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <div class="input-icon-group">
                                    <i class="bi bi-card-text input-icon"></i>
                                    <input type="text" name="lastname" id="lastname" class="form-control"
                                           placeholder="Last name" required
                                           value="<?= old('lastname', $defaults['lastname']) ?>">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label for="middle_initial" class="form-label">M.I.</label>
                                <input type="text" name="middle_initial" id="middle_initial" class="form-control text-center"
                                       placeholder="M.I." maxlength="10" style="height: 46px; border-radius: 12px;"
                                       value="<?= old('middle_initial', $defaults['middle_initial'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="birthdate" class="form-label">Birthdate <span class="text-danger">*</span></label>
                                <div class="input-icon-group">
                                    <i class="bi bi-calendar-event input-icon"></i>
                                    <input type="date" name="birthdate" id="birthdate" class="form-control" required
                                           value="<?= old('birthdate', $defaults['birthdate'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="age" class="form-label d-flex align-items-center">
                                    Age <span class="text-danger ms-1">*</span>
                                    <span class="auto-badge" id="autoAgeBadge"><i class="bi bi-magic me-1"></i>Auto-fills from birthdate</span>
                                </label>
                                <div class="input-icon-group">
                                    <i class="bi bi-hash input-icon"></i>
                                    <input type="number" name="age" id="age" class="form-control"
                                           min="1" max="120" step="1" placeholder="Calculated automatically" required
                                           value="<?= old('age', (string) $defaults['age']) ?>">
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: Address & Location -->
                        <div class="form-section-title mt-4">
                            <div class="form-section-icon bg-success bg-opacity-10 text-success">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <span>Address & Location</span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label for="address" class="form-label">Residential Address <span class="text-danger">*</span></label>
                                <div class="input-icon-group">
                                    <i class="bi bi-pin-map input-icon"></i>
                                    <input type="text" name="address" id="address" class="form-control"
                                           placeholder="Enter street, barangay, city (e.g. Brgy. Talubangi, Kabankalan City)" required
                                           value="<?= old('address', $defaults['address']) ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Action Controls -->
                        <div class="d-flex justify-content-between align-items-center border-top pt-4 mt-5">
                            <a href="<?= base_url('users') ?>" class="btn btn-light border px-4 py-2.5 rounded-3 fw-semibold">
                                <i class="bi bi-x-circle me-1.5"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-create-submit">
                                <i class="bi bi-check2-circle me-1.5"></i> Create User Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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

        // Interactive Role Selection Cards
        const roleCards = document.querySelectorAll('.role-choice-card');
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

        // Smart Automatic Age Calculation from Birthdate
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
                const computedAge = calculateAge(this.value);
                if (computedAge !== '') {
                    ageInput.value = computedAge;
                    ageInput.style.backgroundColor = '#ecfdf5';
                    setTimeout(() => {
                        ageInput.style.backgroundColor = '';
                    }, 500);
                }
            });

            // If birthdate is already prefilled (e.g. from old input) and age is empty, compute it
            if (birthdateInput.value && !ageInput.value) {
                const computed = calculateAge(birthdateInput.value);
                if (computed !== '') ageInput.value = computed;
            }
        }
    });
</script>
<?= $this->endSection() ?>
