<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_login();

if (!is_post()) {
    redirect_to(app_url('/dashboard.php'));
}

$controller = new ManualEntryController();
$result = $controller->save($_POST);

flash($result['success'] ? 'success' : 'error', $result['message']);

$month = month_value($_POST['redirect_month'] ?? null);
$redirectTarget = (string) ($_POST['redirect_target'] ?? 'dashboard');

if ($redirectTarget === 'business_details') {
    $businessId = (int) ($_POST['redirect_business_id'] ?? 0);
    if ($businessId > 0) {
        redirect_to(app_url('/business_details.php?business_id=' . $businessId . '&month=' . urlencode($month)));
    }
}

if ($redirectTarget === 'costs') {
    redirect_to(app_url('/costs.php?month=' . urlencode($month)));
}

redirect_to(app_url('/dashboard.php?month=' . urlencode($month)));
