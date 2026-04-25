<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_login();

$importController = new ImportController();

if (is_post() && isset($_FILES['csv_file'])) {
    $entity = (string) ($_POST['entity'] ?? 'collections');
    $result = $importController->importUploadedFile($_FILES['csv_file'], $entity);
    flash($result['success'] ? 'success' : 'error', $result['message']);
    redirect_to(app_url('/import.php'));
}

$pageTitle = 'Import CSV - ' . app_name();
require __DIR__ . '/../views/layout/header.php';
?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="panel-card">
            <h1 class="h4 fw-bold mb-3">CSV Import</h1>
            <p class="text-muted">Upload billing data exported from Excel in CSV format.</p>
            <form method="post" enctype="multipart/form-data" class="vstack gap-3">
                <div>
                    <label class="form-label">Entity</label>
                    <select name="entity" class="form-select">
                        <option value="businesses">Businesses</option>
                        <option value="collections" selected>Collections</option>
                        <option value="commissions">Commissions</option>
                        <option value="bonuses">Bonuses</option>
                        <option value="deposits">Deposits</option>
                        <option value="discounts">Discounts</option>
                        <option value="costs">Costs</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">CSV File</label>
                    <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                </div>
                <button class="btn btn-dark">Import</button>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../views/layout/footer.php'; ?>
