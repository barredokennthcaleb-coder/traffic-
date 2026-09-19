<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Traffic System</title>
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
            <h2>Reset Password</h2>
            <p>To reset your password, type in your email address and click the Reset button.</p>
        </div>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger mb-4">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success mb-4">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('forgot-password') ?>" method="POST">
            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" id="email" 
                           placeholder="username@email.com" required>
                </div>
            </div>

            <div class="d-grid gap-3">
                <button type="submit" class="btn btn-reset">Reset</button>
                <a href="<?= base_url('login') ?>" class="btn btn-cancel text-center text-decoration-none">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
