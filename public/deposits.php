<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_login();

$month = month_value($_GET['month'] ?? null);
$dashboardController = new DashboardController();
$dashboardData = $dashboardController->index($month);
$pageTitle = 'Deposit - ' . app_name();
require __DIR__ . '/../views/layout/header.php';
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Deposit</h1>
        <p class="text-muted mb-0">Manage deposits and payment entries for <?php echo h($dashboardData['month_label']); ?></p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="<?php echo h(app_url('/report.php?month=' . $month)); ?>">Open Report</a>
        <form class="d-flex gap-2" method="get">
            <input type="month" name="month" value="<?php echo h($month); ?>" class="form-control">
            <button class="btn btn-dark">Filter</button>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="panel-card mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h5 mb-0">Deposit Entries</h2>
                <span class="text-muted small"><?php echo count($dashboardData['deposits']); ?> entries</span>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Business</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Reference</th>
                        <th>Type</th>
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($dashboardData['deposits'] as $deposit): ?>
                        <tr>
                            <td><?php echo h($deposit['date']); ?></td>
                            <td><?php echo h($deposit['business_name']); ?></td>
                            <td><?php echo money($deposit['amount']); ?></td>
                            <td><?php echo h($deposit['medium']); ?></td>
                            <td><?php echo h($deposit['reference']); ?></td>
                            <td><?php echo h($deposit['type']); ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-dark"
                                        data-edit-deposit="1"
                                        data-deposit-id="<?php echo h((string) $deposit['id']); ?>"
                                        data-business-id="<?php echo h((string) $deposit['business_id']); ?>"
                                        data-date="<?php echo h($deposit['date']); ?>"
                                        data-amount="<?php echo h((string) $deposit['amount']); ?>"
                                        data-medium="<?php echo h($deposit['medium']); ?>"
                                        data-reference="<?php echo h($deposit['reference']); ?>"
                                        data-type="<?php echo h($deposit['type']); ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#depositEditModal"
                                    >
                                        Edit
                                    </button>
                                    <form method="post" action="<?php echo h(app_url('/actions/save_manual_entry.php')); ?>" class="d-inline" onsubmit="return confirm('Delete this deposit entry?');">
                                        <input type="hidden" name="entry_type" value="deposit_delete">
                                        <input type="hidden" name="deposit_id" value="<?php echo h((string) $deposit['id']); ?>">
                                        <input type="hidden" name="redirect_target" value="deposits">
                                        <input type="hidden" name="redirect_month" value="<?php echo h($month); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="panel-card mb-4">
            <h2 class="h5 mb-3">Add Deposit Entry</h2>
            <form method="post" action="<?php echo h(app_url('/actions/save_manual_entry.php')); ?>" class="row g-3">
                <input type="hidden" name="entry_type" value="deposit">
                <input type="hidden" name="redirect_month" value="<?php echo h($month); ?>">
                <input type="hidden" name="redirect_target" value="deposits">

                <div class="col-12">
                    <label class="form-label">Existing Business</label>
                    <select class="form-select" name="business_id">
                        <option value="0">Select business</option>
                        <?php foreach ($dashboardData['business_list'] as $row): ?>
                            <option value="<?php echo h((string) $row['business_id']); ?>"><?php echo h($row['business_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Or New Business Name</label>
                    <input class="form-control" name="new_business_name" placeholder="New business name">
                </div>
                <div class="col-12">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" name="date" value="<?php echo h(date('Y-m-d')); ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Amount</label>
                    <input type="number" class="form-control" name="amount" min="0" step="0.01" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Type</label>
                    <input class="form-control" name="type" value="deposit">
                </div>
                <div class="col-12">
                    <label class="form-label">Payment Method</label>
                    <select class="form-select" name="medium" required>
                        <option value="bKash">bKash</option>
                        <option value="cash">cash</option>
                        <option value="Bank" selected>Bank</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Reference</label>
                    <input class="form-control" name="reference" placeholder="Transaction/reference ID">
                </div>
                <div class="col-12">
                    <label class="form-label">Discount Amount (Optional)</label>
                    <input type="number" class="form-control" name="discount_amount" step="0.01" min="0" value="0">
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-dark">Save Deposit Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="depositEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Deposit Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?php echo h(app_url('/actions/save_manual_entry.php')); ?>" id="depositEditForm">
                <div class="modal-body row g-3">
                    <input type="hidden" name="entry_type" value="deposit_update">
                    <input type="hidden" name="deposit_id" id="editDepositId">
                    <input type="hidden" name="business_id" id="editBusinessId">
                    <input type="hidden" name="redirect_target" value="deposits">
                    <input type="hidden" name="redirect_month" value="<?php echo h($month); ?>">
                    <div class="col-12">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" id="editDepositDate" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" id="editDepositAmount" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Type</label>
                        <input type="text" name="type" id="editDepositType" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Payment Method</label>
                        <select name="medium" id="editDepositMedium" class="form-select" required>
                            <option value="bKash">bKash</option>
                            <option value="cash">cash</option>
                            <option value="Bank">Bank</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Reference</label>
                        <input type="text" name="reference" id="editDepositReference" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Discount Amount (Optional)</label>
                        <input type="number" name="discount_amount" id="editDepositDiscount" class="form-control" min="0" step="0.01" value="0">
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

<script src="<?php echo h(app_url('/assets/js/app.js')); ?>"></script>
<?php require __DIR__ . '/../views/layout/footer.php'; ?>
