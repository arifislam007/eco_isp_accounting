<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_login();

header('Content-Type: application/json');

$month = month_value($_GET['month'] ?? null);
$controller = new DashboardController();
echo json_encode($controller->index($month), JSON_UNESCAPED_SLASHES);
