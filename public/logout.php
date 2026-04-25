<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

logout_user();
redirect_to(app_url('/login.php'));
