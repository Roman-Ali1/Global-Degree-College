<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config/app.php';

// Already logged in — redirect to dashboard
if (Auth::isLoggedIn()) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}

$error    = '';
$email    = '';

if (isPost()) {
    CSRF::requireValid();
    $email    = cleanEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $result = Auth::login($email, $password);
        if ($result['success']) {
            $redirect = cleanString($_GET['redirect'] ?? '');
            header('Location: ' . (str_starts_with($redirect, '/') ? $redirect : ADMIN_URL . '/index.php'));
            exit;
        }
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?= h(setting('site_name', 'GDC')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
</head>
<body class="admin-login-body">

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-logo">
            <img src="<?= BASE_URL ?>/<?= h(setting('site_logo','assets/images/logo/logo.png')) ?>"
                 alt="Logo" height="60"
                 onerror="this.style.display='none'">
            <h4><?= h(setting('site_name','Global Degree College')) ?></h4>
            <p>Admin Panel</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger py-2 small">
            <i class="fas fa-exclamation-circle me-2"></i><?= h($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?= CSRF::field() ?>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-control ps-5"
                           value="<?= h($email) ?>" placeholder="admin@college.edu.pk"
                           required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="passwordField"
                           class="form-control ps-5" placeholder="••••••••" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-admin-primary w-100">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In
            </button>
        </form>

        <p class="text-center text-muted small mt-4 mb-0">
            <a href="<?= BASE_URL ?>/" class="text-muted">
                <i class="fas fa-arrow-left me-1"></i>Back to Website
            </a>
        </p>
    </div>
</div>

<script>
function togglePassword() {
    const f = document.getElementById('passwordField');
    const i = document.getElementById('toggleIcon');
    f.type = f.type === 'password' ? 'text' : 'password';
    i.className = f.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
</body>
</html>