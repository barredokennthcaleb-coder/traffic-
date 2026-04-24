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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header .logo-wrapper {
            margin-bottom: 1rem;
        }
        .login-header .logo-wrapper img {
            max-width: 120px;
            height: auto;
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.1));
        }
        .login-header h2 {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }
        .login-header p {
            color: #6c757d;
            font-size: 0.95rem;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background-color: #fff;
        }
        .input-group:focus-within .input-group-text {
            border-color: #667eea;
            color: #667eea;
        }
        .input-group:focus-within .form-control {
            border-color: #667eea;
        }
        .input-group-text {
            border: 2px solid #e9ecef;
            border-right: none;
            background: #fff;
            border-radius: 10px 0 0 10px;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        .input-group .form-control:focus {
            border-left: none;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .alert-success {
            background-color: #d1e7dd;
            color: #0a3622;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #58151c;
        }
        .alert-warning {
            background-color: #fff3cd;
            color: #664d03;
        }
        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
        }
        a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        a:hover {
            color: #764ba2;
        }
        .password-toggle {
            cursor: pointer;
            border: 2px solid #e9ecef;
            border-left: none;
            border-radius: 0 10px 10px 0;
            background: #fff;
            padding: 0 12px;
            display: flex;
            align-items: center;
        }
        .password-toggle:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body>

<div class="card login-card">
    <div class="card-body p-4 p-md-5">
        <div class="login-header">
            <div class="logo-wrapper">
                <img src="/img/pic 1.png" alt="Traffic System Logo">
            </div>
            <h2>Traffic System</h2>
            <p>Sign in to access your account</p>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('warning') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
            </div>
        </form>
        <div class="text-center mt-4">
            <p class="text-muted mb-0">Don't have an account? <a href="/register"><strong>Create Account</strong></a></p>
        </div>
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