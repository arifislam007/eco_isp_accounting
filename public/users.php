<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_login();
require_admin();

$userController = new UserController();
$users = $userController->getAll();
$pageTitle = 'User Management - ' . app_name();
require __DIR__ . '/../views/layout/header.php';
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">User Management</h1>
        <p class="text-muted mb-0">Manage user accounts and access privileges</p>
    </div>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#userCreateModal">Add New User</button>
</div>

<div class="panel-card">
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created</th>
                <th class="text-end">Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo h($user['name']); ?></td>
                    <td><?php echo h($user['email']); ?></td>
                    <td>
                        <span class="badge bg-<?php
                            echo match($user['role']) {
                                'admin' => 'danger',
                                'manager' => 'warning',
                                default => 'secondary'
                            };
                        ?>">
                            <?php echo h(ucfirst($user['role'])); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge <?php echo $user['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td><small><?php echo h(date('M d, Y', strtotime($user['created_at']))); ?></small></td>
                    <td class="text-end">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-dark"
                            data-edit-user="1"
                            data-user-id="<?php echo h((string) $user['id']); ?>"
                            data-user-name="<?php echo h($user['name']); ?>"
                            data-user-email="<?php echo h($user['email']); ?>"
                            data-user-role="<?php echo h($user['role']); ?>"
                            data-user-active="<?php echo h((string) $user['is_active']); ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#userEditModal"
                        >
                            Edit
                        </button>
                        <?php if (count($users) > 1 && current_user_id() !== (int) $user['id']): ?>
                            <form method="post" action="<?php echo h(app_url('/actions/user_action.php')); ?>" class="d-inline" onsubmit="return confirm('Delete this user?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo h((string) $user['id']); ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="userCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?php echo h(app_url('/actions/user_action.php')); ?>">
                <div class="modal-body row g-3">
                    <input type="hidden" name="action" value="create">
                    <div class="col-12">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="viewer">Viewer (Read-only)</option>
                            <option value="manager">Manager (Create & Edit)</option>
                            <option value="admin">Admin (Full Access)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="userEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?php echo h(app_url('/actions/user_action.php')); ?>">
                <div class="modal-body row g-3">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="col-12">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" id="editUserName" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Email</label>
                        <input type="email" id="editUserEmail" class="form-control" disabled>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Role</label>
                        <select name="role" id="editUserRole" class="form-select" required>
                            <option value="viewer">Viewer (Read-only)</option>
                            <option value="manager">Manager (Create & Edit)</option>
                            <option value="admin">Admin (Full Access)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Status</label>
                        <select name="is_active" id="editUserActive" class="form-select" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const editButtons = document.querySelectorAll('[data-edit-user]');
  if (editButtons.length) {
    editButtons.forEach(button => {
      button.addEventListener('click', () => {
        document.getElementById('editUserId').value = button.getAttribute('data-user-id') || '';
        document.getElementById('editUserName').value = button.getAttribute('data-user-name') || '';
        document.getElementById('editUserEmail').value = button.getAttribute('data-user-email') || '';
        document.getElementById('editUserRole').value = button.getAttribute('data-user-role') || 'viewer';
        document.getElementById('editUserActive').value = button.getAttribute('data-user-active') || '1';
      });
    });
  }
});
</script>
<?php require __DIR__ . '/../views/layout/footer.php'; ?>
