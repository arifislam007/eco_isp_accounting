<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_login();

$month = month_value($_GET['month'] ?? null);
$dashboardController = new DashboardController();
$dashboardData = $dashboardController->index($month);
$pageTitle = 'Report - ' . app_name();
require __DIR__ . '/../views/layout/header.php';
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Report</h1>
        <p class="text-muted mb-0">Monthly report output for <?php echo h($dashboardData['month_label']); ?></p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-dark" data-generate-pdf="report" data-share-pdf="0">Export PDF</button>
        <button type="button" class="btn btn-dark" data-generate-pdf="report" data-share-pdf="1">Share PDF</button>
        <a class="btn btn-outline-secondary" href="<?php echo h(app_url('/businesses.php?month=' . $month)); ?>">Businesses</a>
        <form class="d-flex gap-2" method="get">
            <input type="month" name="month" value="<?php echo h($month); ?>" class="form-control">
            <button class="btn btn-dark">Filter</button>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['label' => 'Total Users', 'value' => $dashboardData['summary']['total_users'], 'icon' => 'fa-users', 'class' => 'card-commission'],
        ['label' => 'Total Collection', 'value' => $dashboardData['summary']['total_collection'], 'icon' => 'fa-coins', 'class' => 'card-collection'],
        ['label' => 'Total Deposit', 'value' => $dashboardData['summary']['total_deposit'], 'icon' => 'fa-circle-arrow-down', 'class' => 'card-deposit'],
        ['label' => 'Total Due', 'value' => $dashboardData['summary']['total_due'], 'icon' => 'fa-circle-exclamation', 'class' => 'card-due'],
        ['label' => 'Profit', 'value' => $dashboardData['summary']['profit'], 'icon' => 'fa-chart-line', 'class' => 'card-profit'],
    ];
    foreach ($cards as $card):
    ?>
        <div class="col-12 col-sm-6 col-xl-2-4">
            <div class="stat-card <?php echo h($card['class']); ?>">
                <div class="stat-icon"><i class="fa-solid <?php echo h($card['icon']); ?>"></i></div>
                <div>
                    <div class="stat-label"><?php echo h($card['label']); ?></div>
                    <div class="stat-value"><?php echo $card['label'] === 'Total Users' ? h((string) (int) $card['value']) : money($card['value']); ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-12 col-xxl-7">
        <div class="panel-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h5 mb-0">Bill Info</h2>
                <a class="btn btn-sm btn-outline-secondary" href="<?php echo h(app_url('/bill_info.php?month=' . $month)); ?>">Open Bill Info</a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle business-table" id="reportBillTable">
                    <thead>
                    <tr>
                        <th>Business</th>
                        <th>Collection</th>
                        <th>Carry Forward</th>
                        <th>Current Due</th>
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($dashboardData['business_list'] as $row): ?>
                        <tr>
                            <td><?php echo h($row['business_name']); ?></td>
                            <td><?php echo money($row['total_collection']); ?></td>
                            <td><?php echo money($row['carry_forward_due'] ?? 0); ?></td>
                            <td><?php echo money($row['current_due'] ?? $row['due_after_bonus']); ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-dark" href="<?php echo h(app_url('/business_details.php?business_id=' . $row['business_id'] . '&month=' . $month)); ?>">View Individual Report</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-xxl-5">
        <div class="panel-card h-100 mb-4">
            <h2 class="h5 mb-3">Bill Payment Info</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" id="reportPaymentTable">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Business</th>
                        <th>Amount</th>
                        <th>Method</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach (array_slice($dashboardData['deposits'], 0, 10) as $deposit): ?>
                        <tr>
                            <td><?php echo h($deposit['date']); ?></td>
                            <td><?php echo h($deposit['business_name']); ?></td>
                            <td><?php echo money($deposit['amount']); ?></td>
                            <td><?php echo h($deposit['medium']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel-card h-100 mb-4">
            <h2 class="h5 mb-3">Cost Entries</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" id="reportCostTable">
                    <thead>
                    <tr>
                        <th>Type</th>
                        <th>Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($dashboardData['cost_rows'] as $cost): ?>
                        <tr>
                            <td><?php echo h($cost['type']); ?></td>
                            <td><?php echo money($cost['amount']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel-card h-100">
            <h2 class="h5 mb-3">Cost Breakdown</h2>
            <div class="vstack gap-2">
                <div class="d-flex justify-content-between"><span>ISP Bill</span><strong><?php echo money($dashboardData['summary']['isp_bill']); ?></strong></div>
                <div class="d-flex justify-content-between"><span>Software Cost</span><strong><?php echo money($dashboardData['summary']['software_cost']); ?></strong></div>
                <div class="d-flex justify-content-between"><span>Others</span><strong><?php echo money($dashboardData['summary']['others']); ?></strong></div>
                <div class="d-flex justify-content-between border-top pt-2 mt-2"><span>Total Cost</span><strong><?php echo money($dashboardData['summary']['total_cost']); ?></strong></div>
            </div>
            <div class="d-grid gap-2 mt-4">
                <a class="btn btn-outline-secondary" href="<?php echo h(app_url('/deposits.php?month=' . $month)); ?>">Open Deposit</a>
                <a class="btn btn-outline-secondary" href="<?php echo h(app_url('/charts.php?month=' . $month)); ?>">Open Graph/Chart</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
<script>
window.pdfReportData = {
    type: 'report',
    title: 'Monthly Report - <?php echo h($dashboardData['month_label']); ?>',
    month: '<?php echo h($month); ?>',
    fileName: 'monthly-report-<?php echo h($month); ?>.pdf'
};
</script>
<script src="<?php echo h(app_url('/assets/js/app.js')); ?>"></script>
<?php require __DIR__ . '/../views/layout/footer.php'; ?>
