<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

if (current_user()) {
    redirect_to(app_url('/dashboard.php'));
}

redirect_to(app_url('/login.php'));
