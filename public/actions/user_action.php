<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_login();
require_admin();

$userController = new UserController();
$action = (string) ($_POST['action'] ?? '');
$redirectTarget = $_POST['redirect_target'] ?? 'users';

$response = match ($action) {
    'create' => $userController->create($_POST),
    'update' => $userController->update((int) ($_POST['user_id'] ?? 0), $_POST),
    'delete' => $userController->delete((int) ($_POST['user_id'] ?? 0)),
    default => ['success' => false, 'message' => 'Invalid action.'],
};

if ($response['success']) {
    flash('success', $response['message'] ?? 'Operation completed.');
} else {
    flash('error', $response['message'] ?? 'Operation failed.');
}

redirect_to(app_url('/' . $redirectTarget . '.php'));
