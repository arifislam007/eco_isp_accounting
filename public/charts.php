<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_login();

$month = month_value($_GET['month'] ?? null);
$dashboardController = new DashboardController();
$dashboardData = $dashboardController->index($month);
$pageTitle = 'Charts - ' . app_name();
require __DIR__ . '/../views/layout/header.php';
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Charts</h1>
        <p class="text-muted mb-0">Collection vs Deposit and Profit trend for <?php echo h($dashboardData['month_label']); ?></p>
    </div>
    <form class="d-flex gap-2" method="get">
        <input type="month" name="month" value="<?php echo h($month); ?>" class="form-control">
        <button class="btn btn-dark">Filter</button>
    </form>
</div>

<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['label' => 'Users', 'value' => $dashboardData['summary']['total_users'] ?? 0, 'icon' => 'fa-users', 'class' => 'card-commission'],
        ['label' => 'Collection', 'value' => $dashboardData['summary']['total_collection'], 'icon' => 'fa-coins', 'class' => 'card-collection'],
        ['label' => 'Deposit', 'value' => $dashboardData['summary']['total_deposit'], 'icon' => 'fa-circle-arrow-down', 'class' => 'card-deposit'],
        ['label' => 'Profit', 'value' => $dashboardData['summary']['profit'], 'icon' => 'fa-chart-line', 'class' => 'card-profit'],
    ];
    foreach ($cards as $card):
    ?>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card <?php echo h($card['class']); ?>">
                <div class="stat-icon"><i class="fa-solid <?php echo h($card['icon']); ?>"></i></div>
                <div>
                    <div class="stat-label"><?php echo h($card['label']); ?></div>
                    <div class="stat-value"><?php echo $card['label'] === 'Users' ? h((string) (int) $card['value']) : money($card['value']); ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <div class="panel-card mb-4">
            <h2 class="h5 mb-3">Collection vs Deposit</h2>
            <canvas id="collectionDepositChart" height="180"></canvas>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="panel-card mb-4">
            <h2 class="h5 mb-3">Profit Trend</h2>
            <canvas id="profitChart" height="180"></canvas>
        </div>
    </div>
</div>

<div class="panel-card mb-4">
    <h2 class="h5 mb-3">User Trend</h2>
    <canvas id="userTrendChart" height="120"></canvas>
</div>

<div class="panel-card">
    <h2 class="h5 mb-3">Latest Monthly Snapshot</h2>
    <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead>
            <tr>
                <th>Business</th>
                <th>Collection</th>
                <th>Deposit</th>
                <th>Due</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($dashboardData['business_list'] as $row): ?>
                <tr>
                    <td><?php echo h($row['business_name']); ?></td>
                    <td><?php echo money($row['total_collection']); ?></td>
                    <td><?php echo money($row['total_deposit']); ?></td>
                    <td><?php echo money($row['due_after_bonus']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.dashboardCharts = <?php echo json_encode($dashboardData['charts'], JSON_UNESCAPED_SLASHES); ?>;
</script>
<script src="<?php echo h(app_url('/assets/js/app.js')); ?>"></script>
<?php require __DIR__ . '/../views/layout/footer.php'; ?>
