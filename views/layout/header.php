<?php
$activeUser = current_user();
$pageTitle = $pageTitle ?? app_name();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo h($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="<?php echo h(app_url('/assets/css/app.css')); ?>" rel="stylesheet">
</head>
<body class="app-shell">
<nav class="navbar navbar-expand-lg navbar-dark app-nav shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-semibold" href="<?php echo h(app_url('/dashboard.php')); ?>"><?php echo h(app_name()); ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?php echo h(app_url('/dashboard.php')); ?>">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo h(app_url('/businesses.php')); ?>">Businesses</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo h(app_url('/costs.php')); ?>">ISP Cost</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo h(app_url('/charts.php')); ?>">Charts</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo h(app_url('/import.php')); ?>">Import CSV</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3 text-white-50">
                <?php if ($activeUser): ?>
                    <span class="small"><?php echo h($activeUser['name']); ?></span>
                    <a class="btn btn-outline-light btn-sm" href="<?php echo h(app_url('/logout.php')); ?>">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<main class="container-fluid py-4 px-3 px-lg-4">
    <?php if ($message = flash('success')): ?>
        <div class="alert alert-success"><?php echo h($message); ?></div>
    <?php endif; ?>
    <?php if ($message = flash('error')): ?>
        <div class="alert alert-danger"><?php echo h($message); ?></div>
    <?php endif; ?>
