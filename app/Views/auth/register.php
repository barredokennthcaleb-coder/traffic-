<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Traffic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle at 10% 20%, #28c4b5 0%, #1a9f95 40%, #127f7a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 24px 14px;
            position: relative;
        }
        body::before,
        body::after {
            content: '';
            position: fixed;
            width: 330px;
            height: 330px;
            border-radius: 50%;
            filter: blur(85px);
            z-index: -1;
            opacity: 0.5;
        }
        body::before {
            top: -130px;
            right: -100px;
            background: #66f0d8;
        }
        body::after {
            bottom: -140px;
            left: -100px;
            background: #89ffd3;
        }
        .register-card {
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(255,255,255,0.32);
            border-radius: 24px;
            box-shadow: 0 25px 70px rgba(7, 45, 42, 0.35);
            background: linear-gradient(160deg, rgba(255,255,255,0.93) 0%, rgba(244,255,251,0.82) 100%);
            backdrop-filter: blur(12px);
            overflow: hidden;
        }
        .register-card::before {
            content: "";
            position: absolute;
            inset: -40% auto auto -20%;
            width: 240px;
            height: 240px;
            background: radial-gradient(circle, rgba(98, 255, 204, 0.28) 0%, rgba(98, 255, 204, 0) 68%);
            pointer-events: none;
        }
        .register-card {
            position: relative;
        }
        .card-body {
            padding: 2.5rem;
        }
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .register-header .icon-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #149c92 0%, #3ac78e 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .register-header .icon-wrapper i {
            font-size: 2.5rem;
            color: white;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border: 1px solid #cde9e0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.82);
        }
        .form-control:focus {
            border-color: #11998e;
            box-shadow: 0 0 0 0.2rem rgba(17, 153, 142, 0.25);
        }
        .form-control.is-invalid {
            border-color: #dc3545;
        }
        .input-group-text {
            border: 1px solid #cde9e0;
            border-right: none;
            background: rgba(255,255,255,0.82);
            border-radius: 12px 0 0 12px;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }
        .input-group .form-control:focus {
            border-left: none;
        }
        .btn-register {
            background: linear-gradient(135deg, #149c92 0%, #31bf85 100%);
            border: none;
            border-radius: 12px;
            padding: 0.82rem;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 14px 30px rgba(25, 145, 118, 0.3);
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(17, 153, 142, 0.4);
            color: white;
        }
        .btn-register:active {
            transform: translateY(0);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #58151c;
        }
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
        }
        a {
            color: #11998e;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        a:hover {
            color: #38ef7d;
        }
        .password-toggle {
            cursor: pointer;
            border: 1px solid #cde9e0;
            border-left: none;
            border-radius: 0 12px 12px 0;
            background: rgba(255,255,255,0.82);
            padding: 0 12px;
            display: flex;
            align-items: center;
        }
        .password-toggle:hover {
            background: #f8f9fa;
        }
        .password-requirements {
            background: linear-gradient(135deg, #effaf6 0%, #f7fffb 100%);
            border: 1px solid #d5efe7;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 0.5rem;
        }
        .password-requirements ul {
            margin-bottom: 0;
            padding-left: 1.25rem;
        }
        .password-requirements li {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }
        .password-requirements li i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>

<div class="card register-card">
    <div class="card-body">
        <div class="register-header">
            <div class="icon-wrapper">
                <i class="bi bi-person-plus"></i>
            </div>
            <h3 class="mb-2">Create Account</h3>
            <p class="text-muted">Join the Traffic System today</p>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <ul class="mb-0 ps-3">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="/register" method="POST" id="registerForm" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control <?= isset(session()->getFlashdata('errors')['username']) ? 'is-invalid' : '' ?>" 
                           id="username" 
                           placeholder="Choose a username" 
                           required 
                           value="<?= old('username') ?>"
                           autocomplete="username">
                </div>
                <?php if (session()->getFlashdata('errors') && isset(session()->getFlashdata('errors')['username'])): ?>
                    <div class="invalid-feedback d-block">
                        <i class="bi bi-exclamation-circle me-1"></i><?= session()->getFlashdata('errors')['username'] ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control <?= isset(session()->getFlashdata('errors')['email']) ? 'is-invalid' : '' ?>" 
                           id="email" 
                           placeholder="Enter your email" 
                           required 
                           value="<?= old('email') ?>"
                           autocomplete="email">
                </div>
                <?php if (session()->getFlashdata('errors') && isset(session()->getFlashdata('errors')['email'])): ?>
                    <div class="invalid-feedback d-block">
                        <i class="bi bi-exclamation-circle me-1"></i><?= session()->getFlashdata('errors')['email'] ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group" id="passwordGroup">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control <?= isset(session()->getFlashdata('errors')['password']) ? 'is-invalid' : '' ?>" 
                           id="password" 
                           placeholder="Create a password" 
                           required
                           autocomplete="new-password"
                           oninput="checkPasswordStrength()">
                    <span class="input-group-text password-toggle" onclick="togglePassword('password', 'toggleIcon')">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </span>
                </div>
                <div class="password-requirements">
                    <ul class="mb-0">
                        <li id="req-length"><i class="bi bi-circle"></i> At least 8 characters</li>
                        <li id="req-alpha"><i class="bi bi-circle"></i> Contains a letter</li>
                        <li id="req-number"><i class="bi bi-circle"></i> Contains a number</li>
                    </ul>
                </div>
                <?php if (session()->getFlashdata('errors') && isset(session()->getFlashdata('errors')['password'])): ?>
                    <div class="invalid-feedback d-block">
                        <i class="bi bi-exclamation-circle me-1"></i><?= session()->getFlashdata('errors')['password'] ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="mb-4">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="confirm_password" class="form-control" 
                           id="confirm_password" 
                           placeholder="Confirm your password" 
                           required
                           autocomplete="new-password"
                           oninput="checkPasswordMatch()">
                    <span class="input-group-text password-toggle" onclick="togglePassword('confirm_password', 'toggleIconConfirm')">
                        <i class="bi bi-eye" id="toggleIconConfirm"></i>
                    </span>
                </div>
                <div id="passwordMatch" class="form-text"></div>
            </div>
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary btn-register" id="submitBtn" disabled>
                    <span id="btnText">Create Account</span>
                    <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </form>
        <div class="text-center mt-4">
            <p class="text-muted mb-0">Already have an account? <a href="/login"><strong>Sign in here</strong></a></p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let isPasswordValid = false;
    let isPasswordMatch = false;

    function togglePassword(inputId, iconId) {
        const passwordInput = document.getElementById(inputId);
        const toggleIcon = document.getElementById(iconId);
        
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

    function checkPasswordStrength() {
        const password = document.getElementById('password').value;
        const reqLength = document.getElementById('req-length');
        const reqAlpha = document.getElementById('req-alpha');
        const reqNumber = document.getElementById('req-number');
        const submitBtn = document.getElementById('submitBtn');
        
        const hasLength = password.length >= 8;
        const hasAlpha = /[a-zA-Z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        
        reqLength.innerHTML = `<i class="bi ${hasLength ? 'bi-check-circle-fill text-success' : 'bi-circle'}"></i> At least 8 characters`;
        reqAlpha.innerHTML = `<i class="bi ${hasAlpha ? 'bi-check-circle-fill text-success' : 'bi-circle'}"></i> Contains a letter`;
        reqNumber.innerHTML = `<i class="bi ${hasNumber ? 'bi-check-circle-fill text-success' : 'bi-circle'}"></i> Contains a number`;
        
        isPasswordValid = hasLength && hasAlpha && hasNumber;
        
        if (isPasswordValid && isPasswordMatch) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
        
        checkPasswordMatch();
    }

    function checkPasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const matchDiv = document.getElementById('passwordMatch');
        
        if (confirmPassword.length === 0) {
            matchDiv.innerHTML = '';
            isPasswordMatch = false;
        } else if (password === confirmPassword) {
            matchDiv.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Passwords match!</span>';
            isPasswordMatch = true;
        } else {
            matchDiv.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Passwords do not match</span>';
            isPasswordMatch = false;
        }
        
        const submitBtn = document.getElementById('submitBtn');
        if (isPasswordValid && isPasswordMatch) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    }

    document.getElementById('registerForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const spinner = document.getElementById('btnSpinner');
        
        btn.disabled = true;
        btnText.textContent = 'Creating account...';
        spinner.classList.remove('d-none');
    });
</script>

</body>
</html>