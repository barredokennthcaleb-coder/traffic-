<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Traffic System</title>
    <link rel="icon" type="image/png" href="<?= base_url('img/pic-1.png') ?>">
    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">
    <link href="<?= base_url('vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/bootstrap-icons/bootstrap-icons.css') ?>" rel="stylesheet">
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
        .reset-shell {
            width: 100%;
            max-width: 500px;
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 30px 80px rgba(3, 8, 26, 0.55);
            background: rgba(9, 15, 38, 0.88);
            backdrop-filter: blur(12px);
            padding: 40px;
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
        .form-control {
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
        .form-control-password {
            border-radius: 0 !important;
            border-right: none !important;
        }
        .password-toggle {
            cursor: pointer;
            border-left: none !important;
            border-radius: 0 12px 12px 0 !important;
            padding: 0 14px;
            display: flex;
            align-items: center;
            color: #aebce8;
            transition: all 0.2s ease;
            user-select: none;
        }
        .password-toggle:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.16);
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
        .btn-reset {
            border: none;
            border-radius: 12px;
            padding: 0.85rem;
            font-size: 1rem;
            font-weight: 700;
            background: linear-gradient(135deg, #4d78ff 0%, #7c54f9 100%);
            box-shadow: 0 14px 32px rgba(78, 103, 226, 0.4);
            transition: 0.25s ease;
            color: white;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(78, 103, 226, 0.5);
        }
        .btn-cancel {
            border-radius: 12px;
            border: 1px solid rgba(173, 189, 245, 0.45);
            color: #d9e2ff;
            background: rgba(255, 255, 255, 0.05);
            font-weight: 600;
            padding: 0.85rem;
        }
        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
        }
        .alert {
            border: none;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div class="reset-shell">
        <div class="form-head">
            <h2>Forgot your password?</h2>
            <p>Enter your email and your one-time recovery code, then choose a new password.</p>
        </div>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger mb-4">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success mb-4">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('forgot-password') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" id="email"
                           placeholder="username@email.com" required value="<?= esc(old('email')) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="recovery_code" class="form-label">Recovery Code</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                    <input type="text" name="recovery_code" class="form-control" id="recovery_code"
                           placeholder="XXXX-XXXX-XXXX" required autocomplete="off" spellcheck="false"
                           autocapitalize="characters">
                </div>
                <div class="small mt-1" style="color:#9caad6;">Ask the administrator for your one-time recovery code.</div>
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="new_password" class="form-control form-control-password" id="new_password"
                           placeholder="At least 8 characters" minlength="8" required autocomplete="new-password">
                    <span class="input-group-text password-toggle" onclick="togglePasswordVisibility('new_password', 'toggleNewPassIcon')" title="Show / Hide Password">
                        <i class="bi bi-eye" id="toggleNewPassIcon"></i>
                    </span>
                </div>
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="confirm_password" class="form-control form-control-password" id="confirm_password"
                           placeholder="Type the new password again" minlength="8" required autocomplete="new-password">
                    <span class="input-group-text password-toggle" onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassIcon')" title="Show / Hide Password">
                        <i class="bi bi-eye" id="toggleConfirmPassIcon"></i>
                    </span>
                </div>
            </div>

            <div class="d-grid gap-3">
                <button type="submit" class="btn btn-reset">Reset Password</button>
                <a href="<?= base_url('login') ?>" class="btn btn-cancel text-center text-decoration-none">Back to Sign In</a>
            </div>
        </form>
    </div>

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>
