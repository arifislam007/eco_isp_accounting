<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_login();

$month = month_value($_GET['month'] ?? null);
$dashboardController = new DashboardController();
$dashboardData = $dashboardController->index($month);
$pageTitle = 'Dashboard - ' . app_name();
require __DIR__ . '/../views/layout/header.php';
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Dashboard</h1>
        <p class="text-muted mb-0">Billing overview for <?php echo h($dashboardData['month_label']); ?></p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-secondary" id="toggleRearrangeBtn">Rearrange Dashboard</button>
        <button type="button" class="btn btn-outline-secondary" id="resetDashboardLayoutBtn">Reset Layout</button>
        <button type="button" class="btn btn-outline-dark" data-generate-pdf="dashboard" data-share-pdf="0">Export PDF</button>
        <button type="button" class="btn btn-dark" data-generate-pdf="dashboard" data-share-pdf="1">Share PDF</button>
        <form class="d-flex gap-2" method="get">
            <input type="month" name="month" value="<?php echo h($month); ?>" class="form-control">
            <button class="btn btn-dark">Filter</button>
        </form>
    </div>
</div>

<div id="dashboardLayout" class="dashboard-layout">
    <section class="dashboard-widget" data-widget-id="summary-cards">
        <div class="dashboard-widget-meta d-none">
            <span>Summary cards</span>
            <div class="widget-move-controls btn-group btn-group-sm" role="group" aria-label="Move summary cards">
                <button type="button" class="btn btn-outline-secondary widget-move-up">Up</button>
                <button type="button" class="btn btn-outline-secondary widget-move-down">Down</button>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 row-cols-xxl-5 g-3 mb-4">
            <?php
            $cards = [
                ['label' => 'Total Collection', 'value' => $dashboardData['summary']['total_collection'], 'icon' => 'fa-coins', 'class' => 'card-collection'],
                ['label' => 'Total Commission', 'value' => $dashboardData['summary']['total_commission'], 'icon' => 'fa-percent', 'class' => 'card-commission'],
                ['label' => 'Total Deposit', 'value' => $dashboardData['summary']['total_deposit'], 'icon' => 'fa-circle-arrow-down', 'class' => 'card-deposit'],
                ['label' => 'Total Due', 'value' => $dashboardData['summary']['total_due'], 'icon' => 'fa-circle-exclamation', 'class' => 'card-due'],
                ['label' => 'Profit', 'value' => $dashboardData['summary']['profit'], 'icon' => 'fa-chart-line', 'class' => 'card-profit'],
            ];
            foreach ($cards as $card):
            ?>
                <div class="col">
                    <div class="stat-card <?php echo h($card['class']); ?>">
                        <div class="stat-icon"><i class="fa-solid <?php echo h($card['icon']); ?>"></i></div>
                        <div>
                            <div class="stat-label"><?php echo h($card['label']); ?></div>
                            <div class="stat-value"><?php echo money($card['value']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="dashboard-widget" data-widget-id="main-panels">
        <div class="dashboard-widget-meta d-none">
            <span>Main panels</span>
            <div class="widget-move-controls btn-group btn-group-sm" role="group" aria-label="Move main panels">
                <button type="button" class="btn btn-outline-secondary widget-move-up">Up</button>
                <button type="button" class="btn btn-outline-secondary widget-move-down">Down</button>
            </div>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="panel-card">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="h5 mb-0">Business Table</h2>
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
                                    <td><?php echo money($row['due_after_bonus']); ?></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
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
                                                <input type="hidden" name="redirect_target" value="dashboard">
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
        </div>
    </section>

    <section class="dashboard-widget" data-widget-id="deposits-list">
        <div class="dashboard-widget-meta d-none">
            <span>Deposit history</span>
            <div class="widget-move-controls btn-group btn-group-sm" role="group" aria-label="Move deposit history">
                <button type="button" class="btn btn-outline-secondary widget-move-up">Up</button>
                <button type="button" class="btn btn-outline-secondary widget-move-down">Down</button>
            </div>
        </div>
        <div class="panel-card mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h5 mb-0">All Deposits in Month</h2>
                <span class="text-muted small"><?php echo count($dashboardData['deposits']); ?> entries</span>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Business</th>
                        <th>Amount</th>
                        <th>Medium</th>
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
                                        <input type="hidden" name="redirect_target" value="dashboard">
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
    </section>

    <section class="dashboard-widget" data-widget-id="manual-input">
        <div class="dashboard-widget-meta d-none">
            <span>Manual input</span>
            <div class="widget-move-controls btn-group btn-group-sm" role="group" aria-label="Move manual input">
                <button type="button" class="btn btn-outline-secondary widget-move-up">Up</button>
                <button type="button" class="btn btn-outline-secondary widget-move-down">Down</button>
            </div>
        </div>
        <div class="panel-card">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="h5 mb-0">Manual Input</h2>
        <span class="text-muted small">Add monthly data directly from dashboard</span>
    </div>
    <div class="accordion" id="manualInputAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#billingEntryPanel" aria-expanded="true">
                    Billing Entry
                </button>
            </h2>
            <div id="billingEntryPanel" class="accordion-collapse collapse show" data-bs-parent="#manualInputAccordion">
                <div class="accordion-body">
                    <form method="post" action="<?php echo h(app_url('/actions/save_manual_entry.php')); ?>" class="row g-3">
                        <input type="hidden" name="entry_type" value="billing">
                        <input type="hidden" name="redirect_month" value="<?php echo h($month); ?>">
                        <input type="hidden" name="redirect_target" value="dashboard">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Existing Business</label>
                            <select class="form-select" name="business_id">
                                <option value="0">Select business</option>
                                <?php foreach ($dashboardData['business_list'] as $row): ?>
                                    <option value="<?php echo h((string) $row['business_id']); ?>"><?php echo h($row['business_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Or New Business Name</label>
                            <input class="form-control" name="new_business_name" placeholder="New business name">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Month</label>
                            <input type="month" class="form-control" name="month" value="<?php echo h($month); ?>" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Total Users</label>
                            <input type="number" class="form-control" name="total_users" min="0" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Total Collection</label>
                            <input type="number" class="form-control" name="total_collection" step="0.01" min="0" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Commission %</label>
                            <input type="number" class="form-control" name="commission_percentage" step="0.01" min="0" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Bonus %</label>
                            <input type="number" class="form-control" name="bonus_percentage" step="0.01" min="0" value="0">
                        </div>
                        <div class="col-12 col-md-3">
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

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#depositEntryPanel" aria-expanded="false">
                    Deposit Entry
                </button>
            </h2>
            <div id="depositEntryPanel" class="accordion-collapse collapse" data-bs-parent="#manualInputAccordion">
                <div class="accordion-body">
                    <form method="post" action="<?php echo h(app_url('/actions/save_manual_entry.php')); ?>" class="row g-3">
                        <input type="hidden" name="entry_type" value="deposit">
                        <input type="hidden" name="redirect_month" value="<?php echo h($month); ?>">
                        <input type="hidden" name="redirect_target" value="dashboard">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Existing Business</label>
                            <select class="form-select" name="business_id">
                                <option value="0">Select business</option>
                                <?php foreach ($dashboardData['business_list'] as $row): ?>
                                    <option value="<?php echo h((string) $row['business_id']); ?>"><?php echo h($row['business_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Or New Business Name</label>
                            <input class="form-control" name="new_business_name" placeholder="New business name">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="date" value="<?php echo h(date('Y-m-d')); ?>" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-control" name="amount" min="0" step="0.01" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Type</label>
                            <input class="form-control" name="type" value="deposit">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Medium</label>
                            <input class="form-control" name="medium" value="bank">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Reference</label>
                            <input class="form-control" name="reference" placeholder="Transaction/reference ID">
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-dark">Save Deposit Entry</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
        </div>
    </section>
</div>

<div class="dashboard-mobile-hint text-muted small mt-2">Tip: on mobile, tap Rearrange Dashboard and use the Up/Down buttons to change section order.</div>

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
                    <input type="hidden" name="redirect_target" value="dashboard">
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
                    <input type="hidden" name="redirect_target" value="dashboard">
                    <input type="hidden" name="redirect_month" value="<?php echo h($month); ?>">
                    <div class="col-12">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" id="editDepositDate" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" id="editDepositAmount" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Type</label>
                        <input type="text" name="type" id="editDepositType" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Medium</label>
                        <input type="text" name="medium" id="editDepositMedium" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Reference</label>
                        <input type="text" name="reference" id="editDepositReference" class="form-control">
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
    type: 'dashboard',
    title: 'Dashboard Report - <?php echo h($dashboardData['month_label']); ?>',
    month: '<?php echo h($month); ?>',
    fileName: 'dashboard-report-<?php echo h($month); ?>.pdf'
};
window.dashboardLayoutConfig = {
    storageKey: 'ispDashboardLayoutOrderV1'
};
</script>
<script src="<?php echo h(app_url('/assets/js/app.js')); ?>"></script>
<?php require __DIR__ . '/../views/layout/footer.php'; ?>
