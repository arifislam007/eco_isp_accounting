<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_login();

$month = month_value($_GET['month'] ?? null);
$businessController = new BusinessController();
$businessList = $businessController->list($month);
$pageTitle = 'Businesses - ' . app_name();
require __DIR__ . '/../views/layout/header.php';
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Business List</h1>
        <p class="text-muted mb-0">Monthly billing rows for <?php echo h(month_label($month)); ?></p>
    </div>
    <form class="d-flex gap-2" method="get">
        <input type="month" name="month" value="<?php echo h($month); ?>" class="form-control">
        <button class="btn btn-dark">Filter</button>
    </form>
</div>

<div class="panel-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
            <tr>
                <th>Business</th>
                <th>Collection</th>
                <th>Deposit</th>
                <th>Due</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($businessList as $row): ?>
                <tr>
                    <td><?php echo h($row['business_name']); ?></td>
                    <td><?php echo money($row['total_collection']); ?></td>
                    <td><?php echo money($row['total_deposit']); ?></td>
                    <td><?php echo money($row['due_after_bonus']); ?></td>
                    <td><a class="btn btn-sm btn-outline-dark" href="<?php echo h(app_url('/business_details.php?business_id=' . $row['business_id'] . '&month=' . $month)); ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../views/layout/footer.php'; ?>
