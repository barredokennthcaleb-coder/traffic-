<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Traffic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: #faf3f3ff;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            position: relative;
            color: #1a202c;
        }
        /* blobs removed for clear white theme */
        .login-shell {
            width: 100%;
            max-width: 480px;
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.06);
            background: #ffffff;
        }
        .brand-logo {
            width: 90px;
            height: 90px;
            object-fit: contain;
            border-radius: 18px;
            background: #f8f9fa;
            border: 1px solid rgba(0,0,0,0.05);
            padding: 8px;
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .form-side {
            padding: 42px 34px;
            background: #ffffff;
        }
        .form-head {
            text-align: center;
        }
        .form-head h2 {
            margin: 0;
            font-size: 1.65rem;
            font-weight: 800;
            color: #1a202c;
        }
        .form-head p {
            margin-top: 6px;
            color: #718096;
            margin-bottom: 22px;
        }
        .form-label {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 0.5rem;
        }
        .input-group-text,
        .form-control,
        .password-toggle {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #2d3748;
        }
        .input-group-text {
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: #a0aec0;
        }
        .form-control {
            border-radius: 0 12px 12px 0;
            padding: 0.78rem 0.9rem;
        }
        .form-control::placeholder {
            color: #a0aec0;
        }
        .form-control:focus {
            border-color: #6ee7b7;
            box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.2);
            background: #ffffff;
            color: #1a202c;
        }
        .password-toggle {
            border-left: none;
            border-radius: 0 12px 12px 0;
            cursor: pointer;
            color: #a0aec0;
        }
        .btn-login {
            border: none;
            border-radius: 12px;
            padding: 0.85rem;
            font-size: 1rem;
            font-weight: 700;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            box-shadow: 0 10px 24px rgba(16, 185, 129, 0.3);
            transition: 0.25s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(16, 185, 129, 0.4);
            color: #ffffff;
        }
        .btn-create {
            border-radius: 12px;
            border: 1px solid rgba(173, 189, 245, 0.45);
            color: #d9e2ff;
            background: rgba(255, 255, 255, 0.05);
            font-weight: 600;
        }
        .btn-create:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
        }
        .alert {
            border: none;
            border-radius: 12px;
        }
        .alert-success { background-color: #194336; color: #d0ffe8; }
        .alert-danger { background-color: #4a2530; color: #ffd7de; }
        .alert-warning { background-color: #57451f; color: #ffebb5; }
        .help-text {
            color: #718096;
            font-size: 0.9rem;
            text-align: center;
            margin-top: 12px;
        }
        @media (max-width: 576px) {
            body {
                padding: 12px;
            }
            .login-shell {
                border-radius: 18px;
            }
            .brand-logo {
                width: 72px;
                height: 72px;
                border-radius: 14px;
                margin-bottom: 12px;
            }
            .form-side {
                padding: 20px 16px;
            }
            .form-head h2 {
                font-size: 1.3rem;
                line-height: 1.2;
            }
            .form-head p {
                font-size: 0.86rem;
                margin-bottom: 16px;
                line-height: 1.45;
            }
            .form-label {
                font-size: 0.86rem;
                margin-bottom: 0.35rem;
            }
            .form-control {
                font-size: 0.9rem;
                padding: 0.68rem 0.78rem;
            }
            .btn-login,
            .btn-create {
                font-size: 0.9rem;
                padding: 0.72rem;
            }
            .help-text {
                font-size: 0.8rem;
                line-height: 1.4;
            }
        }
    </style>
</head>
<body>

<div class="login-shell">
    <div class="form-side">
        <div class="form-head text-center">
            <img class="brand-logo" src="<?= base_url('img/pic 1.png') ?>" alt="Traffic System Logo">
            <h2>Traffic System</h2>
            <p>Welcome back. Enter your credentials to continue.</p>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('warning') ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST" id="loginForm" novalidate>
            <?= csrf_field() ?>
            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" id="email" 
                           placeholder="Enter your email" 
                           required 
                           value="<?= old('email') ?>"
                           autocomplete="email"
                           list="emailSuggestions">
                    <datalist id="emailSuggestions"></datalist>
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group" id="passwordGroup">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" id="password" 
                           placeholder="Enter your password" 
                           required
                           autocomplete="current-password">
                    <span class="input-group-text password-toggle" onclick="togglePassword()">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </span>
                </div>
            </div>
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe" style="border-color: #cbd5e0;">
                    <label class="form-check-label text-muted small" for="rememberMe">Remember me</label>
                </div>
                <a href="<?= base_url('forgot-password') ?>" class="text-decoration-none small" style="color: #059669; font-weight: 600;">Forgot password?</a>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary btn-login" id="submitBtn">
                    <span id="btnText">Sign In</span>
                    <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                </button>
                <!-- <a href="/register" class="btn btn-create">
                    <i class="bi bi-person-plus me-1"></i> Create Account
                </a> -->
            </div>
        </form>
        <p class="help-text mb-0">Need access help? Contact your administrator.</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        }
    }

    document.getElementById('loginForm').addEventListener('submit', function() {
        const email = document.getElementById('email').value;
        if (email) {
            saveEmail(email);
        }

        const btn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const spinner = document.getElementById('btnSpinner');
        
        btn.disabled = true;
        btnText.textContent = 'Signing in...';
        spinner.classList.remove('d-none');
    });

    // Handle Recently Used Accounts
    function saveEmail(email) {
        let savedEmails = JSON.parse(localStorage.getItem('traffic_saved_emails') || '[]');
        if (!savedEmails.includes(email)) {
            savedEmails.push(email);
            // Keep only last 5 accounts
            if (savedEmails.length > 5) savedEmails.shift();
            localStorage.setItem('traffic_saved_emails', JSON.stringify(savedEmails));
        }
    }

    function loadSavedEmails() {
        const savedEmails = JSON.parse(localStorage.getItem('traffic_saved_emails') || '[]');
        const datalist = document.getElementById('emailSuggestions');
        
        if (savedEmails.length > 0) {
            datalist.innerHTML = savedEmails.map(email => `<option value="${email}">`).join('');
            
            // Also show a small hint if it's the first visit back
            if (!document.getElementById('email').value && savedEmails.length > 0) {
                // Optional: you could auto-fill the last one or just let the datalist handle it
            }
        }
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', loadSavedEmails);
</script>

</body>
</html>