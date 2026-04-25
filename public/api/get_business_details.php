<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_login();

header('Content-Type: application/json');

$businessId = (int) ($_GET['business_id'] ?? 0);
$month = month_value($_GET['month'] ?? null);
$controller = new BusinessController();
echo json_encode($controller->details($businessId, $month), JSON_UNESCAPED_SLASHES);
