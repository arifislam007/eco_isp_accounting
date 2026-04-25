<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_login();

$month = month_value($_GET['month'] ?? null);
$dashboardController = new DashboardController();
$dashboardData = $dashboardController->index($month);
$pageTitle = 'ISP Cost - ' . app_name();
require __DIR__ . '/../views/layout/header.php';
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">ISP Cost</h1>
        <p class="text-muted mb-0">Monthly ISP, software, and other cost tracking for <?php echo h($dashboardData['month_label']); ?></p>
    </div>
    <form class="d-flex gap-2" method="get">
        <input type="month" name="month" value="<?php echo h($month); ?>" class="form-control">
        <button class="btn btn-dark">Filter</button>
    </form>
</div>

<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['label' => 'ISP Bill', 'value' => $dashboardData['summary']['isp_bill'], 'icon' => 'fa-tower-broadcast', 'class' => 'card-collection'],
        ['label' => 'Software Cost', 'value' => $dashboardData['summary']['software_cost'], 'icon' => 'fa-laptop-code', 'class' => 'card-commission'],
        ['label' => 'Others', 'value' => $dashboardData['summary']['others'], 'icon' => 'fa-ellipsis', 'class' => 'card-due'],
        ['label' => 'Total Cost', 'value' => $dashboardData['summary']['total_cost'], 'icon' => 'fa-receipt', 'class' => 'card-deposit'],
        ['label' => 'Profit', 'value' => $dashboardData['summary']['profit'], 'icon' => 'fa-chart-line', 'class' => 'card-profit'],
    ];
    foreach ($cards as $card):
    ?>
        <div class="col-12 col-md-6 col-xl-2-4">
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

<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="panel-card mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h5 mb-0">Cost Rows</h2>
                <span class="text-muted small"><?php echo count($dashboardData['cost_rows']); ?> rows</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                    <tr>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Month</th>
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($dashboardData['cost_rows'] as $costRow): ?>
                        <tr>
                            <td><?php echo h($costRow['type']); ?></td>
                            <td><?php echo money($costRow['amount']); ?></td>
                            <td><?php echo h($costRow['month']); ?></td>
                            <td class="text-end">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-dark"
                                    data-edit-cost="1"
                                    data-cost-id="<?php echo h((string) $costRow['id']); ?>"
                                    data-cost-type="<?php echo h($costRow['type']); ?>"
                                    data-cost-amount="<?php echo h((string) $costRow['amount']); ?>"
                                    data-cost-month="<?php echo h($costRow['month']); ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#costEditModal"
                                >
                                    Edit
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel-card">
            <h2 class="h5 mb-3">Add Cost Entry</h2>
            <form method="post" action="<?php echo h(app_url('/actions/save_manual_entry.php')); ?>" class="row g-3">
                <input type="hidden" name="entry_type" value="cost">
                <input type="hidden" name="redirect_target" value="costs">
                <input type="hidden" name="redirect_month" value="<?php echo h($month); ?>">
                <div class="col-12 col-md-4">
                    <label class="form-label">Cost Type</label>
                    <select class="form-select" name="cost_type" required>
                        <option value="ISP Bill">ISP Bill</option>
                        <option value="Software Cost">Software Cost</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Amount</label>
                    <input type="number" class="form-control" name="cost_amount" min="0" step="0.01" required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Month</label>
                    <input type="month" class="form-control" name="cost_month" value="<?php echo h($month); ?>" required>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-dark">Save Cost</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="panel-card">
            <h2 class="h5 mb-3">Monthly Cost Breakdown</h2>
            <div class="vstack gap-2">
                <div class="d-flex justify-content-between"><span>ISP Bill</span><strong><?php echo money($dashboardData['summary']['isp_bill']); ?></strong></div>
                <div class="d-flex justify-content-between"><span>Software Cost</span><strong><?php echo money($dashboardData['summary']['software_cost']); ?></strong></div>
                <div class="d-flex justify-content-between"><span>Others</span><strong><?php echo money($dashboardData['summary']['others']); ?></strong></div>
                <div class="d-flex justify-content-between border-top pt-2 mt-2"><span>Total Cost</span><strong><?php echo money($dashboardData['summary']['total_cost']); ?></strong></div>
                <div class="d-flex justify-content-between text-success border-top pt-2 mt-2"><span>Profit</span><strong><?php echo money($dashboardData['summary']['profit']); ?></strong></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="costEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Cost Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?php echo h(app_url('/actions/save_manual_entry.php')); ?>" id="costEditForm">
                <div class="modal-body row g-3">
                    <input type="hidden" name="entry_type" value="cost_update">
                    <input type="hidden" name="cost_id" id="editCostId">
                    <input type="hidden" name="redirect_target" value="costs">
                    <input type="hidden" name="redirect_month" value="<?php echo h($month); ?>">

                    <div class="col-12">
                        <label class="form-label">Cost Type</label>
                        <input type="text" name="cost_type" id="editCostType" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Amount</label>
                        <input type="number" name="cost_amount" id="editCostAmount" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Month</label>
                        <input type="month" name="cost_month" id="editCostMonth" class="form-control" required>
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

<?php require __DIR__ . '/../views/layout/footer.php'; ?>
