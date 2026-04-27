<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_login();
require_admin();

$importModel = new ImportModel();
$action = (string) ($_POST['action'] ?? '');
$redirectTarget = 'costs';

$response = match ($action) {
    'create' => handleCreateCostType($importModel),
    'delete' => handleDeleteCostType($importModel),
    default => ['success' => false, 'message' => 'Invalid action.'],
};

if ($response['success']) {
    flash('success', $response['message'] ?? 'Operation completed.');
} else {
    flash('error', $response['message'] ?? 'Operation failed.');
}

redirect_to(app_url('/' . $redirectTarget . '.php'));

function handleCreateCostType(ImportModel $importModel): array
{
    $name = trim((string) ($_POST['name'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));

    if (empty($name)) {
        return ['success' => false, 'message' => 'Cost type name is required.'];
    }

    if (strlen($name) > 120) {
        return ['success' => false, 'message' => 'Cost type name cannot exceed 120 characters.'];
    }

    try {
        $importModel->insertCostType($name, $description);
        return ['success' => true, 'message' => 'Cost type created successfully.'];
    } catch (Throwable $e) {
        return ['success' => false, 'message' => 'Failed to create cost type: ' . $e->getMessage()];
    }
}

function handleDeleteCostType(ImportModel $importModel): array
{
    $costTypeId = (int) ($_POST['cost_type_id'] ?? 0);

    if ($costTypeId <= 0) {
        return ['success' => false, 'message' => 'Invalid cost type ID.'];
    }

    try {
        $importModel->deleteCostType($costTypeId);
        return ['success' => true, 'message' => 'Cost type deleted successfully.'];
    } catch (Throwable $e) {
        return ['success' => false, 'message' => 'Failed to delete cost type: ' . $e->getMessage()];
    }
}
