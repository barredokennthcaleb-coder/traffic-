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
            background: radial-gradient(circle at 15% 10%, #29336d 0%, #131a3e 38%, #0b1027 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            position: relative;
            color: #e8edff;
        }
        body::before,
        body::after {
            content: "";
            position: fixed;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            filter: blur(105px);
            z-index: -1;
            pointer-events: none;
        }
        body::before {
            background: rgba(73, 132, 255, 0.45);
            top: -140px;
            left: -120px;
        }
        body::after {
            background: rgba(170, 88, 255, 0.4);
            bottom: -170px;
            right: -140px;
        }
        .login-shell {
            width: 100%;
            max-width: 980px;
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 30px 80px rgba(3, 8, 26, 0.55);
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            background: rgba(9, 15, 38, 0.88);
            backdrop-filter: blur(12px);
        }
        .brand-side {
            padding: 44px 40px;
            background: linear-gradient(160deg, rgba(70, 101, 255, 0.26) 0%, rgba(116, 58, 255, 0.14) 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }
        .brand-logo {
            width: 90px;
            height: 90px;
            object-fit: contain;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px;
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .brand-side h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
            text-align: center;
        }
        .brand-side p {
            color: #bdc8f6;
            margin-bottom: 28px;
            text-align: center;
        }
        .premium-points {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 10px;
        }
        .premium-points li {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 12px;
            padding: 11px 12px;
            color: #d9e1ff;
            font-size: 0.92rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
        }
        .form-side {
            padding: 42px 34px;
            background: linear-gradient(165deg, rgba(17, 24, 58, 0.95) 0%, rgba(10, 15, 34, 0.95) 100%);
        }
        .form-head h2 {
            margin: 0;
            font-size: 1.65rem;
            font-weight: 800;
        }
        .form-head p {
            margin-top: 6px;
            color: #aebce8;
            margin-bottom: 22px;
        }
        .form-label {
            font-weight: 600;
            color: #d5defd;
            margin-bottom: 0.5rem;
        }
        .input-group-text,
        .form-control,
        .password-toggle {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(181, 196, 255, 0.25);
            color: #eef2ff;
        }
        .input-group-text {
            border-right: none;
            border-radius: 12px 0 0 12px;
        }
        .form-control {
            border-radius: 0 12px 12px 0;
            padding: 0.78rem 0.9rem;
        }
        .form-control::placeholder {
            color: #9caad6;
        }
        .form-control:focus {
            border-color: #7fa2ff;
            box-shadow: 0 0 0 0.2rem rgba(105, 137, 255, 0.25);
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
        }
        .password-toggle {
            border-left: none;
            border-radius: 0 12px 12px 0;
            cursor: pointer;
        }
        .btn-login {
            border: none;
            border-radius: 12px;
            padding: 0.85rem;
            font-size: 1rem;
            font-weight: 700;
            background: linear-gradient(135deg, #4d78ff 0%, #7c54f9 100%);
            box-shadow: 0 14px 32px rgba(78, 103, 226, 0.4);
            transition: 0.25s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(78, 103, 226, 0.5);
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
            color: #9eaddd;
            font-size: 0.9rem;
            text-align: center;
            margin-top: 12px;
        }
        @media (max-width: 900px) {
            .login-shell {
                grid-template-columns: 1fr;
                max-width: 520px;
            }
            .brand-side {
                padding: 28px 26px;
                border-right: 0;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            .form-side {
                padding: 28px 24px;
            }
        }
        @media (max-width: 576px) {
            body {
                padding: 12px;
            }
            .login-shell {
                border-radius: 18px;
            }
            .brand-side {
                padding: 20px 16px;
            }
            .brand-logo {
                width: 72px;
                height: 72px;
                border-radius: 14px;
                margin-bottom: 12px;
            }
            .brand-side h1 {
                font-size: 1.35rem;
                line-height: 1.2;
                margin-bottom: 6px;
            }
            .brand-side p {
                font-size: 0.88rem;
                line-height: 1.45;
                margin-bottom: 14px;
            }
            .premium-points {
                gap: 8px;
            }
            .premium-points li {
                font-size: 0.82rem;
                padding: 8px 10px;
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
    <div class="brand-side d-flex flex-column justify-content-center align-items-center">
        <img class="brand-logo" src="<?= base_url('img/pic 1.png') ?>" alt="Traffic System Logo">
        <h1>Traffic System</h1>
        <p>Manage violations, payments, and enforcement with a premium-grade workflow.</p>
        <!-- <ul class="premium-points">
            <li><i class="bi bi-shield-check me-2"></i>Secure role-based dashboard access</li>
            <li><i class="bi bi-graph-up-arrow me-2"></i>Real-time traffic violation tracking</li>
            <li><i class="bi bi-receipt-cutoff me-2"></i>Fast digital payment and receipt flow</li>
        </ul> -->
    </div>
    <div class="form-side">
        <div class="form-head">
            <h2>Sign in</h2>
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