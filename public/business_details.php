<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_login();

$businessId = (int) ($_GET['business_id'] ?? 0);
$month = month_value($_GET['month'] ?? null);
$businessController = new BusinessController();
$details = $businessController->details($businessId, $month);
$pageTitle = 'Bill Details - ' . app_name();
require __DIR__ . '/../views/layout/header.php';
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Bill for <?php echo h($details['month_label']); ?></h1>
        <p class="text-muted mb-0"><?php echo h($details['business']['name']); ?></p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-dark" data-generate-pdf="business" data-share-pdf="0">Export PDF</button>
        <button type="button" class="btn btn-dark" data-generate-pdf="business" data-share-pdf="1">Share PDF</button>
        <form class="d-flex gap-2" method="get">
            <input type="hidden" name="business_id" value="<?php echo h((string) $businessId); ?>">
            <input type="month" name="month" value="<?php echo h($month); ?>" class="form-control">
            <button class="btn btn-dark">Filter</button>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php
    $summaryCards = [
        ['label' => 'Users', 'value' => $details['summary']['total_users']],
        ['label' => 'Total Collection', 'value' => $details['summary']['total_collection']],
        ['label' => 'Commission', 'value' => $details['summary']['commission_amount']],
        ['label' => 'Bill Amount', 'value' => $details['summary']['bill_amount']],
        ['label' => 'Deposit', 'value' => $details['summary']['total_deposit']],
        ['label' => 'Bonus', 'value' => $details['summary']['bonus_commission']],
        ['label' => 'Final Due', 'value' => $details['summary']['final_due']],
    ];
    foreach ($summaryCards as $card):
    ?>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="panel-card h-100">
                <div class="text-muted small text-uppercase"><?php echo h($card['label']); ?></div>
                <div class="fs-4 fw-bold"><?php echo is_int($card['value']) ? h((string) $card['value']) : money($card['value']); ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="panel-card">
            <h2 class="h5 mb-3">Deposit Tracking</h2>
            <div class="table-responsive">
                <table class="table table-striped align-middle" id="businessDepositTable">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Medium</th>
                        <th>Reference</th>
                        <th>Type</th>
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($details['deposit_history'] as $row): ?>
                        <tr>
                            <td><?php echo h($row['date']); ?></td>
                            <td><?php echo money($row['amount']); ?></td>
                            <td><?php echo h($row['medium']); ?></td>
                            <td><?php echo h($row['reference']); ?></td>
                            <td><?php echo h($row['type']); ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-dark"
                                        data-edit-deposit="1"
                                        data-deposit-id="<?php echo h((string) $row['id']); ?>"
                                        data-business-id="<?php echo h((string) $businessId); ?>"
                                        data-date="<?php echo h($row['date']); ?>"
                                        data-amount="<?php echo h((string) $row['amount']); ?>"
                                        data-medium="<?php echo h($row['medium']); ?>"
                                        data-reference="<?php echo h($row['reference']); ?>"
                                        data-type="<?php echo h($row['type']); ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#depositEditModal"
                                    >
                                        Edit
                                    </button>
                                    <form method="post" action="<?php echo h(app_url('/actions/save_manual_entry.php')); ?>" class="d-inline" onsubmit="return confirm('Delete this deposit entry?');">
                                        <input type="hidden" name="entry_type" value="deposit_delete">
                                        <input type="hidden" name="deposit_id" value="<?php echo h((string) $row['id']); ?>">
                                        <input type="hidden" name="redirect_target" value="business_details">
                                        <input type="hidden" name="redirect_business_id" value="<?php echo h((string) $businessId); ?>">
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
            <h2 class="h5 mb-3">Monthly Summary</h2>
            <div class="vstack gap-2">
                <div class="d-flex justify-content-between"><span>Due Before Bonus</span><strong><?php echo money($details['summary']['due_before_bonus']); ?></strong></div>
                <div class="d-flex justify-content-between"><span>Deposit by 15th</span><strong><?php echo money($details['summary']['deposit_by_15th']); ?></strong></div>
                <div class="d-flex justify-content-between"><span>Discount</span><strong><?php echo money($details['summary']['discount']); ?></strong></div>
            </div>
        </div>
        <div class="panel-card">
            <h2 class="h5 mb-3">Cost Rows</h2>
            <ul class="list-group list-group-flush">
                <?php foreach ($details['costs'] as $row): ?>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span><?php echo h($row['type']); ?></span>
                        <strong><?php echo money($row['amount']); ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<div class="modal fade" id="depositEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Deposit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?php echo h(app_url('/actions/save_manual_entry.php')); ?>" id="depositEditForm">
                <div class="modal-body row g-3">
                    <input type="hidden" name="entry_type" value="deposit_update">
                    <input type="hidden" name="deposit_id" id="editDepositId">
                    <input type="hidden" name="business_id" id="editBusinessId">
                    <input type="hidden" name="redirect_target" value="business_details">
                    <input type="hidden" name="redirect_business_id" value="<?php echo h((string) $businessId); ?>">
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
    type: 'business',
    title: 'Business Bill Report - <?php echo h($details['business']['name']); ?> - <?php echo h($details['month_label']); ?>',
    month: '<?php echo h($month); ?>',
    businessName: '<?php echo h($details['business']['name']); ?>',
    fileName: 'business-report-<?php echo h($businessId . '-' . $month); ?>.pdf'
};
</script>
<script src="<?php echo h(app_url('/assets/js/app.js')); ?>"></script>
<?php require __DIR__ . '/../views/layout/footer.php'; ?>
