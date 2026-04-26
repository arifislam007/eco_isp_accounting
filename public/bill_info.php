<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_login();

$month = month_value($_GET['month'] ?? null);
$dashboardController = new DashboardController();
$dashboardData = $dashboardController->index($month);
$pageTitle = 'Bill Info - ' . app_name();
require __DIR__ . '/../views/layout/header.php';
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Bill Info</h1>
        <p class="text-muted mb-0">Business billing and bill generation for <?php echo h($dashboardData['month_label']); ?></p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="<?php echo h(app_url('/deposits.php?month=' . $month)); ?>">Open Deposit</a>
        <a class="btn btn-outline-secondary" href="<?php echo h(app_url('/report.php?month=' . $month)); ?>">Open Report</a>
        <form class="d-flex gap-2" method="get">
            <input type="month" name="month" value="<?php echo h($month); ?>" class="form-control">
            <button class="btn btn-dark">Filter</button>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="panel-card h-100">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h2 class="h5 mb-0">Business Bill Table</h2>
                <span class="text-muted small"><?php echo count($dashboardData['business_list']); ?> businesses</span>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle business-table" id="dashboardBusinessTable">
                    <thead>
                    <tr>
                        <th>Business Name</th>
                        <th>Total Users</th>
                        <th>Total Collection</th>
                        <th>Commission %</th>
                        <th>Commission Amount</th>
                        <th>Business Amount</th>
                        <th>Bonus %</th>
                        <th>Bonus Commission</th>
                        <th>Discount</th>
                        <th>Total Deposit</th>
                        <th>Carry Forward</th>
                        <th>Current Due</th>
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($dashboardData['business_list'] as $row): ?>
                        <tr>
                            <td><a href="<?php echo h(app_url('/business_details.php?business_id=' . $row['business_id'] . '&month=' . $month)); ?>"><?php echo h($row['business_name']); ?></a></td>
                            <td><?php echo h((string) $row['total_users']); ?></td>
                            <td><?php echo money($row['total_collection']); ?></td>
                            <td><?php echo percent($row['commission_percentage']); ?>%</td>
                            <td><?php echo money($row['commission_amount']); ?></td>
                            <td><?php echo money($row['business_amount']); ?></td>
                            <td><?php echo percent($row['bonus_percentage']); ?>%</td>
                            <td><?php echo money($row['bonus_commission']); ?></td>
                            <td><?php echo money($row['discount']); ?></td>
                            <td><?php echo money($row['total_deposit']); ?></td>
                            <td><?php echo money($row['carry_forward_due'] ?? 0); ?></td>
                            <td><?php echo money($row['current_due'] ?? $row['due_after_bonus']); ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1 flex-wrap justify-content-end">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-dark"
                                        data-edit-billing="1"
                                        data-business-id="<?php echo h((string) $row['business_id']); ?>"
                                        data-business-name="<?php echo h($row['business_name']); ?>"
                                        data-month="<?php echo h($month); ?>"
                                        data-users="<?php echo h((string) $row['total_users']); ?>"
                                        data-collection="<?php echo h((string) $row['total_collection']); ?>"
                                        data-commission="<?php echo h((string) $row['commission_percentage']); ?>"
                                        data-bonus="<?php echo h((string) $row['bonus_percentage']); ?>"
                                        data-discount="<?php echo h((string) $row['discount']); ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#billingEditModal"
                                    >
                                        Edit
                                    </button>
                                    <form method="post" action="<?php echo h(app_url('/actions/save_manual_entry.php')); ?>" class="d-inline" onsubmit="return confirm('Delete this billing entry?');">
                                        <input type="hidden" name="entry_type" value="billing_delete">
                                        <input type="hidden" name="business_id" value="<?php echo h((string) $row['business_id']); ?>">
                                        <input type="hidden" name="month" value="<?php echo h($month); ?>">
                                        <input type="hidden" name="redirect_target" value="bill_info">
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
        <div class="panel-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h5 mb-0">Manual Billing Entry</h2>
                <span class="text-muted small">Default commission is 45%</span>
            </div>
            <form method="post" action="<?php echo h(app_url('/actions/save_manual_entry.php')); ?>" class="row g-3">
                <input type="hidden" name="entry_type" value="billing">
                <input type="hidden" name="redirect_month" value="<?php echo h($month); ?>">
                <input type="hidden" name="redirect_target" value="bill_info">

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
                    <label class="form-label">Month</label>
                    <input type="month" class="form-control" name="month" value="<?php echo h($month); ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Total Users</label>
                    <input type="number" class="form-control" name="total_users" min="0" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Total Collection</label>
                    <input type="number" class="form-control" name="total_collection" step="0.01" min="0" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Commission %</label>
                    <input type="number" class="form-control" name="commission_percentage" step="0.01" min="0" value="45" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Bonus %</label>
                    <input type="number" class="form-control" name="bonus_percentage" step="0.01" min="0" value="0">
                </div>
                <div class="col-12">
                    <label class="form-label">Discount Amount</label>
                    <input type="number" class="form-control" name="discount_amount" step="0.01" min="0" value="0">
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-dark">Save Billing Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="billingEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Billing Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?php echo h(app_url('/actions/save_manual_entry.php')); ?>" id="billingEditForm">
                <div class="modal-body row g-3">
                    <input type="hidden" name="entry_type" value="billing_update">
                    <input type="hidden" name="business_id" id="editBillingBusinessId">
                    <input type="hidden" name="redirect_target" value="bill_info">
                    <input type="hidden" name="redirect_month" value="<?php echo h($month); ?>">
                    <div class="col-12">
                        <label class="form-label">Business</label>
                        <input type="text" id="editBillingBusinessName" class="form-control" readonly>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Month</label>
                        <input type="month" name="month" id="editBillingMonth" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Total Users</label>
                        <input type="number" name="total_users" id="editBillingUsers" class="form-control" min="0" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Total Collection</label>
                        <input type="number" name="total_collection" id="editBillingCollection" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Commission %</label>
                        <input type="number" name="commission_percentage" id="editBillingCommission" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Bonus %</label>
                        <input type="number" name="bonus_percentage" id="editBillingBonus" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Discount Amount</label>
                        <input type="number" name="discount_amount" id="editBillingDiscount" class="form-control" min="0" step="0.01" required>
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

<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
<script>
window.pdfReportData = {
    type: 'business',
    title: 'Business Bill Report - <?php echo h($dashboardData['month_label']); ?>',
    month: '<?php echo h($month); ?>',
    businessName: 'All Businesses',
    fileName: 'bill-info-report-<?php echo h($month); ?>.pdf'
};
</script>
<script src="<?php echo h(app_url('/assets/js/app.js')); ?>"></script>
<?php require __DIR__ . '/../views/layout/footer.php'; ?>
