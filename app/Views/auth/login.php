<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Traffic System</title>
    <link rel="icon" type="image/png" href="<?= base_url('img/pic-1.png') ?>">
    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">
    <?= view('theme/pwa_head', ['pwaThemeDark' => '#faf3f3', 'pwaThemeLight' => '#faf3f3']) ?>
    <link href="<?= base_url('vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/bootstrap-icons/bootstrap-icons.css') ?>" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            box-sizing: border-box;
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
        .brand-mark {
            width: 90px;
            height: 90px;
            margin: 0 auto 20px;
        }
        .brand-logo {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 18px;
            background: #f8f9fa;
            border: 1px solid rgba(0,0,0,0.05);
            padding: 8px;
            display: block;
            box-sizing: border-box;
        }
        .brand-fallback {
            width: 100%;
            height: 100%;
            display: grid;
            place-items: center;
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 18px;
            background: #f0fdf4;
            color: #059669;
            font-size: 1.5rem;
            font-weight: 800;
        }
        [hidden] {
            display: none !important;
        }
        .form-side {
            padding: 42px 34px;
            background: #ffffff;
            box-sizing: border-box;
            width: 100%;
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
        #loginForm {
            width: 100%;
        }
        .login-field + .login-field {
            margin-top: 1.5rem;
        }
        .login-field .form-label {
            display: block;
        }
        .input-group {
            display: flex;
            align-items: stretch;
            width: 100%;
        }
        .input-group > .input-group-text {
            display: flex;
            flex: 0 0 42px;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }
        .input-group > .form-control {
            flex: 1 1 0;
            width: 1%;
            min-width: 0;
            box-sizing: border-box;
        }
        .login-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 1rem;
        }
        .login-options a {
            white-space: nowrap;
        }
        .form-check {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            min-width: 0;
        }
        .form-check-input {
            flex: 0 0 auto;
            margin: 0;
        }
        .remember-label {
            color: #718096;
            font-size: 0.875rem;
        }
        .login-actions {
            display: grid;
            margin-top: 1.5rem;
        }
        .password-toggle {
            display: flex;
            flex: 0 0 48px;
            align-items: center;
            justify-content: center;
            border-left: none;
            border-radius: 0 12px 12px 0;
            cursor: pointer;
            padding: 0;
            color: #475569;
            font: inherit;
        }
        .password-toggle:hover {
            color: #047857;
            background: #f8fafc;
        }
        .password-toggle:focus-visible {
            position: relative;
            z-index: 1;
            outline: 2px solid #059669;
            outline-offset: 2px;
        }
        .password-toggle i {
            font-size: 1.1rem;
            line-height: 1;
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
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 44px;
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
            margin-bottom: 0;
        }
        .d-none {
            display: none !important;
        }
        .spinner-border {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 0.15em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border 0.75s linear infinite;
        }
        @keyframes spinner-border {
            to { transform: rotate(360deg); }
        }
        @media (max-width: 576px) {
            body {
                padding: 12px;
            }
            .login-shell {
                border-radius: 18px;
            }
            .brand-mark {
                width: 72px;
                height: 72px;
                margin-bottom: 12px;
            }
            .brand-logo,
            .brand-fallback {
                border-radius: 14px;
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
                /* 16px minimum stops iOS Safari from auto-zooming when typing */
                font-size: 16px;
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
            <div class="brand-mark">
                <img class="brand-logo" id="brandLogo" src="img/pic-1.png" alt="Traffic System Logo">
                <span class="brand-fallback" id="brandFallback" aria-hidden="true" hidden>TS</span>
            </div>
            <h2>Traffic System</h2>
            <p>Welcome back. Enter your credentials to continue.</p>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('warning')) ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('login') ?>" method="POST" id="loginForm">
            <?= csrf_field() ?>
            <div class="login-field">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" id="email" 
                           placeholder="Enter your email" 
                           required 
                           value="<?= esc(old('email')) ?>"
                           autocomplete="email"
                           autocapitalize="none"
                           autocorrect="off"
                           spellcheck="false"
                           list="emailSuggestions">
                    <datalist id="emailSuggestions"></datalist>
                </div>
            </div>
            <div class="login-field">
                <label for="password" class="form-label">Password</label>
                <div class="input-group" id="passwordGroup">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" id="password" 
                           placeholder="Enter your password" 
                           required
                           autocomplete="current-password"
                           autocapitalize="none"
                           autocorrect="off"
                           spellcheck="false">
                    <button type="button" class="password-toggle" id="togglePasswordButton"
                            aria-label="Show password" aria-pressed="false">
                        <i class="bi bi-eye" id="toggleIcon" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="login-options">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe" style="border-color: #cbd5e0;">
                    <label class="remember-label" for="rememberMe">Remember me</label>
                </div>
                <a href="<?= base_url('forgot-password') ?>" class="small text-decoration-none"
                   style="color: #059669; font-weight: 600;">Forgot password?</a>
            </div>

            <div class="login-actions">
                <button type="submit" class="btn btn-primary btn-login" id="submitBtn">
                    <span id="btnText">Sign In</span>
                    <span id="btnSpinner" class="spinner-border d-none" role="status" aria-hidden="true"></span>
                </button>
                <!-- <a href="/register" class="btn btn-create">
                    <i class="bi bi-person-plus me-1"></i> Create Account
                </a> -->
            </div>
        </form>
        <p class="help-text">Need access help? Contact your administrator.</p>
        <div class="text-center mt-3">
            <button type="button" class="pwa-install-inline" data-pwa-install hidden>
                <i class="bi bi-download" aria-hidden="true"></i>
                Install app
            </button>
        </div>
    </div>
</div>

<script src="<?= base_url('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script>
    const brandLogo = document.getElementById('brandLogo');
    const brandFallback = document.getElementById('brandFallback');

    function showBrandFallback() {
        brandLogo.hidden = true;
        brandFallback.hidden = false;
    }

    brandLogo.addEventListener('error', showBrandFallback);
    if (brandLogo.complete && brandLogo.naturalWidth === 0) {
        showBrandFallback();
    }

    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        const toggleButton = document.getElementById('togglePasswordButton');
        const showPassword = passwordInput.type === 'password';

        if (showPassword) {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        }

        toggleButton.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
        toggleButton.setAttribute('aria-pressed', String(showPassword));
    }

    document.getElementById('togglePasswordButton').addEventListener('click', togglePassword);

    function resetLoginBtn() {
        const btn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const spinner = document.getElementById('btnSpinner');
        if (btn && btnText && spinner) {
            btn.disabled = false;
            btnText.textContent = 'Sign In';
            spinner.classList.add('d-none');
        }
    }

    window.addEventListener('pageshow', resetLoginBtn);

    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();

        if (!email || !password) {
            resetLoginBtn();
            return;
        }

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

<?= view('theme/posthog') ?>
<?= view('theme/pwa_scripts') ?>
</body>
</html>
