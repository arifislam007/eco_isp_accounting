<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

if (current_user()) {
    redirect_to(app_url('/dashboard.php'));
}

$authController = new AuthController();

if (is_post()) {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $result = $authController->login($email, $password);

    if ($result['success']) {
        flash('success', $result['message']);
        redirect_to(app_url('/dashboard.php'));
    }

    flash('error', $result['message']);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - <?php echo h(app_name()); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo h(app_url('/assets/css/app.css')); ?>" rel="stylesheet">
</head>
<body class="login-page">
<div class="login-background"></div>
<div class="container min-vh-100 d-flex align-items-center justify-content-center position-relative">
    <div class="card login-card border-0 shadow-lg">
        <div class="card-body p-4 p-md-5">
            <div class="mb-4">
                <div class="badge text-bg-dark mb-3">ISP Billing</div>
                <h1 class="h3 fw-bold mb-2">Sign in</h1>
                <p class="text-muted mb-0">Use your admin credentials to open the dashboard.</p>
            </div>

            <?php if ($message = flash('error')): ?>
                <div class="alert alert-danger"><?php echo h($message); ?></div>
            <?php endif; ?>

            <form method="post" class="vstack gap-3">
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control form-control-lg" required>
                </div>
                <div>
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control form-control-lg" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">Login</button>
                <p class="small text-muted mb-0">Demo credentials: admin@isp.local / admin123</p>
            </form>
        </div>
    </div>
</div>
</body>
</html>
